<?php
namespace App\Models;

use App\Config\Database;

class RecadoModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function baseQuery(array $filters, string $select = 'r.*, u.nome AS usuario_nome'): array
    {
        $sql = "SELECT $select
                FROM recados r
                LEFT JOIN usuarios u ON r.usuario_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (r.nome LIKE :search OR r.telefone LIKE :search OR r.mensagem LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        return [$sql, $params];
    }

    public function getAll(array $filters = [], ?int $limit = null, int $offset = 0): array
    {
        [$sql, $params] = $this->baseQuery($filters);
        $sql .= " ORDER BY FIELD(r.status, 'Pendente', 'Lido', 'Respondido', 'Arquivado'),
                         r.data_recado DESC,
                         r.created_at DESC,
                         r.id DESC";

        if ($limit !== null) {
            $limit = max(1, (int) $limit);
            $offset = max(0, (int) $offset);
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(array $filters = []): int
    {
        [$sql, $params] = $this->baseQuery($filters, 'COUNT(*)');
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO recados
            (nome, telefone, mensagem, status, usuario_id, data_recado)
            VALUES (:nome, :telefone, :mensagem, :status, :usuario_id, :data_recado)");
        return $stmt->execute($data);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE recados SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM recados WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function resumoStatus(): array
    {
        $rows = $this->db->query("SELECT status, COUNT(*) AS total FROM recados GROUP BY status")->fetchAll();
        $summary = ['Pendente' => 0, 'Lido' => 0, 'Respondido' => 0, 'Arquivado' => 0];
        foreach ($rows as $row) {
            $summary[$row['status']] = (int) $row['total'];
        }
        return $summary;
    }
}
