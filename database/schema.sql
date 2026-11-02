-- Observatório da Presidência
-- Schema Oficial MySQL 8 (Compatível com UTF8MB4 e Integridade Referencial)

CREATE DATABASE IF NOT EXISTS `observatorio_presidencia`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `observatorio_presidencia`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Pessoas Monitoradas (Lula, Janja)
CREATE TABLE IF NOT EXISTS `pessoas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `nome_completo` VARCHAR(255) NOT NULL,
  `nome_publico` VARCHAR(100) NOT NULL,
  `cargo_papel` VARCHAR(100) NOT NULL,
  `biografia` TEXT NULL,
  `data_nascimento` DATE NULL,
  `foto_url` VARCHAR(255) NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_pessoas_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Mandatos e Períodos Institucionais
CREATE TABLE IF NOT EXISTS `mandatos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pessoa_id` INT UNSIGNED NOT NULL,
  `titulo` VARCHAR(150) NOT NULL,
  `data_inicio` DATE NOT NULL,
  `data_fim` DATE NULL,
  `cargo` VARCHAR(100) NOT NULL,
  `em_exercicio` TINYINT(1) NOT NULL DEFAULT 0,
  `descricao` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_mandatos_pessoa` FOREIGN KEY (`pessoa_id`) REFERENCES `pessoas` (`id`) ON DELETE CASCADE,
  INDEX `idx_mandatos_periodo` (`data_inicio`, `data_fim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Fontes Oficiais de Dados
CREATE TABLE IF NOT EXISTS `fontes_dados` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(50) NOT NULL UNIQUE,
  `nome` VARCHAR(255) NOT NULL,
  `orgao_responsavel` VARCHAR(255) NOT NULL,
  `tipo_fonte` ENUM('API', 'CSV', 'PORTAL_PUBLICO', 'PLANILHA', 'DIARIO_OFICIAL') NOT NULL,
  `url_base` VARCHAR(500) NOT NULL,
  `frequencia_atualizacao` VARCHAR(50) NOT NULL DEFAULT 'Diária',
  `requer_autenticacao` TINYINT(1) NOT NULL DEFAULT 0,
  `ativa` TINYINT(1) NOT NULL DEFAULT 1,
  `ultima_coleta` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_fontes_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Importações Realizadas
CREATE TABLE IF NOT EXISTS `importacoes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `fonte_id` INT UNSIGNED NOT NULL,
  `tipo_registro` VARCHAR(50) NOT NULL,
  `arquivo_origem` VARCHAR(255) NULL,
  `hash_arquivo_origem` VARCHAR(64) NULL,
  `total_lidos` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_inseridos` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_duplicados` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_erros` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('PENDENTE', 'PROCESSANDO', 'CONCLUIDO', 'FALHA') NOT NULL DEFAULT 'PENDENTE',
  `mensagem` TEXT NULL,
  `usuario_id` INT UNSIGNED NULL,
  `iniciado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finalizado_em` DATETIME NULL,
  CONSTRAINT `fk_importacoes_fonte` FOREIGN KEY (`fonte_id`) REFERENCES `fontes_dados` (`id`) ON DELETE RESTRICT,
  INDEX `idx_importacoes_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Categorias de Despesas
CREATE TABLE IF NOT EXISTS `categorias_despesas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(150) NOT NULL,
  `codigo` VARCHAR(50) NOT NULL UNIQUE,
  `descricao` TEXT NULL,
  `natureza_despesa` VARCHAR(100) NULL,
  `icone` VARCHAR(50) NULL,
  `cor` VARCHAR(20) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_categorias_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Fornecedores e Prestadores de Serviço
CREATE TABLE IF NOT EXISTS `fornecedores` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cnpj_cpf` VARCHAR(20) NOT NULL UNIQUE,
  `tipo_documento` ENUM('CNPJ', 'CPF', 'ESTRANGEIRO') NOT NULL DEFAULT 'CNPJ',
  `razao_social` VARCHAR(255) NOT NULL,
  `nome_fantasia` VARCHAR(255) NULL,
  `cidade` VARCHAR(100) NULL,
  `uf` VARCHAR(2) NULL,
  `pais` VARCHAR(50) NOT NULL DEFAULT 'Brasil',
  `situacao_cadastral` VARCHAR(50) NULL,
  `total_recebido` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_fornecedores_cnpj` (`cnpj_cpf`),
  INDEX `idx_fornecedores_razao` (`razao_social`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Despesas Públicas (Execução Orçamentária e CPGF)
CREATE TABLE IF NOT EXISTS `despesas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `identificador_origem` VARCHAR(100) NOT NULL,
  `hash_registro` VARCHAR(64) NOT NULL UNIQUE,
  `fonte_id` INT UNSIGNED NOT NULL,
  `importacao_id` INT UNSIGNED NULL,
  `categoria_id` INT UNSIGNED NULL,
  `fornecedor_id` INT UNSIGNED NULL,
  `orgao_superior` VARCHAR(255) NOT NULL DEFAULT 'Presidência da República',
  `orgao_vinculado` VARCHAR(255) NOT NULL DEFAULT 'Presidência da República',
  `unidade_gestora` VARCHAR(100) NULL,
  `data_despesa` DATE NOT NULL,
  `mes_ano` VARCHAR(7) NOT NULL,
  `valor_nominal` DECIMAL(15,2) NOT NULL,
  `valor_corrigido_ipca` DECIMAL(15,2) NULL,
  `data_referencia_ipca` DATE NULL,
  `fase_contabil` ENUM('EMPENHO', 'LIQUIDACAO', 'PAGAMENTO', 'CPGF') NOT NULL DEFAULT 'PAGAMENTO',
  `tipo_despesa` VARCHAR(100) NOT NULL DEFAULT 'Geral',
  `descricao_original` TEXT NOT NULL,
  `numero_documento` VARCHAR(100) NULL,
  `url_fonte_original` VARCHAR(500) NULL,
  `tipo_cartao` VARCHAR(100) NULL,
  `portador_cartao` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_despesas_fonte` FOREIGN KEY (`fonte_id`) REFERENCES `fontes_dados` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_despesas_importacao` FOREIGN KEY (`importacao_id`) REFERENCES `importacoes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_despesas_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_despesas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_despesas_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL,
  INDEX `idx_despesas_data` (`data_despesa`),
  INDEX `idx_despesas_mesano` (`mes_ano`),
  INDEX `idx_despesas_hash` (`hash_registro`),
  INDEX `idx_despesas_fase` (`fase_contabil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Viagens Oficiais
CREATE TABLE IF NOT EXISTS `viagens` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `identificador_origem` VARCHAR(100) NOT NULL UNIQUE,
  `tipo_viagem` ENUM('NACIONAL', 'INTERNACIONAL') NOT NULL,
  `origem` VARCHAR(150) NULL,
  `destino_cidade` VARCHAR(150) NOT NULL,
  `destino_pais` VARCHAR(100) NOT NULL DEFAULT 'Brasil',
  `data_inicio` DATE NOT NULL,
  `data_fim` DATE NOT NULL,
  `motivo_viagem` TEXT NOT NULL,
  `valor_total_diarias` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `valor_total_passagens` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `valor_outros_gastos` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `valor_total_viagem` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Realizada',
  `url_oficial` VARCHAR(500) NULL,
  `fonte_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_viagens_fonte` FOREIGN KEY (`fonte_id`) REFERENCES `fontes_dados` (`id`) ON DELETE RESTRICT,
  INDEX `idx_viagens_datas` (`data_inicio`, `data_fim`),
  INDEX `idx_viagens_tipo` (`tipo_viagem`),
  INDEX `idx_viagens_destino` (`destino_pais`, `destino_cidade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Participantes e Comitivas de Viagem
CREATE TABLE IF NOT EXISTS `viagem_participantes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `viagem_id` INT UNSIGNED NOT NULL,
  `pessoa_id` INT UNSIGNED NULL,
  `nome_participante` VARCHAR(255) NOT NULL,
  `cargo_funcao` VARCHAR(150) NULL,
  `orgao_origem` VARCHAR(150) NULL,
  `valor_diarias` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `valor_passagens` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `numero_dias` DECIMAL(5,1) NOT NULL DEFAULT 0.0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_participantes_viagem` FOREIGN KEY (`viagem_id`) REFERENCES `viagens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_participantes_pessoa` FOREIGN KEY (`pessoa_id`) REFERENCES `pessoas` (`id`) ON DELETE SET NULL,
  INDEX `idx_participantes_viagem` (`viagem_id`),
  INDEX `idx_participantes_pessoa` (`pessoa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Vínculos de Despesas (Diferenciação Estrita e Auditável)
CREATE TABLE IF NOT EXISTS `vinculos_despesas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `despesa_id` INT UNSIGNED NOT NULL,
  `pessoa_id` INT UNSIGNED NULL,
  `viagem_id` INT UNSIGNED NULL,
  `tipo_vinculo` ENUM(
      'DIRETA',
      'AGENDA_OFICIAL',
      'COMITIVA_VIAGEM',
      'INSTITUCIONAL',
      'NAO_IDENTIFICADO'
  ) NOT NULL DEFAULT 'NAO_IDENTIFICADO',
  `grau_certeza` ENUM('CONFIRMADO_DOCUMENTAL', 'INDICIARIO', 'EM_ANALISE') NOT NULL DEFAULT 'EM_ANALISE',
  `justificativa` TEXT NOT NULL,
  `documento_comprobatorio_url` VARCHAR(500) NULL,
  `revisado_por_usuario_id` INT UNSIGNED NULL,
  `revisado_em` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_vinculos_despesa` FOREIGN KEY (`despesa_id`) REFERENCES `despesas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vinculos_pessoa` FOREIGN KEY (`pessoa_id`) REFERENCES `pessoas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_vinculos_viagem` FOREIGN KEY (`viagem_id`) REFERENCES `viagens` (`id`) ON DELETE SET NULL,
  INDEX `idx_vinculos_tipo` (`tipo_vinculo`),
  INDEX `idx_vinculos_pessoa` (`pessoa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Contratos Administrativos
CREATE TABLE IF NOT EXISTS `contratos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `numero_contrato` VARCHAR(100) NOT NULL,
  `processo_licitacao` VARCHAR(100) NULL,
  `modalidade_licitacao` VARCHAR(100) NULL,
  `fornecedor_id` INT UNSIGNED NOT NULL,
  `objeto` TEXT NOT NULL,
  `valor_inicial` DECIMAL(15,2) NOT NULL,
  `valor_atualizado` DECIMAL(15,2) NOT NULL,
  `data_assinatura` DATE NOT NULL,
  `data_inicio_vigencia` DATE NOT NULL,
  `data_fim_vigencia` DATE NOT NULL,
  `situacao` VARCHAR(50) NOT NULL DEFAULT 'Ativo',
  `orgao_responsavel` VARCHAR(255) NOT NULL DEFAULT 'Presidência da República',
  `url_comprasnet` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_contratos_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE RESTRICT,
  INDEX `idx_contratos_numero` (`numero_contrato`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Biblioteca Documental Oficial
CREATE TABLE IF NOT EXISTS `documentos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(255) NOT NULL,
  `tipo_documento` ENUM('PDF', 'CSV', 'JSON', 'XLSX', 'LINK_GOV', 'PORTARIA', 'NOTA_FISCAL') NOT NULL,
  `fonte_id` INT UNSIGNED NOT NULL,
  `url_original` VARCHAR(500) NOT NULL,
  `caminho_arquivo_local` VARCHAR(255) NULL,
  `hash_sha256` VARCHAR(64) NOT NULL,
  `data_publicacao_oficial` DATE NULL,
  `data_coleta` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `descricao` TEXT NULL,
  `tamanho_bytes` BIGINT UNSIGNED NULL,
  `despesa_id` INT UNSIGNED NULL,
  `viagem_id` INT UNSIGNED NULL,
  `contrato_id` INT UNSIGNED NULL,
  `is_publico` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_documentos_fonte` FOREIGN KEY (`fonte_id`) REFERENCES `fontes_dados` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_documentos_despesa` FOREIGN KEY (`despesa_id`) REFERENCES `despesas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_documentos_viagem` FOREIGN KEY (`viagem_id`) REFERENCES `viagens` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_documentos_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`) ON DELETE SET NULL,
  INDEX `idx_documentos_hash` (`hash_sha256`),
  INDEX `idx_documentos_tipo` (`tipo_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Radar Investigativo (Alertas Automáticos de Anomalias)
CREATE TABLE IF NOT EXISTS `alertas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tipo_alerta` VARCHAR(100) NOT NULL,
  `nivel` ENUM('INFORMATIVO', 'ATENCAO', 'PRIORITARIO') NOT NULL DEFAULT 'INFORMATIVO',
  `regra_acionada` VARCHAR(255) NOT NULL,
  `calculo_realizado` TEXT NOT NULL,
  `dados_comparados_json` JSON NULL,
  `despesa_id` INT UNSIGNED NULL,
  `viagem_id` INT UNSIGNED NULL,
  `fornecedor_id` INT UNSIGNED NULL,
  `justificativa` TEXT NOT NULL,
  `status_analise` ENUM('PENDENTE', 'EM_ANALISE', 'JUSTIFICADO', 'CONFIRMADO_INCONSISTENTE') NOT NULL DEFAULT 'PENDENTE',
  `analisado_por_usuario_id` INT UNSIGNED NULL,
  `notas_analista` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_alertas_despesa` FOREIGN KEY (`despesa_id`) REFERENCES `despesas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_alertas_viagem` FOREIGN KEY (`viagem_id`) REFERENCES `viagens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_alertas_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE,
  INDEX `idx_alertas_nivel` (`nivel`),
  INDEX `idx_alertas_status` (`status_analise`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Central de Investigação Documental (Dossiês Internos)
CREATE TABLE IF NOT EXISTS `investigacoes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `codigo_dossie` VARCHAR(50) NOT NULL UNIQUE,
  `titulo` VARCHAR(255) NOT NULL,
  `status` ENUM('ABERTA', 'EM_APURACAO', 'CONCLUIDA', 'ARQUIVADA') NOT NULL DEFAULT 'ABERTA',
  `fatos_comprovados` TEXT NULL,
  `hipoteses` TEXT NULL,
  `informacoes_nao_confirmadas` TEXT NULL,
  `pendencias` TEXT NULL,
  `conclusao` TEXT NULL,
  `is_publica` TINYINT(1) NOT NULL DEFAULT 0,
  `criado_por_usuario_id` INT UNSIGNED NOT NULL,
  `responsavel_usuario_id` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_investigacoes_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Histórico e Trilha de Auditoria (Logs Imutáveis)
CREATE TABLE IF NOT EXISTS `historico_alteracoes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tabela_afetada` VARCHAR(100) NOT NULL,
  `registro_id` INT UNSIGNED NOT NULL,
  `acao` ENUM('INSERT', 'UPDATE', 'DELETE', 'REVISAO_VINCULO', 'LOGIN', 'IMPORTACAO') NOT NULL,
  `dados_anteriores_json` JSON NULL,
  `dados_novos_json` JSON NULL,
  `usuario_id` INT UNSIGNED NULL,
  `ip_origem` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_historico_tabela_reg` (`tabela_afetada`, `registro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Usuários do Sistema e RBAC
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `senha_hash` VARCHAR(255) NOT NULL,
  `perfil` ENUM('admin', 'editor', 'analista') NOT NULL DEFAULT 'analista',
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `ultimo_login` DATETIME NULL,
  `tentativas_falhas` INT UNSIGNED NOT NULL DEFAULT 0,
  `bloqueado_ate` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_usuarios_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Configurações Globais do Portal
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `chave` VARCHAR(100) PRIMARY KEY,
  `valor` TEXT NOT NULL,
  `descricao` VARCHAR(255) NULL,
  `grupo` VARCHAR(50) NOT NULL DEFAULT 'sistema',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Fila de Tarefas de Importação e Agendamentos
CREATE TABLE IF NOT EXISTS `tarefas_importacao` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tipo_tarefa` VARCHAR(100) NOT NULL,
  `parametros_json` JSON NULL,
  `status` ENUM('PENDENTE', 'EXECUTANDO', 'CONCLUIDA', 'ERRO') NOT NULL DEFAULT 'PENDENTE',
  `agendada_para` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `executada_em` DATETIME NULL,
  `resultado_resumo` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_tarefas_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
