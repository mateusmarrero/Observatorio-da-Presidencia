<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class DocumentoRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function search(array $filters = [], int $page = 1, int $perPage = 20): array {
        $where = [];
        $params = [];

        if (!empty($filters['termo'])) {
            $where[] = "(doc.titulo LIKE ? OR doc.descricao LIKE ? OR doc.hash_sha256 LIKE ?)";
            $term = '%' . $filters['termo'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['tipo'])) {
            $where[] = "doc.tipo_documento = ?";
            $params[] = $filters['tipo'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $countSql = "SELECT COUNT(id) FROM documentos doc {$whereClause}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT
                doc.*,
                fontes.nome as fonte_nome
            FROM documentos doc
            LEFT JOIN fontes_dados fontes ON fontes.id = doc.fonte_id
            {$whereClause}
            ORDER BY doc.data_coleta DESC
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
