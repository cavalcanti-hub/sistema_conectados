<?php

namespace App\Models;

use App\Config\Database;

class TermosCompraVendaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function baseQuery(array $filters, string $select = 't.*, u.nome AS usuario_nome'): array
    {
        $sql = "SELECT $select
                FROM termos_compra_venda t
                LEFT JOIN usuarios u ON t.usuario_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (
                t.numero_termo LIKE :search
                OR t.vendedor_nome LIKE :search
                OR t.vendedor_cpf LIKE :search
                OR t.marca_modelo LIKE :search
                OR t.imei1 LIKE :search
                OR t.imei2 LIKE :search
            )";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND t.data_entrada >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND t.data_entrada <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        return [$sql, $params];
    }

    public function getAll(array $filters = [], ?int $limit = null, int $offset = 0): array
    {
        [$sql, $params] = $this->baseQuery($filters);
        $sql .= " ORDER BY t.data_entrada DESC, t.id DESC";

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

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT t.*, u.nome AS usuario_nome
            FROM termos_compra_venda t
            LEFT JOIN usuarios u ON t.usuario_id = u.id
            WHERE t.id = :id
            LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO termos_compra_venda
            (numero_termo, vendedor_nome, vendedor_contato, vendedor_cpf, vendedor_rg, vendedor_endereco,
             data_entrada, equipamento_tipo, marca_modelo, imei1, imei2, senha_autorizada, chip_ssd_card,
             bateria, acessorios, estado_aparelho, valor_compra, comprador_nome, comprador_contato,
             comprador_documento, comprador_endereco, observacoes, usuario_id)
            VALUES
            (:numero_termo, :vendedor_nome, :vendedor_contato, :vendedor_cpf, :vendedor_rg, :vendedor_endereco,
             :data_entrada, :equipamento_tipo, :marca_modelo, :imei1, :imei2, :senha_autorizada, :chip_ssd_card,
             :bateria, :acessorios, :estado_aparelho, :valor_compra, :comprador_nome, :comprador_contato,
             :comprador_documento, :comprador_endereco, :observacoes, :usuario_id)");

        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM termos_compra_venda WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
