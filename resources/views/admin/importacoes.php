<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Gestão e Monitor de Importações
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Carga manual de arquivos oficiais (CSV/Planilhas) do Portal da Transparência, Casa Civil e SCDP com validação de hash e deduplicação automática.
    </p>
</div>

<!-- Card de Upload Manual -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Carga de Arquivo de Dados Abertos (CSV)</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/importacoes/upload" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <?= SecurityHelper::csrfField() ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label class="form-label">Fonte Oficial Primária *</label>
                    <select name="fonte_id" class="form-select" required>
                        <?php foreach ($fontes as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= SecurityHelper::escape($f['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tipo de Registros do Arquivo *</label>
                    <select name="tipo_registro" class="form-select" required>
                        <option value="CPGF">Cartões de Pagamento (CPGF - Presidência)</option>
                        <option value="VIAGENS">Viagens Oficiais (SCDP - Diárias e Passagens)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Selecionar Arquivo CSV Oficial *</label>
                    <input type="file" name="arquivo_csv" accept=".csv,.txt" class="form-input" required>
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span>Processar e Importar Arquivo</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Histórico de Importações -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Histórico de Cargas e Execuções</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fonte Oficial</th>
                    <th>Tipo</th>
                    <th>Arquivo / Origem</th>
                    <th>Lidos</th>
                    <th>Inseridos</th>
                    <th>Duplicados</th>
                    <th>Erros</th>
                    <th>Status</th>
                    <th>Data Execução</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($historico)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Nenhum ciclo de importação registrado ainda.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($historico as $h): ?>
                        <tr>
                            <td>#<?= $h['id'] ?></td>
                            <td><strong><?= SecurityHelper::escape($h['fonte_nome'] ?? 'Fonte Desconhecida') ?></strong></td>
                            <td><span class="badge badge-institucional"><?= SecurityHelper::escape($h['tipo_registro']) ?></span></td>
                            <td style="font-size: 0.8rem;"><?= SecurityHelper::escape($h['arquivo_origem'] ?? 'API Direta') ?></td>
                            <td><?= (int)$h['total_lidos'] ?></td>
                            <td style="color: var(--success); font-weight: 700;">+<?= (int)$h['total_inseridos'] ?></td>
                            <td style="color: var(--text-muted);"><?= (int)$h['total_duplicados'] ?></td>
                            <td style="color: var(--danger);"><?= (int)$h['total_erros'] ?></td>
                            <td><span class="badge badge-<?= ($h['status'] === 'CONCLUIDO') ? 'institucional' : 'atencao' ?>"><?= $h['status'] ?></span></td>
                            <td><?= SecurityHelper::formatDateTime($h['iniciado_em']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
