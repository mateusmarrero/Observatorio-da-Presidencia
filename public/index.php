<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Router;
use App\Controllers\PublicController;
use App\Controllers\AdminController;
use App\Middleware\AuthMiddleware;

$router = new Router();

// ==========================================
// ROTAS PÚBLICAS (22 PÁGINAS + EXPORT + API)
// ==========================================
$router->get('/', [PublicController::class, 'home']);
$router->get('/dashboard', [PublicController::class, 'dashboard']);
$router->get('/perfil/lula', [PublicController::class, 'perfilLula']);
$router->get('/perfil/janja', [PublicController::class, 'perfilJanja']);
$router->get('/despesas', [PublicController::class, 'despesas']);
$router->get('/cartoes', [PublicController::class, 'cartoes']);
$router->get('/viagens/nacionais', [PublicController::class, 'viagensNacionais']);
$router->get('/viagens/internacionais', [PublicController::class, 'viagensInternacionais']);
$router->get('/diarias-passagens', [PublicController::class, 'diariasPassagens']);
$router->get('/comitivas', [PublicController::class, 'comitivas']);
$router->get('/contratos', [PublicController::class, 'contratos']);
$router->get('/fornecedores', [PublicController::class, 'fornecedores']);
$router->get('/fornecedores/{id}', [PublicController::class, 'fornecedorDetalhe']);
$router->get('/documentos', [PublicController::class, 'documentos']);
$router->get('/radar', [PublicController::class, 'radar']);
$router->get('/historico-financeiro', [PublicController::class, 'historicoFinanceiro']);
$router->get('/comparacao-historica', [PublicController::class, 'comparacaoHistorica']);
$router->get('/metodologia', [PublicController::class, 'metodologia']);
$router->get('/fontes', [PublicController::class, 'fontes']);
$router->get('/sobre', [PublicController::class, 'sobre']);
$router->get('/privacidade', [PublicController::class, 'privacidade']);
$router->get('/termos', [PublicController::class, 'termos']);
$router->get('/contato', [PublicController::class, 'contato']);

// Endpoints auxiliares e exportação
$router->get('/despesas/export', [PublicController::class, 'exportDespesas']);
$router->get('/api/metrics', [PublicController::class, 'apiMetrics']);

// ==========================================
// ROTAS ADMINISTRATIVAS E CENTRAL DE ANÁLISE
// ==========================================
$router->get('/admin/login', [AdminController::class, 'login']);
$router->post('/admin/login', [AdminController::class, 'login']);
$router->get('/admin/logout', [AdminController::class, 'logout']);

// Rotas com autenticação obrigatória
$auth = fn() => AuthMiddleware::requireAuth(['admin', 'editor', 'analista']);
$adminOnly = fn() => AuthMiddleware::requireAuth(['admin']);

$router->get('/admin', [AdminController::class, 'dashboard'], [$auth]);
$router->get('/admin/importacoes', [AdminController::class, 'importacoes'], [$auth]);
$router->post('/admin/importacoes/upload', [AdminController::class, 'uploadCsv'], [$auth]);
$router->get('/admin/despesas', [AdminController::class, 'despesas'], [$auth]);
$router->post('/admin/despesas/vinculo', [AdminController::class, 'salvarVinculo'], [$auth]);
$router->get('/admin/alertas', [AdminController::class, 'alertas'], [$auth]);
$router->post('/admin/alertas/atualizar', [AdminController::class, 'atualizarAlerta'], [$auth]);
$router->get('/admin/investigacoes', [AdminController::class, 'investigacoes'], [$auth]);
$router->post('/admin/investigacoes/salvar', [AdminController::class, 'salvarInvestigacao'], [$auth]);
$router->get('/admin/usuarios', [AdminController::class, 'usuarios'], [$adminOnly]);
$router->get('/admin/auditoria', [AdminController::class, 'auditoria'], [$auth]);
$router->get('/admin/configuracoes', [AdminController::class, 'configuracoes'], [$adminOnly]);
$router->post('/admin/configuracoes', [AdminController::class, 'configuracoes'], [$adminOnly]);

// Despachar requisição
$router->dispatch();
