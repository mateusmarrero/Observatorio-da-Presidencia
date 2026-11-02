<?php
declare(strict_types=1);

namespace App\Middleware;

class SecurityHeadersMiddleware {
    public static function apply(): void {
        $headers = config('security.headers', []);
        foreach ($headers as $key => $val) {
            header("{$key}: {$val}");
        }
    }
}
