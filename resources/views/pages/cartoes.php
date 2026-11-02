<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
            Cartões de Pagamento do Governo Federal (CPGF)
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            Acompanhamento histórico dos gastos com cartões corporativos vinculados à Presidência da República, Casa Civil e Gabinete Presidencial.
        </p>
    </div>
    <div>
        <a href="/despesas/export?fase=CPGF" class="btn btn-secondary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Exportar Lançamentos CPGF</span>
        </a>
    </div>
</div>

<div class="alert-box alert-warning">
    <div>
        <strong>Nota de Transparência sobre o CPGF:</strong>
        O Cartão de Pagamento do Governo Federal (Decreto nº 5.355/2005) é utilizado para despesas operacionais de pronto pagamento, segurança e viagens presidenciais. Os dados desclassificados disponibilizados pela Casa Civil e pelo Portal da Transparência são importados sem qualquer alteração nominal de seus registros.
    </div>
</div>

<!-- Filtros CPGF -->
<form method="GET" action="/cartoes" class="filter-bar">
    <div class="filter-grid">
        <div class="form-group">
            <label class="form-label">Buscar Estabelecimento / Portador</label>
            <input type="text" name="q" class="form-input" placeholder="Nome, hotel, posto..." value="<?= SecurityHelper::escape($filters['termo']) ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Categoria</label>
            <select name="categoria" class="form-select">
                <option value="">Todas as Categorias</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($filters['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                        <?= SecurityHelper::escape($cat['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Data Início</label>
            <input type="date" name="data_inicio" class="form-input" value="<?= SecurityHelper::escape($filters['data_inicio']) ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Data Fim</label>
            <input type="date" name="data_fim" class="form-input" value="<?= SecurityHelper::escape($filters['data_fim']) ?>">
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Filtrar</button>
            <a href="/cartoes" class="btn btn-secondary">Limpar</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Transações CPGF Identificadas</h3>
            <div class="card-subtitle">Exibindo <?= count($result['items']) ?> de <?= number_format($result['total'], 0, ',', '.') ?> lançamentos com cartão</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data Transação</th>
                    <th>Portador Registrado</th>
                    <th>Estabelecimento Favorecido</th>
                    <th>Categoria</th>
                    <th>Vínculo Editorial</th>
                    <th style="text-align: right;">Valor Nominal</th>
                    <th style="text-align: right;">Corrigido IPCA</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['items'])): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            Nenhum registro de cartão CPGF encontrado com os filtros selecionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['items'] as $d): ?>
                        <tr>
                            <td><?= SecurityHelper::formatDate($d['data_despesa']) ?></td>
                            <td>
                                <strong><?= SecurityHelper::escape($d['portador_cartao'] ?? 'Presidência da República') ?></strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= SecurityHelper::escape($d['tipo_cartao'] ?? 'CPGF') ?></div>
                            </td>
                            <td>
                                <div><?= SecurityHelper::escape($d['fornecedor_nome'] ?? 'Não informado') ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;"><?= SecurityHelper::escape($d['fornecedor_documento']) ?></div>
                            </td>
                            <td>
                                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: <?= $d['categoria_cor'] ?? '#64748b' ?>; margin-right: 4px;"></span>
                                <?= SecurityHelper::escape($d['categoria_nome'] ?? 'Outros') ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace('_', '-', $d['tipo_vinculo'] ?? 'nao-identificado')) ?>">
                                    <?= SecurityHelper::escape($d['tipo_vinculo'] ?? 'NÃO IDENTIFICADO') ?>
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                <?= SecurityHelper::formatMoney($d['valor_nominal']) ?>
                            </td>
                            <td style="text-align: right; color: var(--accent); font-weight: 600;">
                                <?= SecurityHelper::formatMoney($d['valor_corrigido_ipca'] ?? $d['valor_nominal']) ?>
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
            <?php if ($result['current_page'] > 1): ?>
                <?php
                $pParams = $_GET;
                $pParams['p'] = $result['current_page'] - 1;
                ?>
                <a href="/cartoes?<?= http_build_query($pParams) ?>" class="page-btn">← Anterior</a>
            <?php endif; ?>

            <?php
            $startPage = max(1, $result['current_page'] - 2);
            $endPage = min($result['total_pages'], $result['current_page'] + 2);
            for ($i = $startPage; $i <= $endPage; $i++):
                $pParams = $_GET;
                $pParams['p'] = $i;
            ?>
                <a href="/cartoes?<?= http_build_query($pParams) ?>" class="page-btn <?= ($i === $result['current_page']) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($result['current_page'] < $result['total_pages']): ?>
                <?php
                $pParams = $_GET;
                $pParams['p'] = $result['current_page'] + 1;
                ?>
                <a href="/cartoes?<?= http_build_query($pParams) ?>" class="page-btn">Próxima →</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
