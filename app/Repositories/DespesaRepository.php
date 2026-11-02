<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class DespesaRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function search(array $filters = [], int $page = 1, int $perPage = 25): array {
        $where = [];
        $params = [];

        if (!empty($filters['termo'])) {
            $where[] = "(d.descricao_original LIKE ? OR f.razao_social LIKE ? OR d.portador_cartao LIKE ? OR d.numero_documento LIKE ?)";
            $term = '%' . $filters['termo'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['data_inicio'])) {
            $where[] = "d.data_despesa >= ?";
            $params[] = $filters['data_inicio'];
        }

        if (!empty($filters['data_fim'])) {
            $where[] = "d.data_despesa <= ?";
            $params[] = $filters['data_fim'];
        }

        if (!empty($filters['pessoa_id'])) {
            $where[] = "v.pessoa_id = ?";
            $params[] = (int)$filters['pessoa_id'];
        }

        if (!empty($filters['tipo_vinculo'])) {
            $where[] = "v.tipo_vinculo = ?";
            $params[] = $filters['tipo_vinculo'];
        }

        if (!empty($filters['categoria_id'])) {
            $where[] = "d.categoria_id = ?";
            $params[] = (int)$filters['categoria_id'];
        }

        if (!empty($filters['fornecedor_id'])) {
            $where[] = "d.fornecedor_id = ?";
            $params[] = (int)$filters['fornecedor_id'];
        }

        if (!empty($filters['valor_min'])) {
            $where[] = "d.valor_nominal >= ?";
            $params[] = (float)$filters['valor_min'];
        }

        if (!empty($filters['valor_max'])) {
            $where[] = "d.valor_nominal <= ?";
            $params[] = (float)$filters['valor_max'];
        }

        if (!empty($filters['fase_contabil'])) {
            $where[] = "d.fase_contabil = ?";
            $params[] = $filters['fase_contabil'];
        }

        if (!empty($filters['fonte_id'])) {
            $where[] = "d.fonte_id = ?";
            $params[] = (int)$filters['fonte_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Contar total
        $countSql = "
            SELECT COUNT(d.id)
            FROM despesas d
            LEFT JOIN vinculos_despesas v ON v.despesa_id = d.id
            LEFT JOIN fornecedores f ON f.id = d.fornecedor_id
            {$whereClause}
        ";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = (int)$countStmt->fetchColumn();

        $totalPages = (int)ceil($totalRecords / $perPage);
        $offset = ($page - 1) * $perPage;

        // Buscar registros
        $order = "ORDER BY d.data_despesa DESC, d.id DESC";
        $sql = "
            SELECT
                d.*,
                c.nome as categoria_nome, c.cor as categoria_cor, c.icone as categoria_icone,
                f.razao_social as fornecedor_nome, f.cnpj_cpf as fornecedor_documento,
                v.tipo_vinculo, v.grau_certeza, v.justificativa as vinculo_justificativa,
                p.nome_publico as pessoa_nome, p.slug as pessoa_slug,
                fontes.nome as fonte_nome
            FROM despesas d
            LEFT JOIN categorias_despesas c ON c.id = d.categoria_id
            LEFT JOIN fornecedores f ON f.id = d.fornecedor_id
            LEFT JOIN vinculos_despesas v ON v.despesa_id = d.id
            LEFT JOIN pessoas p ON p.id = v.pessoa_id
            LEFT JOIN fontes_dados fontes ON fontes.id = d.fonte_id
            {$whereClause}
            {$order}
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'total' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, $totalPages)
        ];
    }

    public function findById(int $id): ?array {
        $sql = "
            SELECT
                d.*,
                c.nome as categoria_nome, c.cor as categoria_cor,
                f.razao_social as fornecedor_nome, f.cnpj_cpf as fornecedor_documento, f.cidade as fornecedor_cidade, f.uf as fornecedor_uf,
                v.tipo_vinculo, v.grau_certeza, v.justificativa as vinculo_justificativa, v.id as vinculo_id,
                p.nome_publico as pessoa_nome, p.slug as pessoa_slug,
                fontes.nome as fonte_nome, fontes.url_base as fonte_url
            FROM despesas d
            LEFT JOIN categorias_despesas c ON c.id = d.categoria_id
            LEFT JOIN fornecedores f ON f.id = d.fornecedor_id
            LEFT JOIN vinculos_despesas v ON v.despesa_id = d.id
            LEFT JOIN pessoas p ON p.id = v.pessoa_id
            LEFT JOIN fontes_dados fontes ON fontes.id = d.fonte_id
            WHERE d.id = ?
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Retorna indicadores agregados do Dashboard
     */
    public function getDashboardMetrics(): array {
        // Totais gerais
        $totais = $this->pdo->query("
            SELECT
                COUNT(d.id) as total_despesas,
                COALESCE(SUM(d.valor_nominal), 0) as valor_total_nominal,
                COALESCE(SUM(d.valor_corrigido_ipca), 0) as valor_total_corrigido
            FROM despesas d
        ")->fetch();

        // Totais por tipo de vínculo
        $vinculos = $this->pdo->query("
            SELECT
                COALESCE(v.tipo_vinculo, 'NAO_IDENTIFICADO') as vinculo,
                COUNT(d.id) as qtd,
                COALESCE(SUM(d.valor_nominal), 0) as total_nominal,
                COALESCE(SUM(d.valor_corrigido_ipca), 0) as total_corrigido
            FROM despesas d
            LEFT JOIN vinculos_despesas v ON v.despesa_id = d.id
            GROUP BY vinculo
        ")->fetchAll();

        // Totais por pessoa (Lula vs Janja)
        $pessoas = $this->pdo->query("
            SELECT
                p.slug, p.nome_publico,
                COUNT(d.id) as qtd,
                COALESCE(SUM(d.valor_nominal), 0) as total_nominal,
                COALESCE(SUM(d.valor_corrigido_ipca), 0) as total_corrigido
            FROM pessoas p
            LEFT JOIN vinculos_despesas v ON v.pessoa_id = p.id
            LEFT JOIN despesas d ON d.id = v.despesa_id
            GROUP BY p.id, p.slug, p.nome_publico
        ")->fetchAll();

        // Categorias top
        $categorias = $this->pdo->query("
            SELECT
                c.nome, c.cor,
                COUNT(d.id) as qtd,
                COALESCE(SUM(d.valor_nominal), 0) as total_nominal
            FROM categorias_despesas c
            JOIN despesas d ON d.categoria_id = c.id
            GROUP BY c.id, c.nome, c.cor
            ORDER BY total_nominal DESC
            LIMIT 8
        ")->fetchAll();

        // Evolução anual
        $evolucaoAnual = $this->pdo->query("
            SELECT
                SUBSTR(d.data_despesa, 1, 4) as ano,
                COUNT(d.id) as qtd,
                COALESCE(SUM(d.valor_nominal), 0) as nominal,
                COALESCE(SUM(d.valor_corrigido_ipca), 0) as corrigido
            FROM despesas d
            GROUP BY ano
            ORDER BY ano ASC
        ")->fetchAll();

        // Viagens totais
        $viagens = $this->pdo->query("
            SELECT
                COUNT(id) as total_viagens,
                COALESCE(SUM(valor_total_diarias), 0) as total_diarias,
                COALESCE(SUM(valor_total_passagens), 0) as total_passagens,
                COALESCE(SUM(valor_total_viagem), 0) as total_viagens_valor,
                SUM(CASE WHEN tipo_viagem = 'INTERNACIONAL' THEN 1 ELSE 0 END) as viagens_internacionais,
                SUM(CASE WHEN tipo_viagem = 'NACIONAL' THEN 1 ELSE 0 END) as viagens_nacionais
            FROM viagens
        ")->fetch();

        // Alertas ativos
        $alertas = $this->pdo->query("
            SELECT
                COUNT(id) as total_alertas,
                SUM(CASE WHEN nivel = 'PRIORITARIO' THEN 1 ELSE 0 END) as prioritarios,
                SUM(CASE WHEN nivel = 'ATENCAO' THEN 1 ELSE 0 END) as atencao,
                SUM(CASE WHEN nivel = 'INFORMATIVO' THEN 1 ELSE 0 END) as informativos
            FROM alertas
            WHERE status_analise IN ('PENDENTE', 'EM_ANALISE')
        ")->fetch();

        // Última atualização
        $ultimaAtualizacao = $this->pdo->query("
            SELECT MAX(finalizado_em) as ultima_data FROM importacoes WHERE status = 'CONCLUIDO'
        ")->fetchColumn() ?: date('Y-m-d H:i:s');

        return [
            'totais' => $totais,
            'vinculos' => $vinculos,
            'pessoas' => $pessoas,
            'categorias' => $categorias,
            'evolucao_anual' => $evolucaoAnual,
            'viagens' => $viagens,
            'alertas' => $alertas,
            'ultima_atualizacao' => $ultimaAtualizacao
        ];
    }

    public function exportCsv(array $filters = []): void {
        $result = $this->search($filters, 1, 50000);
        $items = $result['items'];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="observatorio_despesas_' . date('Y-m-d_His') . '.csv"');

        $out = fopen('php://output', 'w');
        // BOM UTF-8 para compatibilidade nativa com Excel
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'Identificador',
            'Data',
            'Portador/Unidade',
            'Favorecido',
            'CNPJ/CPF',
            'Categoria',
            'Valor Nominal (R$)',
            'Valor Corrigido IPCA (R$)',
            'Vínculo Editorial',
            'Grau de Certeza',
            'Fase Contábil',
            'Fonte Primária',
            'Documento Original'
        ], ';');

        foreach ($items as $row) {
            fputcsv($out, [
                $row['identificador_origem'],
                $row['data_despesa'],
                $row['portador_cartao'] ?? $row['orgao_superior'],
                $row['fornecedor_nome'] ?? 'Não informado',
                $row['fornecedor_documento'] ?? '',
                $row['categoria_nome'] ?? 'Outros',
                number_format((float)$row['valor_nominal'], 2, ',', '.'),
                number_format((float)($row['valor_corrigido_ipca'] ?? $row['valor_nominal']), 2, ',', '.'),
                $row['tipo_vinculo'] ?? 'NAO_IDENTIFICADO',
                $row['grau_certeza'] ?? 'EM_ANALISE',
                $row['fase_contabil'],
                $row['fonte_nome'] ?? 'Portal da Transparência',
                $row['numero_documento'] ?? '-'
            ], ';');
        }

        fclose($out);
        exit;
    }
}
