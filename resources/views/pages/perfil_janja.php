<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: #9333ea; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; flex-shrink: 0;">
            J
        </div>
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
                <?= SecurityHelper::escape($pessoa['nome_completo']) ?>
            </h1>
            <p style="color: var(--text-muted); font-size: 1.05rem;">
                <?= SecurityHelper::escape($pessoa['cargo_papel']) ?> • Período de acompanhamento: <strong>2023 até o presente</strong>
            </p>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Despesas Identificadas</span>
            <div class="stat-icon">📄</div>
        </div>
        <div class="stat-value"><?= number_format($estatisticas['total_despesas'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-subtext">Registros com menção nominal na fonte</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Total Nominal Registrado</span>
            <div class="stat-icon">💵</div>
        </div>
        <div class="stat-value"><?= SecurityHelper::formatMoney($estatisticas['total_nominal'] ?? 0) ?></div>
        <div class="stat-subtext">Valores oficiais declarados</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Total Corrigido (IPCA)</span>
            <div class="stat-icon">📈</div>
        </div>
        <div class="stat-value"><?= SecurityHelper::formatMoney($estatisticas['total_corrigido'] ?? 0) ?></div>
        <div class="stat-subtext">Atualizado para base Set/2026</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Viagens em Delegação</span>
            <div class="stat-icon">✈️</div>
        </div>
        <div class="stat-value"><?= count($viagens) ?></div>
        <div class="stat-subtext">Missões oficiais e agendas de Estado</div>
    </div>
</div>

<div class="alert-box alert-info" style="margin-bottom: 2rem;">
    <div>
        <strong>Metodologia Aplicada à Primeira-Dama:</strong>
        A legislação brasileira não confere à Primeira-Dama a condição de servidora pública com remuneração ou dotação orçamentária própria. Assim, suas viagens e participações em eventos oficiais decorrem de representação institucional da Presidência da República. Custos de transporte em aeronave presidencial pertencem à Força Aérea Brasileira (FAB) ou Casa Civil, sendo registrados como comitiva de Estado quando formalizados.
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Atividades Públicas e Representação Oficial</h3>
    </div>
    <div class="card-body">
        <div class="timeline">
            <?php foreach ($mandatos as $m): ?>
                <div class="timeline-item">
                    <div class="timeline-marker" style="background: #9333ea; box-shadow: 0 0 0 2px #9333ea;"></div>
                    <div class="timeline-title"><?= SecurityHelper::escape($m['titulo']) ?></div>
                    <div class="timeline-date">
                        Desde <?= SecurityHelper::formatDate($m['data_inicio']) ?> (Em exercício)
                    </div>
                    <div class="timeline-content">
                        <p style="font-size: 0.9rem; color: var(--text-muted);"><?= SecurityHelper::escape($m['descricao']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Despesas e Participações Oficiais Documentadas</h3>
            <div class="card-subtitle">Registros onde há comprovação oficial de participação individual ou agenda</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Favorecido / Razão Social</th>
                    <th>Categoria</th>
                    <th>Tipo de Vínculo</th>
                    <th>Grau de Certeza</th>
                    <th style="text-align: right;">Valor Nominal</th>
                    <th style="text-align: right;">Corrigido IPCA</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($despesas)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            Nenhum registro com menção direta à pessoa de Janja identificado nas bases abertas. Despesas de comitiva geral são lançadas em Viagens e Comitivas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($despesas as $d): ?>
                        <tr>
                            <td><?= SecurityHelper::formatDate($d['data_despesa']) ?></td>
                            <td><?= SecurityHelper::escape($d['fornecedor_nome'] ?? 'Não informado') ?></td>
                            <td><?= SecurityHelper::escape($d['categoria_nome'] ?? 'Geral') ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace('_', '-', $d['tipo_vinculo'] ?? 'nao-identificado')) ?>">
                                    <?= SecurityHelper::escape($d['tipo_vinculo'] ?? 'NÃO IDENTIFICADO') ?>
                                </span>
                            </td>
                            <td><span style="font-size: 0.8rem; color: var(--text-muted);"><?= SecurityHelper::escape($d['grau_certeza']) ?></span></td>
                            <td style="text-align: right; font-weight: 600;"><?= SecurityHelper::formatMoney($d['valor_nominal']) ?></td>
                            <td style="text-align: right; color: var(--accent); font-weight: 600;"><?= SecurityHelper::formatMoney($d['valor_corrigido_ipca'] ?? $d['valor_nominal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
