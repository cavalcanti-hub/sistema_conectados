<?php

$base = dirname(__DIR__);
require $base . '/app/Config/App.php';
require $base . '/app/Support/helpers.php';
\App\Config\App::loadEnv($base);
spl_autoload_register(function ($class) use ($base) {
    if (str_starts_with($class, 'App\\')) {
        $file = $base . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

if (($argv[1] ?? '') === 'child') {
    $osId = (int) ($argv[2] ?? 0);
    $amount = (int) ($argv[3] ?? 0);
    $goFile = (string) ($argv[4] ?? '');
    $deadline = time() + 15;
    while (!is_file($goFile) && time() < $deadline) {
        usleep(20000);
    }
    try {
        $result = (new \App\Services\ManualOsPaymentService())->register($osId, $amount, 'Pix', 'Teste concorrencia', 1);
        echo 'ACCEPTED ' . $result['payment_id'] . PHP_EOL;
        exit(0);
    } catch (\App\Services\PaymentException $e) {
        echo 'REJECTED ' . $e->domainCode . PHP_EOL;
        exit(0);
    } catch (Throwable $e) {
        echo 'ERROR ' . $e->getMessage() . PHP_EOL;
        exit(2);
    }
}

$db = \App\Config\Database::getInstance();
$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'os_legacy_concurrency_' . bin2hex(random_bytes(6));
mkdir($tmp);

$cleanupFixture = static function (array $ids) use ($db): void {
    if (!empty($ids['os'])) {
        $db->prepare('DELETE FROM financeiro WHERE os_id = :id')->execute([':id' => $ids['os']]);
        $db->prepare('DELETE FROM os_pagamentos WHERE os_id = :id')->execute([':id' => $ids['os']]);
        $db->prepare('DELETE FROM os_historico WHERE os_id = :id')->execute([':id' => $ids['os']]);
        $db->prepare('DELETE FROM ordens_servico WHERE id = :id')->execute([':id' => $ids['os']]);
    }
    if (!empty($ids['device'])) {
        $db->prepare('DELETE FROM aparelhos WHERE id = :id')->execute([':id' => $ids['device']]);
    }
    if (!empty($ids['client'])) {
        $db->prepare('DELETE FROM clientes WHERE id = :id')->execute([':id' => $ids['client']]);
    }
};

$cleanupTemp = static function () use ($tmp): void {
    foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
        @unlink($file);
    }
    @rmdir($tmp);
};

$createFixture = static function (string $marker) use ($db): array {
    $db->prepare('INSERT INTO clientes(nome,cpf_cnpj) VALUES(:n,:d)')->execute([':n' => 'Teste concorrencia', ':d' => $marker]);
    $client = (int) $db->lastInsertId();
    $db->prepare('INSERT INTO aparelhos(cliente_id,marca,modelo) VALUES(:c,:m,:o)')->execute([':c' => $client, ':m' => 'Teste', ':o' => $marker]);
    $device = (int) $db->lastInsertId();
    $db->prepare("INSERT INTO ordens_servico(numero_os,cliente_id,aparelho_id,status,valor_mao_obra,valor_pecas,desconto) VALUES(:n,:c,:a,'Recebido',100.00,0,0)")
        ->execute([':n' => substr($marker, 0, 20), ':c' => $client, ':a' => $device]);
    return ['client' => $client, 'device' => $device, 'os' => (int) $db->lastInsertId()];
};

$runPair = static function (int $osId, int $amount, string $goFile): array {
    $cmd = [PHP_BINARY, __FILE__, 'child', (string) $osId, (string) $amount, $goFile];
    $spec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $procs = [proc_open($cmd, $spec, $pipesA), proc_open($cmd, $spec, $pipesB)];
    usleep(250000);
    file_put_contents($goFile, 'go');
    $outputs = [];
    foreach ([[$procs[0], $pipesA], [$procs[1], $pipesB]] as [$proc, $pipes]) {
        $outputs[] = trim(stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]));
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        proc_close($proc);
    }
    return $outputs;
};

$failures = [];
try {
    foreach ([10000, 6000] as $amount) {
        $ids = $createFixture('conc_' . $amount . '_' . bin2hex(random_bytes(5)));
        $outputs = $runPair($ids['os'], $amount, $tmp . DIRECTORY_SEPARATOR . 'go_' . $amount);
        $accepted = count(array_filter($outputs, static fn($line) => str_starts_with($line, 'ACCEPTED')));
        $paidCents = \App\Services\ManualOsPaymentService::decimalToCents(
            (string) $db->query('SELECT COALESCE(SUM(valor),0) FROM os_pagamentos WHERE os_id=' . (int) $ids['os'])->fetchColumn()
        );
        $finance = (int) $db->query('SELECT COUNT(*) FROM financeiro WHERE os_id=' . (int) $ids['os'] . " AND tipo='Receita' AND categoria='Pagamento OS'")->fetchColumn();
        if ($accepted !== 1 || $paidCents > 10000 || $finance !== 1) {
            $failures[] = 'concorrencia valor ' . $amount . ' saidas=' . implode('|', $outputs);
        }
        $cleanupFixture($ids);
    }
} catch (Throwable $e) {
    $failures[] = $e->getMessage();
} finally {
    $cleanupTemp();
}

if ($failures) {
    fwrite(STDERR, 'OsLegacyPaymentConcurrencyTest falhou: ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}

echo 'OsLegacyPaymentConcurrencyTest: OK (dois processos, fixtures temporarias limpas)' . PHP_EOL;
