<?php

$baseDir = dirname(__DIR__);
$routes = require $baseDir . '/app/Config/routes.php';
$failures = [];
$assert = static function (bool $ok, string $name) use (&$failures): void { if (!$ok) $failures[] = $name; };

$logout = $routes['routes']['logout'] ?? [];
$assert(($logout['methods'] ?? []) === ['POST'], 'logout somente POST');
$assert(($logout['auth'] ?? false) === true, 'logout autenticado');
$assert(($logout['csrf'] ?? false) === true, 'logout com CSRF');
$alias = $routes['aliases']['login/logout'] ?? [];
$assert(($alias['target'] ?? '') === 'logout', 'alias aponta para logout unico');
$assert(($alias['methods'] ?? []) === ['POST'], 'alias somente POST');
$assert(($alias['internal'] ?? false) === true, 'alias preserva POST internamente');
$storeLogout = $routes['routes']['vitrine/sairconta'] ?? [];
$assert(($storeLogout['methods'] ?? []) === ['POST'], 'vitrine somente POST');
$assert(($storeLogout['csrf'] ?? false) === true, 'vitrine com CSRF');

$header = file_get_contents($baseDir . '/app/Views/layout/header.php');
$assert(substr_count($header, "route_url('logout')") === 2, 'duas interfaces internas');
$assert(!preg_match('/<a[^>]+route_url\([\'\"]logout/', $header), 'sem link GET interno');
$assert(substr_count($header, 'csrf_field()') >= 2, 'formularios internos com CSRF');
$controller = file_get_contents($baseDir . '/app/Controllers/AuthController.php');
$assert(str_contains($controller, "AuthSession::terminate('manual')"), 'controlador usa rotina central');
$session = file_get_contents($baseDir . '/app/Core/AuthSession.php');
$assert(str_contains($session, '$_SESSION = []'), 'sessao esvaziada');
$assert(str_contains($session, 'session_destroy()'), 'sessao destruida');
$assert(str_contains($session, 'setcookie(session_name()'), 'cookie invalidado');
$assert(str_contains($session, "'timeout'"), 'origem timeout permitida');

if ($failures) {
    fwrite(STDERR, 'LogoutTest falhou: ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}
echo 'LogoutTest: OK' . PHP_EOL;
