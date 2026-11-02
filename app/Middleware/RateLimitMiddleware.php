<?php
declare(strict_types=1);

namespace App\Middleware;

class RateLimitMiddleware {
    public static function check(): bool {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $maxRequests = config('security.rate_limit.max_requests', 120);
        $decay = config('security.rate_limit.decay_seconds', 60);

        $cacheDir = dirname(__DIR__, 2) . '/storage/logs/ratelimit';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $hash = md5($ip);
        $file = "{$cacheDir}/{$hash}.json";

        $now = time();
        $data = ['count' => 0, 'reset' => $now + $decay];

        if (file_exists($file)) {
            $content = file_get_contents($file);
            if ($content) {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['reset']) && $decoded['reset'] > $now) {
                    $data = $decoded;
                }
            }
        }

        $data['count']++;
        file_put_contents($file, json_encode($data), LOCK_EX);

        header("X-RateLimit-Limit: {$maxRequests}");
        header("X-RateLimit-Remaining: " . max(0, $maxRequests - $data['count']));
        header("X-RateLimit-Reset: {$data['reset']}");

        if ($data['count'] > $maxRequests) {
            http_response_code(429);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'erro' => 'Muitas requisições. Por favor, aguarde alguns instantes antes de tentar novamente.',
                'retry_after' => $data['reset'] - $now
            ]);
            exit;
        }

        return true;
    }
}
