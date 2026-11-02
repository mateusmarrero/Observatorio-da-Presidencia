<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class ContratoRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function search(array $filters = [], int $page = 1, int $perPage = 20): array {
        $where = [];
        $params = [];

        if (!empty($filters['termo'])) {
            $where[] = "(c.numero_contrato LIKE ? OR c.objeto LIKE ? OR f.razao_social LIKE ?)";
            $term = '%' . $filters['termo'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['situacao'])) {
            $where[] = "c.situacao = ?";
            $params[] = $filters['situacao'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $countSql = "
            SELECT COUNT(c.id)
            FROM contratos c
            JOIN fornecedores f ON f.id = c.fornecedor_id
            {$whereClause}
        ";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT
                c.*,
                f.razao_social as fornecedor_nome, f.cnpj_cpf as fornecedor_documento
            FROM contratos c
            JOIN fornecedores f ON f.id = c.fornecedor_id
            {$whereClause}
            ORDER BY c.data_assinatura DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int)ceil($total / $perPage))
        ];
    }
}
