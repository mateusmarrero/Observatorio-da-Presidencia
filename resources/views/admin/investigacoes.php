<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Central de Investigação Documental
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Dossiês de apuração aprofundada com rigorosa separação metodológica entre fatos comprovados, hipóteses de trabalho e conclusões.
    </p>
</div>

<!-- Abertura de Novo Dossiê -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Abrir Novo Dossiê de Investigação</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/investigacoes/salvar" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <?= SecurityHelper::csrfField() ?>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Título do Dossiê *</label>
                    <input type="text" name="titulo" required class="form-input" placeholder="Ex: Análise das despesas de hospedagem na Cúpula do G20">
                </div>

                <div class="form-group">
                    <label class="form-label">Status da Investigação</label>
                    <select name="status" class="form-select">
                        <option value="ABERTA">Aberta / Em Triagem</option>
                        <option value="EM_APURACAO">Em Apuração Documental</option>
                        <option value="CONCLUIDA">Concluída</option>
                        <option value="ARQUIVADA">Arquivada</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" style="color: var(--success);">1. Fatos Comprovados (Com Documento Oficial)</label>
                    <textarea name="fatos_comprovados" rows="4" class="form-textarea" placeholder="Inserir apenas fatos respaldados por nota de empenho, diário oficial ou nota fiscal..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" style="color: var(--warning);">2. Hipóteses e Linhas de Apuração</label>
                    <textarea name="hipoteses" rows="4" class="form-textarea" placeholder="Hipóteses a verificar (nunca afirmadas como certeza)..."></textarea>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">3. Informações Não Confirmadas</label>
                    <textarea name="informacoes_nao_confirmadas" rows="3" class="form-textarea" placeholder="Rumores, postagens em redes sociais ou dados sem respaldo em fonte primária..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">4. Pendências de Busca / Ofícios</label>
                    <textarea name="pendencias" rows="3" class="form-textarea" placeholder="Pedidos de LAI em aberto, certidões pendentes..."></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">5. Conclusão Provisória / Final</label>
                <textarea name="conclusao" rows="3" class="form-textarea" placeholder="Síntese dos achados..."></textarea>
            </div>

            <div>
                <button type="submit" class="btn btn-primary">Registrar Dossiê Interno</button>
            </div>
        </form>
    </div>
</div>

<!-- Dossiês em Andamento -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Dossiês Catalogados na Central</h3>
    </div>
    <div class="card-body">
        <?php if (empty($investigacoes)): ?>
            <p style="text-align: center; color: var(--text-muted); padding: 2rem;">Nenhum dossiê aberto ainda.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <?php foreach ($investigacoes as $inv): ?>
                    <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; background: var(--bg-body);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                            <div>
                                <span class="badge badge-institucional"><code><?= SecurityHelper::escape($inv['codigo_dossie']) ?></code></span>
                                <strong style="font-size: 1.05rem; margin-left: 0.5rem;"><?= SecurityHelper::escape($inv['titulo']) ?></strong>
                            </div>
                            <span class="badge badge-<?= ($inv['status'] === 'CONCLUIDA') ? 'institucional' : 'atencao' ?>"><?= $inv['status'] ?></span>
                        </div>

                        <?php if ($inv['fatos_comprovados']): ?>
                            <div style="margin-top: 0.5rem; font-size: 0.88rem;">
                                <strong style="color: var(--success);">Fatos Comprovados:</strong> <?= nl2br(SecurityHelper::escape($inv['fatos_comprovados'])) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($inv['hipoteses']): ?>
                            <div style="margin-top: 0.5rem; font-size: 0.88rem;">
                                <strong style="color: var(--warning);">Hipóteses:</strong> <?= nl2br(SecurityHelper::escape($inv['hipoteses'])) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($inv['conclusao']): ?>
                            <div style="margin-top: 0.5rem; font-size: 0.88rem; background: var(--bg-surface); padding: 0.5rem; border-radius: var(--radius-sm);">
                                <strong>Conclusão:</strong> <?= nl2br(SecurityHelper::escape($inv['conclusao'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
