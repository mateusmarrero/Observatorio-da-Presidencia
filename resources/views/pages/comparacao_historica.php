<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Comparação Histórica entre Mandatos Presidenciais
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Análise comparativa proporcional entre os mandatos 2003–2006, 2007–2010 e de 2023 à atualidade, com correção monetária pelo IPCA oficial.
    </p>
</div>

<div class="alert-box alert-warning">
    <div>
        <strong>Aviso Técnico Obrigatório sobre Cobertura de Bases (Seção 15):</strong>
        Comparações históricas entre mandatos distantes no tempo (ex.: década de 2000 vs década de 2020) envolvem sistemas contábeis que evoluíram consideravelmente (implantação da Lei de Acesso à Informação em 2011, reformulações no SIAFI e no Portal da Transparência). Valores nominais do passado sem correção inflacionária não refletem o poder de compra contemporâneo. Portanto, a comparação econômica válida deve considerar sempre o <strong>Valor Corrigido pelo IPCA</strong>.
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Comparativo Consolidado por Mandato</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Período / Mandato</th>
                    <th style="text-align: center;">Total Lançamentos</th>
                    <th style="text-align: right;">Total Nominal da Época</th>
                    <th style="text-align: right;">Total Corrigido (IPCA Ref: Set/2026)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($comparativo)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            Dados em processamento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($comparativo as $comp): ?>
                        <tr>
                            <td><strong><?= SecurityHelper::escape($comp['periodo']) ?></strong></td>
                            <td style="text-align: center;"><span class="badge badge-institucional"><?= (int)$comp['total_transacoes'] ?></span></td>
                            <td style="text-align: right;"><?= SecurityHelper::formatMoney($comp['total_nominal']) ?></td>
                            <td style="text-align: right; font-weight: 700; color: var(--accent);"><?= SecurityHelper::formatMoney($comp['total_corrigido_ipca']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
