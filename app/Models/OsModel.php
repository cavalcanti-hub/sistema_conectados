<?php
namespace App\Models;
use App\Config\Database;

class OsModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }

    private function runWithReconnect(callable $callback)
    {
        try {
            return $callback();
        } catch (\PDOException $e) {
            if (!$this->isLostConnection($e)) {
                throw $e;
            }

            $this->db = Database::reconnect();
            return $callback();
        }
    }

    private function isLostConnection(\PDOException $e): bool
    {
        $message = strtolower($e->getMessage());
        return str_contains($message, 'server has gone away')
            || str_contains($message, 'lost connection')
            || str_contains($message, 'error while sending query');
    }

    private function listQuery(array $filters = [], string $select = "os.*, c.nome as cliente_nome, c.whatsapp as cliente_whatsapp, a.modelo as aparelho_modelo, a.marca as aparelho_marca, u.nome as tecnico_nome"): array
    {
        $sql = "SELECT $select
                FROM ordens_servico os
                JOIN clientes c ON os.cliente_id = c.id
                JOIN aparelhos a ON os.aparelho_id = a.id
                LEFT JOIN usuarios u ON os.tecnico_id = u.id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND os.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (c.nome LIKE :s OR os.numero_os LIKE :s2 OR a.imei LIKE :s3)";
            $params[':s'] = '%'.$filters['search'].'%';
            $params[':s2'] = '%'.$filters['search'].'%';
            $params[':s3'] = '%'.$filters['search'].'%';
        }
        return [$sql, $params];
    }

    public function getAll($filters = [], ?int $limit = null, int $offset = 0) {
        [$sql, $params] = $this->listQuery($filters);
        $sql .= " ORDER BY os.created_at DESC";
        if ($limit !== null) {
            $limit = max(1, (int) $limit);
            $offset = max(0, (int) $offset);
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map([$this, 'normalizeRow'], $stmt->fetchAll());
    }

    public function countFiltered($filters = []): int {
        [$sql, $params] = $this->listQuery($filters, 'COUNT(*)');
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function find($id) {
        $sql = "SELECT os.*, c.nome as cliente_nome, c.whatsapp as cliente_whatsapp, c.telefone as cliente_telefone,
                c.email as cliente_email, c.endereco as cliente_endereco, c.cpf_cnpj as cliente_cpf_cnpj,
                a.modelo, a.marca, a.imei, a.cor, a.senha_padrao, a.estado_fisico,
                u.nome as tecnico_nome
                FROM ordens_servico os
                JOIN clientes c ON os.cliente_id = c.id
                JOIN aparelhos a ON os.aparelho_id = a.id
                LEFT JOIN usuarios u ON os.tecnico_id = u.id
                WHERE os.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->normalizeRow($row) : $row;
    }

    public function create($data) {
        $sql = "INSERT INTO ordens_servico (numero_os, cliente_id, aparelho_id, tecnico_id, problema_relatado, prioridade, status, prazo_estimado, valor_mao_obra, valor_pecas, fotos)
                VALUES (:numero_os, :cliente_id, :aparelho_id, :tecnico_id, :problema_relatado, :prioridade, :status, :prazo_estimado, :valor_mao_obra, :valor_pecas, :fotos)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function updateStatus($id, $status, $obs = '') {
        $stmt = $this->db->prepare("UPDATE ordens_servico SET status=:status WHERE id=:id");
        $stmt->execute([':status' => normalize_os_status($status), ':id' => $id]);
    }

    public function update($id, $data) {
        $sql = "UPDATE ordens_servico SET tecnico_id=:tecnico_id, diagnostico_tecnico=:diagnostico_tecnico,
                servico_realizar=:servico_realizar, status=:status, prioridade=:prioridade,
                valor_mao_obra=:valor_mao_obra, valor_pecas=:valor_pecas, desconto=:desconto,
                prazo_estimado=:prazo_estimado, forma_pagamento=:forma_pagamento, situacao_pagamento=:situacao_pagamento,
                fotos=:fotos, fotos_saida=:fotos_saida
                WHERE id=:id";
        $data[':id'] = $id;
        if (isset($data[':status'])) {
            $data[':status'] = normalize_os_status($data[':status']);
        }
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $id): ?array {
        $os = $this->find($id);
        if (!$os) {
            return null;
        }

        $aparelhoId = (int) ($os['aparelho_id'] ?? 0);

        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE financeiro SET os_id = NULL WHERE os_id = :id")->execute([':id' => $id]);
            $this->db->prepare("UPDATE compras_notas SET os_id = NULL WHERE os_id = :id")->execute([':id' => $id]);
            $this->db->prepare("UPDATE pdv_vendas SET os_id = NULL WHERE os_id = :id")->execute([':id' => $id]);
            $this->db->prepare("UPDATE estoque_movimentacoes SET os_id = NULL WHERE os_id = :id")->execute([':id' => $id]);
            $this->db->prepare("DELETE FROM os_pagamentos WHERE os_id = :id")->execute([':id' => $id]);
            $this->db->prepare("DELETE FROM os_historico WHERE os_id = :id")->execute([':id' => $id]);
            $this->db->prepare("DELETE FROM ordens_servico WHERE id = :id")->execute([':id' => $id]);

            if ($aparelhoId > 0) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM ordens_servico WHERE aparelho_id = :id");
                $stmt->execute([':id' => $aparelhoId]);
                if ((int) $stmt->fetchColumn() === 0) {
                    $this->db->prepare("DELETE FROM aparelhos WHERE id = :id")->execute([':id' => $aparelhoId]);
                }
            }

            $this->db->commit();
            return $os;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getHistorico($os_id) {
        $sql = "SELECT h.*, u.nome as usuario_nome FROM os_historico h LEFT JOIN usuarios u ON h.usuario_id = u.id WHERE h.os_id = :id ORDER BY h.created_at ASC";
        return $this->runWithReconnect(function () use ($sql, $os_id) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $os_id]);
            return $stmt->fetchAll();
        });
    }

    public function addHistorico($os_id, $usuario_id, $status_ant, $status_novo, $obs = '') {
        $sql = "INSERT INTO os_historico (os_id, usuario_id, status_anterior, status_novo, observacao) VALUES (:os_id, :uid, :sa, :sn, :obs)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':os_id'=>$os_id, ':uid'=>$usuario_id, ':sa'=>$status_ant, ':sn'=>$status_novo, ':obs'=>$obs]);
    }

    public function addPagamento(array $data): int {
        $sql = "INSERT INTO os_pagamentos (os_id, valor, forma_pagamento, data_pagamento, observacao, usuario_id)
                VALUES (:os_id, :valor, :forma_pagamento, :data_pagamento, :observacao, :usuario_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function getPagamentos($os_id): array {
        return $this->runWithReconnect(function () use ($os_id) {
            $stmt = $this->db->prepare("SELECT p.*, u.nome as usuario_nome
                FROM os_pagamentos p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.os_id = :id
                ORDER BY p.data_pagamento ASC, p.id ASC");
            $stmt->execute([':id' => $os_id]);
            return $stmt->fetchAll();
        });
    }

    public function totalPagamentos($os_id): float {
        return (float) $this->runWithReconnect(function () use ($os_id) {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(valor), 0) FROM os_pagamentos WHERE os_id = :id");
            $stmt->execute([':id' => $os_id]);
            return $stmt->fetchColumn();
        });
    }

    public function updateSituacaoPagamento(int $os_id, string $situacao, string $forma_pagamento = ''): void {
        $sql = "UPDATE ordens_servico SET situacao_pagamento = :situacao" . ($forma_pagamento ? ", forma_pagamento = :forma" : "") . " WHERE id = :id";
        $params = [':situacao' => $situacao, ':id' => $os_id];
        if ($forma_pagamento) {
            $params[':forma'] = $forma_pagamento;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function generateOSNumber() {
        $year = date('Y');
        $stmt = $this->db->prepare("SELECT numero_os FROM ordens_servico WHERE numero_os LIKE :prefix ORDER BY numero_os DESC LIMIT 1");
        $stmt->execute([':prefix' => $year . '-%']);
        $lastNumber = (string) ($stmt->fetchColumn() ?: '');

        $next = 1;
        if (preg_match('/^' . preg_quote($year, '/') . '-(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $year . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function countByStatus() {
        $results = [];
        $statusList = os_status_list();
        foreach ($statusList as $s) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM ordens_servico WHERE status = :s");
            $stmt->execute([':s' => $s]);
            $results[$s] = $stmt->fetchColumn();
        }
        return $results;
    }

    public function faturamento($periodo = 'mes') {
        $where = "situacao_pagamento IN ('Pago','Parcial')";
        if ($periodo === 'dia') $where .= " AND DATE(created_at) = CURDATE()";
        elseif ($periodo === 'semana') $where .= " AND YEARWEEK(created_at) = YEARWEEK(NOW())";
        elseif ($periodo === 'mes') $where .= " AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())";
        $stmt = $this->db->query("SELECT COALESCE(SUM(valor_total),0) as total FROM ordens_servico WHERE $where");
        return $stmt->fetchColumn();
    }

    public function recentes($limit = 10) {
        $sql = "SELECT os.*, c.nome as cliente_nome, a.modelo FROM ordens_servico os JOIN clientes c ON os.cliente_id=c.id JOIN aparelhos a ON os.aparelho_id=a.id ORDER BY os.created_at DESC LIMIT $limit";
        return array_map([$this, 'normalizeRow'], $this->db->query($sql)->fetchAll());
    }

    private function normalizeRow(array $row): array
    {
        if (isset($row['status'])) {
            $row['status'] = normalize_os_status($row['status']);
        }
        return $row;
    }
}
