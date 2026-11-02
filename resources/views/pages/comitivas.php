<?php
use App\Helpers\SecurityHelper;
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        Comitivas e Delegações Oficiais
    </h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Relação nominal de participantes públicos identificados em comitivas presidenciais, equipes de segurança e apoio logístico.
    </p>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Participantes de Comitiva Registrados</h3>
            <div class="card-subtitle">Exibindo <?= count($result['items']) ?> de <?= number_format($result['total'], 0, ',', '.') ?> registros</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nome do Participante</th>
                    <th>Cargo / Função</th>
                    <th>Destino da Missão</th>
                    <th>Período</th>
                    <th style="text-align: right;">Diárias Recebidas</th>
                    <th style="text-align: right;">Passagens Concedidas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['items'])): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Nenhum participante registrado até o momento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['items'] as $p): ?>
                        <tr>
                            <td>
                                <strong><?= SecurityHelper::escape($p['nome_participante']) ?></strong>
                                <?php if ($p['pessoa_nome']): ?>
                                    <div><a href="/perfil/<?= $p['pessoa_slug'] ?>" class="badge badge-agenda"><?= $p['pessoa_nome'] ?></a></div>
                                <?php endif; ?>
                            </td>
                            <td><?= SecurityHelper::escape($p['cargo_funcao'] ?? 'Membro da Delegação') ?></td>
                            <td><?= SecurityHelper::escape($p['destino_cidade']) ?> (<?= SecurityHelper::escape($p['destino_pais']) ?>)</td>
                            <td><?= SecurityHelper::formatDate($p['data_inicio']) ?> a <?= SecurityHelper::formatDate($p['data_fim']) ?></td>
                            <td style="text-align: right;"><?= SecurityHelper::formatMoney($p['valor_diarias']) ?></td>
                            <td style="text-align: right;"><?= SecurityHelper::formatMoney($p['valor_passagens']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
