<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit(1);
}

$base = dirname(__DIR__);
$releaseDir = $base . DIRECTORY_SEPARATOR . 'release';
$zipPath = $releaseDir . DIRECTORY_SEPARATOR . 'SistemaConectados-v1.0.1-security.zip';
$manifestPath = $releaseDir . DIRECTORY_SEPARATOR . 'SistemaConectados-v1.0.1-security.files.txt';

$required = [
    '.htaccess',
    '.user.ini',
    '.env.example',
    'app',
    'public',
    'resources',
    'scripts',
    'database',
    'database/schema.sql',
    'database/schema_current.sql',
    'database/schema_v2.sql',
    'database/migrations/2026_05_12_estabilizacao_producao.sql',
    'database/migrations/2026_05_15_security_indexes.sql',
    'database/migrations/2026_07_06_compras_notas_os.sql',
    'database/migrations/2026_07_06_mercado_pago_point_orders.sql',
    'database/migrations/2026_07_06_os_fotos_saida.sql',
    'database/migrations/2026_07_06_point_smart_taxas.sql',
    'database/migrations/2026_07_11_expand_device_secret.sql',
    'database/migrations/2026_07_11_pdv_caixa_runtime_schema.sql',
    'resources/views/print/a4.php',
    'resources/views/print/thermal_56.php',
    'resources/views/print/thermal_80.php',
];

$roots = ['app', 'public', 'resources', 'scripts', 'database'];
$rootFiles = ['.htaccess', '.user.ini', '.env.example'];
$committedOverrides = [
    'app/Views/layout/public_header.php',
];
$sensitivePatterns = [
    '#(^|/)\.git(/|$)#',
    '#(^|/)\.env$#',
    '#(^|/)env\.production$#i',
    '#(^|/)private_debug(/|$)#i',
    '#(^|/)release(/|$)#i',
    '#(^|/).+\.zip$#i',
    '#(^|/).+\.log$#i',
    '#(^|/)(cookies?|sessions?|cache|backups?|dumps?|tmp_test_assets|fixtures)(/|$)#i',
    '#(^|/)(php\.ini|\.idea|\.vscode)(/|$)#i',
    '#(^|/)(public_cookie|storage_cookie)\.txt$#i',
    '#(^|/).+\.(sqlite|sqlite3|db)$#i',
];

if (!extension_loaded('zip')) {
    fwrite(STDERR, "Extensao zip ausente no PHP CLI.\n");
    exit(2);
}

foreach ($required as $path) {
    if (!file_exists($base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path))) {
        fwrite(STDERR, "Arquivo obrigatorio ausente: {$path}\n");
        exit(3);
    }
}

if (!is_dir($releaseDir)) {
    mkdir($releaseDir, 0775, true);
}

@unlink($zipPath);
@unlink($manifestPath);

$normalize = static function (string $path) use ($base): string {
    $relative = str_replace('\\', '/', substr($path, strlen($base) + 1));
    return ltrim($relative, '/');
};

$isSensitive = static function (string $relative) use ($sensitivePatterns): bool {
    foreach ($sensitivePatterns as $pattern) {
        if (preg_match($pattern, $relative)) {
            return true;
        }
    }
    if (str_starts_with($relative, 'public/uploads/')) {
        return !preg_match('#^public/uploads/(?:\.gitkeep|\.htaccess|estoque/\.gitkeep|os/\.gitkeep)$#', $relative);
    }
    if ($relative === 'scripts/build_release.php') {
        return true;
    }
    return false;
};

$entries = [];
foreach ($rootFiles as $file) {
    $entries[$file] = $base . DIRECTORY_SEPARATOR . $file;
}
foreach ($roots as $root) {
    $rootPath = $base . DIRECTORY_SEPARATOR . $root;
    $entries[$root] = $rootPath;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        $relative = $normalize($item->getPathname());
        if ($isSensitive($relative)) {
            continue;
        }
        $entries[$relative] = $item->getPathname();
    }
}

ksort($entries, SORT_STRING);

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Nao foi possivel criar o ZIP.\n");
    exit(4);
}

$included = [];
foreach ($entries as $relative => $absolute) {
    $relative = str_replace('\\', '/', $relative);
    if ($isSensitive($relative)) {
        fwrite(STDERR, "Arquivo sensivel bloqueado: {$relative}\n");
        $zip->close();
        @unlink($zipPath);
        exit(5);
    }
    if (is_dir($absolute)) {
        $name = rtrim($relative, '/') . '/';
        $zip->addEmptyDir($name);
        $zip->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, 040755 << 16);
        $included[] = $name;
        continue;
    }
    if (in_array($relative, $committedOverrides, true)) {
        $spec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open(['git', 'show', 'HEAD:' . $relative], $spec, $pipes, $base);
        $content = is_resource($process) ? stream_get_contents($pipes[1]) : false;
        $error = is_resource($process) ? stream_get_contents($pipes[2]) : '';
        if (is_resource($process)) {
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            $exit = proc_close($process);
        } else {
            $exit = 1;
        }
        if ($exit !== 0 || $content === false) {
            fwrite(STDERR, "Falha ao obter versao commitada de {$relative}: {$error}\n");
            $zip->close();
            @unlink($zipPath);
            exit(6);
        }
        $zip->addFromString($relative, $content);
    } else {
        $zip->addFile($absolute, $relative);
    }
    $zip->setExternalAttributesName($relative, ZipArchive::OPSYS_UNIX, 0100644 << 16);
    $included[] = $relative;
}

$zip->close();

file_put_contents($manifestPath, implode(PHP_EOL, $included) . PHP_EOL);
echo 'ZIP: ' . $zipPath . PHP_EOL;
echo 'MANIFEST: ' . $manifestPath . PHP_EOL;
echo 'ARQUIVOS: ' . count($included) . PHP_EOL;
