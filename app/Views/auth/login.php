<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Conectados</title>
    <meta name="theme-color" content="#00349a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Conectados">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= app_url('favicon.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= app_url('assets/icons/icon-16x16.png') ?>">
    <link rel="apple-touch-icon" href="<?= app_url('assets/icons/icon-180x180.png') ?>">
    <link rel="manifest" href="<?= app_url('manifest.webmanifest') ?>?v=20260704-pwa-install-fix">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(ellipse at top right, #00349a, #001233);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .card {
            background: rgba(0, 52, 154, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn .6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .logo-img {
            width: 180px;
            height: auto;
            display: block;
            margin: 0 auto 1.5rem;
            filter:
                brightness(1.4)
                saturate(1.12)
                drop-shadow(0 0 5px rgba(255, 255, 255, 0.86))
                drop-shadow(0 0 16px rgba(96, 190, 255, 0.78))
                drop-shadow(0 10px 20px rgba(0, 0, 0, 0.28));
        }
        h2 { font-family: 'Outfit', sans-serif; color: white; text-align: center; margin-bottom: .25rem; font-size: 1.5rem; }
        .sub { color: #94a3b8; text-align: center; font-size: .85rem; margin-bottom: 2.5rem; }
        label { display: block; color: #cbd5e1; font-size: .82rem; margin-bottom: .5rem; font-weight: 500; }
        input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-family: 'Inter', sans-serif;
            font-size: .95rem;
            transition: all .3s ease;
        }
        input:focus {
            outline: none;
            border-color: #00349a;
            background: rgba(255, 255, 255, 0.07);
            box-shadow: 0 0 0 4px rgba(0, 52, 154, 0.2);
        }
        .form-group { margin-bottom: 1.5rem; }
        .btn {
            width: 100%;
            padding: 16px;
            background: #00349a;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            font-family: 'Outfit', sans-serif;
            transition: all .3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 1rem;
        }
        .btn:hover { background: #0044cc; transform: translateY(-2px); box-shadow: 0 10px 20px -10px rgba(0, 52, 154, 0.5); }
        .btn:active { transform: translateY(0); }
        .hint { text-align: center; margin-top: 2rem; font-size: .75rem; color: #64748b; }
        .hint strong { color: #94a3b8; }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
            padding: 12px;
            border-radius: 12px;
            font-size: .85rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="<?= app_url('assets/img/logo.png') ?>" alt="Conectados" class="logo-img">
        <p class="sub">Gestão de Assistência Técnica</p>

        <?php if (!empty($_GET['erro'])): ?>
            <div class="alert-error">E-mail ou senha incorretos.</div>
        <?php endif; ?>

        <form action="<?= route_url('login/login') ?>" method="POST">
            <div class="form-group">
                <label>E-mail ou Usuário</label>
                <input type="text" name="email" required placeholder="admin@conectadosassistencia.com.br ou Marcelo" autocomplete="username" autofocus>
            </div>
            <div class="form-group">
                <label>Senha de Acesso</label>
                <input type="password" name="senha" required placeholder="••••••••" autocomplete="current-password">
            </div>
            <button type="submit" class="btn">
                <i data-lucide="log-in" style="width:20px;"></i> Acessar Sistema
            </button>
        </form>

        <div class="hint">
            <strong>Acesso Restrito:</strong> CONECTADOS
        </div>
    </div>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                const swUrl = '<?= app_url('sw.js') ?>?v=20260703-local-login';
                navigator.serviceWorker.getRegistrations()
                    .then((registrations) => Promise.all(registrations.map((registration) => {
                        const scriptUrl = registration.active?.scriptURL || registration.waiting?.scriptURL || registration.installing?.scriptURL || '';
                        return scriptUrl.includes('/sw.js') || scriptUrl.includes('/public/sw.js') ? registration.unregister() : Promise.resolve();
                    })))
                    .then(() => navigator.serviceWorker.register(swUrl))
                    .catch((error) => console.warn('Service worker nao registrado:', error));
            });
        }
        lucide.createIcons();
    </script>
</body>
</html>
