<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Visão Geral da Redação e Auditoria
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Bem-vindo, <strong><?= SecurityHelper::escape($user['nome']) ?></strong> (Perfil: <?= SecurityHelper::escape($user['perfil']) ?>). Acompanhe o fluxo de importações e despachos.
    </p>
</div>

<!-- Métricas Administrativas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Despesas no Banco</span>
            <div class="stat-icon">📄</div>
        </div>
        <div class="stat-value"><?= number_format($totais['despesas'], 0, ',', '.') ?></div>
        <div class="stat-subtext"><a href="/admin/despesas" style="color: var(--accent); text-decoration: none;">Revisar vínculos ↗</a></div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Alertas Pendentes</span>
            <div class="stat-icon">⚠️</div>
        </div>
        <div class="stat-value"><?= number_format($totais['alertas_pendentes'], 0, ',', '.') ?></div>
        <div class="stat-subtext"><a href="/admin/alertas" style="color: var(--accent); text-decoration: none;">Analisar no Radar ↗</a></div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Dossiês Investigativos</span>
            <div class="stat-icon">📁</div>
        </div>
        <div class="stat-value"><?= number_format($totais['investigacoes'], 0, ',', '.') ?></div>
        <div class="stat-subtext"><a href="/admin/investigacoes" style="color: var(--accent); text-decoration: none;">Central de Investigação ↗</a></div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Ciclos de Importação</span>
            <div class="stat-icon">🔄</div>
        </div>
        <div class="stat-value"><?= number_format($totais['importacoes'], 0, ',', '.') ?></div>
        <div class="stat-subtext"><a href="/admin/importacoes" style="color: var(--accent); text-decoration: none;">Histórico e sincronização ↗</a></div>
    </div>
</div>

<!-- Grid de Ações Rápidas e Alertas Recentes -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 1.5rem;">
    <!-- Últimos Alertas do Radar -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Alertas Recentes Pendentes de Análise</h3>
                <div class="card-subtitle">Necessitam de revisão técnica pelo analista</div>
            </div>
            <a href="/admin/alertas" class="btn btn-secondary btn-sm">Ver Todos</a>
        </div>
        <div class="card-body">
            <?php if (empty($ultimosAlertas)): ?>
                <p style="color: var(--text-muted); font-size: 0.88rem;">Nenhum alerta pendente no momento.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    <?php foreach ($ultimosAlertas as $a): ?>
                        <div style="padding: 0.85rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: var(--bg-body);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <span class="badge badge-<?= strtolower($a['nivel']) ?>"><?= $a['nivel'] ?></span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);"><?= SecurityHelper::formatDateTime($a['created_at']) ?></span>
                            </div>
                            <div style="font-weight: 600; font-size: 0.9rem; margin-bottom: 0.25rem;"><?= SecurityHelper::escape($a['tipo_alerta']) ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= SecurityHelper::escape($a['regra_acionada']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Últimas Importações -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Últimas Importações Executadas</h3>
                <div class="card-subtitle">Histórico de cargas de arquivos e APIs</div>
            </div>
            <a href="/admin/importacoes" class="btn btn-secondary btn-sm">Nova Carga</a>
        </div>
        <div class="card-body">
            <?php if (empty($ultimasImportacoes)): ?>
                <p style="color: var(--text-muted); font-size: 0.88rem;">Nenhuma importação registrada.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    <?php foreach ($ultimasImportacoes as $imp): ?>
                        <div style="padding: 0.85rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: var(--bg-body);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <strong style="font-size: 0.88rem;"><?= SecurityHelper::escape($imp['fonte_nome'] ?? 'Arquivo') ?></strong>
                                <span class="badge badge-<?= ($imp['status'] === 'CONCLUIDO') ? 'institucional' : 'atencao' ?>"><?= $imp['status'] ?></span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                <?= (int)$imp['total_inseridos'] ?> inseridos • <?= (int)$imp['total_duplicados'] ?> duplicados ignorados • <?= SecurityHelper::formatDateTime($imp['iniciado_em']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
