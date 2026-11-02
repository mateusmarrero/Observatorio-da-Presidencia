<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Repositories\Database;
use App\Helpers\SecurityHelper;
use App\Services\AuthService;
use App\Integrations\BcbClient;
use App\Repositories\DespesaRepository;
use App\Repositories\ViagemRepository;
use App\Services\RadarService;

echo "========================================================\n";
echo "   OBSERVATÓRIO DA PRESIDÊNCIA — BATERIA DE TESTES     \n";
echo "========================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest(string $desc, bool $condition): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "  [PASS] {$desc}\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] {$desc}\n";
        $testsFailed++;
    }
}

// 1. Teste de Banco de Dados
echo "1. BANCO DE DADOS & SCHEMA\n";
$pdo = Database::getConnection();
assertTest("Conexão com PDO estabelecida", $pdo instanceof PDO);
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
assertTest("Tabelas obrigatórias presentes (>= 18)", count($tables) >= 18);
assertTest("Tabela pessoas possui registros", (int)$pdo->query("SELECT COUNT(*) FROM pessoas")->fetchColumn() >= 2);
assertTest("Tabela mandatos possui registros", (int)$pdo->query("SELECT COUNT(*) FROM mandatos")->fetchColumn() >= 4);

// 2. Teste de Segurança & Helpers
echo "\n2. SEGURANÇA & SANITIZAÇÃO\n";
$csrf = SecurityHelper::generateCsrfToken();
assertTest("Geração de token CSRF", !empty($csrf) && strlen($csrf) === 64);
assertTest("Validação de CSRF positivo", SecurityHelper::verifyCsrfToken($csrf));
assertTest("Validação de CSRF rejeita token forjado", !SecurityHelper::verifyCsrfToken('invalido123'));
$escaped = SecurityHelper::escape("<script>alert('xss')</script>");
assertTest("Proteção XSS no escape", !str_contains($escaped, '<script>'));
assertTest("Formatação monetária brasileira", SecurityHelper::formatMoney(1500.5) === 'R$ 1.500,50');

// 3. Teste de Autenticação & RBAC
echo "\n3. AUTENTICAÇÃO & RBAC\n";
$auth = new AuthService();
$loginFalho = $auth->login('admin@observatorio.org', 'SenhaErrada123');
assertTest("Rejeição de senha inválida", $loginFalho['success'] === false);
$loginSucesso = $auth->login('admin@observatorio.org', 'Admin@2026!');
assertTest("Autenticação com credenciais válidas (Admin)", $loginSucesso['success'] === true);
assertTest("Verificação de sessão de usuário", AuthService::check());
assertTest("Verificação de Role (admin)", AuthService::hasRole('admin'));
$auth->logout();
assertTest("Logout encerra sessão", !AuthService::check());

// 4. Teste de Integração BCB IPCA
echo "\n4. INTEGRAÇÃO BANCO CENTRAL (IPCA)\n";
$bcb = new BcbClient();
$fator = $bcb->calcularFatorCorrecao('2003-01-01', '2026-09-01');
assertTest("Cálculo do fator acumulado IPCA > 1.0", $fator > 1.0);
echo "   Fator apurado: {$fator}x\n";

// 5. Teste de Repositório de Despesas & Paginação
echo "\n5. REPOSITÓRIO DE DESPESAS\n";
$despesasRepo = new DespesaRepository();
$res = $despesasRepo->search([], 1, 10);
assertTest("Busca de despesas retorna estrutura paginada", isset($res['items'], $res['total']));
assertTest("Despesas carregadas no banco", count($res['items']) > 0);
$metrics = $despesasRepo->getDashboardMetrics();
assertTest("Cálculo de métricas do dashboard", $metrics['totais']['total_despesas'] > 0);

// 6. Teste de Radar Investigativo
echo "\n6. RADAR INVESTIGATIVO\n";
$radar = new RadarService();
$radarRes = $radar->executarAuditoria();
assertTest("Execução do Radar Investigativo sem exceções", isset($radarRes['status']) && $radarRes['status'] === 'sucesso');
$alertasTotal = (int)$pdo->query("SELECT COUNT(*) FROM alertas")->fetchColumn();
assertTest("Alertas cadastrados no Radar", $alertasTotal > 0);

echo "\n========================================================\n";
echo "RESULTADO: {$testsPassed} testes passaram, {$testsFailed} falharam.\n";
echo "========================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
