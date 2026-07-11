<?php
namespace App\Core;

class AuthSession
{
    public static function terminate(string $origin = 'manual', bool $audit = true): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $userId = isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
        $isLocalTest = !empty($_SESSION['_test_session']) && strtolower((string) app_env('APP_ENV', 'production')) !== 'production';
        if ($audit && $userId && !$isLocalTest) {
            try {
                (new \App\Models\AuditModel())->record('logout', 'sessao', $userId, 'Sessao encerrada.', [
                    'origem' => self::safeOrigin($origin), 'resultado' => 'sucesso'
                ]);
            } catch (\Throwable $e) {
                app_log('Falha tecnica na auditoria de logout', ['origem' => self::safeOrigin($origin)]);
            }
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) self::expireSessionCookie();
        session_destroy();
    }

    public static function isExpired(): bool
    {
        if (empty($_SESSION['usuario_id'])) return false;
        $minutes = filter_var(app_env('SESSION_LIFETIME', '120'), FILTER_VALIDATE_INT);
        $minutes = is_int($minutes) && $minutes >= 5 && $minutes <= 1440 ? $minutes : 120;
        return (time() - (int) ($_SESSION['_last_activity'] ?? time())) > ($minutes * 60);
    }

    public static function touch(): void
    {
        if (!empty($_SESSION['usuario_id'])) $_SESSION['_last_activity'] = time();
    }

    private static function expireSessionCookie(): void
    {
        $params = session_get_cookie_params();
        $paths = [(string) ($params['path'] ?: '/'), '/'];
        $requestPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
        $segments = array_values(array_filter(explode('/', trim($requestPath, '/')), 'strlen'));
        $currentPath = '';
        foreach ($segments as $segment) {
            $currentPath .= '/' . $segment;
            $paths[] = $currentPath;
        }
        foreach (array_unique($paths) as $path) {
            setcookie(session_name(), '', [
                'expires' => time() - 42000, 'path' => $path,
                'domain' => (string) ($params['domain'] ?? ''),
                'secure' => (bool) ($params['secure'] ?? false),
                'httponly' => (bool) ($params['httponly'] ?? true),
                'samesite' => (string) ($params['samesite'] ?? 'Lax'),
            ]);
        }
    }

    private static function safeOrigin(string $origin): string
    {
        return in_array($origin, ['manual', 'invalid_session', 'inactive_user', 'timeout'], true) ? $origin : 'invalid_session';
    }
}
