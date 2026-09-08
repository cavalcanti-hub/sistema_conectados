<?php
/**
 * Reconciliation script for Mercado Pago Point orders.
 * 
 * MUST be run via CLI only (cPanel Terminal or SSH).
 * Connects to the application database, fetches real payment data from MP API,
 * and corrects the financeiro entries.
 *
 * Usage:
 *   php scripts/reconcile_point_fees.php --dry-run
 *   php scripts/reconcile_point_fees.php --apply --confirm=RECONCILIAR
 *   php scripts/reconcile_point_fees.php --apply --confirm=RECONCILIAR --order-id=38
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit(1);
}

$base = dirname(__DIR__);
require $base . '/app/Config/App.php';
require $base . '/app/Support/helpers.php';
\App\Config\App::loadEnv($base);

$opt = getopt('', ['dry-run', 'apply', 'confirm:', 'order-id:', 'limit:']);
$apply = isset($opt['apply']);
if ($apply && ($opt['confirm'] ?? '') !== 'RECONCILIAR') {
    fwrite(STDERR, "Use --apply --confirm=RECONCILIAR para aplicar.\n");
    exit(2);
}
$limit = max(1, min(100, (int) ($opt['limit'] ?? 100)));
$targetOrderId = $opt['order-id'] ?? null;

$pdo = new PDO(
    'mysql:host=' . app_env('DB_HOST') . ';port=' . app_env('DB_PORT') . ';dbname=' . app_env('DB_DATABASE') . ';charset=utf8mb4',
    app_env('DB_USERNAME'),
    app_env('DB_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$accessToken = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'mercadopago_access_token'")->fetchColumn();
if (!$accessToken) {
    fwrite(STDERR, "Access token nao configurado.\n");
    exit(2);
}

function mp_get(string $path, string $token): array {
    $ch = curl_init("https://api.mercadopago.com$path");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode($resp, true)];
}

// Fetch orders to reconcile
if ($targetOrderId) {
    $stmt = $pdo->prepare("SELECT * FROM mercado_pago_point_orders WHERE id = ?");
    $stmt->execute([(int) $targetOrderId]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM mercado_pago_point_orders
        WHERE mp_order_id IS NOT NULL AND mp_order_id != ''
          AND status IN ('approved','processed','paid','finished')
          AND (reconciliation_status IS NULL OR reconciliation_status NOT IN ('reconciled','confirmed'))
        ORDER BY id ASC LIMIT $limit");
    $stmt->execute();
}
$orders = $stmt->fetchAll();

echo "Ordens encontradas: " . count($orders) . "\n\n";

foreach ($orders as $localOrder) {
    $id = $localOrder['id'];
    $orderId = $localOrder['mp_order_id'];
    $osId = $localOrder['os_id'];
    $baseAmount = (float) $localOrder['amount'];

    echo "=== Order DB ID=$id | mp_order_id=$orderId | os_id=$osId | R$ " . number_format($baseAmount, 2) . " ===\n";

    // 1. Get the order from MP API
    list($httpOrder, $orderData) = mp_get("/v1/orders/$orderId", $accessToken);
    if ($httpOrder !== 200) {
        echo "  SKIP: GET /v1/orders/$orderId retornou HTTP $httpOrder\n\n";
        continue;
    }

    // Extract installments_cost from order config
    $installmentsCost = (string) ($orderData['config']['payment_method']['installments_cost'] ?? $localOrder['installments_cost'] ?? 'seller');
    if (!in_array($installmentsCost, ['buyer', 'seller'], true)) {
        $installmentsCost = 'seller';
    }

    // Extract numeric reference_id
    $numericRefId = null;
    $payments = $orderData['transactions']['payments'] ?? [];
    if (is_array($payments) && isset($payments[0]['reference_id']) && ctype_digit((string) $payments[0]['reference_id'])) {
        $numericRefId = (string) $payments[0]['reference_id'];
    }

    if (!$numericRefId) {
        echo "  SKIP: Sem reference_id numerico na order.\n\n";
        continue;
    }

    // 2. Get payment details
    list($httpPay, $paymentData) = mp_get("/v1/payments/$numericRefId", $accessToken);
    if ($httpPay !== 200 || !$paymentData) {
        echo "  SKIP: GET /v1/payments/$numericRefId retornou HTTP $httpPay\n\n";
        continue;
    }

    // 3. Calculate fees using the same logic as extractRealPaymentData
    $installments = (int) ($paymentData['installments'] ?? 1);
    $customerTotalPaid = (float) ($paymentData['transaction_details']['total_paid_amount'] ?? $paymentData['transaction_amount']);
    $netReceived = (float) ($paymentData['transaction_details']['net_received_amount'] ?? 0);
    $customerFinancingCost = round(max(0, $customerTotalPaid - $baseAmount), 2);

    $sellerProcessingFee = 0.0;
    $sellerFinancingCost = 0.0;
    foreach ($paymentData['fee_details'] ?? [] as $fee) {
        if (($fee['fee_payer'] ?? '') === 'collector') {
            $amt = round((float) ($fee['amount'] ?? 0), 2);
            if (($fee['type'] ?? '') === 'financing_fee') {
                $sellerFinancingCost += $amt;
            } else {
                $sellerProcessingFee += $amt;
            }
        }
    }

    // REGRA: quando installments_cost=buyer, financing_fee NÃO entra em seller_total_fee
    if ($installmentsCost === 'buyer') {
        $sellerTotalFee = round($sellerProcessingFee, 2);
    } else {
        $sellerTotalFee = round($sellerProcessingFee + $sellerFinancingCost, 2);
    }

    echo "  installments=$installments | installments_cost=$installmentsCost\n";
    echo "  customer_total_paid=R$ " . number_format($customerTotalPaid, 2) . "\n";
    echo "  customer_financing_cost=R$ " . number_format($customerFinancingCost, 2) . "\n";
    echo "  seller_processing_fee=R$ " . number_format($sellerProcessingFee, 2) . "\n";
    echo "  seller_financing_cost=R$ " . number_format($sellerFinancingCost, 2) . " (" . ($installmentsCost === 'buyer' ? 'NÃO entra em seller_total_fee' : 'entra em seller_total_fee') . ")\n";
    echo "  seller_total_fee=R$ " . number_format($sellerTotalFee, 2) . "\n";
    echo "  net_received=R$ " . number_format($netReceived, 2) . "\n";

    // 4. Find the financeiro entries for this OS
    if ($osId) {
        $stmtFin = $pdo->prepare("SELECT * FROM financeiro WHERE os_id = ? ORDER BY id ASC");
        $stmtFin->execute([$osId]);
        $finEntries = $stmtFin->fetchAll();

        $receita = null;
        $despesaTaxa = null;
        foreach ($finEntries as $f) {
            if ($f['tipo'] === 'Receita' && strpos($f['forma_pagamento'] ?? '', 'Point') !== false) {
                $receita = $f;
            }
            if ($f['tipo'] === 'Despesa' && $f['categoria'] === 'Taxa Maquininha') {
                $despesaTaxa = $f;
            }
        }

        if ($receita) {
            echo "  Receita: ID=" . $receita['id'] . " | R$ " . $receita['valor'] . "\n";
        }
        if ($despesaTaxa) {
            echo "  Taxa atual: ID=" . $despesaTaxa['id'] . " | R$ " . $despesaTaxa['valor'] . " | " . $despesaTaxa['descricao'] . "\n";
            if (abs((float) $despesaTaxa['valor'] - $sellerTotalFee) < 0.01) {
                echo "  RESULTADO: Já correto. Nenhuma alteração necessária.\n\n";
                if (!$apply) continue;
            } else {
                echo "  RESULTADO: Taxa precisa ser atualizada de R$ " . $despesaTaxa['valor'] . " para R$ " . number_format($sellerTotalFee, 2) . "\n";
            }
        } else {
            echo "  RESULTADO: Nenhuma despesa de taxa encontrada para esta OS.\n";
        }
    }

    if (!$apply) {
        echo "  DRY-RUN: nenhuma alteração realizada.\n\n";
        continue;
    }

    // 5. Apply changes in transaction
    $pdo->beginTransaction();
    try {
        // Update financeiro despesa de taxa (somente a de taxa, sem tocar em outras despesas)
        if ($despesaTaxa) {
            $desc = 'Taxa Mercado Pago Point (' . $installments . 'x ' . $installmentsCost . ') - OS #' . ($localOrder['numero_os'] ?? $osId);
            $pdo->prepare("UPDATE financeiro SET valor = ?, descricao = ? WHERE id = ?")
                ->execute([$sellerTotalFee, $desc, $despesaTaxa['id']]);
            echo "  ATUALIZADO financeiro ID=" . $despesaTaxa['id'] . " para R$ " . number_format($sellerTotalFee, 2) . "\n";
        }

        // Update order reconciliation columns
        $pdo->prepare("UPDATE mercado_pago_point_orders SET
            installments_confirmed = ?, installments_cost = ?,
            customer_total_paid = ?, customer_financing_cost = ?,
            seller_processing_fee = ?, seller_financing_cost = ?,
            seller_total_fee = ?, seller_net_received = ?,
            values_source = 'confirmed', reconciliation_status = 'reconciled', reconciliation_date = NOW()
            WHERE id = ?")
            ->execute([
                $installments, $installmentsCost,
                $customerTotalPaid, $customerFinancingCost,
                $sellerProcessingFee, $sellerFinancingCost,
                $sellerTotalFee, $netReceived,
                $id
            ]);
        echo "  ATUALIZADO order ID=$id reconciliation_status=reconciled\n";

        $pdo->commit();
        echo "  COMMIT OK\n\n";
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "  ROLLBACK: " . $e->getMessage() . "\n\n";
    }
}

echo "Concluido.\n";
