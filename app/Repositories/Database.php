<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;
use RuntimeException;

class Database {
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $defaultDriver = config('database.default', 'mysql');
        $dbConfig = config("database.connections.{$defaultDriver}");

        if (!$dbConfig) {
            throw new RuntimeException("Configuração do banco de dados para driver '{$defaultDriver}' não encontrada.");
        }

        try {
            if ($defaultDriver === 'sqlite') {
                $dir = dirname($dbConfig['database']);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $dsn = "sqlite:" . $dbConfig['database'];
                self::$pdo = new PDO($dsn, null, null, $dbConfig['options'] ?? []);
                self::$pdo->exec("PRAGMA foreign_keys = ON;");
            } else {
                $dsn = sprintf(
                    "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                    $dbConfig['host'],
                    $dbConfig['port'],
                    $dbConfig['database'],
                    $dbConfig['charset']
                );
                self::$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options'] ?? []);
            }
        } catch (PDOException $e) {
            // Em ambiente de teste/fallback, se MySQL falhar e houver sqlite configurado
            if ($defaultDriver === 'mysql' && file_exists(dirname(__DIR__, 2) . '/storage/database.sqlite')) {
                error_log("Aviso: MySQL indisponível (" . $e->getMessage() . "). Tentando fallback para SQLite local.");
                $sqlitePath = dirname(__DIR__, 2) . '/storage/database.sqlite';
                self::$pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                self::$pdo->exec("PRAGMA foreign_keys = ON;");
                return self::$pdo;
            }
            throw new RuntimeException("Falha na conexão com o banco de dados: " . $e->getMessage(), (int)$e->getCode(), $e);
        }

        return self::$pdo;
    }

    public static function resetConnection(): void {
        self::$pdo = null;
    }
}
