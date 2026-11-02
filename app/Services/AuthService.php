<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\Database;
use App\Helpers\SecurityHelper;
use PDO;

class AuthService {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function login(string $email, string $password): array {
        SecurityHelper::startSecureSession();

        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([trim(strtolower($email))]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Credenciais incorretas ou usuário não encontrado.'];
        }

        if ((int)$user['ativo'] !== 1) {
            return ['success' => false, 'message' => 'Esta conta de usuário está desativada.'];
        }

        if ($user['bloqueado_ate'] && strtotime($user['bloqueado_ate']) > time()) {
            return ['success' => false, 'message' => 'Conta bloqueada temporariamente devido a sucessivas tentativas incorretas. Tente novamente mais tarde.'];
        }

        if (!password_verify($password, $user['senha_hash'])) {
            $tentativas = (int)$user['tentativas_falhas'] + 1;
            $bloqueio = null;
            if ($tentativas >= 5) {
                $bloqueio = date('Y-m-d H:i:s', time() + 900); // 15 minutos
            }
            $up = $this->pdo->prepare("UPDATE usuarios SET tentativas_falhas = ?, bloqueado_ate = ? WHERE id = ?");
            $up->execute([$tentativas, $bloqueio, $user['id']]);

            return ['success' => false, 'message' => 'Credenciais incorretas.'];
        }

        // Sucesso: regenerar ID de sessão para prevenir session fixation
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id(true);
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_perfil'] = $user['perfil'];

        // Resetar falhas e atualizar último login
        $up = $this->pdo->prepare("UPDATE usuarios SET tentativas_falhas = 0, bloqueado_ate = NULL, ultimo_login = ? WHERE id = ?");
        $up->execute([date('Y-m-d H:i:s'), $user['id']]);

        // Registrar em historico_alteracoes
        $audit = $this->pdo->prepare("
            INSERT INTO historico_alteracoes (tabela_afetada, registro_id, acao, usuario_id, ip_origem, user_agent)
            VALUES ('usuarios', ?, 'LOGIN', ?, ?, ?)
        ");
        $audit->execute([
            $user['id'],
            $user['id'],
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Browser', 0, 255)
        ]);

        return ['success' => true, 'user' => $user];
    }

    public function logout(): void {
        SecurityHelper::startSecureSession();
        $_SESSION = [];
        if (!headers_sent() && ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            @setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }

    public static function user(): ?array {
        SecurityHelper::startSecureSession();
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        return [
            'id' => (int)$_SESSION['user_id'],
            'nome' => $_SESSION['user_nome'],
            'email' => $_SESSION['user_email'],
            'perfil' => $_SESSION['user_perfil'],
        ];
    }

    public static function check(): bool {
        return self::user() !== null;
    }

    public static function hasRole(string|array $roles): bool {
        $u = self::user();
        if (!$u) return false;
        if (is_string($roles)) {
            $roles = [$roles];
        }
        return in_array($u['perfil'], $roles, true);
    }
}
