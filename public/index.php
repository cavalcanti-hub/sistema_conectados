<?php
$baseDir = is_dir(__DIR__ . DIRECTORY_SEPARATOR . 'app') ? __DIR__ : dirname(__DIR__);

require_once $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'App.php';
require_once $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Support' . DIRECTORY_SEPARATOR . 'helpers.php';

\App\Config\App::loadEnv($baseDir);

$debug = filter_var(app_env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
error_reporting($debug ? E_ALL : 0);
ini_set('display_errors', $debug ? '1' : '0');

if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

spl_autoload_register(function ($class) use ($baseDir) {
    if (str_starts_with($class, 'App\\')) {
        $class = 'app\\' . substr($class, 4);
    }

    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = $baseDir . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$url = trim((string) ($_GET['url'] ?? ''), '/');
$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$hostName = preg_replace('/:\d+$/', '', trim($host, '[]'));
$isLocalHost = in_array($hostName, ['localhost', '127.0.0.1', '::1'], true);
$appMode = strtolower((string) app_env('APP_MODE', 'auto'));
$appMode = in_array($appMode, ['auto', 'system', 'vitrine'], true) ? $appMode : 'auto';

$isSystemMode = $appMode === 'system' || ($appMode === 'auto' && str_starts_with($hostName, 'sistema.'));
$isVitrineMode = $appMode === 'vitrine' || (!$isSystemMode && !$isLocalHost && $appMode === 'auto');
$protectInternalRoutes = $isSystemMode || $isLocalHost;

if ($url === '' || $url === 'index') {
    $url = $isSystemMode ? 'dashboard' : 'vitrine';
}

if ($isVitrineMode && $url === 'dashboard') {
    $url = 'vitrine';
}

$parts = explode('/', $url);
$module = $parts[0];
$action = $parts[1] ?? 'index';

if ($module === 'estoque') {
    header('Location: ' . route_url('produtos'));
    exit;
}

$publicModules = ['vitrine', 'media'];
if ($isVitrineMode && !in_array($module, $publicModules, true)) {
    header('Location: ' . route_url('vitrine'));
    exit;
}

$authFreeModules = ['login', 'logout', 'vitrine', 'media'];
if ($protectInternalRoutes && !in_array($module, $authFreeModules, true) && empty($_SESSION['usuario_id'])) {
    header('Location: ' . route_url('login'));
    exit;
}

if ($protectInternalRoutes && $module === 'login' && !empty($_SESSION['usuario_id'])) {
    header('Location: ' . route_url('dashboard'));
    exit;
}

$routes = [
    'dashboard'  => ['controller' => \App\Controllers\DashboardController::class, 'methods' => ['index']],
    'clientes'   => ['controller' => \App\Controllers\ClientesController::class, 'methods' => ['index','create','store','edit','update','delete','show']],
    'os'         => ['controller' => \App\Controllers\OsController::class, 'methods' => ['index','create','store','edit','update','delete','viewDetail','print','storeModelo']],
    'estoque'    => ['controller' => \App\Controllers\EstoqueController::class, 'methods' => ['index','create','store','edit','update','delete','movimentar']],
    'recados'    => ['controller' => \App\Controllers\RecadosController::class, 'methods' => ['index','store','updateStatus','delete']],
    'compras'    => ['controller' => \App\Controllers\ComprasController::class, 'methods' => ['index','print','store','updateStatus','delete']],
    'termos'     => ['controller' => \App\Controllers\TermosController::class, 'methods' => ['index','store','print','delete']],
    'financeiro' => ['controller' => \App\Controllers\FinanceiroController::class, 'methods' => ['index','store','update','delete','print']],
    'gastos_pessoais' => ['controller' => \App\Controllers\GastosPessoaisController::class, 'methods' => ['index','store','update','delete','print','addCategoria','deleteCategoria']],
    'tecnicos'   => ['controller' => \App\Controllers\TecnicosController::class, 'methods' => ['index','create','store','profile']],
    'usuarios'   => ['controller' => \App\Controllers\UsuariosController::class, 'methods' => ['index','create','store','edit','update']],
    'vitrine'    => ['controller' => \App\Controllers\VitrineController::class, 'methods' => ['index','catalogo','papelaria','produto','cadastrarConta','sairConta','checkoutMercadoPago','statusMercadoPago']],
    'produtos'   => ['controller' => \App\Controllers\ProdutosController::class, 'methods' => ['index','print','create','store','edit','update','delete']],
    'fornecedores' => ['controller' => \App\Controllers\FornecedoresController::class, 'methods' => ['index','store','update','storeNota','updateNota','baixarNota','cancelarNota']],
    'media'      => ['controller' => \App\Controllers\MediaController::class, 'methods' => ['estoque']],
    'config'     => ['controller' => \App\Controllers\ConfigController::class, 'methods' => ['index','update','add_categoria','delete_categoria']],
    'mercadolivre' => ['controller' => \App\Controllers\MercadoLivreController::class, 'methods' => ['connect','callback','disconnect','publish']],
    'pdv'        => ['controller' => \App\Controllers\PdvController::class, 'methods' => ['index','finalizarVenda','getprodutos','imprimir']],
    'relatorios' => ['controller' => \App\Controllers\RelatoriosController::class, 'methods' => ['index','print']],
    'login'      => ['controller' => \App\Controllers\AuthController::class, 'methods' => ['index','login','logout']],
    'logout'     => ['controller' => \App\Controllers\AuthController::class, 'methods' => ['logout']],
];

if (isset($routes[$module])) {
    $route = $routes[$module];
    $method = $action;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
        $method = $_POST['_method'];
    }

    $postOnlyMethods = [
        'clientes' => ['store', 'update', 'delete'],
        'compras' => ['store', 'updateStatus', 'delete'],
        'termos' => ['store', 'delete'],
        'config' => ['update', 'add_categoria', 'delete_categoria'],
        'estoque' => ['store', 'update', 'delete', 'movimentar'],
        'financeiro' => ['store', 'update', 'delete'],
        'fornecedores' => ['store', 'update', 'storeNota', 'updateNota', 'baixarNota', 'cancelarNota'],
        'gastos_pessoais' => ['store', 'update', 'delete', 'addCategoria', 'deleteCategoria'],
        'login' => ['login'],
        'mercadolivre' => ['disconnect', 'publish'],
        'os' => ['store', 'update', 'delete', 'storeModelo'],
        'pdv' => ['finalizarVenda'],
        'produtos' => ['store', 'update', 'delete'],
        'recados' => ['store', 'updateStatus', 'delete'],
        'tecnicos' => ['store'],
        'usuarios' => ['store', 'update'],
        'vitrine' => ['cadastrarConta', 'checkoutMercadoPago'],
    ];

    if (isset($postOnlyMethods[$module]) && in_array($method, $postOnlyMethods[$module], true) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        echo 'Metodo nao permitido para esta acao.';
        exit;
    }

    $permissions = [
        'usuarios' => ['Administrador'],
        'config' => ['Administrador'],
        'mercadolivre' => ['Administrador'],
        'financeiro' => ['Administrador', 'Financeiro'],
        'gastos_pessoais' => ['Administrador'],
        'relatorios' => ['Administrador', 'Financeiro'],
        'estoque' => ['Administrador', 'Estoque'],
        'produtos' => ['Administrador', 'Estoque'],
        'fornecedores' => ['Administrador', 'Estoque', 'Financeiro'],
        'compras' => ['Administrador', 'Estoque', 'Financeiro', 'Atendente', 'Técnico', 'Tecnico', 'TÃ©cnico', 'TÃƒÂ©cnico'],
        'termos' => ['Administrador', 'Estoque', 'Financeiro', 'Atendente'],
        'recados' => ['Administrador', 'Atendente'],
        'pdv' => ['Administrador', 'Atendente'],
        'clientes' => ['Administrador', 'Atendente', 'Técnico', 'Tecnico', 'TÃ©cnico'],
        'os' => ['Administrador', 'Atendente', 'Técnico', 'Tecnico', 'TÃ©cnico'],
        'tecnicos' => ['Administrador', 'Atendente'],
    ];

    if ($protectInternalRoutes && isset($permissions[$module])) {
        $profile = current_user_profile();
        if (!in_array($profile, $permissions[$module], true)) {
            http_response_code(403);
            echo 'Acesso negado para o seu perfil.';
            exit;
        }
    }

    if (in_array($method, $route['methods'], true)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (request_exceeds_post_limit()) {
                abort_post_too_large();
            }

            $csrfExempt = ($module === 'vitrine' && $method === 'statusMercadoPago');
            if (!$csrfExempt && !csrf_verify()) {
                abort_csrf();
            }
        }

        $ctrl = new $route['controller']();
        $params = array_slice($parts, 2);
        call_user_func_array([$ctrl, $method], $params);
    } else {
        $ctrl = new $route['controller']();
        $ctrl->index();
    }
} else {
    $ctrl = $isVitrineMode
        ? new \App\Controllers\VitrineController()
        : new \App\Controllers\DashboardController();
    $ctrl->index();
}
