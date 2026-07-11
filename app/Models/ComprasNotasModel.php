<?php
namespace App\Models;

use App\Config\Database;

class ComprasNotasModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(array $filters = []): array
    {
        $sql = "SELECT n.*, f.nome AS fornecedor_nome, u.nome AS usuario_nome,
                    os.numero_os, c.nome AS os_cliente_nome,
                    COUNT(i.id) AS itens_total
                FROM compras_notas n
                LEFT JOIN fornecedores f ON f.id = n.fornecedor_id
                LEFT JOIN usuarios u ON u.id = n.usuario_id
                LEFT JOIN ordens_servico os ON os.id = n.os_id
                LEFT JOIN clientes c ON c.id = os.cliente_id
                LEFT JOIN compras_nota_itens i ON i.nota_id = n.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND n.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['fornecedor_id'])) {
            $sql .= " AND n.fornecedor_id = :fornecedor_id";
            $params[':fornecedor_id'] = (int) $filters['fornecedor_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (n.numero LIKE :search OR f.nome LIKE :search OR n.observacoes LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " GROUP BY n.id ORDER BY n.data_emissao DESC, n.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT n.*, f.nome AS fornecedor_nome, os.numero_os, c.nome AS os_cliente_nome
            FROM compras_notas n
            LEFT JOIN fornecedores f ON f.id = n.fornecedor_id
            LEFT JOIN ordens_servico os ON os.id = n.os_id
            LEFT JOIN clientes c ON c.id = os.cliente_id
            WHERE n.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $nota = $stmt->fetch();
        if (!$nota) {
            return null;
        }

        $nota['itens'] = $this->items($id);
        return $nota;
    }

    public function items(int $notaId): array
    {
        $stmt = $this->db->prepare("SELECT i.*, e.nome AS produto_nome, e.codigo_interno
            FROM compras_nota_itens i
            LEFT JOIN estoque e ON e.id = i.produto_id
            WHERE i.nota_id = :id
            ORDER BY i.id ASC");
        $stmt->execute([':id' => $notaId]);
        return $stmt->fetchAll();
    }

    public function create(array $nota, array $items): int
    {
        return $this->save(null, $nota, $items);
    }

    public function update(int $id, array $nota, array $items): int
    {
        return $this->save($id, $nota, $items);
    }

    private function save(?int $id, array $nota, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $normalizedItems = $this->normalizeItems($items);
            $total = array_reduce($normalizedItems, static fn(float $sum, array $item): float => $sum + (float) $item['total'], 0.0);
            $payload = [
                ':fornecedor_id' => !empty($nota['fornecedor_id']) ? (int) $nota['fornecedor_id'] : null,
                ':os_id' => !empty($nota['os_id']) ? (int) $nota['os_id'] : null,
                ':numero' => trim((string) ($nota['numero'] ?? '')) ?: null,
                ':data_emissao' => trim((string) ($nota['data_emissao'] ?? '')) ?: date('Y-m-d'),
                ':data_vencimento' => trim((string) ($nota['data_vencimento'] ?? '')) ?: null,
                ':valor_total' => round($total, 2),
                ':forma_pagamento' => trim((string) ($nota['forma_pagamento'] ?? '')) ?: null,
                ':observacoes' => trim((string) ($nota['observacoes'] ?? '')) ?: null,
                ':anexo_nome' => trim((string) ($nota['anexo_nome'] ?? '')) ?: null,
                ':anexo_original' => trim((string) ($nota['anexo_original'] ?? '')) ?: null,
                ':anexo_mime' => trim((string) ($nota['anexo_mime'] ?? '')) ?: null,
                ':usuario_id' => current_user_id(),
            ];

            if ($id === null) {
                $stmt = $this->db->prepare("INSERT INTO compras_notas
                    (fornecedor_id, os_id, numero, data_emissao, data_vencimento, valor_total, forma_pagamento, observacoes, anexo_nome, anexo_original, anexo_mime, usuario_id)
                    VALUES (:fornecedor_id, :os_id, :numero, :data_emissao, :data_vencimento, :valor_total, :forma_pagamento, :observacoes, :anexo_nome, :anexo_original, :anexo_mime, :usuario_id)");
                $stmt->execute($payload);
                $id = (int) $this->db->lastInsertId();
            } else {
                $current = $this->find($id);
                if (!$current || $current['status'] !== 'Aberta') {
                    throw new \RuntimeException('Apenas notas abertas podem ser editadas.');
                }
                if ($payload[':anexo_nome'] === null) {
                    $payload[':anexo_nome'] = $current['anexo_nome'] ?? null;
                    $payload[':anexo_original'] = $current['anexo_original'] ?? null;
                    $payload[':anexo_mime'] = $current['anexo_mime'] ?? null;
                }
                $payload[':id'] = $id;
                $stmt = $this->db->prepare("UPDATE compras_notas SET
                    fornecedor_id = :fornecedor_id,
                    os_id = :os_id,
                    numero = :numero,
                    data_emissao = :data_emissao,
                    data_vencimento = :data_vencimento,
                    valor_total = :valor_total,
                    forma_pagamento = :forma_pagamento,
                    observacoes = :observacoes,
                    anexo_nome = :anexo_nome,
                    anexo_original = :anexo_original,
                    anexo_mime = :anexo_mime,
                    usuario_id = :usuario_id
                    WHERE id = :id");
                $stmt->execute($payload);
                $this->db->prepare("DELETE FROM compras_nota_itens WHERE nota_id = :id")->execute([':id' => $id]);
            }

            $this->insertItems($id, $normalizedItems);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function baixar(int $id, ?int $osId = null): void
    {
        $this->db->beginTransaction();
        try {
            $nota = $this->find($id);
            if (!$nota) {
                throw new \RuntimeException('Nota nao encontrada.');
            }
            if ($nota['status'] !== 'Aberta') {
                throw new \RuntimeException('Esta nota ja foi baixada ou cancelada.');
            }
            if ($osId !== null && $osId > 0) {
                $this->db->prepare("UPDATE compras_notas SET os_id = :os_id WHERE id = :id")
                    ->execute([':os_id' => $osId, ':id' => $id]);
                $nota['os_id'] = $osId;
            }

            foreach ($nota['itens'] as $item) {
                $produtoId = (int) ($item['produto_id'] ?? 0);
                $quantity = max(1, (int) ($item['quantidade'] ?? 0));
                if ($produtoId > 0) {
                    $this->db->prepare("UPDATE estoque SET quantidade = quantidade + :qtd WHERE id = :id")
                        ->execute([':qtd' => $quantity, ':id' => $produtoId]);
                    $this->db->prepare("INSERT INTO estoque_movimentacoes (produto_id, tipo, quantidade, motivo, usuario_id)
                        VALUES (:produto_id, 'entrada', :quantidade, :motivo, :usuario_id)")
                        ->execute([
                            ':produto_id' => $produtoId,
                            ':quantidade' => $quantity,
                            ':motivo' => 'Baixa nota de compra ' . ($nota['numero'] ?: '#' . $id),
                            ':usuario_id' => current_user_id(),
                        ]);
                }
            }

            $financeiroId = null;
            if ((float) ($nota['valor_total'] ?? 0) > 0) {
                $financeiro = new FinanceiroModel();
                $financeiroId = (int) $financeiro->create([
                    ':tipo' => 'Despesa',
                    ':categoria' => 'Compra de Estoque',
                    ':descricao' => 'Nota de compra ' . ($nota['numero'] ?: '#' . $id) . ' - ' . ($nota['fornecedor_nome'] ?: 'Fornecedor nao informado'),
                    ':valor' => (float) $nota['valor_total'],
                    ':os_id' => !empty($nota['os_id']) ? (int) $nota['os_id'] : null,
                    ':usuario_id' => current_user_id(),
                    ':data_pagamento' => $nota['data_emissao'] ?: date('Y-m-d'),
                    ':forma_pagamento' => (string) ($nota['forma_pagamento'] ?? ''),
                ]);
            }

            $stmt = $this->db->prepare("UPDATE compras_notas
                SET status = 'Baixada', baixado_at = NOW(), financeiro_id = :financeiro_id
                WHERE id = :id");
            $stmt->execute([':financeiro_id' => $financeiroId, ':id' => $id]);

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function cancelar(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE compras_notas SET status = 'Cancelada' WHERE id = :id AND status = 'Aberta'");
        return $stmt->execute([':id' => $id]);
    }

    private function normalizeItems(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            $description = trim((string) ($item['descricao'] ?? ''));
            $produtoId = (int) ($item['produto_id'] ?? 0);
            if ($description === '' && $produtoId <= 0) {
                continue;
            }
            $quantity = max(1, (int) ($item['quantidade'] ?? 1));
            $unit = normalize_decimal_input($item['valor_unitario'] ?? 0);
            $normalized[] = [
                'produto_id' => $produtoId > 0 ? $produtoId : null,
                'descricao' => $description !== '' ? $description : 'Item vinculado ao estoque',
                'tipo' => in_array(($item['tipo'] ?? ''), ['peca', 'produto'], true) ? $item['tipo'] : 'peca',
                'quantidade' => $quantity,
                'valor_unitario' => $unit,
                'total' => round($quantity * $unit, 2),
            ];
        }

        if (empty($normalized)) {
            throw new \RuntimeException('Informe pelo menos um item na nota.');
        }

        return $normalized;
    }

    private function insertItems(int $notaId, array $items): void
    {
        $stmt = $this->db->prepare("INSERT INTO compras_nota_itens
            (nota_id, produto_id, descricao, tipo, quantidade, valor_unitario, total)
            VALUES (:nota_id, :produto_id, :descricao, :tipo, :quantidade, :valor_unitario, :total)");
        foreach ($items as $item) {
            $stmt->execute([
                ':nota_id' => $notaId,
                ':produto_id' => $item['produto_id'],
                ':descricao' => $item['descricao'],
                ':tipo' => $item['tipo'],
                ':quantidade' => $item['quantidade'],
                ':valor_unitario' => $item['valor_unitario'],
                ':total' => $item['total'],
            ]);
        }
    }
}
