<?php

namespace App\Core;

class Router
{
    private array $routes;
    private array $aliases;
    private string $baseDir;
    private array $context;

    public function __construct(array $config, string $baseDir, array $context = [])
    {
        $this->routes = $config['routes'] ?? [];
        $this->aliases = $config['aliases'] ?? [];
        $this->baseDir = $baseDir;
        $this->context = $context;
    }

    public function dispatch(string $rawPath, string $httpMethod): void
    {
        $httpMethod = strtoupper($httpMethod);
        $normalized = $this->normalizePath($rawPath);
        if ($normalized === null) {
            $this->fail(404, 'Caminho de rota invalido.', $rawPath);
            return;
        }

        if (isset($this->aliases[strtolower($normalized)])) {
            $alias = $this->aliases[strtolower($normalized)];
            if (!in_array($httpMethod, $alias['methods'], true)) {
                $this->fail(405, 'Metodo nao permitido.', $normalized, $alias['methods']);
                return;
            }
            $target = (string) $alias['target'];
            if ($target === '') {
                $target = !empty($this->context['is_system']) ? 'dashboard' : 'vitrine';
            }
            if (!empty($alias['internal'])) {
                $normalized = $target;
            } else {
            header('Location: ' . route_url($target), true, 302);
            return;
            }
        }

        $resolved = $this->resolve($normalized);
        if ($resolved === null) {
            $this->fail(404, 'Pagina nao encontrada.', $normalized);
            return;
        }

        [$route, $params] = $resolved;
        if (!in_array($httpMethod, $route['methods'], true)) {
            $this->fail(405, 'Metodo nao permitido.', $normalized, $route['methods'], $route);
            return;
        }

        if (!empty($this->context['is_vitrine']) && empty($route['public'])) {
            $this->fail(404, 'Pagina nao encontrada.', $normalized, [], $route);
            return;
        }

        if (empty($route['public']) || in_array($normalized, ['login', 'logout'], true)) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        if (!empty($route['auth']) && AuthSession::isExpired()) {
            AuthSession::terminate('timeout');
        }

        if (!empty($route['csrf'])) {
            if (request_exceeds_post_limit()) {
                abort_post_too_large();
            }
            if (!csrf_verify()) {
                $this->fail(419, 'Sessao expirada ou token de seguranca invalido.', $normalized, [], $route);
                return;
            }
        }

        if (!empty($route['auth']) && !$this->hasValidUser()) {
            if (($route['response'] ?? 'html') === 'json' || is_ajax_request()) {
                $this->jsonError(401, 'Acesso nao autorizado.', 'UNAUTHORIZED');
                return;
            }
            $_SESSION['_return_after_login'] = $this->safeReturnPath($normalized);
            header('Location: ' . route_url('login'), true, 302);
            return;
        }

        if ($normalized === 'login' && $this->hasValidUser()) {
            header('Location: ' . route_url('dashboard'), true, 302);
            return;
        }

        if (!empty($route['profiles']) && !$this->profileAllowed((array) $route['profiles'])) {
            $this->fail(403, 'Voce nao possui permissao para esta operacao.', $normalized, [], $route);
            return;
        }

        $controller = $route['controller'];
        $action = $route['action'];
        if (!class_exists($controller) || !method_exists($controller, $action) || str_starts_with($action, '_')) {
            app_log('Falha interna de despacho de rota', ['rota' => $normalized, 'codigo' => 'ROUTE_TARGET_INVALID']);
            $this->fail(500, 'Nao foi possivel processar a solicitacao.', $normalized, [], $route);
            return;
        }

        try {
            $instance = new $controller();
            call_user_func_array([$instance, $action], $params);
        } catch (\Throwable $e) {
            if ($e instanceof SchemaOutdatedException) {
                $this->fail(503, $e->getMessage(), $normalized, [], $route);
                return;
            }
            app_log('Excecao no despacho de rota', [
                'rota' => $normalized,
                'metodo' => $httpMethod,
                'erro_tipo' => get_class($e),
            ]);
            if (filter_var(app_env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN)) {
                throw $e;
            }
            $this->fail(500, 'Nao foi possivel processar a solicitacao.', $normalized, [], $route);
        }
    }

    public function resolve(string $path): ?array
    {
        $lookup = strtolower($path);
        if (isset($this->routes[$lookup])) {
            return [$this->routes[$lookup], []];
        }

        foreach ($this->routes as $key => $route) {
            $specs = $route['params'] ?? [];
            if ($specs === [] || !str_starts_with($lookup, $key . '/')) {
                continue;
            }
            $params = array_slice(explode('/', $path), count(explode('/', $key)));
            if ($this->validateParams($params, $specs)) {
                return [$route, $params];
            }
        }
        return null;
    }

    public function normalizePath(string $path): ?string
    {
        if (str_contains($path, "\0") || str_contains($path, '\\') || preg_match('/%2e|%00|%2f|%5c/i', $path)) {
            return null;
        }
        $path = rawurldecode($path);
        if (preg_match('//u', $path) !== 1) {
            return null;
        }
        $path = trim(preg_replace('#/+#', '/', trim($path)) ?? '', '/');
        if ($path === '') {
            return '';
        }
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..' || strlen($segment) > 120 || !preg_match('/^[\pL\pN_-]+$/u', $segment)) {
                return null;
            }
        }
        return $path;
    }

    private function validateParams(array $params, array $specs): bool
    {
        if (count($params) > count($specs)) {
            return false;
        }
        foreach ($specs as $index => $spec) {
            $optional = str_ends_with($spec, '?');
            $type = rtrim($spec, '?');
            if (!isset($params[$index])) {
                if ($optional) {
                    continue;
                }
                return false;
            }
            if ($type === 'positive_int' && (!preg_match('/^[1-9][0-9]{0,9}$/', $params[$index]) || (int) $params[$index] > 2147483647)) {
                return false;
            }
        }
        return true;
    }

    private function hasValidUser(): bool
    {
        $id = (int) ($_SESSION['usuario_id'] ?? 0);
        if ($id < 1) {
            return false;
        }
        try {
            $db = \App\Config\Database::getInstance();
            $stmt = $db->prepare("SELECT id, nome, perfil FROM usuarios WHERE id = :id AND status = 'Ativo' LIMIT 1");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            if (!$user) {
                AuthSession::terminate('inactive_user');
                return false;
            }
            $previousProfile = trim((string) ($_SESSION['perfil'] ?? ''));
            $currentProfile = trim((string) $user['perfil']);
            if ($previousProfile !== '' && strcasecmp($previousProfile, $currentProfile) !== 0) {
                session_regenerate_id(true);
            }
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['perfil'] = $currentProfile;
            AuthSession::touch();
            return true;
        } catch (\Throwable $e) {
            app_log('Falha ao validar sessao para rota', ['usuario_id' => $id]);
            return false;
        }
    }

    private function profileAllowed(array $allowed): bool
    {
        $normalize = static function (string $value): string {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($value));
            return strtolower(preg_replace('/[^a-z0-9]+/i', '', $ascii !== false ? $ascii : $value) ?? '');
        };
        return in_array($normalize((string) ($_SESSION['perfil'] ?? '')), array_map($normalize, $allowed), true);
    }

    private function safeReturnPath(string $path): string
    {
        return '/' . ltrim($path, '/');
    }

    private function fail(int $status, string $message, string $path, array $allowed = [], array $route = []): void
    {
        app_log('Erro de roteamento', [
            'status' => $status,
            'rota' => substr($path, 0, 240),
            'metodo' => (string) ($_SERVER['REQUEST_METHOD'] ?? ''),
            'permitidos' => $allowed,
            'usuario_id' => current_user_id(),
        ]);
        if ($status === 405 && $allowed !== []) {
            header('Allow: ' . implode(', ', $allowed));
        }
        if (($route['response'] ?? 'html') === 'json' || is_ajax_request()) {
            $codes = [403 => 'FORBIDDEN', 404 => 'NOT_FOUND', 405 => 'METHOD_NOT_ALLOWED', 419 => 'CSRF_INVALID', 500 => 'SERVER_ERROR'];
            $this->jsonError($status, $message, $codes[$status] ?? 'REQUEST_ERROR');
            return;
        }
        http_response_code($status === 419 ? 403 : $status);
        $errorMessage = $message;
        $allowedMethods = $allowed;
        $view = $this->baseDir . '/app/Views/errors/' . $status . '.php';
        if (!is_file($view)) {
            $view = $this->baseDir . '/app/Views/errors/500.php';
        }
        require $view;
    }

    private function jsonError(int $status, string $message, string $code): void
    {
        http_response_code($status === 419 ? 403 : $status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message, 'code' => $code], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
