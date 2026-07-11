<?php

$baseDir = dirname(__DIR__);
$config = require $baseDir . '/app/Config/routes.php';
$lines = [
    '# Mapa de rotas — Sistema Conectados v1.0.1-security',
    '',
    'Gerado a partir do registro executável `app/Config/routes.php`. Nenhuma credencial é incluída.',
    '',
    '| Caminho | Métodos | Controlador | Ação | Pública | Autenticação | Permissão/perfis | CSRF | Resposta | Observação |',
    '|---|---|---|---|---|---|---|---|---|---|',
];
foreach ($config['routes'] as $route) {
    $controller = basename(str_replace('\\', '/', $route['controller']));
    $profiles = $route['profiles'] !== [] ? implode(', ', $route['profiles']) : '—';
    $external = $route['external_auth'] ? 'Autenticação externa: ' . $route['external_auth'] : '';
    $lines[] = sprintf(
        '| `/%s` | %s | %s | `%s` | %s | %s | %s | %s | %s | %s |',
        $route['path'], implode(', ', $route['methods']), $controller, $route['action'],
        $route['public'] ? 'Sim' : 'Não', $route['auth'] ? 'Obrigatória' : 'Não',
        $profiles, $route['csrf'] ? 'Sim' : 'Não', $route['response'], $external
    );
}
$lines[] = '';
$lines[] = '## Aliases de compatibilidade';
$lines[] = '';
$lines[] = '| Caminho legado | Destino | Métodos |';
$lines[] = '|---|---|---|';
foreach ($config['aliases'] as $path => $alias) {
    $target = $alias['target'] !== '' ? '/' . $alias['target'] : 'raiz conforme modo da aplicação';
    $lines[] = '| `/' . $path . '` | ' . $target . ' | ' . implode(', ', $alias['methods']) . ' |';
}
$lines[] = '';
$lines[] = '> Logout interno usa formulários POST com CSRF. `/login/logout` permanece apenas como alias legado POST.';

file_put_contents($baseDir . '/docs/MAPA_DE_ROTAS.md', implode(PHP_EOL, $lines) . PHP_EOL);
echo 'MAPA_DE_ROTAS.md gerado com ' . count($config['routes']) . ' rotas.' . PHP_EOL;
