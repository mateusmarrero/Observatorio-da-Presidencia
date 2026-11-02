<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\Database;
use App\Integrations\CsvParser;
use App\Integrations\BcbClient;
use PDO;
use Exception;

class ImportService {
    private PDO $pdo;
    private BcbClient $bcbClient;
    private ?int $lulaId = null;
    private ?int $janjaId = null;

    public function __construct() {
        $this->pdo = Database::getConnection();
        $this->bcbClient = new BcbClient();
        $this->loadPessoasIds();
    }

    private function loadPessoasIds(): void {
        $stmt = $this->pdo->query("SELECT id, slug FROM pessoas");
        while ($row = $stmt->fetch()) {
            if ($row['slug'] === 'lula') $this->lulaId = (int)$row['id'];
            if ($row['slug'] === 'janja') $this->janjaId = (int)$row['id'];
        }
    }

    /**
     * Importa arquivo CSV de Cartão de Pagamento do Governo Federal (CPGF)
     */
    public function importCpgfCsv(string $filePath, int $fonteId, ?int $usuarioId = null): array {
        if (!file_exists($filePath)) {
            throw new Exception("Arquivo CSV não encontrado: {$filePath}");
        }

        $fileHash = hash_file('sha256', $filePath);

        // Criar registro na tabela importacoes
        $stmt = $this->pdo->prepare("
            INSERT INTO importacoes (fonte_id, tipo_registro, arquivo_origem, hash_arquivo_origem, status, usuario_id, iniciado_em)
            VALUES (?, 'CPGF', ?, ?, 'PROCESSANDO', ?, ?)
        ");
        $stmt->execute([$fonteId, basename($filePath), $fileHash, $usuarioId, date('Y-m-d H:i:s')]);
        $importacaoId = (int)$this->pdo->lastInsertId();

        $inseridos = 0;
        $duplicados = 0;
        $erros = 0;
        $totalLidos = 0;

        $referenceDate = config('configuracoes.ipca_mes_referencia', '2026-09') . '-01';

        $res = CsvParser::parse($filePath, function(array $row) use (
            $fonteId, $importacaoId, $referenceDate,
            &$inseridos, &$duplicados, &$erros, &$totalLidos
        ) {
            $totalLidos++;

            // Localizar colunas independentemente de pequenas variações de cabeçalho
            $dataStr = $row['data transação'] ?? $row['data transacao'] ?? $row['data'] ?? null;
            $valorStr = $row['valor transação'] ?? $row['valor transacao'] ?? $row['valor'] ?? '0';
            $portador = $row['nome portador'] ?? $row['portador'] ?? 'PRESIDÊNCIA DA REPÚBLICA';
            $favorecido = $row['nome favorecido'] ?? $row['favorecido'] ?? $row['estabelecimento'] ?? 'NÃO INFORMADO';
            $cnpjCpf = $row['cnpj ou cpf favorecido'] ?? $row['cnpj/cpf favorecido'] ?? $row['cnpj'] ?? $row['cpf'] ?? '00000000000100';
            $descricao = $row['transação'] ?? $row['transacao'] ?? $row['descricao'] ?? 'Despesa com Cartão de Pagamento';
            $docNum = $row['código unidade gestora'] ?? $row['codigo unidade gestora'] ?? $row['documento'] ?? null;
            $orgao = $row['nome órgão superior'] ?? $row['nome orgao superior'] ?? $row['orgao'] ?? 'Presidência da República';

            $data = CsvParser::parseDate($dataStr);
            if (!$data) {
                $erros++;
                return true;
            }

            $valorNominal = CsvParser::parseMoney($valorStr);
            if ($valorNominal <= 0.001) {
                // Algumas linhas podem ter estorno ou valor zero
                return true;
            }

            $cnpjCpfLimpo = preg_replace('/[^\d]/', '', (string)$cnpjCpf);
            if (empty($cnpjCpfLimpo)) {
                $cnpjCpfLimpo = '00000000000100';
            }

            // Gerar hash único para deduplicação rigorosa
            $hashRegistro = hash('sha256', "CPGF|{$fonteId}|{$data}|{$valorNominal}|{$cnpjCpfLimpo}|{$portador}|{$descricao}");

            // Verificar duplicidade
            $check = $this->pdo->prepare("SELECT id FROM despesas WHERE hash_registro = ?");
            $check->execute([$hashRegistro]);
            if ($check->fetchColumn()) {
                $duplicados++;
                return true;
            }

            // Obter ou criar fornecedor
            $fornecedorId = $this->getOrCreateFornecedor($cnpjCpfLimpo, $favorecido, $valorNominal);

            // Categorizar despesa baseada na descrição
            $categoriaId = $this->detectCategoria($descricao, $favorecido);

            // Calcular correção IPCA
            $fator = $this->bcbClient->calcularFatorCorrecao($data, $referenceDate);
            $valorCorrigido = round($valorNominal * $fator, 2);

            $mesAno = substr($data, 0, 7);

            // Inserir despesa
            $ins = $this->pdo->prepare("
                INSERT INTO despesas (
                    identificador_origem, hash_registro, fonte_id, importacao_id, categoria_id, fornecedor_id,
                    orgao_superior, orgao_vinculado, data_despesa, mes_ano, valor_nominal, valor_corrigido_ipca,
                    data_referencia_ipca, fase_contabil, tipo_despesa, descricao_original, numero_documento,
                    tipo_cartao, portador_cartao, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, 'CPGF', 'Cartão Corporativo', ?, ?,
                    'CPGF - Presidencial', ?, ?
                )
            ");

            $idOrigem = "CPGF-" . date('Ymd', strtotime($data)) . "-" . substr($hashRegistro, 0, 8);
            $ins->execute([
                $idOrigem, $hashRegistro, $fonteId, $importacaoId, $categoriaId, $fornecedorId,
                $orgao, 'Presidência da República', $data, $mesAno, $valorNominal, $valorCorrigido,
                $referenceDate, $descricao, $docNum, $portador, date('Y-m-d H:i:s')
            ]);
            $despesaId = (int)$this->pdo->lastInsertId();

            // Criar vínculo com base nas regras editoriais rigorosas
            $this->vincularDespesa($despesaId, $portador, $descricao, $data);

            $inseridos++;
            return true;
        });

        // Finalizar importação
        $status = ($erros > 0 && $inseridos === 0) ? 'FALHA' : 'CONCLUIDO';
        $up = $this->pdo->prepare("
            UPDATE importacoes
            SET total_lidos = ?, total_inseridos = ?, total_duplicados = ?, total_erros = ?, status = ?, finalizado_em = ?
            WHERE id = ?
        ");
        $up->execute([$totalLidos, $inseridos, $duplicados, $erros, $status, date('Y-m-d H:i:s'), $importacaoId]);

        return [
            'importacao_id' => $importacaoId,
            'total_lidos' => $totalLidos,
            'inseridos' => $inseridos,
            'duplicados' => $duplicados,
            'erros' => $erros,
            'status' => $status
        ];
    }

    /**
     * Importa arquivo CSV de Viagens Oficiais (SCDP)
     */
    public function importViagensCsv(string $filePath, int $fonteId, ?int $usuarioId = null): array {
        if (!file_exists($filePath)) {
            throw new Exception("Arquivo CSV de viagens não encontrado: {$filePath}");
        }

        $fileHash = hash_file('sha256', $filePath);
        $stmt = $this->pdo->prepare("
            INSERT INTO importacoes (fonte_id, tipo_registro, arquivo_origem, hash_arquivo_origem, status, usuario_id, iniciado_em)
            VALUES (?, 'VIAGENS', ?, ?, 'PROCESSANDO', ?, ?)
        ");
        $stmt->execute([$fonteId, basename($filePath), $fileHash, $usuarioId, date('Y-m-d H:i:s')]);
        $importacaoId = (int)$this->pdo->lastInsertId();

        $inseridos = 0;
        $duplicados = 0;
        $erros = 0;
        $totalLidos = 0;

        CsvParser::parse($filePath, function(array $row) use (
            $fonteId, $importacaoId,
            &$inseridos, &$duplicados, &$erros, &$totalLidos
        ) {
            $totalLidos++;

            $idOrigem = $row['identificador do processo de viagem'] ?? $row['id processo'] ?? $row['codigo'] ?? null;
            $motivo = $row['motivo'] ?? $row['motivo da viagem'] ?? $row['justificativa'] ?? 'Missão oficial de interesse público';
            $dataInicio = CsvParser::parseDate($row['período - data de início'] ?? $row['data inicio'] ?? $row['inicio'] ?? null);
            $dataFim = CsvParser::parseDate($row['período - data de fim'] ?? $row['data fim'] ?? $row['fim'] ?? null) ?: $dataInicio;
            $destinos = $row['destinos'] ?? $row['destino'] ?? 'Brasília - DF';
            $diarias = CsvParser::parseMoney($row['valor diárias'] ?? $row['valor diarias'] ?? 0);
            $passagens = CsvParser::parseMoney($row['valor passagens'] ?? 0);
            $outros = CsvParser::parseMoney($row['valor outros gastos'] ?? $row['outros'] ?? 0);
            $passageiro = $row['nome favorecido'] ?? $row['nome'] ?? $row['participante'] ?? 'Equipe Presidencial';
            $cargo = $row['cargo'] ?? $row['funcao'] ?? 'Membro da Comitiva';

            if (!$idOrigem || !$dataInicio) {
                $erros++;
                return true;
            }

            // Detectar nacional / internacional
            $isInternacional = (stripos($destinos, 'Brasil') === false && !preg_match('/\b[A-Z]{2}\b/', $destinos))
                || stripos($destinos, 'Exterior') !== false
                || stripos($destinos, 'Estados Unidos') !== false
                || stripos($destinos, 'França') !== false
                || stripos($destinos, 'China') !== false
                || stripos($destinos, 'Argentina') !== false;

            $tipoViagem = $isInternacional ? 'INTERNACIONAL' : 'NACIONAL';
            $totalViagem = $diarias + $passagens + $outros;

            // Extrair país e cidade
            $partesDestino = explode('-', $destinos);
            $cidade = trim($partesDestino[0] ?? $destinos);
            $pais = $isInternacional ? trim($partesDestino[1] ?? 'Internacional') : 'Brasil';

            // Verificar se viagem já existe
            $check = $this->pdo->prepare("SELECT id FROM viagens WHERE identificador_origem = ?");
            $check->execute([$idOrigem]);
            $viagemId = $check->fetchColumn();

            if ($viagemId) {
                $duplicados++;
            } else {
                $ins = $this->pdo->prepare("
                    INSERT INTO viagens (
                        identificador_origem, tipo_viagem, origem, destino_cidade, destino_pais,
                        data_inicio, data_fim, motivo_viagem, valor_total_diarias, valor_total_passagens,
                        valor_outros_gastos, valor_total_viagem, status, fonte_id, created_at
                    ) VALUES (
                        ?, ?, 'Brasília - DF', ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, 'Realizada', ?, ?
                    )
                ");
                $ins->execute([
                    $idOrigem, $tipoViagem, $cidade, $pais,
                    $dataInicio, $dataFim, $motivo, $diarias, $passagens,
                    $outros, $totalViagem, $fonteId, date('Y-m-d H:i:s')
                ]);
                $viagemId = (int)$this->pdo->lastInsertId();
                $inseridos++;
            }

            // Inserir participante
            if ($passageiro) {
                $pessoaId = null;
                if (stripos($passageiro, 'LULA') !== false && stripos($passageiro, 'LUIZ') !== false) {
                    $pessoaId = $this->lulaId;
                } elseif (stripos($passageiro, 'JANJA') !== false || stripos($passageiro, 'ROSÂNGELA DA SILVA') !== false) {
                    $pessoaId = $this->janjaId;
                }

                $pCheck = $this->pdo->prepare("SELECT id FROM viagem_participantes WHERE viagem_id = ? AND nome_participante = ?");
                $pCheck->execute([$viagemId, $passageiro]);
                if (!$pCheck->fetchColumn()) {
                    $pIns = $this->pdo->prepare("
                        INSERT INTO viagem_participantes (
                            viagem_id, pessoa_id, nome_participante, cargo_funcao,
                            valor_diarias, valor_passagens, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $pIns->execute([
                        $viagemId, $pessoaId, $passageiro, $cargo,
                        $diarias, $passagens, date('Y-m-d H:i:s')
                    ]);
                }
            }

            return true;
        });

        $status = ($erros > 0 && $inseridos === 0) ? 'FALHA' : 'CONCLUIDO';
        $up = $this->pdo->prepare("
            UPDATE importacoes
            SET total_lidos = ?, total_inseridos = ?, total_duplicados = ?, total_erros = ?, status = ?, finalizado_em = ?
            WHERE id = ?
        ");
        $up->execute([$totalLidos, $inseridos, $duplicados, $erros, $status, date('Y-m-d H:i:s'), $importacaoId]);

        return [
            'importacao_id' => $importacaoId,
            'total_lidos' => $totalLidos,
            'inseridos' => $inseridos,
            'duplicados' => $duplicados,
            'erros' => $erros,
            'status' => $status
        ];
    }

    /**
     * Regra de categorização contextual
     */
    private function detectCategoria(string $descricao, string $favorecido): ?int {
        $text = mb_strtolower("{$descricao} {$favorecido}", 'UTF-8');

        $cod = 'cpgf_outros';
        if (preg_match('/hotel|pousada|resort|hospedag|apart/i', $text)) {
            $cod = 'cpgf_hospedagem';
        } elseif (preg_match('/restaurante|refeic|padaria|alimento|bar |churrascaria|lanchonete|panificadora/i', $text)) {
            $cod = 'cpgf_alimentacao';
        } elseif (preg_match('/locadora|rent a car|veiculo|locacao|loc de veic/i', $text)) {
            $cod = 'cpgf_locacao';
        } elseif (preg_match('/posto|combustiv|gasolina|diesel|petrobras|ipiranga|shell|abastec/i', $text)) {
            $cod = 'cpgf_combustivel';
        } elseif (preg_match('/aereo|aerea|passagem|gol |tam |azul |voo/i', $text)) {
            $cod = 'cpgf_passagens';
        } elseif (preg_match('/seguranca|vigilancia|escolta|blindad/i', $text)) {
            $cod = 'cpgf_apoio';
        } elseif (preg_match('/telecom|satelite|internet|telefone|telefonia/i', $text)) {
            $cod = 'cpgf_comunicacao';
        }

        $stmt = $this->pdo->prepare("SELECT id FROM categorias_despesas WHERE codigo = ?");
        $stmt->execute([$cod]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    /**
     * Aplica as regras editoriais estritas para determinação do tipo de vínculo
     */
    private function vincularDespesa(int $despesaId, string $portador, string $descricao, string $data): void {
        $portadorUpper = mb_strtoupper($portador, 'UTF-8');
        $descUpper = mb_strtoupper($descricao, 'UTF-8');

        $tipoVinculo = 'NAO_IDENTIFICADO';
        $pessoaId = null;
        $grau = 'EM_ANALISE';
        $justificativa = 'Despesa registrada sem atribuição individual inequívoca na fonte primária.';

        if (str_contains($portadorUpper, 'LUIZ INACIO LULA DA SILVA') || str_contains($portadorUpper, 'LULA')) {
            $tipoVinculo = 'DIRETA';
            $pessoaId = $this->lulaId;
            $grau = 'CONFIRMADO_DOCUMENTAL';
            $justificativa = 'Cartão emitido nominalmente para o Presidente da República conforme registro oficial da fonte primária.';
        } elseif (str_contains($portadorUpper, 'ROSANGELA') || str_contains($portadorUpper, 'JANJA')) {
            $tipoVinculo = 'DIRETA';
            $pessoaId = $this->janjaId;
            $grau = 'CONFIRMADO_DOCUMENTAL';
            $justificativa = 'Despesa nominalmente associada à Primeira-Dama conforme documento oficial.';
        } elseif (str_contains($portadorUpper, 'AJUDANCIA DE ORDENS') || str_contains($descUpper, 'COMITIVA') || str_contains($descUpper, 'VIAGEM')) {
            $tipoVinculo = 'COMITIVA_VIAGEM';
            $grau = 'CONFIRMADO_DOCUMENTAL';
            $justificativa = 'Despesa operacional executada por equipe de apoio/comitiva em deslocamento oficial.';
        } elseif (str_contains($portadorUpper, 'SECRETARIA DE ADMINISTRACAO') || str_contains($portadorUpper, 'GABINETE')) {
            $tipoVinculo = 'INSTITUCIONAL';
            $grau = 'CONFIRMADO_DOCUMENTAL';
            $justificativa = 'Despesa administrativa de custeio institucional geral do órgão, sem apropriação a pessoa específica.';
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO vinculos_despesas (
                despesa_id, pessoa_id, tipo_vinculo, grau_certeza, justificativa, created_at
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$despesaId, $pessoaId, $tipoVinculo, $grau, $justificativa, date('Y-m-d H:i:s')]);
    }

    private function getOrCreateFornecedor(string $cnpjCpf, string $razaoSocial, float $valor): int {
        $stmt = $this->pdo->prepare("SELECT id, total_recebido FROM fornecedores WHERE cnpj_cpf = ?");
        $stmt->execute([$cnpjCpf]);
        $row = $stmt->fetch();

        if ($row) {
            $novoTotal = (float)$row['total_recebido'] + $valor;
            $up = $this->pdo->prepare("UPDATE fornecedores SET total_recebido = ? WHERE id = ?");
            $up->execute([$novoTotal, $row['id']]);
            return (int)$row['id'];
        }

        $tipo = strlen($cnpjCpf) === 11 ? 'CPF' : 'CNPJ';
        $ins = $this->pdo->prepare("
            INSERT INTO fornecedores (cnpj_cpf, tipo_documento, razao_social, total_recebido, created_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $ins->execute([$cnpjCpf, $tipo, mb_strtoupper($razaoSocial, 'UTF-8'), $valor, date('Y-m-d H:i:s')]);
        return (int)$this->pdo->lastInsertId();
    }
}
