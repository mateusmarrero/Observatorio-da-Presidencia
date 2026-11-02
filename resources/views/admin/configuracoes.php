<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Configurações Globais do Portal
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Parâmetros de cálculo inflacionário, limites estatísticos do Radar e dados editoriais.
    </p>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <h3 class="card-title">Parâmetros Operacionais</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/configuracoes" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <?= SecurityHelper::csrfField() ?>

            <?php foreach ($configs as $cfg): ?>
                <div class="form-group">
                    <label class="form-label">
                        <strong><?= SecurityHelper::escape($cfg['chave']) ?></strong>
                        <span style="font-weight: normal; color: var(--text-muted); margin-left: 0.5rem;">— <?= SecurityHelper::escape($cfg['descricao']) ?></span>
                    </label>
                    <input type="text" name="config[<?= SecurityHelper::escape($cfg['chave']) ?>]" class="form-input" value="<?= SecurityHelper::escape($cfg['valor']) ?>">
                </div>
            <?php endforeach; ?>

            <div>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>
