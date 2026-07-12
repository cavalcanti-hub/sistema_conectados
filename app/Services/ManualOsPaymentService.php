<?php
namespace App\Services;

require_once __DIR__ . '/PaymentException.php';

class ManualOsPaymentService
{
    private \PDO $db;
    private const METHODS = ['Pix', 'Dinheiro'];

    public function __construct(?\PDO $db = null) { $this->db = $db ?? \App\Config\Database::getInstance(); }

    public static function parseMoneyToCents($input): int
    {
        $value = trim((string) $input);
        if ($value === '' || preg_match('/[eE]|[^0-9R$.,\s-]/u', $value)) throw new PaymentException('INVALID_PAYMENT_VALUE', 'Informe um valor valido.');
        $value = preg_replace('/^R\$\s*/u', '', $value);
        if (!preg_match('/^[0-9]{1,3}(?:\.[0-9]{3})*(?:,[0-9]{1,2})?$|^[0-9]+(?:[.,][0-9]{1,2})?$/', $value)) throw new PaymentException('INVALID_PAYMENT_VALUE', 'Informe um valor com no maximo duas casas decimais.');
        if (str_contains($value, ',')) $value = str_replace(['.', ','], ['', '.'], $value);
        [$whole, $decimal] = array_pad(explode('.', $value, 2), 2, '');
        $decimal = str_pad($decimal, 2, '0');
        $cents = ((int) $whole * 100) + (int) $decimal;
        if ($cents <= 0 || $cents > 999999999) throw new PaymentException('INVALID_PAYMENT_VALUE', 'O valor informado esta fora do limite permitido.');
        return $cents;
    }

    public static function decimalToCents(string $value): int
    {
        if (!preg_match('/^-?\d+(?:\.\d{1,2})?$/', $value)) throw new PaymentException('FINANCIAL_INCONSISTENCY', 'Os valores financeiros da OS estao inconsistentes.', 409);
        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '-');
        [$whole, $decimal] = array_pad(explode('.', $value, 2), 2, '');
        $cents = ((int) $whole * 100) + (int) str_pad($decimal, 2, '0');
        return $negative ? -$cents : $cents;
    }

    public function register(int $osId, int $amountCents, string $method, string $note, int $userId): array
    {
        if ($osId < 1) throw new PaymentException('INVALID_ORDER_ID', 'Ordem de servico invalida.', 400);
        if ($userId < 1) throw new PaymentException('UNAUTHORIZED', 'Usuario nao autenticado.', 401);
        if ($amountCents <= 0) throw new PaymentException('INVALID_PAYMENT_VALUE', 'Informe um valor valido.');
        $method = trim($method);
        if (!in_array($method, self::METHODS, true)) throw new PaymentException('PAYMENT_METHOD_INVALID', 'Forma de pagamento manual invalida.');
        $note = trim($note);
        $noteLength = function_exists('mb_strlen') ? mb_strlen($note, 'UTF-8') : strlen($note);
        if ($noteLength > 255) throw new PaymentException('INVALID_PAYMENT_NOTE', 'A observacao deve ter no maximo 255 caracteres.');

        $ownsTransaction = !$this->db->inTransaction();
        if ($ownsTransaction) $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT id, numero_os, status, valor_total, situacao_pagamento FROM ordens_servico WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $osId]);
            $os = $stmt->fetch();
            if (!$os) throw new PaymentException('ORDER_NOT_FOUND', 'Ordem de servico nao encontrada.', 404);
            if (in_array(normalize_os_status((string) $os['status']), ['Cancelado', 'Cancelada'], true)) throw new PaymentException('ORDER_NOT_PAYABLE', 'Esta ordem de servico nao aceita pagamento.');

            $totalCents = self::decimalToCents((string) $os['valor_total']);
            if ($totalCents <= 0) throw new PaymentException('FINANCIAL_INCONSISTENCY', 'A OS nao possui valor final valido.', 409);
            $sum = $this->db->prepare('SELECT valor FROM os_pagamentos WHERE os_id = :id FOR UPDATE');
            $sum->execute([':id' => $osId]);
            $paidBefore = 0;
            foreach ($sum->fetchAll(\PDO::FETCH_COLUMN) as $paidValue) $paidBefore += self::decimalToCents((string) $paidValue);
            $balance = $totalCents - $paidBefore;
            if ($balance <= 0) throw new PaymentException('ORDER_ALREADY_PAID', 'Esta ordem de servico ja esta quitada.', 409);
            if ($amountCents > $balance) throw new PaymentException('PAYMENT_EXCEEDS_BALANCE', 'O valor informado e maior que o saldo atual.');

            $amount = number_format($amountCents / 100, 2, '.', '');
            $payment = $this->db->prepare('INSERT INTO os_pagamentos (os_id, valor, forma_pagamento, data_pagamento, observacao, usuario_id) VALUES (:os, :valor, :forma, CURDATE(), :obs, :usuario)');
            $payment->execute([':os' => $osId, ':valor' => $amount, ':forma' => $method, ':obs' => $note !== '' ? $note : 'Pagamento manual', ':usuario' => $userId]);
            $paymentId = (int) $this->db->lastInsertId();

            $finance = $this->db->prepare("INSERT INTO financeiro (tipo, categoria, descricao, valor, os_id, usuario_id, data_pagamento, forma_pagamento) VALUES ('Receita', 'Pagamento OS', :descricao, :valor, :os, :usuario, CURDATE(), :forma)");
            $finance->execute([':descricao' => 'Pagamento manual OS #' . $os['numero_os'] . ' (' . $method . ')', ':valor' => $amount, ':os' => $osId, ':usuario' => $userId, ':forma' => $method]);
            $financeId = (int) $this->db->lastInsertId();

            $remaining = $balance - $amountCents;
            $financialStatus = $remaining === 0 ? 'Pago' : 'Parcial';
            $update = $this->db->prepare('UPDATE ordens_servico SET situacao_pagamento = :situacao, forma_pagamento = :forma WHERE id = :id');
            $update->execute([':situacao' => $financialStatus, ':forma' => $method, ':id' => $osId]);
            $history = $this->db->prepare('INSERT INTO os_historico (os_id, usuario_id, status_anterior, status_novo, observacao) VALUES (:os, :usuario, :status, :status2, :obs)');
            $history->execute([':os' => $osId, ':usuario' => $userId, ':status' => $os['status'], ':status2' => $os['status'], ':obs' => 'Pagamento manual de R$ ' . number_format($amountCents / 100, 2, ',', '.') . ' via ' . $method . '. Saldo restante: R$ ' . number_format($remaining / 100, 2, ',', '.') . '.']);
            if ($ownsTransaction) $this->db->commit();
            return ['payment_id' => $paymentId, 'finance_id' => $financeId, 'status' => strtolower($financialStatus), 'remaining_cents' => $remaining, 'amount_cents' => $amountCents, 'method' => $method, 'numero_os' => $os['numero_os']];
        } catch (\Throwable $e) {
            if ($ownsTransaction && $this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
