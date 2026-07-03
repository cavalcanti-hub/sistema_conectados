<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Conectados') ?></title>
    <meta name="theme-color" content="#2d2dff">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Conectados">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= app_url('favicon.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= app_url('assets/icons/icon-16x16.png') ?>">
    <link rel="apple-touch-icon" href="<?= app_url('assets/icons/icon-180x180.png') ?>">
    <link rel="manifest" href="<?= app_url('manifest.webmanifest') ?>?v=20260703-electric-blue">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url('assets/css/index.css') ?>?v=20260703-electric-blue">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        .badge { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:600; }
        .badge-blue { background:rgba(59,130,246,.12);color:#2563eb; }
        .badge-yellow { background:rgba(234,179,8,.12);color:#a16207; }
        .badge-green { background:rgba(16,185,129,.12);color:#065f46; }
        .badge-red { background:rgba(239,68,68,.12);color:#dc2626; }
        .badge-gray { background:rgba(100,116,139,.12);color:#475569; }
        .badge-purple { background:rgba(139,92,246,.12);color:#6d28d9; }
        .alert-success { background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 20px;border-radius:10px;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px; }
        .mobile-toggle { display:none;background:none;border:none;cursor:pointer;padding:8px; }
        .sidebar-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99; }
        .system-confirm-overlay { display:none;position:fixed;inset:0;background:rgba(15,23,42,.58);z-index:2400;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(3px); }
        .system-confirm-overlay.active { display:flex; }
        .system-confirm-dialog { width:min(430px,100%);background:var(--bg-card);color:var(--text-main);border:1px solid var(--border);border-radius:16px;box-shadow:0 28px 80px -36px rgba(0,0,0,.7);overflow:hidden; }
        .system-confirm-head { display:flex;gap:.9rem;align-items:flex-start;padding:1.25rem 1.25rem .75rem; }
        .system-confirm-icon { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:#fff1f2;color:var(--danger);flex:0 0 auto; }
        .system-confirm-dialog h3 { font-family:'Outfit',sans-serif;font-size:1.05rem;font-weight:800;margin:0 0 .25rem;color:inherit; }
        .system-confirm-dialog p { font-size:.9rem;line-height:1.45;color:var(--text-muted);margin:0; }
        .system-confirm-actions { display:grid;grid-template-columns:1fr 1fr;gap:.75rem;padding:.35rem 1.25rem 1.25rem; }
        .system-confirm-actions .btn { width:100%;min-height:46px;border-radius:12px;font-size:.9rem; }
        .system-confirm-cancel { background:#f8fafc;color:#0f172a;border:1px solid var(--border); }
        .system-confirm-danger { background:var(--danger);color:#fff !important; }
        @media(max-width:1024px){
            .mobile-toggle{display:flex;}
            .sidebar-overlay.active{display:block;}
        }
    </style>
</head>
<body>
<div class="app-container">
    <div class="sidebar-overlay" id="overlay" onclick="document.body.classList.remove('sidebar-open');document.getElementById('overlay').classList.remove('active')"></div>
    
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header" style="padding:1.5rem 1rem;">
            <img src="<?= app_url('assets/img/logo.png') ?>" alt="Conectados" style="width:100%;max-width:200px;height:auto;display:block;margin:0 auto;filter:brightness(1.42) saturate(1.12) drop-shadow(0 0 5px rgba(255,255,255,.86)) drop-shadow(0 0 16px rgba(96,190,255,.78)) drop-shadow(0 10px 20px rgba(0,0,0,.28));">
        </div>
        
        <nav>
            <ul class="sidebar-menu">
                <?php
                $current = explode('/', ($_GET['url'] ?? 'dashboard'))[0];
                $menuItems = [
                    'dashboard'  => ['icon'=>'layout-dashboard','label'=>'Dashboard'],
                    'recados'    => ['icon'=>'message-square-text','label'=>'Recados'],
                    'os'         => ['icon'=>'file-text','label'=>'Ordens de Serviço'],
                    'clientes'   => ['icon'=>'users','label'=>'Clientes'],
                    'produtos'   => ['icon'=>'shopping-bag', 'label'=>'Produtos (Loja)'],
                    'fornecedores' => ['icon'=>'truck','label'=>'Fornecedores'],
                    'compras'    => ['icon'=>'clipboard-list', 'label'=>'Solicitações de Compra'],
                    'termos'     => ['icon'=>'file-signature', 'label'=>'Termos Compra/Venda'],
                    'vitrine'    => ['icon'=>'monitor','label'=>'Vitrine Virtual'],
                    'financeiro' => ['icon'=>'dollar-sign','label'=>'Financeiro'],
                    'gastos_pessoais' => ['icon'=>'wallet','label'=>'Gastos Pessoais'],
                    'usuarios'   => ['icon'=>'user-cog','label'=>'Usuarios'],
                    'pdv'        => ['icon'=>'shopping-cart','label'=>'PDV / Balcão'],
                    'relatorios' => ['icon'=>'bar-chart-3','label'=>'Relatórios'],
                    'config'     => ['icon'=>'settings','label'=>'Configurações'],
                ];
                foreach ($menuItems as $url => $menuItem): ?>
                <li class="menu-item">
                    <a href="<?= e(route_url($url)) ?>" class="menu-link <?= $current === $url ? 'active' : '' ?>">
                        <i data-lucide="<?= e($menuItem['icon']) ?>"></i>
                        <?= e($menuItem['label']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div style="padding:1rem;border-top:1px solid rgba(255,255,255,.05);margin-top:auto;">
            <div style="display:flex;align-items:center;gap:10px;padding:10px;background:rgba(255,255,255,.05);border-radius:10px;">
                <div style="width:36px;height:36px;background:var(--accent);border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--primary);flex-shrink:0;">A</div>
                <div style="overflow:hidden;">
                    <p style="font-size:.85rem;color:white;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Admin</p>
                    <p style="font-size:.7rem;color:#64748b;">Administrador</p>
                </div>
                <a href="<?= e(route_url('logout')) ?>" style="margin-left:auto;color:#64748b;opacity:.7;" title="Sair"><i data-lucide="log-out" style="width:16px;"></i></a>
            </div>
        </div>
    </aside>

    <main class="main">
        <header class="app-topbar">
            <div class="app-topbar-title" style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-toggle" onclick="document.body.classList.toggle('sidebar-open');document.getElementById('overlay').classList.toggle('active')">
                    <i data-lucide="menu" style="color:var(--primary);"></i>
                </button>
                <div>
                    <h1 style="font-size:1.5rem;font-family:'Outfit',sans-serif;"><?= e($page_title ?? '') ?></h1>
                    <p style="color:var(--text-muted);font-size:.85rem;"><?= date('l, d \d\e F \d\e Y') ?></p>
                </div>
            </div>
            <div class="app-topbar-actions">
                <form class="global-search" action="<?= e(route_url()) ?>" method="GET">
                    <input type="hidden" name="url" value="os">
                    <input type="text" name="search" class="form-control" placeholder="Busca global..." value="<?= e($_GET['search'] ?? '') ?>">
                    <i data-lucide="search"></i>
                </form>
                <a href="<?= e(route_url('os/create')) ?>" class="btn btn-primary topbar-action-btn" style="text-decoration:none;white-space:nowrap;">
                    <i data-lucide="plus"></i> Nova OS
                </a>
                <a href="<?= e(route_url('pdv')) ?>" class="btn topbar-action-btn topbar-action-secondary" style="text-decoration:none;white-space:nowrap;">
                    <i data-lucide="shopping-cart"></i> PDV
                </a>
            </div>
        </header>
        
        <?php if (!empty($_GET['success']) && !in_array($current ?? '', ['compras'], true)): ?>
        <div class="alert-success system-flash"><i data-lucide="check-circle" style="width:18px;"></i> Operação realizada com sucesso!</div>
        <?php endif; ?>

