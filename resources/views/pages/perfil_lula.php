<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: #2563eb; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; flex-shrink: 0;">
            L
        </div>
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
                <?= SecurityHelper::escape($pessoa['nome_completo']) ?>
            </h1>
            <p style="color: var(--text-muted); font-size: 1.05rem;">
                <?= SecurityHelper::escape($pessoa['cargo_papel']) ?> • Períodos de monitoramento: <strong>2003–2010</strong> e <strong>2023–atualidade</strong>
            </p>
        </div>
    </div>
</div>

<!-- Cards de Resumo Financeiro da Autoridade -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Despesas Vinculadas</span>
            <div class="stat-icon">📄</div>
        </div>
        <div class="stat-value"><?= number_format($estatisticas['total_despesas'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-subtext">Lançamentos documentados nominalmente</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Total Nominal Vinculado</span>
            <div class="stat-icon">💵</div>
        </div>
        <div class="stat-value"><?= SecurityHelper::formatMoney($estatisticas['total_nominal'] ?? 0) ?></div>
        <div class="stat-subtext">Valores da época dos mandatos</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Total Corrigido (IPCA)</span>
            <div class="stat-icon">📈</div>
        </div>
        <div class="stat-value"><?= SecurityHelper::formatMoney($estatisticas['total_corrigido'] ?? 0) ?></div>
        <div class="stat-subtext">Atualizado para valores de Set/2026</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Viagens em Missões</span>
            <div class="stat-icon">✈️</div>
        </div>
        <div class="stat-value"><?= count($viagens) ?></div>
        <div class="stat-subtext">Deslocamentos de Estado registrados</div>
    </div>
</div>

<!-- Alerta de Metodologia -->
<div class="alert-box alert-info" style="margin-bottom: 2rem;">
    <div>
        <strong>Critério Metodológico para a Autoridade:</strong>
        Estão computadas aqui exclusivamente as despesas com vinculação direta ou agenda oficial comprovada por documentos públicos. Custos de segurança preventiva do GSI, manutenção de palácios presidenciais e apoio administrativo geral permanecem na categoria <em>Institucional</em> e não compõem a conta pessoal da autoridade.
    </div>
</div>

<!-- Linha do Tempo dos Mandatos -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Linha do Tempo Institucional dos Mandatos</h3>
    </div>
    <div class="card-body">
        <div class="timeline">
            <?php foreach ($mandatos as $m): ?>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-title"><?= SecurityHelper::escape($m['titulo']) ?></div>
                    <div class="timeline-date">
                        <?= SecurityHelper::formatDate($m['data_inicio']) ?> até <?= $m['data_fim'] ? SecurityHelper::formatDate($m['data_fim']) : 'Em exercício' ?>
                    </div>
                    <div class="timeline-content">
                        <p style="font-size: 0.9rem; color: var(--text-muted);"><?= SecurityHelper::escape($m['descricao']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Despesas Documentadas -->
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Despesas com Vínculo Documentado</h3>
            <div class="card-subtitle">Exibindo registros diretamente atribuídos ou ligados a agendas oficiais</div>
        </div>
        <a href="/despesas?pessoa_id=<?= $pessoa['id'] ?>" class="btn btn-secondary btn-sm">Ver Todas</a>
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
                            Nenhum registro específico vinculado no momento. Os dados gerais estão catalogados na aba Despesas Públicas.
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
