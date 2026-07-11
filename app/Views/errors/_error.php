<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($statusCode . ' - Sistema Conectados') ?></title>
    <style>
        :root{font-family:Inter,system-ui,sans-serif;color:#172033;background:#f4f7fb}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;box-sizing:border-box}.box{width:min(560px,100%);background:#fff;border:1px solid #dce4ef;border-radius:20px;padding:36px;box-shadow:0 18px 50px rgba(30,55,90,.12);text-align:center}.code{font-size:3rem;font-weight:800;color:#1769e0;margin:0}.box h1{font-size:1.4rem;margin:8px 0 12px}.box p{color:#607089;line-height:1.6}.actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:24px}.actions a{padding:11px 18px;border-radius:10px;text-decoration:none;font-weight:700;background:#1769e0;color:#fff}.actions a.secondary{background:#edf3fb;color:#23415f}
    </style>
</head>
<body><main class="box">
    <p class="code"><?= e($statusCode) ?></p>
    <h1><?= e($errorTitle) ?></h1>
    <p><?= e($errorMessage ?? $defaultMessage) ?></p>
    <?php if ($statusCode === 405 && !empty($allowedMethods)): ?><p>Métodos permitidos: <?= e(implode(', ', $allowedMethods)) ?></p><?php endif; ?>
    <div class="actions">
        <a class="secondary" href="javascript:history.back()">Voltar</a>
        <?php if (!empty($_SESSION['usuario_id'])): ?><a href="<?= e(route_url('dashboard')) ?>">Ir ao painel</a><?php else: ?><a href="<?= e(route_url('login')) ?>">Ir ao login</a><?php endif; ?>
    </div>
</main></body></html>
