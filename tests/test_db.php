<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Repositories\Database;

$pdo = Database::getConnection();
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

echo "Tabelas criadas com sucesso (" . count($tables) . "):\n";
foreach ($tables as $t) {
    if ($t === 'sqlite_sequence') continue;
    $count = $pdo->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
    echo "  - {$t} (registros: {$count})\n";
}
