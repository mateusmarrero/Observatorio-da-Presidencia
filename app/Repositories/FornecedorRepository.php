<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class FornecedorRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function search(array $filters = [], int $page = 1, int $perPage = 25): array {
        $where = [];
        $params = [];

        if (!empty($filters['termo'])) {
            $where[] = "(f.razao_social LIKE ? OR f.cnpj_cpf LIKE ? OR f.nome_fantasia LIKE ?)";
            $term = '%' . $filters['termo'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['uf'])) {
            $where[] = "f.uf = ?";
            $params[] = $filters['uf'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $countSql = "SELECT COUNT(id) FROM fornecedores f {$whereClause}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT
                f.*,
                (SELECT COUNT(d.id) FROM despesas d WHERE d.fornecedor_id = f.id) as total_transacoes,
                (SELECT COUNT(c.id) FROM contratos c WHERE c.fornecedor_id = f.id) as total_contratos
            FROM fornecedores f
            {$whereClause}
            ORDER BY f.total_recebido DESC, f.razao_social ASC
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

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM fornecedores WHERE id = ?");
        $stmt->execute([$id]);
        $fornecedor = $stmt->fetch();
        if (!$fornecedor) return null;

        // Despesas relacionadas
        $dStmt = $this->pdo->prepare("
            SELECT d.*, c.nome as categoria_nome, v.tipo_vinculo, p.nome_publico as pessoa_nome
            FROM despesas d
            LEFT JOIN categorias_despesas c ON c.id = d.categoria_id
            LEFT JOIN vinculos_despesas v ON v.despesa_id = d.id
            LEFT JOIN pessoas p ON p.id = v.pessoa_id
            WHERE d.fornecedor_id = ?
            ORDER BY d.data_despesa DESC
            LIMIT 50
        ");
        $dStmt->execute([$id]);
        $fornecedor['despesas'] = $dStmt->fetchAll();

        // Contratos relacionados
        $cStmt = $this->pdo->prepare("
            SELECT * FROM contratos WHERE fornecedor_id = ? ORDER BY data_assinatura DESC
        ");
        $cStmt->execute([$id]);
        $fornecedor['contratos'] = $cStmt->fetchAll();

        return $fornecedor;
    }
}
