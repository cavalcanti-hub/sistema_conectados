<?php
namespace App\Services;

require_once __DIR__ . '/OsCancellationException.php';

class OsCancellationService
{
    private \PDO $db;

    public function __construct(?\PDO $db = null)
    {
        $this->db = $db ?? \App\Config\Database::getInstance();
    }

    public function cancel(int $osId, int $userId, string $reason): array
    {
        $reason = trim($reason);
        if ($osId < 1) throw new OsCancellationException('INVALID_ORDER_ID', 'Ordem de servico invalida.', 400);
        if ($userId < 1) throw new OsCancellationException('UNAUTHORIZED', 'Usuario nao autenticado.', 401);
        if ($reason === '') throw new OsCancellationException('CANCELLATION_REASON_REQUIRED', 'Informe o motivo do cancelamento.');
        $length = function_exists('mb_strlen') ? mb_strlen($reason, 'UTF-8') : strlen($reason);
        if ($length > 500) throw new OsCancellationException('CANCELLATION_REASON_INVALID', 'O motivo deve ter no maximo 500 caracteres.');

        $ownsTransaction = !$this->db->inTransaction();
        if ($ownsTransaction) $this->db->beginTransaction();
        try {
            $user = $this->db->prepare("SELECT id, perfil FROM usuarios WHERE id=:id AND status='Ativo' LIMIT 1 FOR UPDATE");
            $user->execute([':id' => $userId]);
            $authorized = $user->fetch(\PDO::FETCH_ASSOC);
            if (!$authorized || (string) $authorized['perfil'] !== 'Administrador') {
                throw new OsCancellationException('FORBIDDEN', 'Apenas administradores podem cancelar ordens de servico.', 403);
            }

            $stmt = $this->db->prepare('SELECT id, numero_os, status FROM ordens_servico WHERE id=:id FOR UPDATE');
            $stmt->execute([':id' => $osId]);
            $os = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$os) throw new OsCancellationException('ORDER_NOT_FOUND', 'Ordem de servico nao encontrada.', 404);
            if (normalize_os_status((string) $os['status']) === 'Cancelado') {
                if ($ownsTransaction) $this->db->commit();
                return ['status' => 'already_cancelled', 'os_id' => $osId, 'numero_os' => $os['numero_os']];
            }

            $movementTables = ['os_pagamentos', 'financeiro', 'estoque_movimentacoes', 'pdv_vendas', 'mercado_pago_point_orders', 'compras_notas'];
            foreach ($movementTables as $table) {
                $movement = $this->db->prepare("SELECT 1 FROM {$table} WHERE os_id=:id LIMIT 1");
                $movement->execute([':id' => $osId]);
                if ($movement->fetchColumn()) {
                    throw new OsCancellationException('CANCELLATION_BLOCKED_MOVEMENTS', 'Esta OS possui movimentacoes e nao pode ser cancelada sem um processo de estorno validado.', 409);
                }
            }

            $previousStatus = normalize_os_status((string) $os['status']);
            $update = $this->db->prepare("UPDATE ordens_servico SET status='Cancelado' WHERE id=:id");
            $update->execute([':id' => $osId]);
            $this->insertHistory($osId, $userId, $previousStatus, $reason);

            if ($ownsTransaction) $this->db->commit();
            return ['status' => 'cancelled', 'os_id' => $osId, 'numero_os' => $os['numero_os'], 'previous_status' => $previousStatus];
        } catch (\Throwable $e) {
            if ($ownsTransaction && $this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    protected function insertHistory(int $osId, int $userId, string $previousStatus, string $reason): void
    {
        $history = $this->db->prepare("INSERT INTO os_historico(os_id,usuario_id,status_anterior,status_novo,observacao) VALUES(:os,:user,:previous,'Cancelado',:reason)");
        $history->execute([':os' => $osId, ':user' => $userId, ':previous' => $previousStatus, ':reason' => 'Cancelamento: ' . $reason]);
    }
}
