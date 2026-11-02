<?php
declare(strict_types=1);

namespace App\Integrations;

class TransparenciaClient {
    private string $baseUrl;
    private string $apiKey;
    private string $headerKey;

    public function __construct() {
        $this->baseUrl = config('sources.transparencia_api.base_url', 'https://api.portaldatransparencia.gov.br/api-de-dados');
        $this->apiKey = config('sources.transparencia_api.key', '');
        $this->headerKey = config('sources.transparencia_api.header_key', 'chave-api-dados');
    }

    public function hasKey(): bool {
        return !empty($this->apiKey);
    }

    public function request(string $endpoint, array $queryParams = []): array {
        if (!$this->hasKey()) {
            return [
                'success' => false,
                'status_code' => 403,
                'error' => 'Chave de API do Portal da Transparência não configurada no arquivo .env (TRANSPARENCIA_API_KEY). Cadastre uma chave gratuita no portal oficial para consultar a API direta.',
                'data' => []
            ];
        }

        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "{$this->headerKey}: {$this->apiKey}",
            'Accept: application/json',
            'User-Agent: ObservatorioPresidencia/1.0'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'status_code' => $httpCode ?: 500,
                'error' => "Falha na comunicação de rede com o Portal da Transparência: {$curlError}",
                'data' => []
            ];
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return [
                'success' => false,
                'status_code' => $httpCode,
                'error' => 'Acesso negado pela API do Portal da Transparência. Verifique se a chave de API é válida e possui permissões ativas.',
                'data' => []
            ];
        }

        if ($httpCode === 429) {
            return [
                'success' => false,
                'status_code' => 429,
                'error' => 'Limite de requisições excedido na API do Portal da Transparência. Aguarde antes de realizar nova chamada.',
                'data' => []
            ];
        }

        $decoded = json_decode((string)$response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'status_code' => $httpCode,
                'error' => null,
                'data' => is_array($decoded) ? $decoded : []
            ];
        }

        return [
            'success' => false,
            'status_code' => $httpCode,
            'error' => "A API retornou código HTTP {$httpCode}: " . substr((string)$response, 0, 200),
            'data' => []
        ];
    }

    public function getCartoes(int $pagina = 1, array $filtros = []): array {
        $params = array_merge(['pagina' => $pagina], $filtros);
        return $this->request('/cartoes', $params);
    }

    public function getViagens(int $pagina = 1, array $filtros = []): array {
        $params = array_merge(['pagina' => $pagina], $filtros);
        return $this->request('/viagens', $params);
    }

    public function getDespesas(int $pagina = 1, array $filtros = []): array {
        $params = array_merge(['pagina' => $pagina], $filtros);
        return $this->request('/despesas', $params);
    }

    public function getContratos(int $pagina = 1, array $filtros = []): array {
        $params = array_merge(['pagina' => $pagina], $filtros);
        return $this->request('/contratos', $params);
    }
}
