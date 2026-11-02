<?php
declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'Observatório da Presidência',
    'slogan' => 'O dinheiro é público. A informação também deve ser.',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/'),
    'secret' => getenv('APP_SECRET') ?: 'fallback-secret-obs-presidencia-2026',
    'timezone' => 'America/Sao_Paulo',
    'locale' => 'pt_BR',
];
