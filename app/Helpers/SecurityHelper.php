<?php
declare(strict_types=1);

namespace App\Helpers;

class SecurityHelper {
    public static function startSecureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                $lifetime = config('security.session.lifetime', 7200);
                $secure = config('security.session.secure', false);
                $samesite = config('security.session.samesite', 'Lax');

                session_set_cookie_params([
                    'lifetime' => $lifetime,
                    'path' => '/',
                    'domain' => '',
                    'secure' => $secure,
                    'httponly' => true,
                    'samesite' => $samesite,
                ]);
            }
            @session_start();
        }
    }

    public static function generateCsrfToken(): string {
        self::startSecureSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(?string $token): bool {
        self::startSecureSession();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function csrfField(): string {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function escape(mixed $data): string {
        if ($data === null) {
            return '';
        }
        return htmlspecialchars((string)$data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function sanitizeString(?string $str): string {
        if ($str === null) return '';
        return trim(strip_tags($str));
    }

    public static function formatMoney(mixed $val): string {
        $num = (float)($val ?? 0);
        return 'R$ ' . number_format($num, 2, ',', '.');
    }

    public static function formatDate(?string $date): string {
        if (!$date) return '-';
        $ts = strtotime($date);
        return $ts ? date('d/m/Y', $ts) : $date;
    }

    public static function formatDateTime(?string $dateTime): string {
        if (!$dateTime) return '-';
        $ts = strtotime($dateTime);
        return $ts ? date('d/m/Y H:i', $ts) : $dateTime;
    }
}
