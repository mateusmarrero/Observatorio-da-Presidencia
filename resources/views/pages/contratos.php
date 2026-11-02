<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Contratos Administrativos e Licitações
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Contratos celebrados pela Presidência da República, Casa Civil e unidades gestoras vinculadas.
    </p>
</div>

<form method="GET" action="/contratos" class="filter-bar">
    <div class="filter-grid">
        <div class="form-group">
            <label class="form-label">Buscar Objeto / Contrato / Fornecedor</label>
            <input type="text" name="q" class="form-input" placeholder="Termo, número do contrato..." value="<?= SecurityHelper::escape($filters['termo']) ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Situação</label>
            <select name="situacao" class="form-select">
                <option value="">Todas</option>
                <option value="Ativo" <?= ($filters['situacao'] === 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                <option value="Encerrado" <?= ($filters['situacao'] === 'Encerrado') ? 'selected' : '' ?>>Encerrado</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Filtrar</button>
            <a href="/contratos" class="btn btn-secondary">Limpar</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Contratos Cadastrados</h3>
            <div class="card-subtitle">Exibindo <?= count($result['items']) ?> de <?= number_format($result['total'], 0, ',', '.') ?> contratos</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Número Contrato</th>
                    <th>Fornecedor Contratado</th>
                    <th>Objeto da Contratação</th>
                    <th>Vigência</th>
                    <th>Situação</th>
                    <th style="text-align: right;">Valor Inicial</th>
                    <th style="text-align: right;">Valor Atualizado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['items'])): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Nenhum contrato localizado no banco de dados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['items'] as $c): ?>
                        <tr>
                            <td><code><?= SecurityHelper::escape($c['numero_contrato']) ?></code></td>
                            <td>
                                <strong><?= SecurityHelper::escape($c['fornecedor_nome']) ?></strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;"><?= SecurityHelper::escape($c['fornecedor_documento']) ?></div>
                            </td>
                            <td style="max-width: 320px; font-size: 0.85rem;"><?= SecurityHelper::escape($c['objeto']) ?></td>
                            <td>
                                <div><?= SecurityHelper::formatDate($c['data_inicio_vigencia']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">até <?= SecurityHelper::formatDate($c['data_fim_vigencia']) ?></div>
                            </td>
                            <td><span class="badge badge-institucional"><?= SecurityHelper::escape($c['situacao']) ?></span></td>
                            <td style="text-align: right;"><?= SecurityHelper::formatMoney($c['valor_inicial']) ?></td>
                            <td style="text-align: right; font-weight: 700; color: var(--accent);"><?= SecurityHelper::formatMoney($c['valor_atualizado']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
