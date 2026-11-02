<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Gestão de Usuários e Controle de Acesso (RBAC)
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Perfis de acesso: <strong>admin</strong> (controle total), <strong>editor</strong> (importações e revisão), <strong>analista</strong> (consultas e dossiês).
    </p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Usuários Autorizados</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil (Role)</th>
                    <th>Status</th>
                    <th>Último Login</th>
                    <th>Cadastrado em</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td><strong><?= SecurityHelper::escape($u['nome']) ?></strong></td>
                        <td><?= SecurityHelper::escape($u['email']) ?></td>
                        <td><span class="badge badge-institucional"><?= strtoupper(SecurityHelper::escape($u['perfil'])) ?></span></td>
                        <td>
                            <span class="badge badge-<?= ($u['ativo'] == 1) ? 'institucional' : 'atencao' ?>">
                                <?= ($u['ativo'] == 1) ? 'Ativo' : 'Desativado' ?>
                            </span>
                        </td>
                        <td><?= SecurityHelper::formatDateTime($u['ultimo_login']) ?></td>
                        <td><?= SecurityHelper::formatDate($u['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
