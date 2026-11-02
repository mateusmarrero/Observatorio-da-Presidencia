<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthService;

class AuthMiddleware {
    public static function requireAuth(array|string $roles = []): void {
        if (!AuthService::check()) {
            header('Location: /admin/login');
            exit;
        }

        if (!empty($roles) && !AuthService::hasRole($roles)) {
            http_response_code(403);
            echo "<h1>403 - Acesso Negado</h1><p>Você não possui permissão para acessar esta área.</p><p><a href='/admin'>Voltar ao Painel</a></p>";
            exit;
        }
    }
}
