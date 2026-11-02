<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Fontes Governamentais Oficiais
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        O portal utiliza com exclusividade dados abertos primários provenientes dos órgãos oficiais do Governo Federal e do Banco Central do Brasil.
    </p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Fontes Integradas e Monitoradas</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nome da Fonte</th>
                    <th>Órgão Responsável</th>
                    <th>Tipo</th>
                    <th>Frequência</th>
                    <th>Link Oficial</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fontes as $f): ?>
                    <tr>
                        <td><code><?= SecurityHelper::escape($f['codigo']) ?></code></td>
                        <td><strong><?= SecurityHelper::escape($f['nome']) ?></strong></td>
                        <td><?= SecurityHelper::escape($f['orgao_responsavel']) ?></td>
                        <td><span class="badge badge-institucional"><?= SecurityHelper::escape($f['tipo_fonte']) ?></span></td>
                        <td><?= SecurityHelper::escape($f['frequencia_atualizacao']) ?></td>
                        <td>
                            <a href="<?= SecurityHelper::escape($f['url_base']) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
                                Acessar Fonte ↗
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
