<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Despacho e Análise de Alertas do Radar
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Triagem técnica e registro de pareceres do analista sobre anomalias e desvios estatísticos detectados.
    </p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Fila de Alertas Detectados</h3>
    </div>
    <div class="card-body">
        <?php if (empty($alertas)): ?>
            <p style="text-align: center; color: var(--text-muted); padding: 2rem;">Nenhum alerta registrado.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <?php foreach ($alertas as $a): ?>
                    <div style="border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.25rem; background: var(--bg-body);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="badge badge-<?= strtolower($a['nivel']) ?>"><?= $a['nivel'] ?></span>
                                <strong style="font-size: 1rem;"><?= SecurityHelper::escape($a['tipo_alerta']) ?></strong>
                            </div>
                            <span class="badge badge-institucional">Status: <?= $a['status_analise'] ?></span>
                        </div>

                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                            Regra: <code><?= SecurityHelper::escape($a['regra_acionada']) ?></code> • <?= SecurityHelper::formatDateTime($a['created_at']) ?>
                        </div>

                        <p style="font-size: 0.88rem; background: var(--bg-surface); padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 0.75rem;">
                            <?= SecurityHelper::escape($a['calculo_realizado']) ?>
                        </p>

                        <!-- Formulário de Despacho -->
                        <form method="POST" action="/admin/alertas/atualizar" style="display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap;">
                            <?= SecurityHelper::csrfField() ?>
                            <input type="hidden" name="alerta_id" value="<?= $a['id'] ?>">

                            <div class="form-group" style="min-width: 180px;">
                                <label class="form-label">Atualizar Status</label>
                                <select name="status_analise" class="form-select">
                                    <option value="PENDENTE" <?= ($a['status_analise'] === 'PENDENTE') ? 'selected' : '' ?>>Pendente</option>
                                    <option value="EM_ANALISE" <?= ($a['status_analise'] === 'EM_ANALISE') ? 'selected' : '' ?>>Em Análise</option>
                                    <option value="JUSTIFICADO" <?= ($a['status_analise'] === 'JUSTIFICADO') ? 'selected' : '' ?>>Justificado / Regular</option>
                                    <option value="CONFIRMADO_INCONSISTENTE" <?= ($a['status_analise'] === 'CONFIRMADO_INCONSISTENTE') ? 'selected' : '' ?>>Inconsistência Confirmada</option>
                                </select>
                            </div>

                            <div class="form-group" style="flex: 1; min-width: 250px;">
                                <label class="form-label">Parecer do Analista / Notas</label>
                                <input type="text" name="notas_analista" class="form-input" placeholder="Justificativa da decisão..." value="<?= SecurityHelper::escape($a['notas_analista']) ?>">
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm" style="height: 38px;">Gravar Despacho</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
