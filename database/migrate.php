<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Repositories\Migrator;
use Database\Seeds\DatabaseSeeder;

echo "=== Observatório da Presidência: Executando Migrações e Seeds ===\n";

try {
    $migrator = new Migrator();
    $migrator->run();
    echo "✓ Schema de banco de dados executado com sucesso!\n";

    $seeder = new DatabaseSeeder();
    $seeder->run();
    echo "✓ Seeds iniciais populadas com sucesso (Pessoas, Mandatos, Fontes, Categorias, Usuários)!\n";
    echo "Tudo pronto e configurado.\n";
} catch (Exception $e) {
    echo "❌ Erro na migração: " . $e->getMessage() . "\n";
    exit(1);
}
