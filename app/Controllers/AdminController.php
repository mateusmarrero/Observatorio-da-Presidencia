<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\Database;
use App\Services\AuthService;
use App\Services\ImportService;
use App\Services\RadarService;
use App\Helpers\SecurityHelper;
use PDO;

class AdminController extends Controller {
    private AuthService $auth;
    private PDO $pdo;

    public function __construct() {
        $this->auth = new AuthService();
        $this->pdo = Database::getConnection();
    }

    public function login(): void {
        if (AuthService::check()) {
            $this->redirect('/admin');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!SecurityHelper::verifyCsrfToken($token)) {
                $this->render('admin/login', [
                    'title' => 'Login Administrativo',
                    'error' => 'Sessão expirada ou token de segurança inválido. Recarregue a página.',
                ], 'none');
                return;
            }

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $result = $this->auth->login($email, $password);
            if ($result['success']) {
                $this->redirect('/admin');
            } else {
                $this->render('admin/login', [
                    'title' => 'Login Administrativo',
                    'error' => $result['message'],
                    'oldEmail' => $email,
                ], 'none');
                return;
            }
        }

        $this->render('admin/login', [
            'title' => 'Login Administrativo — Observatório da Presidência',
            'error' => null,
            'oldEmail' => '',
        ], 'none');
    }

    public function logout(): void {
        $this->auth->logout();
        $this->redirect('/admin/login');
    }

    public function dashboard(): void {
        $user = AuthService::user();

        $totais = [
            'despesas' => (int)$this->pdo->query("SELECT COUNT(*) FROM despesas")->fetchColumn(),
            'viagens' => (int)$this->pdo->query("SELECT COUNT(*) FROM viagens")->fetchColumn(),
            'fornecedores' => (int)$this->pdo->query("SELECT COUNT(*) FROM fornecedores")->fetchColumn(),
            'alertas_pendentes' => (int)$this->pdo->query("SELECT COUNT(*) FROM alertas WHERE status_analise = 'PENDENTE'")->fetchColumn(),
            'investigacoes' => (int)$this->pdo->query("SELECT COUNT(*) FROM investigacoes WHERE status = 'ABERTA'")->fetchColumn(),
            'importacoes' => (int)$this->pdo->query("SELECT COUNT(*) FROM importacoes")->fetchColumn(),
        ];

        $ultimasImportacoes = $this->pdo->query("
            SELECT i.*, f.nome as fonte_nome
            FROM importacoes i
            LEFT JOIN fontes_dados f ON f.id = i.fonte_id
            ORDER BY i.iniciado_em DESC
            LIMIT 5
        ")->fetchAll();

        $ultimosAlertas = $this->pdo->query("
            SELECT a.*, d.valor_nominal
            FROM alertas a
            LEFT JOIN despesas d ON d.id = a.despesa_id
            WHERE a.status_analise = 'PENDENTE'
            ORDER BY a.created_at DESC
            LIMIT 5
        ")->fetchAll();

        $this->render('admin/dashboard', [
            'title' => 'Painel Administrativo',
            'user' => $user,
            'totais' => $totais,
            'ultimasImportacoes' => $ultimasImportacoes,
            'ultimosAlertas' => $ultimosAlertas,
        ], 'admin');
    }

    public function importacoes(): void {
        $user = AuthService::user();
        $fontes = $this->pdo->query("SELECT * FROM fontes_dados ORDER BY nome ASC")->fetchAll();
        $historico = $this->pdo->query("
            SELECT i.*, f.nome as fonte_nome, u.nome as usuario_nome
            FROM importacoes i
            LEFT JOIN fontes_dados f ON f.id = i.fonte_id
            LEFT JOIN usuarios u ON u.id = i.usuario_id
            ORDER BY i.iniciado_em DESC
            LIMIT 50
        ")->fetchAll();

        $this->render('admin/importacoes', [
            'title' => 'Gestão e Monitor de Importações',
            'user' => $user,
            'fontes' => $fontes,
            'historico' => $historico,
        ], 'admin');
    }

    public function uploadCsv(): void {
        if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Token CSRF inválido.');
            $this->redirect('/admin/importacoes');
        }

        $fonteId = (int)($_POST['fonte_id'] ?? 0);
        $tipo = $_POST['tipo_registro'] ?? 'CPGF';
        $user = AuthService::user();

        if (empty($_FILES['arquivo_csv']['tmp_name'])) {
            $this->setFlash('error', 'Nenhum arquivo enviado.');
            $this->redirect('/admin/importacoes');
        }

        $uploadDir = dirname(__DIR__, 2) . '/storage/imports';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'import_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['arquivo_csv']['name']);
        $destPath = "{$uploadDir}/{$filename}";

        if (!move_uploaded_file($_FILES['arquivo_csv']['tmp_name'], $destPath)) {
            $this->setFlash('error', 'Falha ao salvar arquivo no servidor.');
            $this->redirect('/admin/importacoes');
        }

        $importService = new ImportService();
        try {
            if ($tipo === 'VIAGENS') {
                $res = $importService->importViagensCsv($destPath, $fonteId, $user['id']);
            } else {
                $res = $importService->importCpgfCsv($destPath, $fonteId, $user['id']);
            }

            // Executar radar automaticamente após importação
            $radar = new RadarService();
            $radarRes = $radar->executarAuditoria();

            $msg = "Importação concluída: {$res['inseridos']} registros inseridos, {$res['duplicados']} duplicados ignorados. {$radarRes['alertas_novos']} novos alertas gerados no Radar.";
            $this->setFlash('success', $msg);
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Erro durante o processamento do CSV: ' . $e->getMessage());
        }

        $this->redirect('/admin/importacoes');
    }

    public function despesas(): void {
        $user = AuthService::user();
        $page = (int)($_GET['p'] ?? 1);
        $termo = $_GET['q'] ?? '';

        $despesasRepo = new \App\Repositories\DespesaRepository();
        $result = $despesasRepo->search(['termo' => $termo], $page, 30);
        $pessoas = $this->pdo->query("SELECT id, nome_publico FROM pessoas")->fetchAll();

        $this->render('admin/despesas', [
            'title' => 'Revisão Editorial de Despesas e Vínculos',
            'user' => $user,
            'result' => $result,
            'termo' => $termo,
            'pessoas' => $pessoas,
        ], 'admin');
    }

    public function salvarVinculo(): void {
        if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Token de segurança expirado.');
            $this->redirect('/admin/despesas');
        }

        $user = AuthService::user();
        $despesaId = (int)($_POST['despesa_id'] ?? 0);
        $pessoaId = !empty($_POST['pessoa_id']) ? (int)$_POST['pessoa_id'] : null;
        $tipoVinculo = $_POST['tipo_vinculo'] ?? 'NAO_IDENTIFICADO';
        $grauCerteza = $_POST['grau_certeza'] ?? 'EM_ANALISE';
        $justificativa = trim($_POST['justificativa'] ?? '');

        if (empty($justificativa)) {
            $this->setFlash('error', 'A justificativa editorial é obrigatória para alteração de vínculo.');
            $this->redirect('/admin/despesas');
        }

        // Atualizar ou inserir na tabela vinculos_despesas
        $stmt = $this->pdo->prepare("SELECT id, tipo_vinculo, pessoa_id FROM vinculos_despesas WHERE despesa_id = ?");
        $stmt->execute([$despesaId]);
        $existente = $stmt->fetch();

        if ($existente) {
            $up = $this->pdo->prepare("
                UPDATE vinculos_despesas
                SET pessoa_id = ?, tipo_vinculo = ?, grau_certeza = ?, justificativa = ?,
                    revisado_por_usuario_id = ?, revisado_em = ?
                WHERE id = ?
            ");
            $up->execute([$pessoaId, $tipoVinculo, $grauCerteza, $justificativa, $user['id'], date('Y-m-d H:i:s'), $existente['id']]);
        } else {
            $ins = $this->pdo->prepare("
                INSERT INTO vinculos_despesas (
                    despesa_id, pessoa_id, tipo_vinculo, grau_certeza, justificativa,
                    revisado_por_usuario_id, revisado_em, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([$despesaId, $pessoaId, $tipoVinculo, $grauCerteza, $justificativa, $user['id'], date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
        }

        // Registrar na trilha de auditoria
        $audit = $this->pdo->prepare("
            INSERT INTO historico_alteracoes (
                tabela_afetada, registro_id, acao, dados_anteriores_json, dados_novos_json, usuario_id, ip_origem
            ) VALUES ('vinculos_despesas', ?, 'REVISAO_VINCULO', ?, ?, ?, ?)
        ");
        $audit->execute([
            $despesaId,
            json_encode($existente),
            json_encode(['pessoa_id' => $pessoaId, 'tipo_vinculo' => $tipoVinculo, 'justificativa' => $justificativa]),
            $user['id'],
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        $this->setFlash('success', 'Vínculo da despesa atualizado e auditado com sucesso!');
        $this->redirect('/admin/despesas');
    }

    public function alertas(): void {
        $user = AuthService::user();
        $alertas = $this->pdo->query("
            SELECT a.*, d.valor_nominal, f.razao_social as fornecedor_nome
            FROM alertas a
            LEFT JOIN despesas d ON d.id = a.despesa_id
            LEFT JOIN fornecedores f ON f.id = a.fornecedor_id
            ORDER BY a.created_at DESC
        ")->fetchAll();

        $this->render('admin/alertas', [
            'title' => 'Gestão e Despacho de Alertas do Radar',
            'user' => $user,
            'alertas' => $alertas,
        ], 'admin');
    }

    public function atualizarAlerta(): void {
        if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Token CSRF inválido.');
            $this->redirect('/admin/alertas');
        }

        $id = (int)($_POST['alerta_id'] ?? 0);
        $status = $_POST['status_analise'] ?? 'PENDENTE';
        $notas = trim($_POST['notas_analista'] ?? '');
        $user = AuthService::user();

        $up = $this->pdo->prepare("
            UPDATE alertas
            SET status_analise = ?, notas_analista = ?, analisado_por_usuario_id = ?, updated_at = ?
            WHERE id = ?
        ");
        $up->execute([$status, $notas, $user['id'], date('Y-m-d H:i:s'), $id]);

        $this->setFlash('success', 'Status do alerta atualizado com sucesso.');
        $this->redirect('/admin/alertas');
    }

    public function investigacoes(): void {
        $user = AuthService::user();
        $investigacoes = $this->pdo->query("
            SELECT inv.*, u.nome as autor_nome
            FROM investigacoes inv
            LEFT JOIN usuarios u ON u.id = inv.criado_por_usuario_id
            ORDER BY inv.updated_at DESC
        ")->fetchAll();

        $this->render('admin/investigacoes', [
            'title' => 'Central de Investigação Documental',
            'user' => $user,
            'investigacoes' => $investigacoes,
        ], 'admin');
    }

    public function salvarInvestigacao(): void {
        if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Token CSRF inválido.');
            $this->redirect('/admin/investigacoes');
        }

        $user = AuthService::user();
        $titulo = trim($_POST['titulo'] ?? '');
        $fatos = trim($_POST['fatos_comprovados'] ?? '');
        $hipoteses = trim($_POST['hipoteses'] ?? '');
        $naoConfirmadas = trim($_POST['informacoes_nao_confirmadas'] ?? '');
        $pendencias = trim($_POST['pendencias'] ?? '');
        $conclusao = trim($_POST['conclusao'] ?? '');
        $status = $_POST['status'] ?? 'ABERTA';

        if (empty($titulo)) {
            $this->setFlash('error', 'O título da investigação é obrigatório.');
            $this->redirect('/admin/investigacoes');
        }

        $codigo = 'DOSSIE-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $ins = $this->pdo->prepare("
            INSERT INTO investigacoes (
                codigo_dossie, titulo, status, fatos_comprovados, hipoteses,
                informacoes_nao_confirmadas, pendencias, conclusao, criado_por_usuario_id, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([
            $codigo, $titulo, $status, $fatos, $hipoteses,
            $naoConfirmadas, $pendencias, $conclusao, $user['id'], date('Y-m-d H:i:s'), date('Y-m-d H:i:s')
        ]);

        $this->setFlash('success', "Dossiê investigativo {$codigo} aberto com sucesso.");
        $this->redirect('/admin/investigacoes');
    }

    public function usuarios(): void {
        $user = AuthService::user();
        $usuarios = $this->pdo->query("SELECT id, nome, email, perfil, ativo, ultimo_login, created_at FROM usuarios ORDER BY id ASC")->fetchAll();

        $this->render('admin/usuarios', [
            'title' => 'Gestão de Usuários e Permissões (RBAC)',
            'user' => $user,
            'usuarios' => $usuarios,
        ], 'admin');
    }

    public function auditoria(): void {
        $user = AuthService::user();
        $logs = $this->pdo->query("
            SELECT h.*, u.nome as usuario_nome
            FROM historico_alteracoes h
            LEFT JOIN usuarios u ON u.id = h.usuario_id
            ORDER BY h.created_at DESC
            LIMIT 100
        ")->fetchAll();

        $this->render('admin/auditoria', [
            'title' => 'Trilha de Auditoria e Logs Imutáveis',
            'user' => $user,
            'logs' => $logs,
        ], 'admin');
    }

    public function configuracoes(): void {
        $user = AuthService::user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('error', 'Token CSRF inválido.');
                $this->redirect('/admin/configuracoes');
            }

            foreach ($_POST['config'] ?? [] as $key => $val) {
                $up = $this->pdo->prepare("UPDATE configuracoes SET valor = ? WHERE chave = ?");
                $up->execute([trim($val), $key]);
            }

            $this->setFlash('success', 'Configurações salvas com sucesso.');
            $this->redirect('/admin/configuracoes');
        }

        $configs = $this->pdo->query("SELECT * FROM configuracoes ORDER BY grupo, chave")->fetchAll();

        $this->render('admin/configuracoes', [
            'title' => 'Configurações Globais do Portal',
            'user' => $user,
            'configs' => $configs,
        ], 'admin');
    }
}
