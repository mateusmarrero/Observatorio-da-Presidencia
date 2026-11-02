<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Biblioteca Documental Oficial
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Repositório auditável de arquivos originais, planilhas governamentais desclassificadas e extratos oficiais com integridade validada por hash SHA-256.
    </p>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Documentos Primários Cadastrados</h3>
            <div class="card-subtitle">Exibindo <?= count($result['items']) ?> de <?= number_format($result['total'], 0, ',', '.') ?> documentos</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Título do Documento</th>
                    <th>Tipo</th>
                    <th>Fonte Oficial</th>
                    <th>Data Coleta</th>
                    <th>Assinatura Criptográfica (SHA-256)</th>
                    <th>Acesso</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['items'])): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Nenhum documento avulso registrado no repositório.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['items'] as $doc): ?>
                        <tr>
                            <td><strong><?= SecurityHelper::escape($doc['titulo']) ?></strong></td>
                            <td><span class="badge badge-institucional"><?= SecurityHelper::escape($doc['tipo_documento']) ?></span></td>
                            <td><?= SecurityHelper::escape($doc['fonte_nome'] ?? 'Portal da Transparência') ?></td>
                            <td><?= SecurityHelper::formatDate($doc['data_coleta']) ?></td>
                            <td><code style="font-size: 0.75rem;"><?= substr($doc['hash_sha256'], 0, 16) ?>...</code></td>
                            <td>
                                <a href="<?= SecurityHelper::escape($doc['url_original']) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Fonte Original ↗</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
