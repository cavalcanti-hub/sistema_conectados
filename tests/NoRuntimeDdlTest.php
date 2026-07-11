<?php
$base = dirname(__DIR__);
$failures = [];
$ddl = '/\b(?:CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE|DROP\s+COLUMN|CREATE\s+INDEX|DROP\s+INDEX|TRUNCATE\s+(?:TABLE\s+)?)\b/i';
$runtimeRoots = [$base . '/app/Controllers', $base . '/app/Core', $base . '/app/Models', $base . '/app/Support', $base . '/public'];
$allowedRuntimeFiles = [
    realpath($base . '/app/Models/BackupModel.php'), // gera texto SQL no arquivo de backup; não executa DDL
];

foreach ($runtimeRoots as $root) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') continue;
        $path = $file->getRealPath();
        $source = file_get_contents($path);
        if (preg_match($ddl, $source) && !in_array($path, $allowedRuntimeFiles, true)) {
            $failures[] = str_replace($base . DIRECTORY_SEPARATOR, '', $path);
        }
    }
}

$databaseSource = file_get_contents($base . '/app/Config/Database.php');
if (preg_match($ddl, $databaseSource) || str_contains($databaseSource, 'ensureSchema')) $failures[] = 'Database contem DDL runtime';
foreach (['PdvModel.php' => 'ensurePdvSchema', 'MercadoPagoPointModel.php' => 'ensurePointOrderSchema'] as $file => $method) {
    $source = file_get_contents($base . '/app/Models/' . $file);
    if (str_contains($source, $method) || preg_match($ddl, $source)) $failures[] = $file . ' contem DDL runtime';
}
if ($failures !== []) {
    fwrite(STDERR, 'NoRuntimeDdlTest falhou: ' . implode(', ', array_unique($failures)) . PHP_EOL);
    exit(1);
}
echo "NoRuntimeDdlTest: OK (DDL automatico ausente do fluxo de requests)\n";
