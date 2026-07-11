<?php

$baseDir = dirname(__DIR__);
require $baseDir . '/app/Config/App.php';
require $baseDir . '/app/Support/helpers.php';
require $baseDir . '/app/Core/Router.php';
spl_autoload_register(static function (string $class) use ($baseDir): void {
    if (str_starts_with($class, 'App\\')) {
        $file = $baseDir . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

$config = require $baseDir . '/app/Config/routes.php';
$router = new \App\Core\Router($config, $baseDir, ['is_system' => true, 'is_vitrine' => false]);
$failures = [];

$assert = static function (bool $condition, string $name) use (&$failures): void {
    if (!$condition) {
        $failures[] = $name;
    }
};

$assert($router->normalizePath('/clientes/') === 'clientes', 'barra final');
$assert($router->normalizePath('//clientes///create') === 'clientes/create', 'barras repetidas');
$assert($router->normalizePath('../config') === null, 'path traversal literal');
$assert($router->normalizePath('%2e%2e/config') === null, 'path traversal codificado');
$assert($router->normalizePath('clientes\\delete') === null, 'barra invertida');
$assert($router->resolve('vitrine') !== null, 'GET publico registrado');
$assert($router->resolve('dashboard') !== null, 'rota interna registrada');
$assert($router->resolve('os/registrarPagamento') !== null, 'pagamento registrado');
$assert($router->resolve('mercadopago/liberarPoint') !== null, 'Point registrado');
$assert($router->resolve('mercadopago/pointWebhook') !== null, 'webhook registrado');
$assert($router->resolve('produtos/edit/15') !== null, 'parametro numerico valido');
$assert($router->resolve('produtos/edit/-1') === null, 'parametro numerico com sinal');
$assert($router->resolve('produtos/edit/1e2') === null, 'notacao cientifica');
$assert($router->resolve('produtos/edit/1/extra') === null, 'segmento inesperado');
$assert($router->resolve('os/metodoPrivado') === null, 'metodo arbitrario');
$assert($router->resolve('controladorInexistente/index') === null, 'controlador inexistente');

$logout = $router->resolve('logout')[0] ?? [];
$assert(($logout['methods'] ?? []) === ['POST'], 'logout somente POST');
$assert(($logout['auth'] ?? false) === true && ($logout['csrf'] ?? false) === true, 'logout protegido');
$webhook = $router->resolve('mercadopago/pointWebhook')[0] ?? [];
$assert(($webhook['methods'] ?? []) === ['POST'], 'webhook somente POST');
$assert(($webhook['public'] ?? false) === true && ($webhook['csrf'] ?? true) === false, 'webhook externo sem CSRF');
$assert(($webhook['external_auth'] ?? null) === 'mercadopago_signature', 'webhook marcado para assinatura');
$assert(isset($config['aliases']['checklist']), 'alias legado checklist');
foreach ($config['routes'] as $path => $route) {
    $assert(class_exists($route['controller']), 'controlador declarado: ' . $path);
    $assert(method_exists($route['controller'], $route['action']), 'acao declarada: ' . $path);
    if ($route['auth'] && in_array('POST', $route['methods'], true)) {
        $assert($route['csrf'] === true, 'POST interno com CSRF: ' . $path);
    }
}

if ($failures !== []) {
    fwrite(STDERR, "RouterTest falhou: " . implode(', ', $failures) . PHP_EOL);
    exit(1);
}

echo 'RouterTest: OK (registro, alvos, metodos, CSRF, parametros e normalizacao)' . PHP_EOL;
