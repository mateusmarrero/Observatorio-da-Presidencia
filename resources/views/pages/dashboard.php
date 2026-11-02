<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
            Dashboard Geral de Despesas e Atividades
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            Consolidação dos dados financeiros governamentais calculados a partir dos registros reais do banco de dados.
        </p>
    </div>
    <div style="font-size: 0.8rem; color: var(--text-muted); background: var(--bg-surface); padding: 0.4rem 0.8rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
        🔄 Última sincronização de dados: <strong><?= SecurityHelper::formatDateTime($metrics['ultima_atualizacao']) ?></strong>
    </div>
</div>

<!-- Cards de Indicadores Agregados -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Total Geral Nominal</span>
            <div class="stat-icon">💵</div>
        </div>
        <div class="stat-value"><?= SecurityHelper::formatMoney($metrics['totais']['valor_total_nominal'] ?? 0) ?></div>
        <div class="stat-subtext">Total de <?= number_format($metrics['totais']['total_despesas'] ?? 0, 0, ',', '.') ?> lançamentos</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Total Corrigido (IPCA)</span>
            <div class="stat-icon">📊</div>
        </div>
        <div class="stat-value"><?= SecurityHelper::formatMoney($metrics['totais']['valor_total_corrigido'] ?? 0) ?></div>
        <div class="stat-subtext">Atualizado pela inflação oficial BCB</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Diárias Presidenciais</span>
            <div class="stat-icon">🏨</div>
        </div>
        <div class="stat-value"><?= SecurityHelper::formatMoney($metrics['viagens']['total_diarias'] ?? 0) ?></div>
        <div class="stat-subtext">Custos de estada de comitivas/apoio</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Passagens e Transportes</span>
            <div class="stat-icon">🎫</div>
        </div>
        <div class="stat-value"><?= SecurityHelper::formatMoney($metrics['viagens']['total_passagens'] ?? 0) ?></div>
        <div class="stat-subtext">Deslocamentos de equipes e missões</div>
    </div>
</div>

<!-- Classificação Rigorosa de Vínculos Editoriais -->
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Classificação de Despesas por Natureza do Vínculo</h3>
            <div class="card-subtitle">Critério rigoroso: despesas administrativas gerais não são imputadas a pessoas físicas</div>
        </div>
        <a href="/metodologia" class="btn btn-secondary btn-sm">Ver Metodologia Editorial</a>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
            <?php
            $tiposVinculoInfo = [
                'DIRETA' => ['label' => 'Diretamente Atribuída', 'desc' => 'Despesas comprovadamente individuais da autoridade', 'cor' => '#92400e', 'badge' => 'badge-direta'],
                'AGENDA_OFICIAL' => ['label' => 'Agenda Oficial', 'desc' => 'Custos atrelados a compromissos públicos específicos', 'cor' => '#6d28d9', 'badge' => 'badge-agenda'],
                'COMITIVA_VIAGEM' => ['label' => 'Comitivas e Viagens', 'desc' => 'Apoio logístico, escolta e transporte de delegações', 'cor' => '#0369a1', 'badge' => 'badge-comitiva'],
                'INSTITUCIONAL' => ['label' => 'Despesa Institucional', 'desc' => 'Manutenção administrativa dos órgãos (Casa Civil/GSI/SECOM)', 'cor' => '#475569', 'badge' => 'badge-institucional'],
                'NAO_IDENTIFICADO' => ['label' => 'Sem Vínculo Identificado', 'desc' => 'Lançamentos da fonte primária sem detalhamento suficiente', 'cor' => '#6b7280', 'badge' => 'badge-nao-identificado'],
            ];

            $totaisVinculo = [];
            foreach ($metrics['vinculos'] ?? [] as $v) {
                $totaisVinculo[$v['vinculo']] = $v;
            }

            foreach ($tiposVinculoInfo as $chave => $info):
                $dadosV = $totaisVinculo[$chave] ?? ['qtd' => 0, 'total_nominal' => 0, 'total_corrigido' => 0];
            ?>
                <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; background: var(--bg-body);">
                    <div style="margin-bottom: 0.5rem;">
                        <span class="badge <?= $info['badge'] ?>"><?= $info['label'] ?></span>
                    </div>
                    <div style="font-size: 1.35rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">
                        <?= SecurityHelper::formatMoney($dadosV['total_nominal']) ?>
                    </div>
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                        <?= number_format((int)$dadosV['qtd'], 0, ',', '.') ?> lançamentos registrados
                    </div>
                    <p style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">
                        <?= $info['desc'] ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Gráficos Analíticos -->
<div class="chart-grid">
    <div class="chart-card">
        <h3 class="card-title">Comparação Histórica: Nominal vs Corrigido IPCA</h3>
        <p class="card-subtitle" style="margin-bottom: 1rem;">Evolução anual com deflação base Setembro/2026</p>
        <div class="chart-wrapper">
            <canvas id="chartEvolucaoDashboard"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <h3 class="card-title">Distribuição de Gastos por Categoria</h3>
        <p class="card-subtitle" style="margin-bottom: 1rem;">Composição percentual dos recursos públicos</p>
        <div class="chart-wrapper">
            <canvas id="chartCategoriasDashboard"></canvas>
        </div>
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
                OPCharts.createComparisonChart('chartEvolucaoDashboard', anos, nominal, corrigido);
            }

            if (data.categorias && data.categorias.length > 0) {
                const labels = data.categorias.map(c => c.nome);
                const valores = data.categorias.map(c => c.total_nominal);
                const cores = data.categorias.map(c => c.cor);
                OPCharts.createDoughnutChart('chartCategoriasDashboard', labels, valores, cores);
            }
        })
        .catch(err => console.error(err));
});
</script>
