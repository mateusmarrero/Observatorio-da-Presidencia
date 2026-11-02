<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Integrations\BcbClient;

$client = new BcbClient();
$serie = $client->getSerieIpca();
echo "Total de meses IPCA (desde 2003): " . count($serie) . "\n";
if (!empty($serie)) {
    $ultimo = end($serie);
    echo "Último mês catalogado: " . $ultimo['data'] . " -> " . $ultimo['valor'] . "%\n";
    $fator = $client->calcularFatorCorrecao('2003-01-01', '2026-09-01');
    echo "Fator de correção IPCA (Jan/2003 até Set/2026): {$fator}x\n";
    echo "Exemplo: R$ 10.000,00 de 2003 equivale a R$ " . number_format(10000 * $fator, 2, ',', '.') . " a preços atuais.\n";
}
