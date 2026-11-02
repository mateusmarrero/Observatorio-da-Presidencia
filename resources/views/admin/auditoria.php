<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Trilha de Auditoria e Logs Imutáveis
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Registro cronológico de todas as ações de revisão de vínculos, logins e cargas de dados com IP de origem.
    </p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Últimos Eventos de Auditoria</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data / Hora</th>
                    <th>Ação Executada</th>
                    <th>Tabela</th>
                    <th>Registro ID</th>
                    <th>Usuário Responsável</th>
                    <th>IP Origem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Nenhum evento registrado ainda na trilha de auditoria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><?= SecurityHelper::formatDateTime($l['created_at']) ?></td>
                            <td><span class="badge badge-institucional"><?= SecurityHelper::escape($l['acao']) ?></span></td>
                            <td><code><?= SecurityHelper::escape($l['tabela_afetada']) ?></code></td>
                            <td>#<?= (int)$l['registro_id'] ?></td>
                            <td><strong><?= SecurityHelper::escape($l['usuario_nome'] ?? 'Sistema') ?></strong></td>
                            <td style="font-family: monospace; font-size: 0.8rem;"><?= SecurityHelper::escape($l['ip_origem'] ?? '127.0.0.1') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
