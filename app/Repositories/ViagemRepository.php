<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class ViagemRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function search(array $filters = [], int $page = 1, int $perPage = 20): array {
        $where = [];
        $params = [];

        if (!empty($filters['termo'])) {
            $where[] = "(v.destino_cidade LIKE ? OR v.destino_pais LIKE ? OR v.motivo_viagem LIKE ? OR v.identificador_origem LIKE ?)";
            $term = '%' . $filters['termo'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['tipo_viagem'])) {
            $where[] = "v.tipo_viagem = ?";
            $params[] = $filters['tipo_viagem'];
        }

        if (!empty($filters['ano'])) {
            $where[] = "SUBSTR(v.data_inicio, 1, 4) = ?";
            $params[] = $filters['ano'];
        }

        if (!empty($filters['pessoa_id'])) {
            $where[] = "EXISTS (SELECT 1 FROM viagem_participantes vp WHERE vp.viagem_id = v.id AND vp.pessoa_id = ?)";
            $params[] = (int)$filters['pessoa_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Contagem
        $countSql = "SELECT COUNT(v.id) FROM viagens v {$whereClause}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        // Dados
        $sql = "
            SELECT
                v.*,
                fontes.nome as fonte_nome,
                (SELECT COUNT(id) FROM viagem_participantes vp WHERE vp.viagem_id = v.id) as total_participantes
            FROM viagens v
            LEFT JOIN fontes_dados fontes ON fontes.id = v.fonte_id
            {$whereClause}
            ORDER BY v.data_inicio DESC
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
            'total_pages' => max(1, $totalPages)
        ];
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT v.*, fontes.nome as fonte_nome
            FROM viagens v
            LEFT JOIN fontes_dados fontes ON fontes.id = v.fonte_id
            WHERE v.id = ?
        ");
        $stmt->execute([$id]);
        $viagem = $stmt->fetch();
        if (!$viagem) return null;

        // Buscar participantes
        $pStmt = $this->pdo->prepare("
            SELECT vp.*, p.nome_publico as pessoa_nome, p.slug as pessoa_slug
            FROM viagem_participantes vp
            LEFT JOIN pessoas p ON p.id = vp.pessoa_id
            WHERE vp.viagem_id = ?
            ORDER BY vp.valor_diarias DESC, vp.nome_participante ASC
        ");
        $pStmt->execute([$id]);
        $viagem['participantes'] = $pStmt->fetchAll();

        // Buscar despesas diretamente vinculadas à viagem
        $dStmt = $this->pdo->prepare("
            SELECT d.*, c.nome as categoria_nome, f.razao_social as fornecedor_nome
            FROM despesas d
            JOIN vinculos_despesas v ON v.despesa_id = d.id
            LEFT JOIN categorias_despesas c ON c.id = d.categoria_id
            LEFT JOIN fornecedores f ON f.id = d.fornecedor_id
            WHERE v.viagem_id = ?
            ORDER BY d.data_despesa ASC
        ");
        $dStmt->execute([$id]);
        $viagem['despesas_vinculadas'] = $dStmt->fetchAll();

        return $viagem;
    }

    public function getComitivas(int $page = 1, int $perPage = 25): array {
        $offset = ($page - 1) * $perPage;

        $count = $this->pdo->query("SELECT COUNT(id) FROM viagem_participantes")->fetchColumn();
        $total = (int)$count;

        $stmt = $this->pdo->query("
            SELECT
                vp.*,
                v.destino_cidade, v.destino_pais, v.tipo_viagem, v.data_inicio, v.data_fim,
                p.nome_publico as pessoa_nome, p.slug as pessoa_slug
            FROM viagem_participantes vp
            JOIN viagens v ON v.id = vp.viagem_id
            LEFT JOIN pessoas p ON p.id = vp.pessoa_id
            ORDER BY v.data_inicio DESC, vp.valor_diarias DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage)
        ];
    }
}
