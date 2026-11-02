<?php
declare(strict_types=1);

namespace Database\Seeds;

use App\Repositories\Database;
use PDO;

class DatabaseSeeder {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function run(): void {
        $this->seedPessoasEMandatos();
        $this->seedFontesDados();
        $this->seedCategorias();
        $this->seedUsuarios();
        $this->seedConfiguracoes();
    }

    private function seedPessoasEMandatos(): void {
        // Lula
        $stmt = $this->pdo->prepare("SELECT id FROM pessoas WHERE slug = ?");
        $stmt->execute(['lula']);
        $lulaId = $stmt->fetchColumn();

        if (!$lulaId) {
            $insert = $this->pdo->prepare("
                INSERT INTO pessoas (slug, nome_completo, nome_publico, cargo_papel, biografia, data_nascimento, ativo)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            $insert->execute([
                'lula',
                'Luiz Inácio Lula da Silva',
                'Lula',
                'Presidente da República Federativa do Brasil',
                '35º e 39º Presidente da República do Brasil. Mandatos: 2003–2006, 2007–2010 e de 2023 até o presente.',
                '1945-10-27'
            ]);
            $lulaId = (int)$this->pdo->lastInsertId();
        }

        // Janja
        $stmt = $this->pdo->prepare("SELECT id FROM pessoas WHERE slug = ?");
        $stmt->execute(['janja']);
        $janjaId = $stmt->fetchColumn();

        if (!$janjaId) {
            $insert = $this->pdo->prepare("
                INSERT INTO pessoas (slug, nome_completo, nome_publico, cargo_papel, biografia, data_nascimento, ativo)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            $insert->execute([
                'janja',
                'Rosângela Lula da Silva',
                'Janja',
                'Primeira-dama do Brasil e Socióloga',
                'Socióloga brasileira e primeira-dama do Brasil desde 1º de janeiro de 2023, atuando em pautas sociais, culturais e representações públicas.',
                '1966-08-27'
            ]);
            $janjaId = (int)$this->pdo->lastInsertId();
        }

        // Mandatos Lula
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM mandatos WHERE pessoa_id = ?");
        $check->execute([$lulaId]);
        if ((int)$check->fetchColumn() === 0) {
            $mStmt = $this->pdo->prepare("
                INSERT INTO mandatos (pessoa_id, titulo, data_inicio, data_fim, cargo, em_exercicio, descricao)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $mStmt->execute([$lulaId, 'Mandato Presidencial I', '2003-01-01', '2006-12-31', 'Presidente da República', 0, 'Primeiro mandato presidencial (2003-2006)']);
            $mStmt->execute([$lulaId, 'Mandato Presidencial II', '2007-01-01', '2010-12-31', 'Presidente da República', 0, 'Segundo mandato presidencial (2007-2010)']);
            $mStmt->execute([$lulaId, 'Mandato Presidencial III', '2023-01-01', null, 'Presidente da República', 1, 'Terceiro mandato presidencial (2023-atualidade)']);
        }

        // Mandatos Janja
        $check->execute([$janjaId]);
        if ((int)$check->fetchColumn() === 0) {
            $mStmt = $this->pdo->prepare("
                INSERT INTO mandatos (pessoa_id, titulo, data_inicio, data_fim, cargo, em_exercicio, descricao)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $mStmt->execute([$janjaId, 'Atuação como Primeira-Dama', '2023-01-01', null, 'Primeira-Dama do Brasil', 1, 'Atividades públicas e institucionais a partir de 2023']);
        }
    }

    private function seedFontesDados(): void {
        $fontes = [
            ['cgu_api', 'API do Portal da Transparência (CGU)', 'Controladoria-Geral da União', 'API', 'https://api.portaldatransparencia.gov.br/api-de-dados', 'Diária', 1],
            ['cgu_csv', 'Download de Dados Abertos (CGU)', 'Controladoria-Geral da União', 'CSV', 'https://portaldatransparencia.gov.br/download-de-dados', 'Diária', 0],
            ['casa_civil_cpgf', 'Casa Civil — Cartões de Pagamento (CPGF)', 'Casa Civil da Presidência da República', 'PLANILHA', 'https://www.gov.br/casacivil/pt-br/acesso-a-informacao/informacoes-classificadas/despesas-com-cartao-de-pagamento', 'Mensal', 0],
            ['gsi_viagens', 'GSI — Diárias e Passagens', 'Gabinete de Segurança Institucional', 'PORTAL_PUBLICO', 'https://www.gov.br/gsi/pt-br/acesso-a-informacao/receitas-e-despesas/despesas-com-diarias-e-passagens', 'Mensal', 0],
            ['bcb_ipca', 'Banco Central do Brasil — Série Histórica IPCA (433)', 'Banco Central do Brasil', 'API', 'https://api.bcb.gov.br/dados/serie/bcdata.sgs.433/dados', 'Mensal', 0],
        ];

        $stmt = $this->pdo->prepare("
            INSERT OR IGNORE INTO fontes_dados (codigo, nome, orgao_responsavel, tipo_fonte, url_base, frequencia_atualizacao, requer_autenticacao, ativa)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ");
        // Em mysql INSERT IGNORE, em sqlite INSERT OR IGNORE
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sql = ($driver === 'sqlite')
            ? "INSERT OR IGNORE INTO fontes_dados (codigo, nome, orgao_responsavel, tipo_fonte, url_base, frequencia_atualizacao, requer_autenticacao, ativa) VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
            : "INSERT INTO fontes_dados (codigo, nome, orgao_responsavel, tipo_fonte, url_base, frequencia_atualizacao, requer_autenticacao, ativa) VALUES (?, ?, ?, ?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE nome=VALUES(nome)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($fontes as $f) {
            $stmt->execute($f);
        }
    }

    private function seedCategorias(): void {
        $cats = [
            ['cpgf_hospedagem', 'Hospedagem e Hotelaria', 'Gastos com acomodações e diárias de hospedagem em viagens institucionais', 'hotel', '#2563eb'],
            ['cpgf_alimentacao', 'Alimentação e Refeições', 'Gastos com fornecimento de alimentação institucional e eventos oficiais', 'utensils', '#16a34a'],
            ['cpgf_locacao', 'Locação de Veículos e Imóveis', 'Locação temporária de frotas e estruturas em viagens oficiais', 'car', '#ca8a04'],
            ['cpgf_combustivel', 'Combustíveis e Lubrificantes', 'Abastecimento terrestre e aéreo para deslocamentos de segurança e comitivas', 'fuel', '#ea580c'],
            ['cpgf_comunicacao', 'Comunicação e Tecnologia', 'Serviços de telefonia via satélite, link de dados e infraestrutura', 'wifi', '#9333ea'],
            ['cpgf_apoio', 'Apoio Operacional e Segurança', 'Despesas com apoio à segurança presidencial (GSI/PR)', 'shield', '#dc2626'],
            ['cpgf_passagens', 'Passagens e Deslocamento', 'Passagens comerciais e fretamentos autorizados', 'plane', '#0891b2'],
            ['cpgf_outros', 'Outros Serviços e Materiais', 'Materiais de consumo, taxas e serviços diversos devidamente descritos', 'receipt', '#64748b'],
        ];

        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sql = ($driver === 'sqlite')
            ? "INSERT OR IGNORE INTO categorias_despesas (codigo, nome, descricao, icone, cor) VALUES (?, ?, ?, ?, ?)"
            : "INSERT INTO categorias_despesas (codigo, nome, descricao, icone, cor) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE nome=VALUES(nome)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($cats as $c) {
            $stmt->execute([$c[0], $c[1], $c[2], $c[3], $c[4]]);
        }
    }

    private function seedUsuarios(): void {
        $users = [
            ['Administrador Geral', 'admin@observatorio.org', '$2y$12$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'admin'], // Admin@2026!
            ['Editor Chefe', 'editor@observatorio.org', '$2y$12$4mU3Z8p7JtWqm9bF6sJ1x.rZ.8o9lZ4N0WcTkmwK4aL9c8O8lU7eS', 'editor'], // Editor@2026!
            ['Analista de Dados', 'analista@observatorio.org', '$2y$12$6/Yd5B2a3d7M6sP5j7k0w.bB2r3h5K1l0P9o8uLOickgx2ZM7l17lh', 'analista'], // Analista@2026!
        ];

        // Usar password_hash seguro em tempo de execução para garantir hash perfeito
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sql = ($driver === 'sqlite')
            ? "INSERT OR IGNORE INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (?, ?, ?, ?, 1)"
            : "INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE nome=VALUES(nome)";
        $stmt = $this->pdo->prepare($sql);

        $defaultPasswords = [
            'admin@observatorio.org' => 'Admin@2026!',
            'editor@observatorio.org' => 'Editor@2026!',
            'analista@observatorio.org' => 'Analista@2026!',
        ];

        foreach ($users as $u) {
            $pass = $defaultPasswords[$u[1]];
            $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt->execute([$u[0], $u[1], $hash, $u[3]]);
        }
    }

    private function seedConfiguracoes(): void {
        $configs = [
            ['portal_titulo', 'Observatório da Presidência', 'Título principal do portal'],
            ['portal_slogan', 'O dinheiro é público. A informação também deve ser.', 'Slogan editorial'],
            ['ipca_mes_referencia', '2026-09', 'Mês base para correção de valores nominais via IPCA'],
            ['radar_limite_atípico_cpgf', '50000.00', 'Valor em reais para disparo de alerta atípico em cartões'],
            ['radar_fornecedor_recorrente_min', '5', 'Número de contratações no ano para alerta de fornecedor recorrente'],
        ];

        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sql = ($driver === 'sqlite')
            ? "INSERT OR REPLACE INTO configuracoes (chave, valor, descricao) VALUES (?, ?, ?)"
            : "INSERT INTO configuracoes (chave, valor, descricao) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE valor=VALUES(valor)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($configs as $c) {
            $stmt->execute($c);
        }
    }
}
