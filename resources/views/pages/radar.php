<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Radar Investigativo de Anomalias
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Motor estatístico de detecção automatizada de valores atípicos, fornecedores altamente recorrentes, transações concentradas e divergências de dados abertos.
    </p>
</div>

<div class="alert-box alert-warning" style="border-left: 4px solid var(--warning);">
    <div>
        <strong>Nota Metodológica Essencial:</strong>
        Alertas gerados pelo Radar Investigativo são <strong>instrumentos puramente analíticos e estatísticos de triagem</strong>, nunca acusações ou presunção de ilicitude. Este portal <em>não cria índices de corrupção ou culpabilidade</em>. Cada alerta aponta uma discrepância estatística ou documental para averiguação fundamentada.
    </div>
</div>

<!-- Filtros de Nível de Alerta -->
<div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <a href="/radar" class="btn <?= empty($filtroNivel) ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Todos os Alertas</a>
    <a href="/radar?nivel=PRIORITARIO" class="btn <?= ($filtroNivel === 'PRIORITARIO') ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Prioritários</a>
    <a href="/radar?nivel=ATENCAO" class="btn <?= ($filtroNivel === 'ATENCAO') ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Atenção</a>
    <a href="/radar?nivel=INFORMATIVO" class="btn <?= ($filtroNivel === 'INFORMATIVO') ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Informativos</a>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Alertas Estatísticos e Documentais Ativos</h3>
            <div class="card-subtitle">Exibindo <?= count($alertas) ?> ocorrências apontadas pelo motor algorítmico</div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($alertas)): ?>
            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                Nenhum alerta ativo gerado no momento. As checagens são executadas a cada nova importação ou ciclo de auditoria.
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <?php foreach ($alertas as $a): ?>
                    <div style="border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.25rem; background: var(--bg-body);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="badge badge-<?= strtolower($a['nivel']) ?>"><?= $a['nivel'] ?></span>
                                <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0;"><?= SecurityHelper::escape($a['tipo_alerta']) ?></h4>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">
                                Gerado em: <?= SecurityHelper::formatDateTime($a['created_at']) ?> • Status: <strong><?= SecurityHelper::escape($a['status_analise']) ?></strong>
                            </div>
                        </div>

                        <div style="font-size: 0.88rem; margin-bottom: 0.5rem;">
                            <strong>Regra Acionada:</strong> <code><?= SecurityHelper::escape($a['regra_acionada']) ?></code>
                        </div>

                        <p style="font-size: 0.9rem; color: var(--text-main); margin-bottom: 0.75rem; background: var(--bg-surface); padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                            <?= SecurityHelper::escape($a['calculo_realizado']) ?>
                        </p>

                        <div style="font-size: 0.85rem; color: var(--text-muted);">
                            <strong>Justificativa Técnica:</strong> <?= SecurityHelper::escape($a['justificativa']) ?>
                        </div>

                        <?php if ($a['dados_comparados_json']): ?>
                            <details style="margin-top: 0.75rem; font-size: 0.8rem; color: var(--text-muted);">
                                <summary style="cursor: pointer; font-weight: 600;">Ver Dados Estruturados da Comparação (JSON)</summary>
                                <pre style="background: var(--bg-surface); padding: 0.75rem; border-radius: var(--radius-md); margin-top: 0.5rem; overflow-x: auto; font-family: monospace;"><?= SecurityHelper::escape($a['dados_comparados_json']) ?></pre>
                            </details>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
