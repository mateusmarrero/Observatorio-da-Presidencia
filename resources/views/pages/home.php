<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 0.5rem;">
        Monitoramento de Gastos e Atividades da Presidência
    </h1>
    <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 900px;">
        Plataforma técnica e independente dedicada à consolidação de dados públicos oficiais sobre despesas, viagens, cartões de pagamento (CPGF) e contratos governamentais, com metodologia auditável e distinção rigorosa de vínculos.
    </p>
</div>

<!-- Banner de Diretriz Editorial Inegociável -->
<div class="alert-box alert-info" style="border-left: 4px solid var(--accent); margin-bottom: 2rem;">
    <div>
        <strong style="display: block; margin-bottom: 0.25rem;">Compromisso de Imparcialidade e Rigor Metodológico</strong>
        Despesas administrativas gerais da Presidência da República <strong>nunca são imputadas automaticamente</strong> a uma pessoa física. Cada lançamento é classificado estritamente de acordo com a documentação primária oficial: <em>Despesa Direta</em>, <em>Agenda Oficial</em>, <em>Comitiva em Viagem</em> ou <em>Institucional do Órgão</em>.
    </div>
</div>

<!-- Métricas Principais / Estatísticas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Despesas Identificadas</span>
            <div class="stat-icon">📄</div>
        </div>
        <div class="stat-value"><?= number_format($metrics['totais']['total_despesas'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-subtext">Registros contábeis e CPGF no banco</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Total Nominal Registrado</span>
            <div class="stat-icon">💰</div>
        </div>
        <div class="stat-value"><?= SecurityHelper::formatMoney($metrics['totais']['valor_total_nominal'] ?? 0) ?></div>
        <div class="stat-subtext">Valores históricos originais de liquidação</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Corrigido pelo IPCA</span>
            <div class="stat-icon">📈</div>
        </div>
        <div class="stat-value"><?= SecurityHelper::formatMoney($metrics['totais']['valor_total_corrigido'] ?? 0) ?></div>
        <div class="stat-subtext">Base BCB Série 433 (Ref: Set/2026)</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Viagens Monitoradas</span>
            <div class="stat-icon">✈️</div>
        </div>
        <div class="stat-value"><?= number_format($metrics['viagens']['total_viagens'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-subtext"><?= $metrics['viagens']['viagens_internacionais'] ?? 0 ?> internacionais • <?= $metrics['viagens']['viagens_nacionais'] ?? 0 ?> nacionais</div>
    </div>
</div>

<!-- Gráficos Rápidos da Home -->
<div class="chart-grid">
    <div class="chart-card">
        <h3 class="card-title" style="margin-bottom: 0.25rem;">Evolução de Despesas por Período</h3>
        <p class="card-subtitle" style="margin-bottom: 1rem;">Comparação entre valores nominais e valores corrigidos pela inflação oficial (IPCA)</p>
        <div class="chart-wrapper">
            <canvas id="chartEvolucaoHome"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <h3 class="card-title" style="margin-bottom: 0.25rem;">Distribuição por Categoria</h3>
        <p class="card-subtitle" style="margin-bottom: 1rem;">Maiores volumes de despesas consolidadas por finalidade</p>
        <div class="chart-wrapper">
            <canvas id="chartCategoriasHome"></canvas>
        </div>
    </div>
</div>

<!-- Seções de Atalhos Rápidos para Perfis -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Luiz Inácio Lula da Silva</h3>
                <div class="card-subtitle">Mandatos: 2003–2006 • 2007–2010 • 2023–atualidade</div>
            </div>
            <a href="/perfil/lula" class="btn btn-primary btn-sm">Ver Perfil Completo ↗</a>
        </div>
        <div class="card-body">
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">
                Acompanhe o histórico de mandatos, viagens ao exterior e pelo país, agendas públicas e as despesas com documentação primária oficial associada.
            </p>
            <div style="display: flex; gap: 1rem; font-size: 0.85rem;">
                <div><strong>Mandatos:</strong> 3 oficiais</div>
                <div><strong>Vínculos:</strong> Estritamente auditados</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Rosângela da Silva (Janja)</h3>
                <div class="card-subtitle">Atividades como Primeira-Dama desde 2023</div>
            </div>
            <a href="/perfil/janja" class="btn btn-primary btn-sm">Ver Perfil Completo ↗</a>
        </div>
        <div class="card-body">
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">
                Monitoramento transparente de representações oficiais, missões internacionais, comitivas e participações com diárias ou passagens custeadas pelo erário.
            </p>
            <div style="display: flex; gap: 1rem; font-size: 0.85rem;">
                <div><strong>Início de monitoramento:</strong> 01/01/2023</div>
                <div><strong>Atuação:</strong> Representação pública</div>
            </div>
        </div>
    </div>
</div>

<!-- Últimos Lançamentos de Despesas -->
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Últimas Despesas Registradas na Base</h3>
            <div class="card-subtitle">Registros contábeis e transações de cartões oficiais</div>
        </div>
        <a href="/despesas" class="btn btn-secondary btn-sm">Ver Todas as Despesas</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Portador / Unidade</th>
                    <th>Favorecido / Razão Social</th>
                    <th>Categoria</th>
                    <th>Vínculo Editorial</th>
                    <th style="text-align: right;">Valor Nominal</th>
                    <th style="text-align: right;">Valor Corrigido IPCA</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ultimasDespesas)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            Nenhum registro de despesa importado até o momento. Acesse o <a href="/admin/importacoes">Painel de Importações</a> para sincronizar dados oficiais.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ultimasDespesas as $d): ?>
                        <tr>
                            <td><?= SecurityHelper::formatDate($d['data_despesa']) ?></td>
                            <td><strong><?= SecurityHelper::escape($d['portador_cartao'] ?? $d['orgao_superior']) ?></strong></td>
                            <td><?= SecurityHelper::escape($d['fornecedor_nome'] ?? 'Não informado') ?></td>
                            <td>
                                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: <?= $d['categoria_cor'] ?? '#64748b' ?>; margin-right: 5px;"></span>
                                <?= SecurityHelper::escape($d['categoria_nome'] ?? 'Geral') ?>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Buscar métricas da API
    fetch('/api/metrics')
        .then(res => res.json())
        .then(data => {
            // Gráfico de Evolução Anual
            if (data.evolucao_anual && data.evolucao_anual.length > 0) {
                const anos = data.evolucao_anual.map(i => i.ano);
                const nominal = data.evolucao_anual.map(i => i.nominal);
                const corrigido = data.evolucao_anual.map(i => i.corrigido);
                OPCharts.createComparisonChart('chartEvolucaoHome', anos, nominal, corrigido);
            }

            // Gráfico de Categorias
            if (data.categorias && data.categorias.length > 0) {
                const labels = data.categorias.map(c => c.nome);
                const valores = data.categorias.map(c => c.total_nominal);
                const cores = data.categorias.map(c => c.cor);
                OPCharts.createDoughnutChart('chartCategoriasHome', labels, valores, cores);
            }
        })
        .catch(err => console.error("Erro ao carregar dados do gráfico:", err));
});
</script>
