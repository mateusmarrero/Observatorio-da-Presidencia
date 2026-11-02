<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;

class Migrator {
    private PDO $pdo;
    private string $driver;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getConnection();
        $this->driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function run(): void {
        if ($this->driver === 'sqlite') {
            $this->runSqliteSchema();
        } else {
            $this->runMysqlSchema();
        }
    }

    private function runMysqlSchema(): void {
        $schemaPath = dirname(__DIR__, 2) . '/database/schema.sql';
        $sql = file_get_contents($schemaPath);
        $this->pdo->exec($sql);
    }

    private function runSqliteSchema(): void {
        $statements = [
            "CREATE TABLE IF NOT EXISTS pessoas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT NOT NULL UNIQUE,
                nome_completo TEXT NOT NULL,
                nome_publico TEXT NOT NULL,
                cargo_papel TEXT NOT NULL,
                biografia TEXT NULL,
                data_nascimento TEXT NULL,
                foto_url TEXT NULL,
                ativo INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS mandatos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                pessoa_id INTEGER NOT NULL,
                titulo TEXT NOT NULL,
                data_inicio TEXT NOT NULL,
                data_fim TEXT NULL,
                cargo TEXT NOT NULL,
                em_exercicio INTEGER NOT NULL DEFAULT 0,
                descricao TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (pessoa_id) REFERENCES pessoas(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS fontes_dados (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                codigo TEXT NOT NULL UNIQUE,
                nome TEXT NOT NULL,
                orgao_responsavel TEXT NOT NULL,
                tipo_fonte TEXT NOT NULL,
                url_base TEXT NOT NULL,
                frequencia_atualizacao TEXT NOT NULL DEFAULT 'Diária',
                requer_autenticacao INTEGER NOT NULL DEFAULT 0,
                ativa INTEGER NOT NULL DEFAULT 1,
                ultima_coleta TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS importacoes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fonte_id INTEGER NOT NULL,
                tipo_registro TEXT NOT NULL,
                arquivo_origem TEXT NULL,
                hash_arquivo_origem TEXT NULL,
                total_lidos INTEGER NOT NULL DEFAULT 0,
                total_inseridos INTEGER NOT NULL DEFAULT 0,
                total_duplicados INTEGER NOT NULL DEFAULT 0,
                total_erros INTEGER NOT NULL DEFAULT 0,
                status TEXT NOT NULL DEFAULT 'PENDENTE',
                mensagem TEXT NULL,
                usuario_id INTEGER NULL,
                iniciado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                finalizado_em TEXT NULL,
                FOREIGN KEY (fonte_id) REFERENCES fontes_dados(id)
            )",
            "CREATE TABLE IF NOT EXISTS categorias_despesas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                codigo TEXT NOT NULL UNIQUE,
                descricao TEXT NULL,
                natureza_despesa TEXT NULL,
                icone TEXT NULL,
                cor TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS fornecedores (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                cnpj_cpf TEXT NOT NULL UNIQUE,
                tipo_documento TEXT NOT NULL DEFAULT 'CNPJ',
                razao_social TEXT NOT NULL,
                nome_fantasia TEXT NULL,
                cidade TEXT NULL,
                uf TEXT NULL,
                pais TEXT NOT NULL DEFAULT 'Brasil',
                situacao_cadastral TEXT NULL,
                total_recebido REAL NOT NULL DEFAULT 0.00,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS despesas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                identificador_origem TEXT NOT NULL,
                hash_registro TEXT NOT NULL UNIQUE,
                fonte_id INTEGER NOT NULL,
                importacao_id INTEGER NULL,
                categoria_id INTEGER NULL,
                fornecedor_id INTEGER NULL,
                orgao_superior TEXT NOT NULL DEFAULT 'Presidência da República',
                orgao_vinculado TEXT NOT NULL DEFAULT 'Presidência da República',
                unidade_gestora TEXT NULL,
                data_despesa TEXT NOT NULL,
                mes_ano TEXT NOT NULL,
                valor_nominal REAL NOT NULL,
                valor_corrigido_ipca REAL NULL,
                data_referencia_ipca TEXT NULL,
                fase_contabil TEXT NOT NULL DEFAULT 'PAGAMENTO',
                tipo_despesa TEXT NOT NULL DEFAULT 'Geral',
                descricao_original TEXT NOT NULL,
                numero_documento TEXT NULL,
                url_fonte_original TEXT NULL,
                tipo_cartao TEXT NULL,
                portador_cartao TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (fonte_id) REFERENCES fontes_dados(id),
                FOREIGN KEY (importacao_id) REFERENCES importacoes(id),
                FOREIGN KEY (categoria_id) REFERENCES categorias_despesas(id),
                FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
            )",
            "CREATE TABLE IF NOT EXISTS viagens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                identificador_origem TEXT NOT NULL UNIQUE,
                tipo_viagem TEXT NOT NULL,
                origem TEXT NULL,
                destino_cidade TEXT NOT NULL,
                destino_pais TEXT NOT NULL DEFAULT 'Brasil',
                data_inicio TEXT NOT NULL,
                data_fim TEXT NOT NULL,
                motivo_viagem TEXT NOT NULL,
                valor_total_diarias REAL NOT NULL DEFAULT 0.00,
                valor_total_passagens REAL NOT NULL DEFAULT 0.00,
                valor_outros_gastos REAL NOT NULL DEFAULT 0.00,
                valor_total_viagem REAL NOT NULL DEFAULT 0.00,
                status TEXT NOT NULL DEFAULT 'Realizada',
                url_oficial TEXT NULL,
                fonte_id INTEGER NOT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (fonte_id) REFERENCES fontes_dados(id)
            )",
            "CREATE TABLE IF NOT EXISTS viagem_participantes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                viagem_id INTEGER NOT NULL,
                pessoa_id INTEGER NULL,
                nome_participante TEXT NOT NULL,
                cargo_funcao TEXT NULL,
                orgao_origem TEXT NULL,
                valor_diarias REAL NOT NULL DEFAULT 0.00,
                valor_passagens REAL NOT NULL DEFAULT 0.00,
                numero_dias REAL NOT NULL DEFAULT 0.0,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (viagem_id) REFERENCES viagens(id) ON DELETE CASCADE,
                FOREIGN KEY (pessoa_id) REFERENCES pessoas(id)
            )",
            "CREATE TABLE IF NOT EXISTS vinculos_despesas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                despesa_id INTEGER NOT NULL,
                pessoa_id INTEGER NULL,
                viagem_id INTEGER NULL,
                tipo_vinculo TEXT NOT NULL DEFAULT 'NAO_IDENTIFICADO',
                grau_certeza TEXT NOT NULL DEFAULT 'EM_ANALISE',
                justificativa TEXT NOT NULL,
                documento_comprobatorio_url TEXT NULL,
                revisado_por_usuario_id INTEGER NULL,
                revisado_em TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (despesa_id) REFERENCES despesas(id) ON DELETE CASCADE,
                FOREIGN KEY (pessoa_id) REFERENCES pessoas(id),
                FOREIGN KEY (viagem_id) REFERENCES viagens(id)
            )",
            "CREATE TABLE IF NOT EXISTS contratos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                numero_contrato TEXT NOT NULL,
                processo_licitacao TEXT NULL,
                modalidade_licitacao TEXT NULL,
                fornecedor_id INTEGER NOT NULL,
                objeto TEXT NOT NULL,
                valor_inicial REAL NOT NULL,
                valor_atualizado REAL NOT NULL,
                data_assinatura TEXT NOT NULL,
                data_inicio_vigencia TEXT NOT NULL,
                data_fim_vigencia TEXT NOT NULL,
                situacao TEXT NOT NULL DEFAULT 'Ativo',
                orgao_responsavel TEXT NOT NULL DEFAULT 'Presidência da República',
                url_comprasnet TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
            )",
            "CREATE TABLE IF NOT EXISTS documentos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                titulo TEXT NOT NULL,
                tipo_documento TEXT NOT NULL,
                fonte_id INTEGER NOT NULL,
                url_original TEXT NOT NULL,
                caminho_arquivo_local TEXT NULL,
                hash_sha256 TEXT NOT NULL,
                data_publicacao_oficial TEXT NULL,
                data_coleta TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                descricao TEXT NULL,
                tamanho_bytes INTEGER NULL,
                despesa_id INTEGER NULL,
                viagem_id INTEGER NULL,
                contrato_id INTEGER NULL,
                is_publico INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (fonte_id) REFERENCES fontes_dados(id),
                FOREIGN KEY (despesa_id) REFERENCES despesas(id),
                FOREIGN KEY (viagem_id) REFERENCES viagens(id),
                FOREIGN KEY (contrato_id) REFERENCES contratos(id)
            )",
            "CREATE TABLE IF NOT EXISTS alertas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tipo_alerta TEXT NOT NULL,
                nivel TEXT NOT NULL DEFAULT 'INFORMATIVO',
                regra_acionada TEXT NOT NULL,
                calculo_realizado TEXT NOT NULL,
                dados_comparados_json TEXT NULL,
                despesa_id INTEGER NULL,
                viagem_id INTEGER NULL,
                fornecedor_id INTEGER NULL,
                justificativa TEXT NOT NULL,
                status_analise TEXT NOT NULL DEFAULT 'PENDENTE',
                analisado_por_usuario_id INTEGER NULL,
                notas_analista TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (despesa_id) REFERENCES despesas(id),
                FOREIGN KEY (viagem_id) REFERENCES viagens(id),
                FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
            )",
            "CREATE TABLE IF NOT EXISTS investigacoes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                codigo_dossie TEXT NOT NULL UNIQUE,
                titulo TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'ABERTA',
                fatos_comprovados TEXT NULL,
                hipoteses TEXT NULL,
                informacoes_nao_confirmadas TEXT NULL,
                pendencias TEXT NULL,
                conclusao TEXT NULL,
                is_publica INTEGER NOT NULL DEFAULT 0,
                criado_por_usuario_id INTEGER NOT NULL,
                responsavel_usuario_id INTEGER NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS historico_alteracoes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tabela_afetada TEXT NOT NULL,
                registro_id INTEGER NOT NULL,
                acao TEXT NOT NULL,
                dados_anteriores_json TEXT NULL,
                dados_novos_json TEXT NULL,
                usuario_id INTEGER NULL,
                ip_origem TEXT NULL,
                user_agent TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                senha_hash TEXT NOT NULL,
                perfil TEXT NOT NULL DEFAULT 'analista',
                ativo INTEGER NOT NULL DEFAULT 1,
                ultimo_login TEXT NULL,
                tentativas_falhas INTEGER NOT NULL DEFAULT 0,
                bloqueado_ate TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS configuracoes (
                chave TEXT PRIMARY KEY,
                valor TEXT NOT NULL,
                descricao TEXT NULL,
                grupo TEXT NOT NULL DEFAULT 'sistema',
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS tarefas_importacao (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tipo_tarefa TEXT NOT NULL,
                parametros_json TEXT NULL,
                status TEXT NOT NULL DEFAULT 'PENDENTE',
                agendada_para TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                executada_em TEXT NULL,
                resultado_resumo TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )"
        ];

        foreach ($statements as $stmt) {
            $this->pdo->exec($stmt);
        }
    }
}
