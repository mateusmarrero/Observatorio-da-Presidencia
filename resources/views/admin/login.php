<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Login Administrativo', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--bg-body);
        }
        .login-box {
            width: 100%;
            max-width: 420px;
            padding: 2.25rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }
    </style>
</head>
<body>
<div class="login-box">
    <div style="text-align: center; margin-bottom: 2rem;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 50%; background: var(--accent-soft); color: var(--accent); margin-bottom: 1rem;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <h1 style="font-size: 1.4rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">Área Restrita</h1>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Observatório da Presidência • Controle Editorial</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-box alert-error" style="margin-bottom: 1.25rem; font-size: 0.85rem;">
            <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" action="/admin/login" style="display: flex; flex-direction: column; gap: 1.15rem;">
        <?= \App\Helpers\SecurityHelper::csrfField() ?>

        <div class="form-group">
            <label class="form-label">E-mail de Acesso</label>
            <input type="email" name="email" class="form-input" required autofocus placeholder="seu.email@observatorio.org" value="<?= htmlspecialchars($oldEmail ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Senha de Segurança</label>
            <input type="password" name="password" class="form-input" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 0.75rem; margin-top: 0.5rem; font-size: 0.95rem;">
            Entrar no Painel
        </button>
    </form>

    <div style="text-align: center; margin-top: 1.75rem; font-size: 0.82rem; color: var(--text-muted);">
        <a href="/" style="color: var(--text-muted); text-decoration: none;">← Voltar ao Portal Público</a>
    </div>
</div>
</body>
</html>
