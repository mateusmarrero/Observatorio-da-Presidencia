<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\Database;
use PDO;

class RadarService {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /**
     * Executa a bateria completa de checagens estatísticas e heurísticas do Radar Investigativo
     */
    public function executarAuditoria(): array {
        $alertasGerados = 0;

        $alertasGerados += $this->verificarValoresAtipicos();
        $alertasGerados += $this->verificarFornecedoresRecorrentes();
        $alertasGerados += $this->verificarDespesasSemDocumento();
        $alertasGerados += $this->verificarInconsistenciasDeDatas();
        $alertasGerados += $this->verificarConcentracaoEmPeriodosCurtos();

        return [
            'status' => 'sucesso',
            'alertas_novos' => $alertasGerados,
            'executado_em' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Regra 1: Valores Estatisticamente Atípicos (Outliers superiores)
     */
    private function verificarValoresAtipicos(): int {
        $limite = (float)($this->getConfig('radar_limite_atípico_cpgf', '50000.00'));

        $stmt = $this->pdo->prepare("
            SELECT d.id, d.valor_nominal, d.data_despesa, d.descricao_original, f.razao_social
            FROM despesas d
            LEFT JOIN fornecedores f ON d.fornecedor_id = f.id
            WHERE d.valor_nominal >= ?
            AND NOT EXISTS (
                SELECT 1 FROM alertas a WHERE a.despesa_id = d.id AND a.regra_acionada = 'VALOR_ATIPICO_ACIMA_LIMITE'
            )
        ");
        $stmt->execute([$limite]);
        $rows = $stmt->fetchAll();

        $novos = 0;
        $ins = $this->pdo->prepare("
            INSERT INTO alertas (
                tipo_alerta, nivel, regra_acionada, calculo_realizado, dados_comparados_json,
                despesa_id, justificativa, status_analise, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDENTE', ?)
        ");

        foreach ($rows as $row) {
            $dadosJson = json_encode([
                'valor_nominal' => (float)$row['valor_nominal'],
                'limite_alerta' => $limite,
                'favorecido' => $row['razao_social'],
                'data' => $row['data_despesa']
            ], JSON_UNESCAPED_UNICODE);

            $ins->execute([
                'Valor Atípico Elevado',
                'ATENCAO',
                'VALOR_ATIPICO_ACIMA_LIMITE',
                "Despesa unitária no valor de R$ " . number_format((float)$row['valor_nominal'], 2, ',', '.') . " excede o limiar de atenção configurado de R$ " . number_format($limite, 2, ',', '.') . ".",
                $dadosJson,
                $row['id'],
                "Transação com valor individual expressivo identificada na base de dados para análise documental complementar.",
                date('Y-m-d H:i:s')
            ]);
            $novos++;
        }

        return $novos;
    }

    /**
     * Regra 2: Fornecedores com alta frequência de transações no mesmo órgão
     */
    private function verificarFornecedoresRecorrentes(): int {
        $minTransacoes = (int)($this->getConfig('radar_fornecedor_recorrente_min', '5'));

        $stmt = $this->pdo->prepare("
            SELECT f.id, f.razao_social, f.cnpj_cpf, COUNT(d.id) as total_transacoes, SUM(d.valor_nominal) as total_pago
            FROM fornecedores f
            JOIN despesas d ON d.fornecedor_id = f.id
            GROUP BY f.id, f.razao_social, f.cnpj_cpf
            HAVING total_transacoes >= ?
            AND NOT EXISTS (
                SELECT 1 FROM alertas a WHERE a.fornecedor_id = f.id AND a.regra_acionada = 'FORNECEDOR_RECORRENTE_FREQUENTE'
            )
        ");
        $stmt->execute([$minTransacoes]);
        $rows = $stmt->fetchAll();

        $novos = 0;
        $ins = $this->pdo->prepare("
            INSERT INTO alertas (
                tipo_alerta, nivel, regra_acionada, calculo_realizado, dados_comparados_json,
                fornecedor_id, justificativa, status_analise, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDENTE', ?)
        ");

        foreach ($rows as $row) {
            $dadosJson = json_encode([
                'razao_social' => $row['razao_social'],
                'cnpj_cpf' => $row['cnpj_cpf'],
                'transacoes_identificadas' => (int)$row['total_transacoes'],
                'total_faturado' => (float)$row['total_pago']
            ], JSON_UNESCAPED_UNICODE);

            $ins->execute([
                'Fornecedor de Alta Recorrência',
                'INFORMATIVO',
                'FORNECEDOR_RECORRENTE_FREQUENTE',
                "Fornecedor acumula {$row['total_transacoes']} transações registradas, totalizando R$ " . number_format((float)$row['total_pago'], 2, ',', '.') . ".",
                $dadosJson,
                $row['id'],
                "Volume expressivo de contratações recorrentes com o mesmo fornecedor. Conveniente conferir aderência a licitações ou atas de registro de preços.",
                date('Y-m-d H:i:s')
            ]);
            $novos++;
        }

        return $novos;
    }

    /**
     * Regra 3: Despesas sem identificador de documento original
     */
    private function verificarDespesasSemDocumento(): int {
        $stmt = $this->pdo->query("
            SELECT d.id, d.valor_nominal, d.data_despesa, d.descricao_original
            FROM despesas d
            WHERE (d.numero_documento IS NULL OR d.numero_documento = '')
            AND d.valor_nominal > 5000
            AND NOT EXISTS (
                SELECT 1 FROM alertas a WHERE a.despesa_id = d.id AND a.regra_acionada = 'DESPESA_SEM_NUMERO_DOCUMENTO'
            )
            LIMIT 50
        ");
        $rows = $stmt->fetchAll();

        $novos = 0;
        $ins = $this->pdo->prepare("
            INSERT INTO alertas (
                tipo_alerta, nivel, regra_acionada, calculo_realizado, dados_comparados_json,
                despesa_id, justificativa, status_analise, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDENTE', ?)
        ");

        foreach ($rows as $row) {
            $dadosJson = json_encode([
                'valor_nominal' => (float)$row['valor_nominal'],
                'data' => $row['data_despesa'],
                'descricao' => $row['descricao_original']
            ], JSON_UNESCAPED_UNICODE);

            $ins->execute([
                'Documento Primário Ausente',
                'INFORMATIVO',
                'DESPESA_SEM_NUMERO_DOCUMENTO',
                "Despesa de valor R$ " . number_format((float)$row['valor_nominal'], 2, ',', '.') . " foi importada sem indicação explícita de número do documento fiscal/processo na base primária.",
                $dadosJson,
                $row['id'],
                "Ausência de código de empenho ou nota fiscal no lançamento de dados abertos. Recomenda-se busca ativa na base do SIAFI ou portal da CGU.",
                date('Y-m-d H:i:s')
            ]);
            $novos++;
        }

        return $novos;
    }

    /**
     * Regra 4: Inconsistências de datas (ex: despesa fora de mandato ou viagem com retorno antes da ida)
     */
    private function verificarInconsistenciasDeDatas(): int {
        $stmt = $this->pdo->query("
            SELECT v.id, v.identificador_origem, v.data_inicio, v.data_fim, v.motivo_viagem
            FROM viagens v
            WHERE v.data_fim < v.data_inicio
            AND NOT EXISTS (
                SELECT 1 FROM alertas a WHERE a.viagem_id = v.id AND a.regra_acionada = 'VIAGEM_DATA_RETORNO_INVALIDA'
            )
        ");
        $rows = $stmt->fetchAll();

        $novos = 0;
        $ins = $this->pdo->prepare("
            INSERT INTO alertas (
                tipo_alerta, nivel, regra_acionada, calculo_realizado, dados_comparados_json,
                viagem_id, justificativa, status_analise, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDENTE', ?)
        ");

        foreach ($rows as $row) {
            $dadosJson = json_encode([
                'data_inicio' => $row['data_inicio'],
                'data_fim' => $row['data_fim'],
                'identificador' => $row['identificador_origem']
            ], JSON_UNESCAPED_UNICODE);

            $ins->execute([
                'Inconsistência Cronológica de Viagem',
                'PRIORITARIO',
                'VIAGEM_DATA_RETORNO_INVALIDA',
                "A data de retorno informada ({$row['data_fim']}) é cronologicamente anterior à data de partida ({$row['data_inicio']}).",
                $dadosJson,
                $row['id'],
                "Divergência evidente no preenchimento do sistema SCDP. Necessária retificação manual ou consulta ao processo administrativo original.",
                date('Y-m-d H:i:s')
            ]);
            $novos++;
        }

        return $novos;
    }

    /**
     * Regra 5: Múltiplas transações no mesmo estabelecimento em um período inferior a 24 horas
     */
    private function verificarConcentracaoEmPeriodosCurtos(): int {
        $stmt = $this->pdo->query("
            SELECT d.fornecedor_id, d.data_despesa, COUNT(d.id) as transacoes_no_dia, SUM(d.valor_nominal) as valor_dia, f.razao_social
            FROM despesas d
            JOIN fornecedores f ON d.fornecedor_id = f.id
            GROUP BY d.fornecedor_id, d.data_despesa, f.razao_social
            HAVING transacoes_no_dia >= 3 AND valor_dia > 15000
            AND NOT EXISTS (
                SELECT 1 FROM alertas a WHERE a.fornecedor_id = d.fornecedor_id AND a.regra_acionada = 'CONCENTRACAO_TRANSACOES_MESMO_DIA'
            )
            LIMIT 20
        ");
        $rows = $stmt->fetchAll();

        $novos = 0;
        $ins = $this->pdo->prepare("
            INSERT INTO alertas (
                tipo_alerta, nivel, regra_acionada, calculo_realizado, dados_comparados_json,
                fornecedor_id, justificativa, status_analise, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDENTE', ?)
        ");

        foreach ($rows as $row) {
            $dadosJson = json_encode([
                'data' => $row['data_despesa'],
                'transacoes' => (int)$row['transacoes_no_dia'],
                'total_gasto' => (float)$row['valor_dia'],
                'fornecedor' => $row['razao_social']
            ], JSON_UNESCAPED_UNICODE);

            $ins->execute([
                'Concentração de Transações Diárias',
                'ATENCAO',
                'CONCENTRACAO_TRANSACOES_MESMO_DIA',
                "Foram registradas {$row['transacoes_no_dia']} transações no mesmo dia ({$row['data_despesa']}) somando R$ " . number_format((float)$row['valor_dia'], 2, ',', '.') . " com o mesmo estabelecimento.",
                $dadosJson,
                $row['fornecedor_id'],
                "Padrão de múltiplos pagamentos fracionados ao mesmo credor no mesmo dia civil. Indicado para averiguação de fracionamento de despesa.",
                date('Y-m-d H:i:s')
            ]);
            $novos++;
        }

        return $novos;
    }

    private function getConfig(string $key, string $default): string {
        $stmt = $this->pdo->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (string)$val : $default;
    }
}
