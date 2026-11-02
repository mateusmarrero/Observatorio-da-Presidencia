<?php
declare(strict_types=1);

namespace App;

use App\Middleware\SecurityHeadersMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Helpers\SecurityHelper;

class Router {
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middlewares = []): void {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): void {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middlewares): void {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . rtrim($pattern, '/') . '/?$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'original' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(): void {
        SecurityHeadersMiddleware::apply();
        RateLimitMiddleware::check();
        SecurityHelper::startSecureSession();

        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = ($uri === '') ? '/' : $uri;

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Executar middlewares
                foreach ($route['middlewares'] as $mw) {
                    if (is_callable($mw)) {
                        $mw();
                    }
                }

                // Extrair parâmetros nomeados
                $params = [];
                foreach ($matches as $k => $v) {
                    if (!is_int($k)) {
                        $params[$k] = $v;
                    }
                }

                $handler = $route['handler'];
                if (is_callable($handler)) {
                    $handler($params);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$class, $method] = $handler;
                    $controller = new $class();
                    $controller->$method($params);
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        $viewPath = dirname(__DIR__) . '/resources/views/pages/404.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "<h1>404 - Página Não Encontrada</h1><p>O recurso solicitado não existe ou foi movido.</p><p><a href='/'>Ir para o início</a></p>";
        }
    }
}
