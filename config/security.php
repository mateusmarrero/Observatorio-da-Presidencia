<?php
declare(strict_types=1);

return [
    'session' => [
        'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 7200),
        'secure' => filter_var(getenv('COOKIE_SECURE') ?: false, FILTER_VALIDATE_BOOLEAN),
        'httponly' => true,
        'samesite' => getenv('COOKIE_SAMESITE') ?: 'Lax',
    ],
    'rate_limit' => [
        'max_requests' => (int)(getenv('RATE_LIMIT_REQUESTS') ?: 120),
        'decay_seconds' => (int)(getenv('RATE_LIMIT_DECAY') ?: 60),
    ],
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self';",
    ],
    'roles' => ['admin', 'editor', 'analista'],
];
