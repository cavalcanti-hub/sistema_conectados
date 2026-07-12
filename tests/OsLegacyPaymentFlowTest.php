<?php

$base = dirname(__DIR__);
$failures = [];
$ok = static function (bool $condition, string $name) use (&$failures): void {
    if (!$condition) {
        $failures[] = $name;
    }
};

$controller = file_get_contents($base . '/app/Controllers/OsController.php') ?: '';
$service = file_get_contents($base . '/app/Services/ManualOsPaymentService.php') ?: '';
$createView = file_get_contents($base . '/app/Views/os/create.php') ?: '';
$editView = file_get_contents($base . '/app/Views/os/edit.php') ?: '';
$helpers = file_get_contents($base . '/app/Support/helpers.php') ?: '';

$ok(!str_contains($controller, 'INSERT INTO os_pagamentos'), 'controller sem insert direto em os_pagamentos');
$ok(!str_contains($controller, "':categoria' => 'Pagamento OS'"), 'controller sem receita manual Pagamento OS');
$ok(!str_contains($controller, 'registerOsPaymentFromPost'), 'helper legado removido');
$ok(!str_contains($editView, 'name="situacao_pagamento"'), 'situacao de pagamento nao vem do navegador na edicao');
$ok(substr_count($controller, 'new \\App\\Services\\ManualOsPaymentService($db)') >= 2, 'criacao e edicao usam servico central');
$ok(str_contains($controller, 'validate_os_operation_nonce'), 'criacao com pagamento usa nonce de operacao');
$ok(str_contains($controller, 'validate_os_payment_nonce'), 'edicao com pagamento usa nonce da OS');
$ok(str_contains($createView, 'pagamento_inicial_valor'), 'formulario de criacao identifica pagamento inicial');
$ok(str_contains($createView, 'os_create_payment_nonce'), 'formulario de criacao envia nonce');
$ok(str_contains($editView, 'name="payment_nonce"'), 'formulario de edicao envia nonce');
$ok(str_contains($editView, 'name="pagamento_valor"'), 'formulario de edicao identifica nova parcela');
$ok(str_contains($service, 'FOR UPDATE'), 'servico mantem bloqueio FOR UPDATE');
$ok(str_contains($service, 'amountCents <= 0'), 'servico rejeita zero e negativo');
$ok(str_contains($helpers, 'issue_os_operation_nonce'), 'helper de nonce por operacao existe');
$ok(str_contains($controller, 'TOTAL_BELOW_PAID'), 'edicao rejeita total inferior ao valor ja pago');
$ok(str_contains($controller, 'totalPaymentsCents'), 'decisao financeira da edicao soma pagamentos em centavos');

if ($failures) {
    fwrite(STDERR, 'OsLegacyPaymentFlowTest falhou: ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}

echo 'OsLegacyPaymentFlowTest: OK (sem alterar banco)' . PHP_EOL;
