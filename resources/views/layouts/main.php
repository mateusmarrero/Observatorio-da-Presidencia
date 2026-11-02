<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Observatório da Presidência', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="Portal independente de transparência pública para acompanhamento histórico de despesas, viagens, cartões e contratos da Presidência da República.">
    
    <!-- Open Graph / SEO -->
    <meta property="og:title" content="<?= htmlspecialchars($title ?? 'Observatório da Presidência', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="O dinheiro é público. A informação também deve ser. Dados oficiais governamentais analisados com rigor jornalístico.">
    <meta property="og:type" content="website">
    
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Chart.js Oficial CDN (Sem dependência de Node.js em produção) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
<div class="app-container">
    <!-- Menu Lateral (Sidebar Desktop & Drawer Mobile) -->
    <aside class="sidebar" id="app-sidebar" aria-label="Menu Principal de Navegação">
        <div class="sidebar-header">
            <a href="/" class="brand-link">
                <div class="brand-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <circle cx="12" cy="11" r="3"/>
                    </svg>
                    <span>Observatório</span>
                </div>
                <div class="brand-slogan">O dinheiro é público.<br>A informação também deve ser.</div>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-group-title">Visão Geral</div>
            <a href="/" class="nav-item <?= ($_SERVER['REQUEST_URI'] === '/') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                <span>Início</span>
            </a>
            <a href="/dashboard" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/dashboard') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span>Dashboard Geral</span>
            </a>

            <div class="nav-group-title">Perfis Monitorados</div>
            <a href="/perfil/lula" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/perfil/lula') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Luiz Inácio Lula da Silva</span>
            </a>
            <a href="/perfil/janja" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/perfil/janja') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                <span>Rosângela da Silva (Janja)</span>
            </a>

            <div class="nav-group-title">Finanças & Cartões</div>
            <a href="/despesas" class="nav-item <?= ($_SERVER['REQUEST_URI'] === '/despesas') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>Despesas Públicas</span>
            </a>
            <a href="/cartoes" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/cartoes') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                <span>Cartões de Pagamento (CPGF)</span>
            </a>
            <a href="/historico-financeiro" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/historico-financeiro') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <span>Histórico Financeiro</span>
            </a>
            <a href="/comparacao-historica" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/comparacao-historica') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
                <span>Comparação Histórica</span>
            </a>

            <div class="nav-group-title">Viagens & Comitivas</div>
            <a href="/viagens/nacionais" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/viagens/nacionais') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="12 6 12 12 16 14"/></svg>
                <span>Viagens Nacionais</span>
            </a>
            <a href="/viagens/internacionais" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/viagens/internacionais') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                <span>Viagens Internacionais</span>
            </a>
            <a href="/diarias-passagens" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/diarias-passagens') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <span>Diárias e Passagens</span>
            </a>
            <a href="/comitivas" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/comitivas') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Comitivas e Delegações</span>
            </a>

            <div class="nav-group-title">Contratos & Fornecedores</div>
            <a href="/contratos" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/contratos') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>Contratos</span>
            </a>
            <a href="/fornecedores" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/fornecedores') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                <span>Fornecedores</span>
            </a>

            <div class="nav-group-title">Investigação & Documentos</div>
            <a href="/radar" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/radar') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                <span>Radar Investigativo</span>
            </a>
            <a href="/documentos" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/documentos') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                <span>Documentos Oficiais</span>
            </a>

            <div class="nav-group-title">Transparência & Metodologia</div>
            <a href="/metodologia" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/metodologia') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span>Metodologia Editorial</span>
            </a>
            <a href="/fontes" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/fontes') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Fontes Governamentais</span>
            </a>
            <a href="/sobre" class="nav-item <?= str_starts_with($_SERVER['REQUEST_URI'], '/sobre') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Sobre o Projeto</span>
            </a>
        </nav>
    </aside>

    <!-- Estrutura Principal -->
    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="menu-toggle" id="menu-toggle" aria-label="Abrir ou fechar menu de navegação">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="topbar-title">Observatório da Presidência</div>
            </div>
            <div class="topbar-right">
                <button type="button" class="theme-toggle-btn" id="theme-toggle" aria-label="Alternar tema de cores">
                    <span class="theme-icon">🌙</span>
                    <span class="theme-label">Tema Escuro</span>
                </button>
                <a href="/admin" class="btn btn-secondary btn-sm" title="Acesso Administrativo">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span>Área Restrita</span>
                </a>
            </div>
        </header>

        <!-- Mensagens Flash -->
        <main class="content-body">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert-box alert-success alert-auto-dismiss" role="alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <div><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert-box alert-error alert-auto-dismiss" role="alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <!-- Slot de Anúncio Superior (Compatível com Google AdSense, Não Intrusivo) -->
            <div class="ad-slot" aria-label="Espaço Publicitário Institucional">
                <div style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Publicidade Institucional</div>
                <div>Espaço reservado para veiculação de anúncios compatíveis com as políticas editoriais e AdSense.</div>
            </div>

            <!-- Conteúdo da Página -->
            <?= $content ?>
        </main>

        <!-- Rodapé Institucional -->
        <footer class="site-footer">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>Observatório da Presidência</h4>
                    <p style="margin-bottom: 0.75rem;">Portal independente de jornalismo de dados e fiscalização contábil da Presidência da República do Brasil.</p>
                    <p><em>O dinheiro é público. A informação também deve ser.</em></p>
                </div>
                <div class="footer-col">
                    <h4>Navegação</h4>
                    <ul>
                        <li><a href="/dashboard">Dashboard Financeiro</a></li>
                        <li><a href="/perfil/lula">Perfil Luiz Inácio Lula da Silva</a></li>
                        <li><a href="/perfil/janja">Perfil Rosângela da Silva (Janja)</a></li>
                        <li><a href="/despesas">Despesas e Pagamentos</a></li>
                        <li><a href="/cartoes">Cartões Corporativos CPGF</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Fiscalização</h4>
                    <ul>
                        <li><a href="/radar">Radar Investigativo</a></li>
                        <li><a href="/documentos">Biblioteca de Documentos</a></li>
                        <li><a href="/comparacao-historica">Comparação de Mandatos</a></li>
                        <li><a href="/fornecedores">Fornecedores Contratados</a></li>
                        <li><a href="/comitivas">Comitivas e Delegações</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Institucional</h4>
                    <ul>
                        <li><a href="/metodologia">Metodologia Editorial</a></li>
                        <li><a href="/fontes">Fontes Oficiais</a></li>
                        <li><a href="/sobre">Sobre o Observatório</a></li>
                        <li><a href="/privacidade">Privacidade e LGPD</a></li>
                        <li><a href="/termos">Termos de Uso</a></li>
                        <li><a href="/contato">Correção de Informações</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div>© <?= date('Y') ?> Observatório da Presidência. Dados públicos primários protegidos pela Lei de Acesso à Informação (Lei 12.527/2011).</div>
                <div>Padrão de Acessibilidade WCAG 2.2 AA • PHP 8.3 • MySQL 8</div>
            </div>
        </footer>
    </div>
</div>
</body>
</html>
