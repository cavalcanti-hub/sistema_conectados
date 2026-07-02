<?php
namespace App\Models;

use App\Config\Database;

class ComprasModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function baseQuery(array $filters, string $select = 'cs.*, u.nome AS usuario_nome'): array
    {
        $sql = "SELECT $select
                FROM compras_solicitacoes cs
                LEFT JOIN usuarios u ON cs.usuario_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND cs.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['tipo'])) {
            $sql .= " AND cs.tipo = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (cs.item_nome LIKE :search OR cs.fornecedor LIKE :search OR cs.observacoes LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        return [$sql, $params];
    }

    public function getAll(array $filters = [], ?int $limit = null, int $offset = 0): array
    {
        [$sql, $params] = $this->baseQuery($filters);
        $sql .= " ORDER BY FIELD(cs.status, 'Pendente', 'Solicitado', 'Comprado', 'Recebido', 'Cancelado'),
                         FIELD(cs.prioridade, 'Urgente', 'Alta', 'Normal', 'Baixa'),
                         cs.data_solicitacao DESC,
                         cs.id DESC";

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
        $stmt = $this->db->prepare("INSERT INTO compras_solicitacoes
            (item_nome, tipo, quantidade, fornecedor, prioridade, status, observacoes, usuario_id, data_solicitacao)
            VALUES (:item_nome, :tipo, :quantidade, :fornecedor, :prioridade, :status, :observacoes, :usuario_id, :data_solicitacao)");
        return $stmt->execute($data);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE compras_solicitacoes SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM compras_solicitacoes WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function resumoStatus(): array
    {
        $rows = $this->db->query("SELECT status, COUNT(*) AS total FROM compras_solicitacoes GROUP BY status")->fetchAll();
        $summary = ['Pendente' => 0, 'Solicitado' => 0, 'Comprado' => 0, 'Recebido' => 0, 'Cancelado' => 0];
        foreach ($rows as $row) {
            $summary[$row['status']] = (int) $row['total'];
        }
        return $summary;
    }
}
