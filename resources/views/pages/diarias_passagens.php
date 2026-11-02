<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Diárias e Passagens Governamentais
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Transparência detalhada dos custos unitários de passagens aéreas e diárias de hospedagem concedidas a servidores e autoridades em missões oficiais.
    </p>
</div>

<div class="alert-box alert-info">
    <div>
        <strong>Fonte Primária:</strong> Dados coletados diretamente do Sistema de Concessão de Diárias e Passagens (SCDP) via Portal da Transparência e painéis do GSI. Diárias indenizatórias destinam-se ao custeio de alimentação e estada conforme tabelas do Poder Executivo Federal.
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Processos de Deslocamento e Concessões</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Processo</th>
                    <th>Destino</th>
                    <th>Período</th>
                    <th>Finalidade</th>
                    <th style="text-align: right;">Diárias (R$)</th>
                    <th style="text-align: right;">Passagens (R$)</th>
                    <th style="text-align: right;">Total Processo (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['items'])): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Nenhum processo listado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['items'] as $v): ?>
                        <tr>
                            <td><code><?= SecurityHelper::escape($v['identificador_origem']) ?></code></td>
                            <td><strong><?= SecurityHelper::escape($v['destino_cidade']) ?></strong> (<?= SecurityHelper::escape($v['destino_pais']) ?>)</td>
                            <td><?= SecurityHelper::formatDate($v['data_inicio']) ?> a <?= SecurityHelper::formatDate($v['data_fim']) ?></td>
                            <td style="max-width: 300px;"><?= SecurityHelper::escape($v['motivo_viagem']) ?></td>
                            <td style="text-align: right;"><?= SecurityHelper::formatMoney($v['valor_total_diarias']) ?></td>
                            <td style="text-align: right;"><?= SecurityHelper::formatMoney($v['valor_total_passagens']) ?></td>
                            <td style="text-align: right; font-weight: 700; color: var(--accent);"><?= SecurityHelper::formatMoney($v['valor_total_viagem']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
