<?php
namespace App\Models;

use App\Config\Database;

class GastosPessoaisModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function periodCondition(string $periodo): string
    {
        if ($periodo === 'dia') {
            return " AND data_lancamento = CURDATE()";
        }
        if ($periodo === 'semana') {
            return " AND YEARWEEK(data_lancamento, 1) = YEARWEEK(CURDATE(), 1)";
        }
        if ($periodo === 'mes') {
            return " AND MONTH(data_lancamento) = MONTH(CURDATE()) AND YEAR(data_lancamento) = YEAR(CURDATE())";
        }
        return '';
    }

    public function getLancamentos(string $periodo = 'mes', string $tipo = '', ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT gp.*, u.nome AS usuario_nome
                FROM gastos_pessoais gp
                LEFT JOIN usuarios u ON gp.usuario_id = u.id
                WHERE 1=1";
        $params = [];

        if ($tipo !== '') {
            $sql .= " AND gp.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $sql .= $this->periodCondition($periodo);
        $sql .= " ORDER BY gp.data_lancamento DESC, gp.created_at DESC, gp.id DESC";

        if ($limit !== null) {
            $limit = max(1, (int) $limit);
            $offset = max(0, (int) $offset);
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCategorias(): array
    {
        return $this->db->query("SELECT * FROM gastos_pessoais_categorias ORDER BY nome ASC")->fetchAll();
    }

    public function createCategoria(string $nome, string $tipo = 'Despesa'): bool
    {
        $nome = trim($nome);
        $tipo = in_array($tipo, ['Receita', 'Despesa', 'Ambos'], true) ? $tipo : 'Despesa';
        if ($nome === '') {
            return false;
        }

        $stmt = $this->db->prepare("INSERT IGNORE INTO gastos_pessoais_categorias (nome, tipo) VALUES (:nome, :tipo)");
        return $stmt->execute([':nome' => $nome, ':tipo' => $tipo]);
    }

    public function deleteCategoria(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM gastos_pessoais_categorias WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function countLancamentos(string $periodo = 'mes', string $tipo = ''): int
    {
        $sql = "SELECT COUNT(*) FROM gastos_pessoais WHERE 1=1";
        $params = [];

        if ($tipo !== '') {
            $sql .= " AND tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $sql .= $this->periodCondition($periodo);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function total(string $tipo, string $periodo = 'mes'): float
    {
        $where = "tipo = " . $this->db->quote($tipo) . $this->periodCondition($periodo);
        return (float) $this->db->query("SELECT COALESCE(SUM(valor), 0) FROM gastos_pessoais WHERE $where")->fetchColumn();
    }

    public function resumoCategorias(string $periodo = 'mes', string $tipo = 'Despesa'): array
    {
        $where = "tipo = " . $this->db->quote($tipo) . $this->periodCondition($periodo);
        return $this->db->query("SELECT COALESCE(NULLIF(categoria, ''), 'Sem categoria') AS categoria,
                    SUM(valor) AS total,
                    COUNT(*) AS qtd
                FROM gastos_pessoais
                WHERE $where
                GROUP BY COALESCE(NULLIF(categoria, ''), 'Sem categoria')
                ORDER BY total DESC
                LIMIT 6")->fetchAll();
    }

    public function resumoFormas(string $periodo = 'mes'): array
    {
        $where = "tipo = 'Despesa'" . $this->periodCondition($periodo);
        return $this->db->query("SELECT COALESCE(NULLIF(forma_pagamento, ''), 'Nao informado') AS forma_pagamento,
                    SUM(valor) AS total,
                    COUNT(*) AS qtd
                FROM gastos_pessoais
                WHERE $where
                GROUP BY COALESCE(NULLIF(forma_pagamento, ''), 'Nao informado')
                ORDER BY total DESC
                LIMIT 5")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM gastos_pessoais WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $this->createCategoria((string) ($data[':categoria'] ?? ''), (string) ($data[':tipo'] ?? 'Despesa'));
        $stmt = $this->db->prepare("INSERT INTO gastos_pessoais
            (tipo, categoria, descricao, valor, data_lancamento, forma_pagamento, recorrente, observacoes, usuario_id)
            VALUES (:tipo, :categoria, :descricao, :valor, :data_lancamento, :forma_pagamento, :recorrente, :observacoes, :usuario_id)");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->createCategoria((string) ($data[':categoria'] ?? ''), (string) ($data[':tipo'] ?? 'Despesa'));
        $data[':id'] = $id;
        $stmt = $this->db->prepare("UPDATE gastos_pessoais
            SET tipo = :tipo,
                categoria = :categoria,
                descricao = :descricao,
                valor = :valor,
                data_lancamento = :data_lancamento,
                forma_pagamento = :forma_pagamento,
                recorrente = :recorrente,
                observacoes = :observacoes
            WHERE id = :id");
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM gastos_pessoais WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
