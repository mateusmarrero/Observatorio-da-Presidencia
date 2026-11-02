<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\SecurityHelper;

abstract class Controller {
    protected function render(string $view, array $data = [], string $layout = 'main'): void {
        extract($data);

        $flashSuccess = $this->getFlash('success');
        $flashError = $this->getFlash('error');

        $viewFile = dirname(__DIR__, 2) . "/resources/views/{$view}.php";
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View não encontrada: {$viewFile}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === 'none') {
            echo $content;
            return;
        }

        $layoutFile = dirname(__DIR__, 2) . "/resources/views/layouts/{$layout}.php";
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json(mixed $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    protected function setFlash(string $key, string $message): void {
        SecurityHelper::startSecureSession();
        $_SESSION["flash_{$key}"] = $message;
    }

    protected function getFlash(string $key): ?string {
        SecurityHelper::startSecureSession();
        if (isset($_SESSION["flash_{$key}"])) {
            $msg = $_SESSION["flash_{$key}"];
            unset($_SESSION["flash_{$key}"]);
            return $msg;
        }
        return null;
    }
}
