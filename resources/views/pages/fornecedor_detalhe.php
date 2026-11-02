<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">
        <a href="/fornecedores" style="color: var(--accent); text-decoration: none;">← Voltar para Todos os Fornecedores</a>
    </div>
    <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        <?= SecurityHelper::escape($fornecedor['razao_social']) ?>
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        CNPJ/CPF: <strong><?= SecurityHelper::escape($fornecedor['cnpj_cpf']) ?></strong> • Total acumulado recebido da Presidência: <strong><?= SecurityHelper::formatMoney($fornecedor['total_recebido']) ?></strong>
    </p>
</div>

<div class="alert-box alert-warning">
    <div>
        <strong>Diretriz Editorial sobre Fornecedores:</strong>
        A menção de um fornecedor nesta plataforma decorre exclusivamente de lançamentos oficiais nos dados abertos do governo federal. Relações com autoridades ou comitivas somente são apontadas quando sustentadas por notas de empenho ou documentos comprobatórios públicos.
    </div>
</div>

<!-- Despesas com o fornecedor -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Despesas e Ordens de Pagamento Vinculadas</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição Original</th>
                    <th>Categoria</th>
                    <th>Vínculo Editorial</th>
                    <th style="text-align: right;">Valor Nominal</th>
                    <th style="text-align: right;">Corrigido IPCA</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($fornecedor['despesas'])): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            Nenhuma despesa individual detalhada para este fornecedor.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($fornecedor['despesas'] as $d): ?>
                        <tr>
                            <td><?= SecurityHelper::formatDate($d['data_despesa']) ?></td>
                            <td style="max-width: 350px;"><?= SecurityHelper::escape($d['descricao_original']) ?></td>
                            <td><?= SecurityHelper::escape($d['categoria_nome'] ?? 'Geral') ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace('_', '-', $d['tipo_vinculo'] ?? 'nao-identificado')) ?>">
                                    <?= SecurityHelper::escape($d['tipo_vinculo'] ?? 'NÃO IDENTIFICADO') ?>
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 600;"><?= SecurityHelper::formatMoney($d['valor_nominal']) ?></td>
                            <td style="text-align: right; color: var(--accent); font-weight: 600;"><?= SecurityHelper::formatMoney($d['valor_corrigido_ipca'] ?? $d['valor_nominal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
