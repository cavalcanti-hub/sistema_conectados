<?php
$baseDir = is_dir(__DIR__ . DIRECTORY_SEPARATOR . 'app') ? __DIR__ : dirname(__DIR__);

require_once $baseDir . '/app/Config/App.php';
require_once $baseDir . '/app/Support/helpers.php';

\App\Config\App::loadEnv($baseDir);

$timezone = trim((string) app_env('APP_TIMEZONE', 'America/Sao_Paulo'));
if ($timezone === '' || !in_array($timezone, timezone_identifiers_list(), true)) {
    $timezone = 'America/Sao_Paulo';
}
date_default_timezone_set($timezone);

$debug = filter_var(app_env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
error_reporting($debug ? E_ALL : 0);
ini_set('display_errors', $debug ? '1' : '0');

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

spl_autoload_register(function (string $class) use ($baseDir): void {
    if (str_starts_with($class, 'App\\')) {
        $class = 'app\\' . substr($class, 4);
    }
    $file = $baseDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$sessionName = trim((string) app_env('SESSION_NAME', 'conectados_session'));
if (!preg_match('/^[A-Za-z0-9_-]{1,64}$/', $sessionName)) $sessionName = 'conectados_session';
session_name($sessionName);
$configuredCookiePath = trim((string) app_env('APP_BASE_PATH', ''));
$cookiePath = $configuredCookiePath === '' ? '/' : '/' . trim($configuredCookiePath, '/');
$cookieSecureDefault = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'true' : 'false';
$sameSite = (string) app_env('SESSION_SAME_SITE', 'Lax');
if (!in_array($sameSite, ['Lax', 'Strict', 'None'], true)) $sameSite = 'Lax';
session_set_cookie_params([
    'lifetime' => 0, 'path' => $cookiePath, 'domain' => '',
    'secure' => filter_var(app_env('SESSION_SECURE', $cookieSecureDefault), FILTER_VALIDATE_BOOLEAN),
    'httponly' => filter_var(app_env('SESSION_HTTP_ONLY', 'true'), FILTER_VALIDATE_BOOLEAN),
    'samesite' => $sameSite,
]);
ini_set('session.use_only_cookies', '1');
session_cache_limiter('');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$rawCookies = (string) ($_SERVER['HTTP_COOKIE'] ?? '');
$sessionCookieCount = 0;
foreach (explode(';', $rawCookies) as $rawCookie) {
    if (str_starts_with(trim($rawCookie), $sessionName . '=')) {
        $sessionCookieCount++;
    }
}
if ($sessionCookieCount > 1) {
    \App\Core\AuthSession::terminate('invalid_session', false);
    header('Location: ' . route_url('login'), true, 302);
    return;
}

$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$hostName = preg_replace('/:\d+$/', '', trim($host, '[]'));
$isLocalHost = in_array($hostName, ['localhost', '127.0.0.1', '::1'], true);
$appMode = strtolower((string) app_env('APP_MODE', 'auto'));
$appMode = in_array($appMode, ['auto', 'system', 'vitrine'], true) ? $appMode : 'auto';
$isSystemMode = $appMode === 'system' || ($appMode === 'auto' && ($isLocalHost || str_starts_with($hostName, 'sistema.')));
$isVitrineMode = $appMode === 'vitrine' || (!$isSystemMode && !$isLocalHost && $appMode === 'auto');

$path = (string) ($_GET['url'] ?? '');
if (trim($path, '/') === '') {
    $path = $isSystemMode ? 'dashboard' : 'vitrine';
}

$routeConfig = require $baseDir . '/app/Config/routes.php';
$router = new \App\Core\Router($routeConfig, $baseDir, [
    'is_system' => $isSystemMode,
    'is_vitrine' => $isVitrineMode,
]);
$router->dispatch($path, (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
