<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Fornecedores e Prestadores de Serviço
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Pessoas jurídicas e físicas credenciadas ou contratadas pela Presidência da República.
    </p>
</div>

<form method="GET" action="/fornecedores" class="filter-bar">
    <div class="filter-grid">
        <div class="form-group">
            <label class="form-label">Buscar Razão Social / CNPJ / Nome Fantasia</label>
            <input type="text" name="q" class="form-input" placeholder="Empresa, número do documento..." value="<?= SecurityHelper::escape($filters['termo']) ?>">
        </div>

        <div class="form-group">
            <label class="form-label">UF</label>
            <input type="text" name="uf" class="form-input" maxlength="2" placeholder="DF, SP, RJ..." value="<?= SecurityHelper::escape($filters['uf']) ?>">
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Filtrar</button>
            <a href="/fornecedores" class="btn btn-secondary">Limpar</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Empresas e Credores Registrados</h3>
            <div class="card-subtitle">Exibindo <?= count($result['items']) ?> de <?= number_format($result['total'], 0, ',', '.') ?> fornecedores</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Razão Social / Nome</th>
                    <th>CNPJ / CPF</th>
                    <th>Localização</th>
                    <th style="text-align: center;">Transações</th>
                    <th style="text-align: center;">Contratos</th>
                    <th style="text-align: right;">Total Faturado Declarado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['items'])): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Nenhum fornecedor localizado com os critérios informados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['items'] as $f): ?>
                        <tr>
                            <td><strong><?= SecurityHelper::escape($f['razao_social']) ?></strong></td>
                            <td style="font-family: monospace;"><?= SecurityHelper::escape($f['cnpj_cpf']) ?></td>
                            <td><?= SecurityHelper::escape($f['cidade'] ?? 'Brasília') ?> / <?= SecurityHelper::escape($f['uf'] ?? 'DF') ?></td>
                            <td style="text-align: center;"><span class="badge badge-institucional"><?= (int)$f['total_transacoes'] ?></span></td>
                            <td style="text-align: center;"><span class="badge badge-institucional"><?= (int)$f['total_contratos'] ?></span></td>
                            <td style="text-align: right; font-weight: 700; color: var(--accent);">
                                <?= SecurityHelper::formatMoney($f['total_recebido']) ?>
                            </td>
                            <td>
                                <a href="/fornecedores/<?= $f['id'] ?>" class="btn btn-secondary btn-sm">Ver Detalhes ↗</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
