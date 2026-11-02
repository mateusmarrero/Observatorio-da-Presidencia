<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Histórico Financeiro da Presidência
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Série temporal completa dos dispêndios federais executados na Presidência da República desde 2003.
    </p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Evolução Histórica Anual (Nominal x Corrigido IPCA)</h3>
    </div>
    <div class="card-body">
        <div class="chart-wrapper" style="height: 380px;">
            <canvas id="chartHistoricoCompleto"></canvas>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tabela Consolidada por Exercício Financeiro</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ano do Exercício</th>
                    <th style="text-align: center;">Quantidade de Registros</th>
                    <th style="text-align: right;">Total Nominal (R$)</th>
                    <th style="text-align: right;">Total Corrigido IPCA (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($metrics['evolucao_anual'])): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            Sem dados consolidados no momento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($metrics['evolucao_anual'] as $anoRow): ?>
                        <tr>
                            <td><strong><?= SecurityHelper::escape($anoRow['ano']) ?></strong></td>
                            <td style="text-align: center;"><span class="badge badge-institucional"><?= (int)$anoRow['qtd'] ?></span></td>
                            <td style="text-align: right;"><?= SecurityHelper::formatMoney($anoRow['nominal']) ?></td>
                            <td style="text-align: right; font-weight: 700; color: var(--accent);"><?= SecurityHelper::formatMoney($anoRow['corrigido']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    fetch('/api/metrics')
        .then(res => res.json())
        .then(data => {
            if (data.evolucao_anual && data.evolucao_anual.length > 0) {
                const anos = data.evolucao_anual.map(i => i.ano);
                const nominal = data.evolucao_anual.map(i => i.nominal);
                const corrigido = data.evolucao_anual.map(i => i.corrigido);
                OPCharts.createComparisonChart('chartHistoricoCompleto', anos, nominal, corrigido);
            }
        });
});
</script>
