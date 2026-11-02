<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Painel Administrativo', ENT_QUOTES, 'UTF-8') ?> — Observatório</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
<div class="app-container">
    <aside class="sidebar" id="app-sidebar">
        <div class="sidebar-header">
            <a href="/admin" class="brand-link">
                <div class="brand-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <span>Gestão Editorial</span>
                </div>
                <div class="brand-slogan">Painel Administrativo & Investigação</div>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-group-title">Administração</div>
            <a href="/admin" class="nav-item <?= ($_SERVER['REQUEST_URI'] === '/admin') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span>Visão Geral</span>
            </a>
            <a href="/admin/importacoes" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/importacoes') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Importações & Integrações</span>
            </a>
            <a href="/admin/despesas" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/despesas') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span>Revisão de Vínculos</span>
            </a>
            <a href="/admin/alertas" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/alertas') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>Despacho de Alertas</span>
            </a>
            <a href="/admin/investigacoes" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/investigacoes') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                <span>Central de Investigação</span>
            </a>

            <?php if (\App\Services\AuthService::hasRole(['admin'])): ?>
                <div class="nav-group-title">Controle & Segurança</div>
                <a href="/admin/usuarios" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/usuarios') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Usuários (RBAC)</span>
                </a>
                <a href="/admin/auditoria" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/auditoria') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <span>Trilha de Auditoria</span>
                </a>
                <a href="/admin/configuracoes" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/configuracoes') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>Configurações</span>
                </a>
            <?php endif; ?>

            <div class="nav-group-title">Navegação</div>
            <a href="/" target="_blank" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>Ver Portal Público ↗</span>
            </a>
            <a href="/admin/logout" class="nav-item" style="color: #f87171;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Sair do Sistema</span>
            </a>
        </nav>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="menu-toggle" id="menu-toggle">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="topbar-title">Painel de Controle — Observatório da Presidência</div>
            </div>
            <div class="topbar-right">
                <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">
                    👤 <?= htmlspecialchars($_SESSION['user_nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?>
                    <span class="badge badge-institucional"><?= strtoupper(htmlspecialchars($_SESSION['user_perfil'] ?? 'analista', ENT_QUOTES, 'UTF-8')) ?></span>
                </div>
                <button type="button" class="theme-toggle-btn" id="theme-toggle">
                    <span class="theme-icon">🌙</span>
                    <span class="theme-label">Tema</span>
                </button>
            </div>
        </header>

        <main class="content-body">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert-box alert-success alert-auto-dismiss">
                    <div><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert-box alert-error alert-auto-dismiss">
                    <div><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>
</body>
</html>
