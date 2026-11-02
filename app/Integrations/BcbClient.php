<?php
declare(strict_types=1);

namespace App\Integrations;

class BcbClient {
    private string $baseUrl;

    public function __construct() {
        $this->baseUrl = config('sources.bcb_api.ipca_url', 'https://api.bcb.gov.br/dados/serie/bcdata.sgs.433/dados');
    }

    /**
     * Retorna a série histórica de variação mensal do IPCA (%)
     * Exemplo de retorno: [ ['data' => '01/01/2003', 'valor' => '2.25'], ... ]
     */
    public function getSerieIpca(?string $dataInicio = null, ?string $dataFim = null): array {
        $url = $this->baseUrl . '?formato=json';
        if ($dataInicio) {
            $url .= '&dataInicial=' . urlencode($dataInicio); // DD/MM/AAAA
        } else {
            $url .= '&dataInicial=01/01/2003';
        }
        if ($dataFim) {
            $url .= '&dataFinal=' . urlencode($dataFim);
        }

        $cacheFile = dirname(__DIR__, 2) . '/storage/imports/ipca_serie_cache.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400 * 7) && !$dataInicio) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cached) && !empty($cached)) {
                return $cached;
            }
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ObservatorioPresidencia/1.0 (Portal de Transparencia)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Compatibilidade ampla de ambientes sem CA bundle local
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                if (!$dataInicio) {
                    @file_put_contents($cacheFile, json_encode($data));
                }
                return $data;
            }
        }

        // Se offline ou falha temporária, ler cache se disponível
        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true) ?: [];
        }

        return [];
    }

    /**
     * Calcula o fator acumulado de correção entre uma data original e a data base.
     */
    public function calcularFatorCorrecao(string $dataOriginal, string $dataReferencia): float {
        $serie = $this->getSerieIpca();
        if (empty($serie)) {
            return 1.0;
        }

        $tsOriginal = strtotime($dataOriginal);
        $tsReferencia = strtotime($dataReferencia);

        if ($tsOriginal >= $tsReferencia) {
            return 1.0;
        }

        $fator = 1.0;
        foreach ($serie as $item) {
            // Data do item: DD/MM/AAAA
            $parts = explode('/', $item['data']);
            if (count($parts) === 3) {
                $itemDate = "{$parts[2]}-{$parts[1]}-01";
                $tsItem = strtotime($itemDate);
                if ($tsItem >= $tsOriginal && $tsItem <= $tsReferencia) {
                    $taxa = (float)$item['valor'] / 100.0;
                    $fator *= (1.0 + $taxa);
                }
            }
        }

        return round($fator, 6);
    }
}
