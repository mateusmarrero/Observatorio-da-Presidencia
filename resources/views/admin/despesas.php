<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Revisão Editorial de Vínculos de Despesas
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Atribuição editorial auditável. Toda alteração de vínculo exige justificativa fundamentada e é registrada na trilha de auditoria.
    </p>
</div>

<!-- Modal / Formulário de Edição de Vínculo -->
<div class="card" id="form-edicao-card" style="display: none; border-left: 4px solid var(--accent);">
    <div class="card-header">
        <h3 class="card-title">Alterar Vínculo Editorial da Despesa <span id="lbl-despesa-id"></span></h3>
        <button type="button" class="btn btn-secondary btn-sm" onclick="fecharEdicao()">Cancelar</button>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/despesas/vinculo">
            <?= SecurityHelper::csrfField() ?>
            <input type="hidden" name="despesa_id" id="input-despesa-id" value="">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label">Pessoa Monitorada</label>
                    <select name="pessoa_id" id="select-pessoa-id" class="form-select">
                        <option value="">Nenhuma (Geral / Institucional)</option>
                        <?php foreach ($pessoas as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= SecurityHelper::escape($p['nome_publico']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Natureza do Vínculo *</label>
                    <select name="tipo_vinculo" id="select-tipo-vinculo" class="form-select" required>
                        <option value="DIRETA">DIRETA (Individual da autoridade)</option>
                        <option value="AGENDA_OFICIAL">AGENDA_OFICIAL (Compromisso público específico)</option>
                        <option value="COMITIVA_VIAGEM">COMITIVA_VIAGEM (Apoio logístico em viagem)</option>
                        <option value="INSTITUCIONAL">INSTITUCIONAL (Custeio geral do órgão)</option>
                        <option value="NAO_IDENTIFICADO">NAO_IDENTIFICADO (Sem elemento de prova)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Grau de Certeza Documental *</label>
                    <select name="grau_certeza" id="select-grau-certeza" class="form-select" required>
                        <option value="CONFIRMADO_DOCUMENTAL">Confirmado por Documento Oficial</option>
                        <option value="INDICIARIO">Indiciário / Contextual</option>
                        <option value="EM_ANALISE">Em Análise / Pendente de Checagem</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Justificativa Editorial e Documental (Obrigatória) *</label>
                <textarea name="justificativa" id="input-justificativa" rows="3" class="form-textarea" required placeholder="Explique os fundamentos e a fonte documental que embasam esta classificação..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Salvar e Registrar na Auditoria</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Despesas para Revisão</h3>
            <div class="card-subtitle">Exibindo <?= count($result['items']) ?> de <?= number_format($result['total'], 0, ',', '.') ?> registros</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Portador / Unidade</th>
                    <th>Favorecido</th>
                    <th>Vínculo Atual</th>
                    <th>Pessoa Vinculada</th>
                    <th style="text-align: right;">Valor Nominal</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['items'])): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Nenhum registro localizado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['items'] as $d): ?>
                        <tr>
                            <td><?= SecurityHelper::formatDate($d['data_despesa']) ?></td>
                            <td><?= SecurityHelper::escape($d['portador_cartao'] ?? $d['orgao_superior']) ?></td>
                            <td><?= SecurityHelper::escape($d['fornecedor_nome'] ?? 'Não informado') ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace('_', '-', $d['tipo_vinculo'] ?? 'nao-identificado')) ?>">
                                    <?= SecurityHelper::escape($d['tipo_vinculo'] ?? 'NAO_IDENTIFICADO') ?>
                                </span>
                            </td>
                            <td><strong><?= SecurityHelper::escape($d['pessoa_nome'] ?? '-') ?></strong></td>
                            <td style="text-align: right; font-weight: 600;"><?= SecurityHelper::formatMoney($d['valor_nominal']) ?></td>
                            <td>
                                <button type="button" class="btn btn-secondary btn-sm" onclick='abrirEdicao(<?= json_encode($d) ?>)'>
                                    Revisar Vínculo ✎
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function abrirEdicao(despesa) {
    document.getElementById('form-edicao-card').style.display = 'block';
    document.getElementById('lbl-despesa-id').textContent = '#' + despesa.id + ' (' + despesa.identificador_origem + ')';
    document.getElementById('input-despesa-id').value = despesa.id;
    document.getElementById('select-tipo-vinculo').value = despesa.tipo_vinculo || 'NAO_IDENTIFICADO';
    document.getElementById('select-grau-certeza').value = despesa.grau_certeza || 'EM_ANALISE';
    document.getElementById('select-pessoa-id').value = despesa.pessoa_id || '';
    document.getElementById('input-justificativa').value = despesa.vinculo_justificativa || '';
    window.scrollTo({ top: document.getElementById('form-edicao-card').offsetTop - 80, behavior: 'smooth' });
}

function fecharEdicao() {
    document.getElementById('form-edicao-card').style.display = 'none';
}
</script>
