<?php
namespace App\Models;

use App\Config\Database;

class FornecedoresModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(string $search = '', bool $onlyActive = false): array
    {
        $sql = "SELECT f.*,
                    COUNT(n.id) AS notas_total,
                    COALESCE(SUM(CASE WHEN n.status <> 'Cancelada' THEN n.valor_total ELSE 0 END), 0) AS valor_total
                FROM fornecedores f
                LEFT JOIN compras_notas n ON n.fornecedor_id = f.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (f.nome LIKE :search OR f.documento LIKE :search OR f.telefone LIKE :search OR f.email LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($onlyActive) {
            $sql .= " AND f.ativo = 1";
        }

        $sql .= " GROUP BY f.id ORDER BY f.ativo DESC, f.nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM fornecedores WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO fornecedores
            (nome, documento, telefone, email, endereco, observacoes, ativo)
            VALUES (:nome, :documento, :telefone, :email, :endereco, :observacoes, :ativo)");
        $stmt->execute($this->payload($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $payload = $this->payload($data);
        $payload[':id'] = $id;
        $stmt = $this->db->prepare("UPDATE fornecedores SET
            nome = :nome,
            documento = :documento,
            telefone = :telefone,
            email = :email,
            endereco = :endereco,
            observacoes = :observacoes,
            ativo = :ativo
            WHERE id = :id");
        return $stmt->execute($payload);
    }

    private function payload(array $data): array
    {
        return [
            ':nome' => trim((string) ($data['nome'] ?? '')),
            ':documento' => trim((string) ($data['documento'] ?? '')) ?: null,
            ':telefone' => trim((string) ($data['telefone'] ?? '')) ?: null,
            ':email' => trim((string) ($data['email'] ?? '')) ?: null,
            ':endereco' => trim((string) ($data['endereco'] ?? '')) ?: null,
            ':observacoes' => trim((string) ($data['observacoes'] ?? '')) ?: null,
            ':ativo' => !empty($data['ativo']) ? 1 : 0,
        ];
    }
}
