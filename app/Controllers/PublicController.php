<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\Database;
use App\Repositories\DespesaRepository;
use App\Repositories\ViagemRepository;
use App\Repositories\FornecedorRepository;
use App\Repositories\ContratoRepository;
use App\Repositories\DocumentoRepository;
use App\Integrations\BcbClient;
use PDO;

class PublicController extends Controller {
    private DespesaRepository $despesasRepo;
    private ViagemRepository $viagensRepo;
    private FornecedorRepository $fornecedoresRepo;
    private ContratoRepository $contratosRepo;
    private DocumentoRepository $documentosRepo;

    public function __construct() {
        $this->despesasRepo = new DespesaRepository();
        $this->viagensRepo = new ViagemRepository();
        $this->fornecedoresRepo = new FornecedorRepository();
        $this->contratosRepo = new ContratoRepository();
        $this->documentosRepo = new DocumentoRepository();
    }

    public function home(): void {
        $metrics = $this->despesasRepo->getDashboardMetrics();
        $ultimasDespesas = $this->despesasRepo->search([], 1, 6)['items'];
        $ultimasViagens = $this->viagensRepo->search([], 1, 4)['items'];
        $ultimosAlertas = $this->getUltimosAlertas(4);

        $this->render('pages/home', [
            'title' => 'Início — Observatório da Presidência',
            'metrics' => $metrics,
            'ultimasDespesas' => $ultimasDespesas,
            'ultimasViagens' => $ultimasViagens,
            'ultimosAlertas' => $ultimosAlertas,
        ]);
    }

    public function dashboard(): void {
        $metrics = $this->despesasRepo->getDashboardMetrics();
        $this->render('pages/dashboard', [
            'title' => 'Dashboard Financeiro e Executivo',
            'metrics' => $metrics,
        ]);
    }

    public function perfilLula(): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM pessoas WHERE slug = 'lula' LIMIT 1");
        $stmt->execute();
        $pessoa = $stmt->fetch();

        $mStmt = $pdo->prepare("SELECT * FROM mandatos WHERE pessoa_id = ? ORDER BY data_inicio ASC");
        $mStmt->execute([$pessoa['id']]);
        $mandatos = $mStmt->fetchAll();

        // Despesas vinculadas diretamente ou por agenda
        $despesasResult = $this->despesasRepo->search(['pessoa_id' => $pessoa['id']], 1, 15);
        $viagensResult = $this->viagensRepo->search(['pessoa_id' => $pessoa['id']], 1, 10);

        // Agregações financeiras de Lula
        $totais = $pdo->prepare("
            SELECT
                COUNT(d.id) as total_despesas,
                COALESCE(SUM(d.valor_nominal), 0) as total_nominal,
                COALESCE(SUM(d.valor_corrigido_ipca), 0) as total_corrigido
            FROM vinculos_despesas v
            JOIN despesas d ON d.id = v.despesa_id
            WHERE v.pessoa_id = ?
        ");
        $totais->execute([$pessoa['id']]);
        $estatisticas = $totais->fetch();

        $this->render('pages/perfil_lula', [
            'title' => 'Perfil Institucional e Histórico — Luiz Inácio Lula da Silva',
            'pessoa' => $pessoa,
            'mandatos' => $mandatos,
            'despesas' => $despesasResult['items'],
            'viagens' => $viagensResult['items'],
            'estatisticas' => $estatisticas,
        ]);
    }

    public function perfilJanja(): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM pessoas WHERE slug = 'janja' LIMIT 1");
        $stmt->execute();
        $pessoa = $stmt->fetch();

        $mStmt = $pdo->prepare("SELECT * FROM mandatos WHERE pessoa_id = ? ORDER BY data_inicio ASC");
        $mStmt->execute([$pessoa['id']]);
        $mandatos = $mStmt->fetchAll();

        $despesasResult = $this->despesasRepo->search(['pessoa_id' => $pessoa['id']], 1, 15);
        $viagensResult = $this->viagensRepo->search(['pessoa_id' => $pessoa['id']], 1, 10);

        $totais = $pdo->prepare("
            SELECT
                COUNT(d.id) as total_despesas,
                COALESCE(SUM(d.valor_nominal), 0) as total_nominal,
                COALESCE(SUM(d.valor_corrigido_ipca), 0) as total_corrigido
            FROM vinculos_despesas v
            JOIN despesas d ON d.id = v.despesa_id
            WHERE v.pessoa_id = ?
        ");
        $totais->execute([$pessoa['id']]);
        $estatisticas = $totais->fetch();

        $this->render('pages/perfil_janja', [
            'title' => 'Atividades Públicas e Representação — Rosângela da Silva (Janja)',
            'pessoa' => $pessoa,
            'mandatos' => $mandatos,
            'despesas' => $despesasResult['items'],
            'viagens' => $viagensResult['items'],
            'estatisticas' => $estatisticas,
        ]);
    }

    public function despesas(): void {
        $page = (int)($_GET['p'] ?? 1);
        $filters = [
            'termo' => $_GET['q'] ?? null,
            'tipo_vinculo' => $_GET['vinculo'] ?? null,
            'categoria_id' => $_GET['categoria'] ?? null,
            'data_inicio' => $_GET['data_inicio'] ?? null,
            'data_fim' => $_GET['data_fim'] ?? null,
            'fase_contabil' => $_GET['fase'] ?? null,
        ];

        $result = $this->despesasRepo->search($filters, $page, 25);
        $categorias = $this->getCategoriasList();

        $this->render('pages/despesas', [
            'title' => 'Despesas Públicas e Execução Orçamentária',
            'result' => $result,
            'filters' => $filters,
            'categorias' => $categorias,
        ]);
    }

    public function cartoes(): void {
        $page = (int)($_GET['p'] ?? 1);
        $filters = [
            'termo' => $_GET['q'] ?? null,
            'fase_contabil' => 'CPGF',
            'categoria_id' => $_GET['categoria'] ?? null,
            'data_inicio' => $_GET['data_inicio'] ?? null,
            'data_fim' => $_GET['data_fim'] ?? null,
        ];

        $result = $this->despesasRepo->search($filters, $page, 25);
        $categorias = $this->getCategoriasList();

        $this->render('pages/cartoes', [
            'title' => 'Cartões de Pagamento do Governo Federal (CPGF)',
            'result' => $result,
            'filters' => $filters,
            'categorias' => $categorias,
        ]);
    }

    public function viagensNacionais(): void {
        $page = (int)($_GET['p'] ?? 1);
        $filters = [
            'termo' => $_GET['q'] ?? null,
            'tipo_viagem' => 'NACIONAL',
            'ano' => $_GET['ano'] ?? null,
        ];

        $result = $this->viagensRepo->search($filters, $page, 20);

        $this->render('pages/viagens', [
            'title' => 'Viagens Oficiais Nacionais',
            'subtitulo' => 'Deslocamentos institucionais pelo território brasileiro',
            'tipo' => 'NACIONAL',
            'result' => $result,
            'filters' => $filters,
        ]);
    }

    public function viagensInternacionais(): void {
        $page = (int)($_GET['p'] ?? 1);
        $filters = [
            'termo' => $_GET['q'] ?? null,
            'tipo_viagem' => 'INTERNACIONAL',
            'ano' => $_GET['ano'] ?? null,
        ];

        $result = $this->viagensRepo->search($filters, $page, 20);

        $this->render('pages/viagens', [
            'title' => 'Viagens Oficiais Internacionais',
            'subtitulo' => 'Missões diplomáticas e cúpulas de Estado no exterior',
            'tipo' => 'INTERNACIONAL',
            'result' => $result,
            'filters' => $filters,
        ]);
    }

    public function diariasPassagens(): void {
        $page = (int)($_GET['p'] ?? 1);
        $filters = ['termo' => $_GET['q'] ?? null];
        $result = $this->viagensRepo->search($filters, $page, 20);

        $this->render('pages/diarias_passagens', [
            'title' => 'Diárias e Passagens Governamentais',
            'result' => $result,
            'filters' => $filters,
        ]);
    }

    public function comitivas(): void {
        $page = (int)($_GET['p'] ?? 1);
        $result = $this->viagensRepo->getComitivas($page, 25);

        $this->render('pages/comitivas', [
            'title' => 'Comitivas e Delegações Oficiais',
            'result' => $result,
        ]);
    }

    public function contratos(): void {
        $page = (int)($_GET['p'] ?? 1);
        $filters = [
            'termo' => $_GET['q'] ?? null,
            'situacao' => $_GET['situacao'] ?? null,
        ];
        $result = $this->contratosRepo->search($filters, $page, 20);

        $this->render('pages/contratos', [
            'title' => 'Contratos Administrativos e Licitações',
            'result' => $result,
            'filters' => $filters,
        ]);
    }

    public function fornecedores(): void {
        $page = (int)($_GET['p'] ?? 1);
        $filters = [
            'termo' => $_GET['q'] ?? null,
            'uf' => $_GET['uf'] ?? null,
        ];
        $result = $this->fornecedoresRepo->search($filters, $page, 25);

        $this->render('pages/fornecedores', [
            'title' => 'Fornecedores e Prestadores de Serviço',
            'result' => $result,
            'filters' => $filters,
        ]);
    }

    public function fornecedorDetalhe(array $params): void {
        $id = (int)($params['id'] ?? 0);
        $fornecedor = $this->fornecedoresRepo->findById($id);

        if (!$fornecedor) {
            http_response_code(404);
            $this->render('pages/404', ['title' => 'Fornecedor Não Encontrado']);
            return;
        }

        $this->render('pages/fornecedor_detalhe', [
            'title' => "Fornecedor: {$fornecedor['razao_social']}",
            'fornecedor' => $fornecedor,
        ]);
    }

    public function documentos(): void {
        $page = (int)($_GET['p'] ?? 1);
        $filters = [
            'termo' => $_GET['q'] ?? null,
            'tipo' => $_GET['tipo'] ?? null,
        ];
        $result = $this->documentosRepo->search($filters, $page, 20);

        $this->render('pages/documentos', [
            'title' => 'Biblioteca Documental Oficial',
            'result' => $result,
            'filters' => $filters,
        ]);
    }

    public function radar(): void {
        $pdo = Database::getConnection();
        $nivel = $_GET['nivel'] ?? null;
        $status = $_GET['status'] ?? null;

        $where = [];
        $params = [];
        if ($nivel) {
            $where[] = "a.nivel = ?";
            $params[] = $nivel;
        }
        if ($status) {
            $where[] = "a.status_analise = ?";
            $params[] = $status;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $pdo->prepare("
            SELECT
                a.*,
                d.valor_nominal as despesa_valor, d.data_despesa, d.descricao_original,
                f.razao_social as fornecedor_nome,
                v.destino_cidade as viagem_destino, v.data_inicio as viagem_data
            FROM alertas a
            LEFT JOIN despesas d ON d.id = a.despesa_id
            LEFT JOIN fornecedores f ON f.id = a.fornecedor_id
            LEFT JOIN viagens v ON v.id = a.viagem_id
            {$whereClause}
            ORDER BY
                CASE a.nivel
                    WHEN 'PRIORITARIO' THEN 1
                    WHEN 'ATENCAO' THEN 2
                    ELSE 3
                END ASC,
                a.created_at DESC
        ");
        $stmt->execute($params);
        $alertas = $stmt->fetchAll();

        $this->render('pages/radar', [
            'title' => 'Radar Investigativo — Detecção de Anomalias',
            'alertas' => $alertas,
            'filtroNivel' => $nivel,
            'filtroStatus' => $status,
        ]);
    }

    public function historicoFinanceiro(): void {
        $metrics = $this->despesasRepo->getDashboardMetrics();
        $this->render('pages/historico_financeiro', [
            'title' => 'Histórico Financeiro e Execução Contábil',
            'metrics' => $metrics,
        ]);
    }

    public function comparacaoHistorica(): void {
        $pdo = Database::getConnection();
        // Agrupamento por mandatos presidenciais com correção IPCA
        $comparativo = $pdo->query("
            SELECT
                CASE
                    WHEN d.data_despesa BETWEEN '2003-01-01' AND '2006-12-31' THEN 'Lula 1º Mandato (2003-2006)'
                    WHEN d.data_despesa BETWEEN '2007-01-01' AND '2010-12-31' THEN 'Lula 2º Mandato (2007-2010)'
                    WHEN d.data_despesa >= '2023-01-01' THEN 'Lula 3º Mandato (2023-atualidade)'
                    ELSE 'Outros Períodos'
                END as periodo,
                COUNT(d.id) as total_transacoes,
                COALESCE(SUM(d.valor_nominal), 0) as total_nominal,
                COALESCE(SUM(d.valor_corrigido_ipca), 0) as total_corrigido_ipca
            FROM despesas d
            GROUP BY periodo
            ORDER BY periodo ASC
        ")->fetchAll();

        $this->render('pages/comparacao_historica', [
            'title' => 'Comparação Histórica entre Mandatos',
            'comparativo' => $comparativo,
        ]);
    }

    public function metodologia(): void {
        $this->render('pages/metodologia', [
            'title' => 'Metodologia Editorial e Critérios Técnicos',
        ]);
    }

    public function fontes(): void {
        $pdo = Database::getConnection();
        $fontes = $pdo->query("SELECT * FROM fontes_dados ORDER BY id ASC")->fetchAll();
        $this->render('pages/fontes', [
            'title' => 'Fontes Oficiais Governamentais de Dados',
            'fontes' => $fontes,
        ]);
    }

    public function sobre(): void {
        $this->render('pages/sobre', [
            'title' => 'Sobre o Projeto — Observatório da Presidência',
        ]);
    }

    public function privacidade(): void {
        $this->render('pages/privacidade', [
            'title' => 'Política de Privacidade e Proteção de Dados (LGPD)',
        ]);
    }

    public function termos(): void {
        $this->render('pages/termos', [
            'title' => 'Termos de Uso e Licenciamento Aberto',
        ]);
    }

    public function contato(): void {
        $this->render('pages/contato', [
            'title' => 'Contato e Solicitação de Correção de Informações',
        ]);
    }

    public function apiMetrics(): void {
        $metrics = $this->despesasRepo->getDashboardMetrics();
        $this->json($metrics);
    }

    public function exportDespesas(): void {
        $filters = [
            'termo' => $_GET['q'] ?? null,
            'tipo_vinculo' => $_GET['vinculo'] ?? null,
            'categoria_id' => $_GET['categoria'] ?? null,
            'data_inicio' => $_GET['data_inicio'] ?? null,
            'data_fim' => $_GET['data_fim'] ?? null,
            'fase_contabil' => $_GET['fase'] ?? null,
        ];
        $this->despesasRepo->exportCsv($filters);
    }

    private function getCategoriasList(): array {
        $pdo = Database::getConnection();
        return $pdo->query("SELECT id, nome, cor FROM categorias_despesas ORDER BY nome ASC")->fetchAll();
    }

    private function getUltimosAlertas(int $limit = 4): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, d.valor_nominal as despesa_valor
            FROM alertas a
            LEFT JOIN despesas d ON d.id = a.despesa_id
            ORDER BY a.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
