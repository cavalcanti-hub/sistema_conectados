<?php
/**
 * Testes para a reconciliação e cálculo de taxas do Mercado Pago Point.
 */

putenv('APP_ENV=local');
$_ENV['APP_ENV'] = 'local';

$baseDir = dirname(__DIR__);
require_once $baseDir . '/app/Config/App.php';
require_once $baseDir . '/app/Support/helpers.php';

// Ativar autoloading manual
spl_autoload_register(function (string $class) use ($baseDir): void {
    if (str_starts_with($class, 'App\\')) {
        $class = 'app\\' . substr($class, 4);
    }
    $file = $baseDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

// Mock PDO Statement compatible with PHP 8+
class MockPDOStatement extends \PDOStatement {
    private string $query;
    
    public function __construct(string $query) {
        $this->query = $query;
    }

    public function execute(?array $params = null): bool {
        return true;
    }

    public function fetchAll(int $mode = \PDO::FETCH_DEFAULT, mixed ...$args): array {
        if (strpos($this->query, 'COLUMNS') !== false) {
            return [
                'id', 'os_id', 'pdv_venda_id', 'external_reference', 'status',
                'amount', 'payload', 'financeiro_lancado_at',
                'installments_requested', 'installments_confirmed', 'installments_cost', 'customer_total_paid', 'customer_financing_cost', 'seller_processing_fee', 'seller_financing_cost', 'seller_total_fee', 'seller_net_received', 'values_source', 'reconciliation_status', 'reconciliation_date'
            ];
        }
        if (strpos($this->query, 'STATISTICS') !== false) {
            return ['idx_mp_point_orders_pdv', 'idx_mp_point_orders_status'];
        }
        return [];
    }

    public function fetchColumn(int $column = 0): mixed {
        return 'mock_token';
    }

    public function fetch(int $mode = \PDO::FETCH_DEFAULT, int $cursorOrientation = \PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed {
        return ['chave' => 'mercadopago_access_token', 'valor' => 'mock_token'];
    }
}

class MockPDO extends \PDO {
    public function __construct() {
        parent::__construct('sqlite::memory:');
    }

    public function prepare(string $query, array $options = []): \PDOStatement|false {
        return new MockPDOStatement($query);
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): \PDOStatement|false {
        return new MockPDOStatement($query);
    }
}

$failures = [];
$assert = static function (bool $ok, string $name) use (&$failures): void {
    if (!$ok) {
        $failures[] = $name;
    }
};

// Instanciar Database sem chamar o construtor usando reflection
$reflectionDb = new \ReflectionClass(\App\Config\Database::class);
$instance = $reflectionDb->newInstanceWithoutConstructor();

// Injetar o MockPDO na propriedade privada conn
$connProp = $reflectionDb->getProperty('conn');
$connProp->setAccessible(true);
$connProp->setValue($instance, new MockPDO());

// Injetar a instancia mockada no Database::$instance estático
$instanceProp = $reflectionDb->getProperty('instance');
$instanceProp->setAccessible(true);
$instanceProp->setValue(null, $instance);

$mockDb = $instance->getConnection();
$model = new \App\Models\MercadoPagoPointModel($mockDb);
$reflection = new \ReflectionClass($model);
$method = $reflection->getMethod('extractRealPaymentData');
$method->setAccessible(true);

$extractData = function(?array $payment, float $baseAmount, string $installmentsCost) use ($model, $method) {
    return $method->invokeArgs($model, [$payment, $baseAmount, $installmentsCost]);
};

// ================= TESTES BUYER (1X A 12X) =================
for ($i = 1; $i <= 12; $i++) {
    $interestRate = ($i === 1) ? 0.0 : (0.02 + ($i * 0.015));
    $baseAmount = 190.00;
    $financingCost = round($baseAmount * $interestRate, 2);
    $customerTotal = $baseAmount + $financingCost;
    $processingFee = round($baseAmount * 0.03, 2);

    $payment = [
        'installments' => $i,
        'transaction_amount' => $customerTotal,
        'transaction_details' => [
            'net_received_amount' => round($baseAmount - $processingFee, 2),
            'total_paid_amount' => $customerTotal,
        ],
        'fee_details' => [
            [
                'type' => 'mercadopago_fee',
                'amount' => $processingFee,
                'fee_payer' => 'collector'
            ]
        ]
    ];

    if ($i > 1) {
        $payment['fee_details'][] = [
            'type' => 'financing_fee',
            'amount' => $financingCost,
            'fee_payer' => 'collector'
        ];
    }

    $res = $extractData($payment, $baseAmount, 'buyer');

    $assert($res['installments_confirmed'] === $i, "Buyer {$i}x: parcelas confirmadas");
    $assert(abs($res['customer_total_paid'] - $customerTotal) < 0.01, "Buyer {$i}x: total pago pelo cliente");
    $assert(abs($res['customer_financing_cost'] - $financingCost) < 0.01, "Buyer {$i}x: juros do cliente");
    $assert(abs($res['seller_processing_fee'] - $processingFee) < 0.01, "Buyer {$i}x: taxa de processamento");
    $assert(abs($res['seller_financing_cost'] - $financingCost) < 0.01, "Buyer {$i}x: juros registrados do lojista");
    $assert(abs($res['seller_total_fee'] - $processingFee) < 0.01, "Buyer {$i}x: seller_total_fee NAO deve incluir juros");
    $assert(abs($res['seller_net_received'] - ($baseAmount - $processingFee)) < 0.01, "Buyer {$i}x: liquido recebido");
}

// ================= TESTES SELLER (1X A 12X) =================
for ($i = 1; $i <= 12; $i++) {
    $interestRate = ($i === 1) ? 0.0 : (0.02 + ($i * 0.015));
    $baseAmount = 190.00;
    $financingCost = round($baseAmount * $interestRate, 2);
    $processingFee = round($baseAmount * 0.03, 2);
    $totalFee = $processingFee + $financingCost;

    $payment = [
        'installments' => $i,
        'transaction_amount' => $baseAmount,
        'transaction_details' => [
            'net_received_amount' => round($baseAmount - $totalFee, 2),
            'total_paid_amount' => $baseAmount,
        ],
        'fee_details' => [
            [
                'type' => 'mercadopago_fee',
                'amount' => $processingFee,
                'fee_payer' => 'collector'
            ]
        ]
    ];

    if ($i > 1) {
        $payment['fee_details'][] = [
            'type' => 'financing_fee',
            'amount' => $financingCost,
            'fee_payer' => 'collector'
        ];
    }

    $res = $extractData($payment, $baseAmount, 'seller');

    $assert($res['installments_confirmed'] === $i, "Seller {$i}x: parcelas");
    $assert(abs($res['customer_total_paid'] - $baseAmount) < 0.01, "Seller {$i}x: total pago");
    $assert(abs($res['customer_financing_cost'] - 0) < 0.01, "Seller {$i}x: juros cliente zero");
    $assert(abs($res['seller_processing_fee'] - $processingFee) < 0.01, "Seller {$i}x: taxa proc");
    $assert(abs($res['seller_financing_cost'] - $financingCost) < 0.01, "Seller {$i}x: juros lojista");
    $assert(abs($res['seller_total_fee'] - $totalFee) < 0.01, "Seller {$i}x: seller_total_fee DEVE incluir juros");
    $assert(abs($res['seller_net_received'] - ($baseAmount - $totalFee)) < 0.01, "Seller {$i}x: liquido");
}

// ================= TESTES DÉBITO E PIX =================
// Débito
$resDebit = $extractData([
    'installments' => 1,
    'transaction_amount' => 100.00,
    'transaction_details' => [
        'net_received_amount' => 98.10,
        'total_paid_amount' => 100.00,
    ],
    'fee_details' => [
        ['type' => 'mercadopago_fee', 'amount' => 1.90, 'fee_payer' => 'collector']
    ]
], 100.00, 'seller');
$assert($resDebit['seller_total_fee'] === 1.90, "Debito: taxa total");
$assert($resDebit['seller_net_received'] === 98.10, "Debito: liquido");

// Pix
$resPix = $extractData([
    'installments' => 1,
    'transaction_amount' => 100.00,
    'transaction_details' => [
        'net_received_amount' => 99.01,
        'total_paid_amount' => 100.00,
    ],
    'fee_details' => [
        ['type' => 'mercadopago_fee', 'amount' => 0.99, 'fee_payer' => 'collector']
    ]
], 100.00, 'seller');
$assert($resPix['seller_total_fee'] === 0.99, "Pix: taxa total");
$assert($resPix['seller_net_received'] === 99.01, "Pix: liquido");

// ================= OUTROS TESTES DE BORDA =================
// Ausência de fee_details
$resNoFee = $extractData([
    'installments' => 1,
    'transaction_amount' => 100.00,
    'transaction_details' => [
        'net_received_amount' => 95.00,
        'total_paid_amount' => 100.00,
    ],
    'fee_details' => null
], 100.00, 'seller');
$assert(abs($resNoFee['seller_total_fee'] - 5.00) < 0.01, "Sem fee_details: estimativa correta");

// API 404
$res404 = $extractData(null, 150.00, 'seller');
$assert(abs($res404['seller_total_fee'] - 0.0) < 0.01, "API 404: taxa zero");
$assert(abs($res404['seller_net_received'] - 150.00) < 0.01, "API 404: liquido completo");


if ($failures) {
    fwrite(STDERR, 'MercadoPagoPointReconciliationTest falhou: ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}
echo 'MercadoPagoPointReconciliationTest: OK' . PHP_EOL;
exit(0);
