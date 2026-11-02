<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Services\ImportService;
use App\Services\RadarService;
use App\Repositories\Database;

echo "=== Importando dados iniciais oficiais de amostra ===\n";

$importService = new ImportService();
$pdo = Database::getConnection();

// Obter ID da fonte Casa Civil
$stmt = $pdo->query("SELECT id FROM fontes_dados WHERE codigo = 'casa_civil_cpgf'");
$fonteCpgfId = (int)$stmt->fetchColumn() ?: 1;

$stmt = $pdo->query("SELECT id FROM fontes_dados WHERE codigo = 'cgu_csv'");
$fonteViagensId = (int)$stmt->fetchColumn() ?: 2;

$cpgfCsv = dirname(__DIR__, 2) . '/storage/imports/amostra_cpgf_oficial.csv';
$viagensCsv = dirname(__DIR__, 2) . '/storage/imports/amostra_viagens_oficial.csv';

$resCpgf = $importService->importCpgfCsv($cpgfCsv, $fonteCpgfId, 1);
echo "CPGF: {$resCpgf['inseridos']} inseridos, {$resCpgf['duplicados']} duplicados.\n";

$resViagens = $importService->importViagensCsv($viagensCsv, $fonteViagensId, 1);
echo "Viagens: {$resViagens['inseridos']} inseridos, {$resViagens['duplicados']} duplicados.\n";

// Executar Radar Investigativo
$radar = new RadarService();
$radarRes = $radar->executarAuditoria();
echo "Radar Investigativo executado: {$radarRes['alertas_novos']} alertas gerados.\n";
echo "=== Concluído com sucesso! ===\n";
