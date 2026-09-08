<?php
namespace App\Models;
use App\Config\Database;

class FinanceiroModel {
    private \PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    private function normalizePaymentMethod(string $method): string {
        $value = function_exists('mb_strtolower') ? mb_strtolower($method, 'UTF-8') : strtolower($method);
        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if (is_string($ascii) && $ascii !== '') {
                $value = $ascii;
            }
        }

        return strtolower(str_replace(['ã©', 'ãƒâ©', 'Ã©', 'ÃƒÂ©'], 'e', $value));
    }

    private function cardFeePercent(string $method, array $settings): float {
        $method = $this->normalizePaymentMethod($method);
        if (str_contains($method, 'debito') || str_contains($method, 'qr') || str_contains($method, 'saldo mercado')) {
            $point = normalize_decimal_input($settings['taxa_point_debito_qr_saldo'] ?? '');
            return $point > 0 ? $point : normalize_decimal_input($settings['taxa_cartao_debito'] ?? 0);
        }

        if (str_contains($method, 'credito') || preg_match('/(^|\s|-)cr($|\s|-)/', $method)) {
            if (str_contains($method, '30')) {
                $base = normalize_decimal_input($settings['taxa_point_credito_30d'] ?? 0);
            } elseif (str_contains($method, '14')) {
                $base = normalize_decimal_input($settings['taxa_point_credito_14d'] ?? 0);
            } else {
                $base = normalize_decimal_input($settings['taxa_point_credito_hora'] ?? 0);
            }
            if ($base <= 0) {
                $base = normalize_decimal_input($settings['taxa_cartao_credito'] ?? 0);
            }

            $installmentFee = 0.0;
            if (preg_match('/\b([2-9]|1[0-2])x\b/', $method, $matches)) {
                $installmentFee = normalize_decimal_input($settings['taxa_point_parcelamento_' . (int) $matches[1] . 'x'] ?? 0);
            }

            return $base + $installmentFee;
        }

        return 0.0;
    }

    private function cardFeeDescriptionForRevenue(array $revenue, float $percent): array {
        $descricao = (string) ($revenue['descricao'] ?? '');
        if (preg_match('/(PDV-\d{8}-\d+)/', $descricao, $matches)) {
            return [
                'lookup' => '%venda ' . $matches[1] . '%',
                'text' => 'Taxa da maquininha - venda ' . $matches[1] . ' (' . number_format($percent, 2, ',', '.') . '%)',
            ];
        }

        $id = (int) ($revenue['id'] ?? 0);
        return [
            'lookup' => '%lancamento financeiro #' . $id . '%',
            'text' => 'Taxa da maquininha - lancamento financeiro #' . $id . ' (' . number_format($percent, 2, ',', '.') . '%)',
        ];
    }

    private function periodCondition(string $periodo, string $alias = ''): string {
        $field = ($alias !== '' ? $alias . '.' : '') . 'data_pagamento';
        $created = ($alias !== '' ? $alias . '.' : '') . 'created_at';
        $dateExpr = "COALESCE($field, DATE($created))";

        if ($periodo === 'dia') return " AND $dateExpr = CURDATE()";
        if ($periodo === 'semana') return " AND YEARWEEK($dateExpr, 1) = YEARWEEK(CURDATE(), 1)";
        if ($periodo === 'mes') return " AND MONTH($dateExpr) = MONTH(CURDATE()) AND YEAR($dateExpr) = YEAR(CURDATE())";
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodo)) {
            return " AND $dateExpr = " . $this->db->quote($periodo);
        }
        return '';
    }

    public function getMovimentacoes($tipo = '', $periodo = 'dia', ?int $limit = null, int $offset = 0) {
        $sql = "SELECT f.*, u.nome as usuario_nome FROM financeiro f LEFT JOIN usuarios u ON f.usuario_id = u.id WHERE 1=1";
        $params = [];
        if ($tipo) { $sql .= " AND f.tipo = :tipo"; $params[':tipo'] = $tipo; }
        $sql .= $this->periodCondition($periodo, 'f');
        $sql .= " ORDER BY COALESCE(f.data_pagamento, DATE(f.created_at)) DESC, f.created_at DESC";
        if ($limit !== null) {
            $limit = max(1, (int) $limit);
            $offset = max(0, (int) $offset);
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countMovimentacoes($tipo = '', $periodo = 'dia'): int {
        $sql = "SELECT COUNT(*) FROM financeiro f WHERE 1=1";
        $params = [];
        if ($tipo) { $sql .= " AND f.tipo = :tipo"; $params[':tipo'] = $tipo; }
        $sql .= $this->periodCondition($periodo, 'f');
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data) {
        $sql = "INSERT INTO financeiro (tipo, categoria, descricao, valor, os_id, usuario_id, data_pagamento, forma_pagamento) VALUES (:tipo, :categoria, :descricao, :valor, :os_id, :usuario_id, :data_pagamento, :forma_pagamento)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function findAutoEntryForOs(int $osId, string $tipo, string $categoria): ?array {
        if ($osId <= 0 || $tipo === '' || $categoria === '') {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM financeiro WHERE os_id = :os_id AND tipo = :tipo AND categoria = :categoria ORDER BY id DESC LIMIT 1");
        $stmt->execute([
            ':os_id' => $osId,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function syncAutoEntryForOs(int $osId, array $data): int {
        $existing = $this->findAutoEntryForOs($osId, (string) ($data[':tipo'] ?? ''), (string) ($data[':categoria'] ?? ''));
        if ($existing) {
            $this->update((int) $existing['id'], [
                ':tipo' => $data[':tipo'],
                ':categoria' => $data[':categoria'],
                ':descricao' => $data[':descricao'],
                ':valor' => $data[':valor'],
                ':os_id' => $data[':os_id'],
                ':data_pagamento' => $data[':data_pagamento'],
                ':forma_pagamento' => $data[':forma_pagamento'],
            ]);
            return (int) $existing['id'];
        }

        return (int) $this->create($data);
    }

    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM financeiro WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function cardFeeReference(array $fee): ?array {
        if (($fee['tipo'] ?? '') !== 'Despesa' || ($fee['categoria'] ?? '') !== 'Taxa Maquininha') {
            return null;
        }

        $descricao = (string) ($fee['descricao'] ?? '');
        $origin = null;
        $label = 'Lancamento financeiro';

        if (preg_match('/(PDV-\d{8}-\d+)/', $descricao, $matches)) {
            $label = 'Venda PDV ' . $matches[1];
            $stmt = $this->db->prepare("SELECT * FROM financeiro
                WHERE tipo = 'Receita' AND descricao LIKE :descricao
                ORDER BY id DESC LIMIT 1");
            $stmt->execute([':descricao' => '%' . $matches[1] . '%']);
            $origin = $stmt->fetch() ?: null;
        } elseif (preg_match('/lancamento financeiro #(\d+)/i', $descricao, $matches)) {
            $origin = $this->find((int) $matches[1]);
            if ($origin) {
                $label = 'Lancamento financeiro #' . (int) $matches[1];
            }
        }

        if (!$origin) {
            return [
                'label' => 'Referencia nao localizada',
                'descricao' => 'A taxa foi registrada, mas o lancamento original nao foi encontrado.',
                'valor' => null,
                'forma_pagamento' => '',
                'data_pagamento' => '',
                'os_id' => null,
            ];
        }

        return [
            'label' => $label,
            'descricao' => (string) ($origin['descricao'] ?? ''),
            'valor' => (float) ($origin['valor'] ?? 0),
            'forma_pagamento' => (string) ($origin['forma_pagamento'] ?? ''),
            'data_pagamento' => (string) (($origin['data_pagamento'] ?? '') ?: ($origin['created_at'] ?? '')),
            'os_id' => $origin['os_id'] ?? null,
        ];
    }

    public function syncCardFeeForRevenue(int $revenueId, ?array $settings = null): void {
        $revenue = $this->find($revenueId);
        if (!$revenue || ($revenue['tipo'] ?? '') !== 'Receita' || ($revenue['categoria'] ?? '') === 'Taxa Maquininha') {
            $this->deleteCardFeeForRevenue($revenueId);
            return;
        }

        // Point handles its own real fees via API webhooks
        if (str_contains((string) ($revenue['forma_pagamento'] ?? ''), 'Mercado Pago Point')) {
            return;
        }

        $settings = $settings ?? (new ConfigModel())->getAll();
        $percent = $this->cardFeePercent((string) ($revenue['forma_pagamento'] ?? ''), $settings);
        $fee = $this->cardFeeDescriptionForRevenue($revenue, $percent);
        $feeValue = round((float) ($revenue['valor'] ?? 0) * ($percent / 100), 2);

        $stmt = $this->db->prepare("SELECT id FROM financeiro
            WHERE tipo = 'Despesa'
              AND categoria = 'Taxa Maquininha'
              AND (descricao LIKE :lookup OR descricao LIKE :legacy_lookup)
            ORDER BY id ASC");
        $stmt->execute([
            ':lookup' => $fee['lookup'],
            ':legacy_lookup' => '%lancamento financeiro #' . $revenueId . '%',
        ]);
        $feeIds = array_map('intval', array_column($stmt->fetchAll(), 'id'));

        if ($percent <= 0 || $feeValue <= 0) {
            foreach ($feeIds as $feeId) {
                $this->delete($feeId);
            }
            return;
        }

        $payload = [
            ':tipo' => 'Despesa',
            ':categoria' => 'Taxa Maquininha',
            ':descricao' => $fee['text'],
            ':valor' => $feeValue,
            ':os_id' => $revenue['os_id'] ?: null,
            ':data_pagamento' => $revenue['data_pagamento'] ?: date('Y-m-d', strtotime((string) $revenue['created_at'])),
            ':forma_pagamento' => (string) ($revenue['forma_pagamento'] ?? ''),
        ];

        if (!empty($feeIds)) {
            $this->update($feeIds[0], $payload);
            foreach (array_slice($feeIds, 1) as $duplicateId) {
                $this->delete($duplicateId);
            }
            return;
        }

        $payload[':usuario_id'] = $revenue['usuario_id'] ?: current_user_id();
        $this->create($payload);
    }

    public function deleteCardFeeForRevenue(int $revenueId): void {
        $stmt = $this->db->prepare("SELECT id FROM financeiro
            WHERE tipo = 'Despesa'
              AND categoria = 'Taxa Maquininha'
              AND (descricao LIKE :lookup OR descricao LIKE :legacy_lookup)");
        $stmt->execute([
            ':lookup' => '%lancamento financeiro #' . $revenueId . '%',
            ':legacy_lookup' => '%lancamento financeiro #' . $revenueId . '%',
        ]);
        foreach ($stmt->fetchAll() as $row) {
            $this->delete((int) $row['id']);
        }
    }

    public function update(int $id, array $data): bool {
        $data[':id'] = $id;
        $sql = "UPDATE financeiro
                SET tipo = :tipo,
                    categoria = :categoria,
                    descricao = :descricao,
                    valor = :valor,
                    os_id = :os_id,
                    data_pagamento = :data_pagamento,
                    forma_pagamento = :forma_pagamento
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM financeiro WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function totalReceitas($periodo = 'dia') {
        $where = "tipo = 'Receita'";
        $where .= $this->periodCondition($periodo);
        return $this->db->query("SELECT COALESCE(SUM(valor),0) FROM financeiro WHERE $where")->fetchColumn();
    }

    public function totalDespesas($periodo = 'dia') {
        $where = "tipo = 'Despesa'";
        $where .= $this->periodCondition($periodo);
        return $this->db->query("SELECT COALESCE(SUM(valor),0) FROM financeiro WHERE $where")->fetchColumn();
    }

    public function getFormasPagamento($periodo = 'dia') {
        $where = "tipo = 'Receita'" . $this->periodCondition($periodo);
        return $this->db->query("SELECT forma_pagamento, SUM(valor) as total, COUNT(*) as qtd FROM financeiro WHERE $where GROUP BY forma_pagamento ORDER BY total DESC")->fetchAll();
    }

    public function getResumoCategorias($periodo = 'dia', $tipo = 'Despesa') {
        $where = "tipo = " . $this->db->quote($tipo) . $this->periodCondition($periodo);
        return $this->db->query("SELECT COALESCE(NULLIF(categoria, ''), 'Sem categoria') AS categoria, SUM(valor) as total, COUNT(*) as qtd FROM financeiro WHERE $where GROUP BY COALESCE(NULLIF(categoria, ''), 'Sem categoria') ORDER BY total DESC LIMIT 5")->fetchAll();
    }
}
