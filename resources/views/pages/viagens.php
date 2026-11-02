<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        <?= htmlspecialchars($title) ?>
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        <?= htmlspecialchars($subtitulo) ?>
    </p>
</div>

<!-- Filtros de Viagens -->
<form method="GET" action="<?= ($_SERVER['REQUEST_URI'] ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '') ?>" class="filter-bar">
    <div class="filter-grid">
        <div class="form-group">
            <label class="form-label">Buscar Destino / Motivo / Processo</label>
            <input type="text" name="q" class="form-input" placeholder="Cidade, país, cúpula..." value="<?= SecurityHelper::escape($filters['termo']) ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Ano</label>
            <select name="ano" class="form-select">
                <option value="">Todos os Anos</option>
                <?php for ($y = 2026; $y >= 2003; $y--): ?>
                    <option value="<?= $y ?>" <?= ($filters['ano'] == $y) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Filtrar</button>
            <a href="<?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?>" class="btn btn-secondary">Limpar</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Missões Registradas</h3>
            <div class="card-subtitle">Exibindo <?= count($result['items']) ?> de <?= number_format($result['total'], 0, ',', '.') ?> viagens</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Processo SCDP</th>
                    <th>Período</th>
                    <th>Origem → Destino</th>
                    <th>Motivo / Finalidade Oficial</th>
                    <th>Participantes</th>
                    <th style="text-align: right;">Total Diárias</th>
                    <th style="text-align: right;">Total Passagens</th>
                    <th style="text-align: right;">Custo Declarado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['items'])): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            Nenhuma viagem localizada com os parâmetros informados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['items'] as $v): ?>
                        <tr>
                            <td><code><?= SecurityHelper::escape($v['identificador_origem']) ?></code></td>
                            <td>
                                <div><?= SecurityHelper::formatDate($v['data_inicio']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">até <?= SecurityHelper::formatDate($v['data_fim']) ?></div>
                            </td>
                            <td>
                                <strong><?= SecurityHelper::escape($v['destino_cidade']) ?></strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= SecurityHelper::escape($v['destino_pais']) ?></div>
                            </td>
                            <td style="max-width: 320px;">
                                <div style="font-size: 0.85rem; line-height: 1.4;"><?= SecurityHelper::escape($v['motivo_viagem']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-institucional"><?= (int)$v['total_participantes'] ?> identificados</span>
                            </td>
                            <td style="text-align: right; font-weight: 500;">
                                <?= SecurityHelper::formatMoney($v['valor_total_diarias']) ?>
                            </td>
                            <td style="text-align: right; font-weight: 500;">
                                <?= SecurityHelper::formatMoney($v['valor_total_passagens']) ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: var(--accent);">
                                <?= SecurityHelper::formatMoney($v['valor_total_viagem']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Paginação -->
<?php if ($result['total_pages'] > 1): ?>
    <div class="pagination">
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Página <?= $result['current_page'] ?> de <?= $result['total_pages'] ?>
        </div>
        <div class="page-links">
            <?php
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $startPage = max(1, $result['current_page'] - 2);
            $endPage = min($result['total_pages'], $result['current_page'] + 2);
            for ($i = $startPage; $i <= $endPage; $i++):
                $pParams = $_GET;
                $pParams['p'] = $i;
            ?>
                <a href="<?= $uri ?>?<?= http_build_query($pParams) ?>" class="page-btn <?= ($i === $result['current_page']) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
<?php endif; ?>
