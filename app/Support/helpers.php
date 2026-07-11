<?php

use App\Config\App;

if (!function_exists('app_env')) {
    function app_env(string $key, ?string $default = null): ?string
    {
        return App::env($key, $default);
    }
}

if (!function_exists('config_status')) {
    /** Retorna somente o estado da configuracao, nunca o seu valor. */
    function config_status(string $key, ?callable $validator = null): string
    {
        $value = app_env($key, null);
        if ($value === null || trim($value) === '') {
            return 'Não configurado';
        }
        if ($validator !== null && !$validator($value)) {
            return 'Inválido';
        }
        return 'Configurado';
    }
}

if (!function_exists('backup_storage_path')) {
    function backup_storage_path(): ?string
    {
        $configured = trim((string) app_env('BACKUP_PATH', ''));
        return $configured !== '' ? rtrim($configured, "\\/") : null;
    }
}

if (!function_exists('issue_os_payment_nonce')) {
    function issue_os_payment_nonce(int $osId): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['_os_payment_nonces'][$token] = ['os_id' => $osId, 'user_id' => current_user_id(), 'expires' => time() + 900];
        if (count($_SESSION['_os_payment_nonces']) > 12) $_SESSION['_os_payment_nonces'] = array_slice($_SESSION['_os_payment_nonces'], -12, null, true);
        return $token;
    }
}

if (!function_exists('validate_os_payment_nonce')) {
    function validate_os_payment_nonce(string $token, int $osId): bool
    {
        $entry = $_SESSION['_os_payment_nonces'][$token] ?? null;
        return is_array($entry) && hash_equals((string) array_search($entry, $_SESSION['_os_payment_nonces'], true), $token)
            && (int) $entry['os_id'] === $osId && (int) $entry['user_id'] === (int) current_user_id() && (int) $entry['expires'] >= time();
    }
}

if (!function_exists('consume_os_payment_nonce')) {
    function consume_os_payment_nonce(string $token): void { unset($_SESSION['_os_payment_nonces'][$token]); }
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('json_attr')) {
    function json_attr($value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null';
    }
}

if (!function_exists('current_user_id')) {
    function current_user_id(): ?int
    {
        $id = $_SESSION['usuario_id'] ?? null;
        return $id !== null && (int) $id > 0 ? (int) $id : null;
    }
}

if (!function_exists('current_user_profile')) {
    function current_user_profile(): string
    {
        static $profileSynced = false;

        $profile = trim((string) ($_SESSION['perfil'] ?? ''));
        $userId = current_user_id();
        if (!$profileSynced && $userId !== null && class_exists(\App\Config\Database::class)) {
            $profileSynced = true;
            try {
                $db = \App\Config\Database::getInstance();
                $stmt = $db->prepare('SELECT nome, perfil FROM usuarios WHERE id = :id AND status = :status LIMIT 1');
                $stmt->execute([':id' => $userId, ':status' => 'Ativo']);
                $user = $stmt->fetch();
                if ($user && !empty($user['perfil'])) {
                    $_SESSION['usuario_nome'] = $user['nome'] ?? ($_SESSION['usuario_nome'] ?? '');
                    $_SESSION['perfil'] = trim((string) $user['perfil']);
                    $profile = (string) $_SESSION['perfil'];
                }
            } catch (Throwable $e) {
                app_log('Nao foi possivel recarregar perfil da sessao.', ['usuario_id' => $userId]);
            }
        }

        return $profile;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['_csrf_token'])) {
            try {
                $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
            } catch (Throwable $e) {
                $_SESSION['_csrf_token'] = sha1(uniqid('', true));
            }
        }

        csrf_remember_token((string) $_SESSION['_csrf_token']);

        return (string) $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_remember_token')) {
    function csrf_remember_token(string $token): void
    {
        if ($token === '') {
            return;
        }

        $tokens = $_SESSION['_csrf_tokens'] ?? [];
        if (!is_array($tokens)) {
            $tokens = [];
        }

        $tokens[$token] = time();
        arsort($tokens);
        $_SESSION['_csrf_tokens'] = array_slice($tokens, 0, 8, true);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(): bool
    {
        $tokenValue = $_POST['_csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $token = is_array($tokenValue) ? (string) end($tokenValue) : (string) $tokenValue;
        $sessionToken = (string) ($_SESSION['_csrf_token'] ?? '');
        if ($token === '') {
            return false;
        }

        if ($sessionToken !== '' && hash_equals($sessionToken, $token)) {
            csrf_remember_token($sessionToken);
            return true;
        }

        $tokens = $_SESSION['_csrf_tokens'] ?? [];
        if (!is_array($tokens)) {
            return false;
        }

        foreach ($tokens as $knownToken => $createdAt) {
            if ((time() - (int) $createdAt) > 86400) {
                unset($tokens[$knownToken]);
                continue;
            }

            if (hash_equals((string) $knownToken, $token)) {
                $_SESSION['_csrf_tokens'] = $tokens;
                return true;
            }
        }

        $_SESSION['_csrf_tokens'] = $tokens;
        return false;
    }
}

if (!function_exists('is_ajax_request')) {
    function is_ajax_request(): bool
    {
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

        return str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json')
            || $requestedWith === 'xmlhttprequest';
    }
}

if (!function_exists('abort_csrf')) {
    function abort_csrf(): void
    {
        http_response_code(403);
        if (is_ajax_request()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => 'Sessao expirada ou token de seguranca invalido. Atualize a pagina e tente novamente.']);
        } else {
            echo 'Sessao expirada ou token de seguranca invalido. Volte, atualize a pagina e tente novamente.';
        }
        exit;
    }
}

if (!function_exists('ini_bytes')) {
    function ini_bytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;
        if ($unit === 'g') {
            $number *= 1024 * 1024 * 1024;
        } elseif ($unit === 'm') {
            $number *= 1024 * 1024;
        } elseif ($unit === 'k') {
            $number *= 1024;
        }

        return (int) $number;
    }
}

if (!function_exists('request_exceeds_post_limit')) {
    function request_exceeds_post_limit(): bool
    {
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        $postLimit = ini_bytes((string) ini_get('post_max_size'));
        return $contentLength > 0 && $postLimit > 0 && $contentLength > $postLimit;
    }
}

if (!function_exists('abort_post_too_large')) {
    function abort_post_too_large(): void
    {
        $limitMb = max(1, round(ini_bytes((string) ini_get('post_max_size')) / 1024 / 1024, 1));
        http_response_code(413);
        $message = 'O envio passou do limite do servidor (' . $limitMb . ' MB). Reduza as imagens ou envie menos fotos por vez.';

        if (is_ajax_request()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => $message]);
        } else {
            echo $message;
        }
        exit;
    }
}

if (!function_exists('normalize_decimal_input')) {
    function normalize_decimal_input($value): float
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0.0;
        }

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
        }

        return (float) str_replace(',', '.', $value);
    }
}

if (!function_exists('normalize_os_status')) {
    function normalize_os_status(?string $status): string
    {
        $status = trim((string) $status);
        $map = [
            'Em analise' => 'Em análise',
            'Em anÃ¡lise' => 'Em análise',
            'Em anÃƒÂ¡lise' => 'Em análise',
            'Aguardando aprovacao' => 'Aguardando aprovação',
            'Aguardando aprovaÃ§Ã£o' => 'Aguardando aprovação',
            'Aguardando aprovaÃƒÂ§ÃƒÂ£o' => 'Aguardando aprovação',
            'Aguardando peca' => 'Aguardando peça',
            'Aguardando peÃ§a' => 'Aguardando peça',
            'Aguardando peÃƒÂ§a' => 'Aguardando peça',
        ];

        return $map[$status] ?? $status;
    }
}

if (!function_exists('os_status_list')) {
    function os_status_list(): array
    {
        return ['Recebido', 'Em análise', 'Aguardando aprovação', 'Aprovado', 'Reprovado', 'Em reparo', 'Aguardando peça', 'Pronto', 'Entregue', 'Cancelado'];
    }
}

if (!function_exists('app_log')) {
    function app_log(string $message, array $context = []): void
    {
        $line = '[Conectados] ' . $message;
        if (!empty($context)) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        error_log($line);
    }
}

if (!function_exists('is_public_url')) {
    function is_public_url(string $url): bool
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?: ''));
        return $host !== '' && !in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}

if (!function_exists('validate_and_store_image_upload')) {
    function validate_and_store_image_upload(array $file, string $subdir): array
    {
        $result = ['ok' => true, 'name' => null, 'mime' => null, 'blob' => null, 'warning' => null, 'error' => null];

        if (empty($file['name']) || (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)) {
            return $result;
        }

        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'O arquivo excede o limite configurado no servidor.',
            UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o limite permitido pelo formulario.',
            UPLOAD_ERR_PARTIAL => 'O envio do arquivo foi interrompido.',
            UPLOAD_ERR_NO_TMP_DIR => 'A pasta temporaria de uploads nao esta disponivel.',
            UPLOAD_ERR_CANT_WRITE => 'O servidor nao conseguiu gravar o upload.',
            UPLOAD_ERR_EXTENSION => 'Uma extensao do PHP bloqueou o upload.',
        ];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $result['ok'] = false;
            $result['error'] = $uploadErrors[(int) $file['error']] ?? 'Nao foi possivel enviar o arquivo.';
            app_log('Falha de upload', ['error_code' => $file['error'] ?? null, 'message' => $result['error']]);
            return $result;
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $result['ok'] = false;
            $result['error'] = 'Nao foi possivel validar o arquivo enviado.';
            return $result;
        }

        $size = (int) ($file['size'] ?? filesize($file['tmp_name']));
        $maxSize = 24 * 1024 * 1024;
        if ($size <= 0 || $size > $maxSize) {
            $result['ok'] = false;
            $result['error'] = 'A imagem deve ter ate 24 MB.';
            return $result;
        }

        $originalExt = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (in_array($originalExt, ['heic', 'heif'], true)) {
            $result['ok'] = false;
            $result['error'] = 'Formato HEIC/HEIF nao suportado neste servidor. Envie a imagem em JPG, PNG ou WEBP.';
            return $result;
        }

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = (string) finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        } else {
            $result['warning'] = 'Extensao fileinfo ausente; validacao MIME limitada.';
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        if (!$imageInfo || empty($imageInfo['mime'])) {
            $result['ok'] = false;
            $result['error'] = 'O arquivo enviado nao parece ser uma imagem valida.';
            return $result;
        }

        $mime = $mime !== '' ? $mime : (string) $imageInfo['mime'];
        if (!isset($allowedMimes[$mime]) || (string) $imageInfo['mime'] !== $mime) {
            $result['ok'] = false;
            $result['error'] = 'Imagem invalida. Use JPG, PNG, WEBP ou GIF.';
            return $result;
        }

        $safeSubdir = trim(str_replace('\\', '/', $subdir), '/');
        $safeSubdir = preg_replace('#[^a-zA-Z0-9_/-]#', '', $safeSubdir) ?? '';
        $safeSubdir = preg_replace('#/{2,}#', '/', $safeSubdir) ?? '';
        if ($safeSubdir === '' || str_contains($safeSubdir, '..')) {
            $result['ok'] = false;
            $result['error'] = 'Pasta de upload invalida.';
            return $result;
        }

        $targetDir = public_path('uploads/' . $safeSubdir);
        if (!is_dir($targetDir) && !@mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            $result['ok'] = false;
            $result['error'] = 'Nao foi possivel criar a pasta de uploads.';
            return $result;
        }

        if (!is_writable($targetDir)) {
            $result['ok'] = false;
            $result['error'] = 'A pasta de uploads nao tem permissao de escrita.';
            return $result;
        }

        try {
            $name = bin2hex(random_bytes(12)) . '.' . $allowedMimes[$mime];
        } catch (Throwable $e) {
            $name = sha1(uniqid('', true)) . '.' . $allowedMimes[$mime];
        }

        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $result['ok'] = false;
            $result['error'] = 'O servidor nao conseguiu gravar a imagem.';
            return $result;
        }

        @chmod($targetPath, 0644);
        $blob = null;
        $maxDatabaseImageSize = 2 * 1024 * 1024;
        if ($size <= $maxDatabaseImageSize) {
            $blob = @file_get_contents($targetPath);
        } else {
            $result['warning'] = trim(($result['warning'] ? $result['warning'] . ' ' : '') . 'Imagem salva apenas no disco por exceder o limite seguro do banco.');
        }

        if ($blob === false || $blob === '') {
            $blob = null;
            $result['warning'] = trim(($result['warning'] ? $result['warning'] . ' ' : '') . 'Imagem salva no disco, mas nao foi possivel copiar para o banco.');
        }

        if (!function_exists('imagecreatetruecolor')) {
            $result['warning'] = trim(($result['warning'] ? $result['warning'] . ' ' : '') . 'Extensao GD ausente; imagem foi salva sem redimensionamento.');
        }

        $result['name'] = $name;
        $result['mime'] = $mime;
        $result['blob'] = $blob;
        return $result;
    }
}

if (!function_exists('validate_and_store_document_upload')) {
    function validate_and_store_document_upload(array $file, string $subdir): array
    {
        $result = ['ok' => true, 'name' => null, 'original' => null, 'mime' => null, 'error' => null];

        if (empty($file['name']) || (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)) {
            return $result;
        }

        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'O arquivo excede o limite configurado no servidor.',
            UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o limite permitido pelo formulario.',
            UPLOAD_ERR_PARTIAL => 'O envio do arquivo foi interrompido.',
            UPLOAD_ERR_NO_TMP_DIR => 'A pasta temporaria de uploads nao esta disponivel.',
            UPLOAD_ERR_CANT_WRITE => 'O servidor nao conseguiu gravar o upload.',
            UPLOAD_ERR_EXTENSION => 'Uma extensao do PHP bloqueou o upload.',
        ];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $result['ok'] = false;
            $result['error'] = $uploadErrors[(int) $file['error']] ?? 'Nao foi possivel enviar o arquivo.';
            return $result;
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $result['ok'] = false;
            $result['error'] = 'Nao foi possivel validar o arquivo enviado.';
            return $result;
        }

        $size = (int) ($file['size'] ?? filesize($file['tmp_name']));
        $maxSize = 20 * 1024 * 1024;
        if ($size <= 0 || $size > $maxSize) {
            $result['ok'] = false;
            $result['error'] = 'O anexo deve ter ate 20 MB.';
            return $result;
        }

        $allowedMimes = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = (string) finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        }

        $originalExt = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if ($mime === '' && $originalExt === 'pdf') {
            $mime = 'application/pdf';
        }

        if (!isset($allowedMimes[$mime])) {
            $result['ok'] = false;
            $result['error'] = 'Anexo invalido. Use PDF, JPG, PNG, WEBP ou GIF.';
            return $result;
        }

        if (str_starts_with($mime, 'image/') && !@getimagesize($file['tmp_name'])) {
            $result['ok'] = false;
            $result['error'] = 'A imagem anexada nao parece ser valida.';
            return $result;
        }

        if ($mime === 'application/pdf') {
            $handle = @fopen($file['tmp_name'], 'rb');
            $signature = $handle ? (string) fread($handle, 5) : '';
            if ($handle) {
                fclose($handle);
            }
            if ($signature !== '%PDF-') {
                $result['ok'] = false;
                $result['error'] = 'O PDF anexado nao parece ser valido.';
                return $result;
            }
        }

        $safeSubdir = trim(str_replace('\\', '/', $subdir), '/');
        $safeSubdir = preg_replace('#[^a-zA-Z0-9_/-]#', '', $safeSubdir) ?? '';
        $safeSubdir = preg_replace('#/{2,}#', '/', $safeSubdir) ?? '';
        if ($safeSubdir === '' || str_contains($safeSubdir, '..')) {
            $result['ok'] = false;
            $result['error'] = 'Pasta de upload invalida.';
            return $result;
        }

        $targetDir = public_path('uploads/' . $safeSubdir);
        if (!is_dir($targetDir) && !@mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            $result['ok'] = false;
            $result['error'] = 'Nao foi possivel criar a pasta de uploads.';
            return $result;
        }

        if (!is_writable($targetDir)) {
            $result['ok'] = false;
            $result['error'] = 'A pasta de uploads nao tem permissao de escrita.';
            return $result;
        }

        try {
            $name = bin2hex(random_bytes(16)) . '.' . $allowedMimes[$mime];
        } catch (Throwable $e) {
            $name = sha1(uniqid('', true)) . '.' . $allowedMimes[$mime];
        }

        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $result['ok'] = false;
            $result['error'] = 'O servidor nao conseguiu gravar o anexo.';
            return $result;
        }

        @chmod($targetPath, 0644);
        $result['name'] = $name;
        $result['original'] = basename((string) $file['name']);
        $result['mime'] = $mime;
        return $result;
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $configured = trim((string) app_env('APP_BASE_PATH', ''));
        if ($configured !== '') {
            return '/' . trim($configured, '/');
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $dir = trim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($dir === 'public') {
            $dir = '';
        } elseif (str_ends_with($dir, '/public')) {
            $dir = substr($dir, 0, -7);
        }

        return $dir === '' || $dir === '.' ? '' : '/' . $dir;
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        
        $baseUrl = rtrim((string) app_env('APP_URL', ''), '/');
        if ($baseUrl === '') {
            $baseUrl = rtrim($protocol . $host, '/');
        }

        $basePath = app_base_path();
        $basePathForUrl = $basePath;
        $baseUrlPath = rtrim((string) (parse_url($baseUrl, PHP_URL_PATH) ?: ''), '/');
        if ($basePath !== '' && $baseUrlPath === $basePath) {
            $basePathForUrl = '';
        }

        $normalizedPath = ltrim($path, '/');

        if ($normalizedPath === '') {
            return $baseUrl !== '' ? $baseUrl . $basePathForUrl : ($basePath ?: '/');
        }

        $suffix = ($basePathForUrl ?: '') . '/' . $normalizedPath;
        return $baseUrl !== '' ? $baseUrl . $suffix : $suffix;
    }
}

if (!function_exists('route_url')) {
    function route_url(string $route = '', array $query = []): string
    {
        $basePath = app_base_path();
        $normalizedRoute = trim($route, '/');
        $url = ($basePath ?: '') . ($normalizedRoute !== '' ? '/' . $normalizedRoute : '/');
        $params = [];
        if (!empty($query)) {
            $params = array_merge($params, $query);
        }

        return empty($params) ? $url : $url . '?' . http_build_query($params);
    }
}

if (!function_exists('absolute_route_url')) {
    function absolute_route_url(string $route = '', array $query = []): string
    {
        $path = route_url($route, $query);
        $baseUrl = rtrim((string) app_env('APP_URL', ''), '/');

        if ($baseUrl === '') {
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $baseUrl = rtrim($protocol . $host, '/');
        }

        $basePath = app_base_path();
        $baseUrlPath = rtrim((string) (parse_url($baseUrl, PHP_URL_PATH) ?: ''), '/');
        if ($basePath !== '' && $baseUrlPath === $basePath && str_starts_with($path, $basePath . '/')) {
            $path = substr($path, strlen($basePath));
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        return app_url($path);
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        $baseDir = dirname(__DIR__, 2);
        return $path === '' ? $baseDir : $baseDir . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        $publicDir = storage_path('public');
        $baseDir = is_dir($publicDir) ? $publicDir : storage_path();
        return $path === '' ? $baseDir : $baseDir . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
    }
}

if (!function_exists('resource_path')) {
    function resource_path(string $path = ''): string
    {
        $resourceDir = storage_path('resources');
        return $path === '' ? $resourceDir : $resourceDir . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
    }
}

if (!function_exists('render_resource_view')) {
    function render_resource_view(string $view, array $data = []): void
    {
        $viewFile = str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
        $viewPath = resource_path('views' . DIRECTORY_SEPARATOR . $viewFile);
        if (!file_exists($viewPath)) {
            $viewPath = resource_path($viewFile);
        }

        if (!file_exists($viewPath)) {
            die("View $view nao encontrada.");
        }

        extract($data);
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $basePath = app_base_path();

        echo str_replace(
            ['/sistema_conectados/public', '/sistema_conectados/uploads'],
            [$basePath, ($basePath ?: '') . '/uploads'],
            $content
        );
    }
}

if (!function_exists('normalize_print_mode')) {
    function normalize_print_mode(?string $mode, string $default = '80'): string
    {
        $normalized = strtolower(trim((string) $mode));

        $aliases = [
            '80mm' => '80',
            'thermal' => '80',
            'termica' => '80',
            'termico' => '80',
            'thermal_80' => '80',
            'termica_80' => '80',
            '56' => '80',
            '56mm' => '80',
            '58' => '80',
            '58mm' => '80',
            'thermal_56' => '80',
            'termica_56' => '80',
        ];

        $normalized = $aliases[$normalized] ?? $normalized;
        $allowed = ['a4', '80'];
        return in_array($normalized, $allowed, true) ? $normalized : $default;
    }
}

if (!function_exists('money_br')) {
    function money_br($value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }
}

if (!function_exists('pagination_request')) {
    function pagination_request(int $defaultPerPage = 20, int $maxPerPage = 100): array
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) ($_GET['per_page'] ?? $defaultPerPage);
        $perPage = max(5, min($maxPerPage, $perPage > 0 ? $perPage : $defaultPerPage));

        return [
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}

if (!function_exists('pagination_meta')) {
    function pagination_meta(int $total, int $page, int $perPage): array
    {
        $total = max(0, $total);
        $perPage = max(1, $perPage);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'offset' => $offset,
            'total_pages' => $totalPages,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => $total > 0 ? min($total, $offset + $perPage) : 0,
        ];
    }
}

if (!function_exists('render_pagination')) {
    function render_pagination(array $pagination, string $route, array $query = []): string
    {
        $totalPages = (int) ($pagination['total_pages'] ?? 1);
        $total = (int) ($pagination['total'] ?? 0);
        if ($totalPages <= 1 || $total <= 0) {
            return '';
        }

        $page = (int) ($pagination['page'] ?? 1);
        $perPage = (int) ($pagination['per_page'] ?? 20);
        $query = array_filter($query, static fn($value) => $value !== null && $value !== '');
        unset($query['page'], $query['per_page']);

        $windowStart = max(1, $page - 2);
        $windowEnd = min($totalPages, $page + 2);

        $link = static function (int $targetPage, string $label, bool $disabled = false, bool $active = false) use ($route, $query, $perPage): string {
            $class = 'app-page-link';
            if ($disabled) {
                $class .= ' disabled';
            }
            if ($active) {
                $class .= ' active';
            }

            if ($disabled) {
                return '<span class="' . $class . '">' . e($label) . '</span>';
            }

            $url = route_url($route, array_merge($query, [
                'page' => $targetPage,
                'per_page' => $perPage,
            ]));

            return '<a class="' . $class . '" href="' . e($url) . '">' . e($label) . '</a>';
        };

        $html = '<nav class="app-pagination" aria-label="Paginacao">';
        $html .= '<div class="app-pagination-summary">Exibindo ' . (int) ($pagination['from'] ?? 0) . '-' . (int) ($pagination['to'] ?? 0) . ' de ' . $total . '</div>';
        $html .= '<div class="app-pagination-links">';
        $html .= $link(max(1, $page - 1), 'Anterior', $page <= 1);
        if ($windowStart > 1) {
            $html .= $link(1, '1', false, $page === 1);
            if ($windowStart > 2) {
                $html .= '<span class="app-page-ellipsis">...</span>';
            }
        }
        for ($i = $windowStart; $i <= $windowEnd; $i++) {
            $html .= $link($i, (string) $i, false, $i === $page);
        }
        if ($windowEnd < $totalPages) {
            if ($windowEnd < $totalPages - 1) {
                $html .= '<span class="app-page-ellipsis">...</span>';
            }
            $html .= $link($totalPages, (string) $totalPages, false, $page === $totalPages);
        }
        $html .= $link(min($totalPages, $page + 1), 'Proxima', $page >= $totalPages);
        $html .= '</div></nav>';

        return $html;
    }
}
