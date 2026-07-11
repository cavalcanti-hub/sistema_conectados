<?php
$currentUrl = $_GET['url'] ?? 'vitrine';
$isHome = $currentUrl === 'vitrine' || $currentUrl === 'vitrine/index' || $currentUrl === '';
$isCatalog = strpos($currentUrl, 'vitrine/catalogo') === 0;
$isStationery = strpos($currentUrl, 'vitrine/papelaria') === 0;
$headerCategories = array_values(array_filter($categorias ?? [], function ($cat) {
    return trim((string) $cat) !== '';
}));
$headerCategories = array_slice($headerCategories, 0, 6);
$searchValue = $filters['search'] ?? '';
$selectedCategory = $filters['categoria'] ?? '';
$companyName = trim($settings['nome_empresa'] ?? '') ?: 'Conectados';
$whats = trim($settings['whatsapp'] ?? '') ?: '5511999999999';
$publicAccountName = trim($_SESSION['conta_publica_nome'] ?? '');
$adminLoggedIn = !empty($_SESSION['usuario_id']);
$accountCreated = isset($_GET['account_success']);
$accountError = $_GET['account_error'] ?? '';
$shareTitle = $title ?? 'Vitrine Virtual - Conectados';
$shareDescription = trim((string) ($settings['vitrine_hero_texto'] ?? 'Escolha produtos com visual premium e atendimento direto pelo WhatsApp da loja.'));
$shareDescription = $shareDescription !== '' ? $shareDescription : 'Confira produtos, acessorios e ofertas da Conectados.';
$shareImage = app_url('assets/img/share-catalogo-facebook.jpg');
$shareUrl = ($isCatalog || $isHome) ? app_url('') : absolute_route_url($currentUrl);
$shareType = 'website';
$shareImageType = 'image/jpeg';
$safeOfferEmoji = static function (string $icon, string $fallback = '✨'): string {
    $icon = trim($icon);
    if ($icon === '') {
        return $fallback;
    }

    if (preg_match('/^[a-z0-9-]+$/', $icon)) {
        $legacyMap = [
            'truck' => '🚚',
            'package-check' => '📦',
            'badge-percent' => '🏷️',
            'credit-card' => '💳',
            'shield-check' => '🛡️',
            'message-circle' => '💬',
            'smartphone' => '📱',
            'headphones' => '🎧',
            'gift' => '🎁',
            'zap' => '⚡',
            'clock' => '⏰',
            'map-pin' => '📍',
            'wrench' => '🔧',
            'sparkles' => '✨',
            'star' => '⭐',
        ];
        return $legacyMap[$icon] ?? $fallback;
    }

    return function_exists('mb_substr') ? mb_substr($icon, 0, 4, 'UTF-8') : $icon;
};
$offerDefaults = [
    ['text' => 'Frete gratis em ofertas selecionadas', 'icon' => '🚚'],
    ['text' => 'Parcelamento facilitado', 'icon' => '💳'],
    ['text' => 'Compra segura na loja virtual', 'icon' => '🛡️'],
    ['text' => 'Retirada rapida na loja', 'icon' => '📦'],
    ['text' => 'Acessorios com preco especial', 'icon' => '🏷️'],
    ['text' => 'Atendimento direto pelo WhatsApp', 'icon' => '💬'],
];
$offerItems = [];
foreach ($offerDefaults as $index => $defaultOffer) {
    $number = $index + 1;
    $text = trim((string) ($settings['vitrine_offer_' . $number . '_texto'] ?? ''));
    $icon = trim((string) ($settings['vitrine_offer_' . $number . '_icone'] ?? ''));
    if ($text === '') {
        $text = $defaultOffer['text'];
    }
    if ($icon === '') {
        $icon = $defaultOffer['icon'];
    }
    $offerItems[] = [
        'text' => $text,
        'icon' => $safeOfferEmoji($icon, $defaultOffer['icon']),
    ];
}

$absolutePublicUrl = static function (string $url): string {
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }

    $baseUrl = rtrim((string) app_env('APP_URL', ''), '/');
    if ($baseUrl === '') {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $baseUrl = rtrim($protocol . $host, '/');
    }

    $path = '/' . ltrim($url, '/');
    $basePath = app_base_path();
    $baseUrlPath = rtrim((string) (parse_url($baseUrl, PHP_URL_PATH) ?: ''), '/');
    if ($basePath !== '' && $baseUrlPath === $basePath && str_starts_with($path, $basePath . '/')) {
        $path = substr($path, strlen($basePath));
    }

    return $baseUrl . '/' . ltrim($path, '/');
};

$crawlerImageUrl = static function (string $url): string {
    $parts = parse_url($url);
    $path = (string) ($parts['path'] ?? '');
    if ($path === '' || !preg_match('#/media/estoque/?$#', $path)) {
        return $url;
    }

    $query = [];
    parse_str((string) ($parts['query'] ?? ''), $query);
    $file = basename(str_replace('\\', '/', rawurldecode((string) ($query['file'] ?? ''))));
    if ($file === '' || !preg_match('/\.(jpe?g|jfif|png|gif|webp)$/i', $file)) {
        return $url;
    }

    $scheme = (string) ($parts['scheme'] ?? 'https');
    $host = (string) ($parts['host'] ?? '');
    if ($host === '') {
        return $url;
    }

    $port = isset($parts['port']) ? ':' . $parts['port'] : '';
    $version = isset($query['v']) && $query['v'] !== '' ? '?v=' . rawurlencode((string) $query['v']) : '';

    return $scheme . '://' . $host . $port . rtrim($path, '/') . '/' . rawurlencode($file) . $version;
};

if (!empty($produto) && is_array($produto)) {
    $shareTitle = (string) ($produto['nome'] ?? $shareTitle);
    $priceText = isset($produto['preco_venda']) && (float) $produto['preco_venda'] > 0
        ? ' por R$ ' . number_format((float) $produto['preco_venda'], 2, ',', '.')
        : '';
    $shareDescription = trim('Veja ' . $shareTitle . $priceText . ' na ' . $companyName . '.');
    $shareImage = !empty($produto['imagem_url']) ? (string) $produto['imagem_url'] : $shareImage;
    $shareUrl = absolute_route_url('vitrine/produto/' . (int) ($produto['id'] ?? 0));
    $shareType = 'product';
} elseif ($isStationery) {
    $shareTitle = 'Papelaria - ' . $companyName;
    $shareDescription = 'Materiais escolares, escritorio e papelaria com atendimento direto pela Conectados.';
} elseif ($isCatalog || $isHome) {
    $shareTitle = 'Catalogo de Produtos - ' . $companyName;
}

$shareImage = $crawlerImageUrl($absolutePublicUrl($shareImage));
$shareUrl = $absolutePublicUrl($shareUrl);
$shareImagePath = (string) (parse_url($shareImage, PHP_URL_PATH) ?: '');
$shareImageExt = strtolower(pathinfo($shareImagePath, PATHINFO_EXTENSION));
if ($shareImageExt === '') {
    $shareImageQuery = [];
    parse_str((string) (parse_url($shareImage, PHP_URL_QUERY) ?: ''), $shareImageQuery);
    $shareImageExt = strtolower(pathinfo((string) ($shareImageQuery['file'] ?? ''), PATHINFO_EXTENSION));
}
$shareImageType = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp',
    'gif' => 'image/gif',
][$shareImageExt] ?? $shareImageType;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Vitrine Virtual - Conectados') ?></title>
    <meta name="description" content="<?= htmlspecialchars($shareDescription, ENT_QUOTES) ?>">
    <meta name="theme-color" content="#2d2dff">
    <link rel="canonical" href="<?= htmlspecialchars($shareUrl, ENT_QUOTES) ?>">
    <meta property="og:type" content="<?= htmlspecialchars($shareType, ENT_QUOTES) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($companyName, ENT_QUOTES) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($shareTitle, ENT_QUOTES) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($shareDescription, ENT_QUOTES) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($shareUrl, ENT_QUOTES) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($shareImage, ENT_QUOTES) ?>">
    <meta property="og:image:url" content="<?= htmlspecialchars($shareImage, ENT_QUOTES) ?>">
    <meta property="og:image:secure_url" content="<?= htmlspecialchars($shareImage, ENT_QUOTES) ?>">
    <meta property="og:image:type" content="<?= htmlspecialchars($shareImageType, ENT_QUOTES) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= htmlspecialchars($shareTitle, ENT_QUOTES) ?>">
    <?php if (!empty($produto) && is_array($produto) && isset($produto['preco_venda'])): ?>
    <meta property="product:price:amount" content="<?= htmlspecialchars(number_format((float) $produto['preco_venda'], 2, '.', ''), ENT_QUOTES) ?>">
    <meta property="product:price:currency" content="BRL">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($shareTitle, ENT_QUOTES) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($shareDescription, ENT_QUOTES) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($shareImage, ENT_QUOTES) ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Conectados">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= app_url('favicon.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= app_url('assets/icons/icon-16x16.png') ?>">
    <link rel="apple-touch-icon" href="<?= app_url('assets/icons/icon-180x180.png') ?>">
    <link rel="manifest" href="<?= app_url('manifest.webmanifest') ?>?v=20260704-pwa-install-fix">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url('assets/css/index.css') ?>?v=20260703-compact-blue">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<style>
    body {
        --bg-main: #eaf4ff;
        background:
            radial-gradient(circle at 50% 0%, rgba(45, 45, 255, 0.08), transparent 34rem),
            linear-gradient(180deg, #f7fbff 0%, #edf6ff 48%, #e5f1ff 100%);
    }
    .commerce-shell {
        max-width: 1480px;
        margin: 0 auto;
        padding: 0 clamp(1rem, 2vw, 1.75rem);
    }
    .store-offer-strip {
        background:
            linear-gradient(90deg, rgba(255,255,255,.12), rgba(255,255,255,0) 22%, rgba(255,255,255,.12) 74%, rgba(255,255,255,0)),
            linear-gradient(90deg, #1012b8, var(--primary) 48%, #1012b8);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
        font-size: 0.8rem;
        font-weight: 800;
        overflow: hidden;
        user-select: none;
        cursor: pointer;
        box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.14);
    }
    .store-offer-strip:focus-visible {
        outline: 3px solid rgba(255, 255, 255, 0.85);
        outline-offset: -4px;
    }
    .offer-track {
        display: flex;
        width: max-content;
        min-height: 34px;
        align-items: center;
        animation: offerMarquee 24s linear infinite;
    }
    .store-offer-strip.is-paused .offer-track,
    .store-offer-strip:active .offer-track {
        animation-play-state: paused;
    }
    .offer-group {
        display: flex;
        align-items: center;
        gap: 1.8rem;
        padding-right: 1.8rem;
    }
    .offer-item {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        white-space: nowrap;
        letter-spacing: 0;
    }
    .offer-emoji {
        font-size: 1.05rem;
        line-height: 1;
        flex: 0 0 auto;
    }
    @keyframes offerMarquee {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }
    .store-header {
        position: sticky;
        top: 0;
        z-index: 1100;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.13), rgba(255, 255, 255, 0) 58%),
            linear-gradient(110deg, #3131ff 0%, var(--primary) 48%, #2020dc 100%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.16);
        box-shadow: 0 20px 46px -36px rgba(0, 20, 70, 0.88);
    }
    .store-header-main {
        display: grid;
        grid-template-columns: minmax(154px, 220px) minmax(360px, 1fr) auto;
        gap: clamp(0.8rem, 1.45vw, 1.35rem);
        align-items: center;
        min-height: 86px;
        padding-top: 0.82rem;
        padding-bottom: 0.82rem;
    }
    .brand-link {
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        min-width: 0;
    }
    .brand-logo {
        width: min(100%, 210px);
        max-height: 62px;
        height: auto;
        display: block;
        filter:
            brightness(1.45)
            saturate(1.12)
            drop-shadow(0 0 4px rgba(255, 255, 255, 0.9))
            drop-shadow(0 0 14px rgba(96, 190, 255, 0.85))
            drop-shadow(0 8px 18px rgba(0, 0, 0, 0.25));
    }
    .store-search {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(166px, 218px) 58px;
        gap: 0;
        align-items: stretch;
        min-height: 54px;
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(255, 255, 255, 0.76);
        border-radius: 14px;
        overflow: hidden;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            0 18px 42px -32px rgba(0, 0, 0, 0.62);
        transition: box-shadow .18s ease, transform .18s ease, border-color .18s ease;
    }
    .store-search:focus-within {
        border-color: rgba(255, 255, 255, 0.98);
        box-shadow:
            0 0 0 4px rgba(255, 255, 255, 0.2),
            0 22px 48px -30px rgba(0, 0, 0, 0.7);
        transform: translateY(-1px);
    }
    .store-search-input,
    .store-search-select {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        background: #fff;
    }
    .store-search-input {
        padding: 0 1rem 0 1.05rem;
    }
    .store-search-input input,
    .store-search-select select {
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        color: #0f172a;
        font-size: 0.95rem;
        line-height: 1.2;
    }
    .store-search-input input::placeholder {
        color: #64748b;
    }
    .store-search-select {
        position: relative;
        padding: 0 1rem;
        border-left: 1px solid rgba(0, 52, 154, 0.1);
        background: #f8fbff;
    }
    .store-search-select select {
        appearance: none;
        padding-right: 1.35rem;
        cursor: pointer;
        font-weight: 700;
    }
    .store-search-select::after {
        content: "";
        position: absolute;
        right: 1rem;
        width: 8px;
        height: 8px;
        border-right: 2px solid #1f2a44;
        border-bottom: 2px solid #1f2a44;
        transform: rotate(45deg) translateY(-2px);
        pointer-events: none;
    }
    .store-search-submit {
        border: none;
        background: #111827;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .18s ease, transform .18s ease;
    }
    .store-search-submit:hover {
        background: #020617;
    }
    .store-search-submit:active {
        transform: scale(0.97);
    }
    .store-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.56rem;
        min-width: 0;
    }
    .icon-link,
    .cart-trigger {
        width: 48px;
        height: 48px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.54);
        background: rgba(255, 255, 255, 0.96);
        color: #10233f;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        position: relative;
        cursor: pointer;
        box-shadow: 0 16px 28px -24px rgba(0, 0, 0, 0.6);
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
    }
    .icon-link:hover,
    .cart-trigger:hover {
        transform: translateY(-2px);
        background: #fff;
        box-shadow: 0 20px 34px -24px rgba(0, 0, 0, 0.72);
    }
    .icon-link:focus-visible,
    .cart-trigger:focus-visible,
    .store-search-submit:focus-visible,
    .store-nav-link:focus-visible {
        outline: 3px solid rgba(255, 255, 255, 0.72);
        outline-offset: 3px;
    }
    .store-actions .admin-panel-link {
        width: auto;
        min-width: max-content;
        min-height: 48px;
        padding: 0 1.05rem;
        gap: 0.55rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .cart-trigger {
        width: auto;
        padding: 0 0.7rem 0 1rem;
        gap: 0.65rem;
        font-weight: 800;
        border-radius: 999px;
        background: #0f172a;
        border-color: rgba(255, 255, 255, 0.32);
        color: #fff;
    }
    .cart-trigger-count {
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 999px;
        background: #fff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.74rem;
    }
    .favorites-trigger {
        position: relative;
    }
    .favorites-count {
        position: absolute;
        top: -5px;
        right: -2px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 999px;
        background: #e11d48;
        color: #fff;
        font-size: 0.68rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.95);
    }
    .store-nav {
        background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(245,249,255,.98));
        color: #092347;
        border-top: 1px solid rgba(0, 52, 154, 0.08);
        border-bottom: 1px solid rgba(0, 52, 154, 0.08);
        backdrop-filter: blur(14px);
        box-shadow: 0 18px 42px -36px rgba(0, 25, 80, 0.55);
    }
    .store-nav-inner {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 1rem;
        min-height: 58px;
        min-width: 0;
        position: relative;
    }
    .store-nav-inner::before,
    .store-nav-inner::after {
        content: "";
        position: absolute;
        top: 0.35rem;
        bottom: 0.35rem;
        width: 2rem;
        pointer-events: none;
        z-index: 2;
    }
    .store-nav-inner::before {
        left: 0;
        background: linear-gradient(90deg, rgba(245,249,255,.98), transparent);
    }
    .store-nav-inner::after {
        right: 0;
        background: linear-gradient(270deg, rgba(245,249,255,.98), transparent);
    }
    .store-nav-primary {
        display: flex;
        align-items: center;
        gap: .42rem;
        min-width: 0;
    }
    .store-nav-primary {
        overflow-x: auto;
        scrollbar-width: none;
        padding: .5rem 0;
        flex: 1 1 auto;
        scroll-padding-inline: 1rem;
    }
    .store-nav-primary::-webkit-scrollbar {
        display: none;
    }
    .store-nav-link {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 38px;
        padding: 0 .88rem;
        border: 1px solid transparent;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.54);
        color: #123057;
        font: inherit;
        text-decoration: none;
        white-space: nowrap;
        font-size: 0.84rem;
        font-weight: 800;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .store-nav-link i {
        width: 16px;
        height: 16px;
        color: #2f5eaa;
        transition: color .18s ease;
    }
    .store-nav-link::after {
        content: "";
        position: absolute;
        left: 1.05rem;
        right: 1.05rem;
        bottom: 4px;
        height: 2px;
        border-radius: 999px;
        background: transparent;
        transition: background .18s ease;
    }
    .store-nav-link:hover {
        background: #fff;
        color: var(--primary);
        transform: translateY(-1px);
        border-color: rgba(45, 45, 255, 0.12);
    }
    .store-nav-link.is-active {
        background: #fff;
        color: var(--primary);
        border-color: rgba(45, 45, 255, 0.16);
        box-shadow: 0 12px 26px -22px rgba(0, 52, 154, 0.7);
    }
    .store-nav-link.is-active i {
        color: var(--primary);
    }
    .store-nav-link.is-active::after {
        background: var(--primary);
    }
    .store-nav-link.is-emphasis {
        background: var(--primary);
        border-color: rgba(45, 45, 255, 0.2);
        color: #fff;
        box-shadow: 0 16px 28px -22px rgba(0, 52, 154, .85);
    }
    .store-nav-link.is-emphasis i {
        color: #fff;
    }
    .store-nav-link.is-muted {
        background: #f5f8fe;
        border-color: rgba(0, 52, 154, 0.08);
    }
    .category-dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #8fb2eb;
        flex: 0 0 auto;
    }
    .public-container {
        max-width: 1440px;
        margin: 0 auto;
        padding: 0 1.5rem 2rem;
    }
    .public-notice-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.52);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        z-index: 2400;
        backdrop-filter: blur(3px);
    }
    .public-notice-overlay.active {
        display: flex;
    }
    .public-notice-card {
        width: min(420px, 100%);
        background: #fff;
        border: 1px solid rgba(203, 213, 225, 0.9);
        border-radius: 18px;
        box-shadow: 0 28px 80px -36px rgba(0, 0, 0, 0.7);
        overflow: hidden;
    }
    .public-notice-head {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
        padding: 1.25rem 1.25rem 0.8rem;
    }
    .public-notice-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(0, 52, 154, 0.08);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .public-notice-card h3 {
        margin: 0 0 0.25rem;
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        color: #0f172a;
    }
    .public-notice-card p {
        margin: 0;
        color: #475569;
        font-size: 0.9rem;
        line-height: 1.45;
    }
    .public-notice-actions {
        padding: 0.35rem 1.25rem 1.25rem;
    }
    .public-notice-actions .btn {
        width: 100%;
        min-height: 46px;
        border-radius: 12px;
    }
    .sold-out-ribbon {
        position: absolute;
        left: -46px;
        top: 18px;
        z-index: 12;
        width: 160px;
        min-height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transform: rotate(-38deg);
        background: #dc2626;
        color: #fff;
        font-size: 0.76rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        box-shadow: 0 14px 26px -18px rgba(127, 29, 29, 0.85);
        pointer-events: none;
    }
    .is-sold-out .product-image-container img,
    .is-sold-out .featured-image img,
    .product-gallery.is-sold-out img,
    .is-sold-out .product-gallery img {
        filter: grayscale(0.18);
        opacity: 0.72;
    }
    .btn-sold-out,
    .btn-checkout.btn-sold-out,
    .featured-btn.btn-sold-out,
    .btn-buy-now.btn-sold-out {
        background: #e5e7eb;
        color: #64748b;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }
    .btn-sold-out:hover,
    .btn-checkout.btn-sold-out:hover,
    .featured-btn.btn-sold-out:hover,
    .btn-buy-now.btn-sold-out:hover {
        background: #e5e7eb;
        color: #64748b;
        transform: none;
        box-shadow: none;
    }
    @media (max-width: 1180px) {
        .store-header-main {
            grid-template-columns: minmax(130px, 210px) minmax(0, 1fr);
            min-height: auto;
        }
        .store-search {
            grid-column: 1 / -1;
            grid-row: 2;
        }
        .store-actions {
            justify-content: flex-end;
        }
        .offer-track {
            animation-duration: 22s;
        }
    }
    @media (max-width: 980px) {
        .store-nav-inner {
            align-items: stretch;
            flex-direction: column;
            gap: .35rem;
            padding-top: .35rem;
            padding-bottom: .35rem;
        }
    }
    @media (max-width: 780px) {
        .commerce-shell,
        .public-container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .offer-group {
            gap: 1.2rem;
            padding-right: 1.2rem;
        }
        .offer-track {
            min-height: 32px;
            animation-duration: 18s;
        }
        .store-header-main {
            grid-template-columns: auto minmax(0, 1fr);
            gap: .72rem;
            padding-top: .65rem;
            padding-bottom: .75rem;
        }
        .brand-logo {
            width: clamp(126px, 34vw, 172px);
            max-height: 52px;
        }
        .store-actions {
            gap: .42rem;
            overflow-x: auto;
            overscroll-behavior-inline: contain;
            scrollbar-width: none;
            padding: .1rem 0;
        }
        .store-actions::-webkit-scrollbar {
            display: none;
        }
        .icon-link,
        .cart-trigger {
            width: 44px;
            height: 44px;
            flex: 0 0 auto;
        }
        .store-actions .admin-panel-link {
            width: 44px;
            min-width: 44px;
            min-height: 44px;
            padding: 0;
            justify-content: center;
        }
        .store-actions .admin-panel-link span {
            display: none;
        }
        .cart-trigger {
            width: auto;
            height: 44px;
            padding: 0 .55rem 0 .78rem;
        }
        .store-nav-link {
            min-height: 38px;
            padding: 0 .75rem;
            font-size: .82rem;
        }
        .store-search {
            grid-template-columns: minmax(0, 1fr) minmax(130px, 38%) 52px;
            min-height: 48px;
            border-radius: 12px;
        }
        .store-search-input {
            grid-column: 1;
            min-height: 48px;
            padding-left: .9rem;
        }
        .store-search-select {
            padding: 0 .85rem;
        }
        .store-search-select::after {
            right: .82rem;
        }
        .store-search-input input,
        .store-search-select select {
            font-size: .9rem;
        }
        .store-search-submit {
            grid-column: 3;
            min-height: 48px;
        }
    }
    @media (max-width: 620px) {
        .store-search {
            grid-template-columns: minmax(0, 1fr) 50px;
        }
        .store-search-select {
            display: none;
        }
        .store-search-submit {
            grid-column: 2;
        }
    }
    @media (max-width: 520px) {
        .store-header-main {
            grid-template-columns: 1fr;
        }
        .brand-link {
            justify-content: center;
        }
        .store-actions {
            justify-content: center;
            width: 100%;
        }
        .cart-trigger > span:not(.cart-trigger-count) {
            display: none;
        }
        .cart-trigger {
            width: 44px;
            padding: 0;
            justify-content: center;
        }
    }
</style>
</head>
<body>
<div class="store-offer-strip" id="store-offer-strip" role="button" tabindex="0" aria-label="Faixa de ofertas. Clique para pausar ou continuar.">
    <div class="offer-track">
        <?php for ($repeat = 0; $repeat < 2; $repeat++): ?>
        <div class="offer-group" aria-hidden="<?= $repeat === 1 ? 'true' : 'false' ?>">
            <?php foreach ($offerItems as $offer): ?>
            <span class="offer-item">
                <span class="offer-emoji" aria-hidden="true"><?= htmlspecialchars($offer['icon']) ?></span>
                <?= htmlspecialchars($offer['text']) ?>
            </span>
            <?php endforeach; ?>
        </div>
        <?php endfor; ?>
    </div>
</div>

<header class="store-header">
    <div class="commerce-shell store-header-main">
        <a class="brand-link" href="<?= route_url('vitrine') ?>" aria-label="<?= htmlspecialchars($companyName) ?>">
            <img class="brand-logo" src="<?= app_url('assets/img/logo.png') ?>" alt="<?= htmlspecialchars($companyName) ?>">
        </a>

        <form class="store-search" action="<?= route_url('vitrine/catalogo') ?>" method="GET" role="search" aria-label="Buscar produtos na vitrine">
            <label class="store-search-input">
                <i data-lucide="search" style="width: 18px; height: 18px; color: #475569;"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($searchValue) ?>" placeholder="Pesquisar produtos, marcas e acessorios" aria-label="Pesquisar produtos" autocomplete="off">
            </label>
            <label class="store-search-select">
                <select name="categoria" aria-label="Filtrar por categoria">
                    <option value="">Categorias</option>
                    <?php foreach ($headerCategories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= strcasecmp($selectedCategory, $cat) === 0 ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="store-search-submit" type="submit" aria-label="Buscar">
                <i data-lucide="search" style="width: 20px; height: 20px;"></i>
            </button>
        </form>

        <div class="store-actions">
            <?php if ($adminLoggedIn): ?>
            <a class="icon-link admin-panel-link" href="<?= route_url('dashboard') ?>" title="Voltar para o painel admin" aria-label="Voltar para o painel admin">
                <i data-lucide="arrow-left" style="width: 19px; height: 19px;"></i>
                <span>Painel admin</span>
            </a>
            <?php endif; ?>
            <a class="icon-link" href="<?= route_url('vitrine') ?>" title="Inicio" aria-label="Ir para o inicio">
                <i data-lucide="house" style="width: 20px; height: 20px;"></i>
            </a>
            <button class="icon-link favorites-trigger" type="button" onclick="toggleFavorites()" title="Favoritos" aria-label="Abrir favoritos">
                <i data-lucide="heart" style="width: 20px; height: 20px;"></i>
                <span class="favorites-count" id="favorites-count">0</span>
            </button>
            <button class="icon-link" type="button" onclick="toggleAccountModal()" title="Conta" aria-label="Abrir conta">
                <i data-lucide="user" style="width: 20px; height: 20px;"></i>
            </button>
            <button onclick="toggleCart()" class="cart-trigger" type="button" title="Carrinho" aria-label="Abrir carrinho">
                <i data-lucide="shopping-cart" style="width: 19px; height: 19px;"></i>
                <span>Carrinho</span>
                <span class="cart-trigger-count" id="cart-count">0</span>
            </button>
        </div>
    </div>

    <div class="store-nav">
        <div class="commerce-shell store-nav-inner">
            <nav class="store-nav-primary" aria-label="Navegacao principal da loja">
            <a class="store-nav-link is-muted <?= $isHome ? 'is-active' : '' ?>" href="<?= route_url('vitrine') ?>">
                <i data-lucide="house"></i>
                Inicio
            </a>
            <a class="store-nav-link is-emphasis <?= $isCatalog && empty($selectedCategory) ? 'is-active' : '' ?>" href="<?= route_url('vitrine/catalogo') ?>">
                <i data-lucide="shopping-bag"></i>
                Todos os produtos
            </a>
            <a class="store-nav-link <?= $isStationery ? 'is-active' : '' ?>" href="<?= route_url('vitrine/papelaria') ?>">
                <i data-lucide="notebook-tabs"></i>
                Papelaria
            </a>
            <button class="store-nav-link is-muted" type="button" onclick="toggleDepartments()">
                <i data-lucide="panels-top-left"></i>
                Departamentos
            </button>
            <?php foreach ($headerCategories as $cat): ?>
            <a class="store-nav-link <?= strcasecmp($selectedCategory, $cat) === 0 ? 'is-active' : '' ?>" href="<?= route_url('vitrine/catalogo', ['categoria' => $cat]) ?>">
                <span class="category-dot" aria-hidden="true"></span>
                <?= htmlspecialchars($cat) ?>
            </a>
            <?php endforeach; ?>
            </nav>
        </div>
    </div>
</header>

<!-- Drawer do Carrinho -->
<div id="cart-drawer" style="position: fixed; top: 0; right: -400px; width: 400px; height: 100vh; background: var(--bg-card); z-index: 2000; box-shadow: -10px 0 30px rgba(0,0,0,0.2); transition: 0.4s; display: flex; flex-direction: column;">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <h3 class="brand-font">Meu Carrinho</h3>
        <button onclick="toggleCart()" style="background: none; border: none; cursor: pointer;"><i data-lucide="x"></i></button>
    </div>
    <div id="cart-items" style="flex: 1; overflow-y: auto; padding: 1.5rem;">
        <!-- Itens injetados via JS -->
    </div>
    <div id="cart-footer" style="padding: 1.5rem; border-top: 1px solid var(--border); background: rgba(0,0,0,0.02); display: none;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-weight: 700; font-size: 1.2rem;">
            <span>Total:</span>
            <span id="cart-total">R$ 0,00</span>
        </div>
        <button onclick="checkoutMP()" class="btn btn-primary" style="width: 100%; padding: 16px; border-radius: 12px; font-size: 1rem; background: #009EE3;">
            <i data-lucide="credit-card"></i> Comprar com Mercado Pago
        </button>
    </div>
</div>
<div id="cart-overlay" onclick="toggleCart()" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1999; display: none;"></div>

<div id="favorites-drawer" style="position: fixed; top: 0; left: -400px; width: 400px; height: 100vh; background: var(--bg-card); z-index: 2000; box-shadow: 10px 0 30px rgba(0,0,0,0.2); transition: 0.4s; display: flex; flex-direction: column;">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <h3 class="brand-font">Favoritos</h3>
        <button onclick="toggleFavorites()" style="background: none; border: none; cursor: pointer;"><i data-lucide="x"></i></button>
    </div>
    <div id="favorites-items" style="flex: 1; overflow-y: auto; padding: 1.5rem;"></div>
</div>
<div id="favorites-overlay" onclick="toggleFavorites()" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1999; display: none;"></div>

<div id="departments-modal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.48); z-index: 2100; display: none; align-items: center; justify-content: center; padding: 1rem;">
    <div style="width: min(880px, 100%); max-height: 85vh; overflow-y: auto; background: #fff; border-radius: 28px; padding: 1.5rem; box-shadow: 0 30px 70px -35px rgba(0,0,0,0.45);">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1rem;">
            <div>
                <h3 class="brand-font" style="margin:0;">Departamentos</h3>
                <p style="margin:0.35rem 0 0;color:var(--text-muted);">Acesse todas as categorias da vitrine.</p>
            </div>
            <button onclick="toggleDepartments()" style="background:none;border:none;cursor:pointer;"><i data-lucide="x"></i></button>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:0.85rem;">
            <a href="<?= route_url('vitrine/catalogo') ?>" class="btn btn-outline" style="text-decoration:none;">Todos os produtos</a>
            <?php foreach (($categorias ?? []) as $cat): ?>
            <a href="<?= route_url('vitrine/catalogo', ['categoria' => $cat]) ?>" class="btn btn-outline" style="text-decoration:none;justify-content:flex-start;"><?= htmlspecialchars($cat) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div id="account-modal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.48); z-index: 2100; display: none; align-items: center; justify-content: center; padding: 1rem;">
    <div style="width:min(520px,100%); background:#fff; border-radius:28px; padding:1.5rem; box-shadow:0 30px 70px -35px rgba(0,0,0,0.45);">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1rem;">
            <div>
                <h3 class="brand-font" style="margin:0;"><?= $publicAccountName !== '' ? 'Sua conta' : 'Criar conta' ?></h3>
                <p style="margin:0.35rem 0 0;color:var(--text-muted);"><?= $publicAccountName !== '' ? 'Conta criada com sucesso na vitrine.' : 'Cadastre-se para salvar seus dados e continuar comprando com mais facilidade.' ?></p>
            </div>
            <button onclick="toggleAccountModal()" style="background:none;border:none;cursor:pointer;"><i data-lucide="x"></i></button>
        </div>
        <?php if ($publicAccountName !== ''): ?>
        <div class="card" style="padding:1.25rem;">
            <p style="margin:0 0 0.5rem;font-weight:800;"><?= htmlspecialchars($publicAccountName) ?></p>
            <p style="margin:0;color:var(--text-muted);"><?= htmlspecialchars($_SESSION['conta_publica_email'] ?? '') ?></p>
            <form action="<?= e(route_url('vitrine/sairConta')) ?>" method="POST" style="margin-top:1rem;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline" style="width:100%;">Sair</button>
            </form>
        </div>
        <?php else: ?>
        <form action="<?= e(route_url('vitrine/cadastrarConta')) ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">WhatsApp</label>
                <input type="text" name="whatsapp" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" required autocomplete="new-password">
            </div>
            <?php if ($accountError !== ''): ?>
            <div class="alert-box alert-yellow" style="margin-bottom:1rem;">
                <?= $accountError === 'exists' ? 'Ja existe uma conta com esse e-mail.' : 'Preencha os campos obrigatorios.' ?>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary" style="width:100%;">Criar conta</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div id="public-notice-overlay" class="public-notice-overlay" aria-hidden="true" onclick="fecharAvisoVitrine(event)">
    <div class="public-notice-card" role="dialog" aria-modal="true" aria-labelledby="public-notice-title" aria-describedby="public-notice-message" onclick="event.stopPropagation()">
        <div class="public-notice-head">
            <div class="public-notice-icon">
                <i id="public-notice-icon" data-lucide="info"></i>
            </div>
            <div>
                <h3 id="public-notice-title">Aviso</h3>
                <p id="public-notice-message">Verifique as informações da compra.</p>
            </div>
        </div>
        <div class="public-notice-actions">
            <button type="button" class="btn btn-primary" id="public-notice-ok" onclick="fecharAvisoVitrine()">Entendi</button>
        </div>
    </div>
</div>

<script>
let cart = [];
let favoriteIds = JSON.parse(localStorage.getItem('conectados_favorites') || '[]');

function abrirAvisoVitrine(title, message, icon = 'info') {
    const overlay = document.getElementById('public-notice-overlay');
    const titleEl = document.getElementById('public-notice-title');
    const messageEl = document.getElementById('public-notice-message');
    const iconEl = document.getElementById('public-notice-icon');
    const okBtn = document.getElementById('public-notice-ok');

    titleEl.textContent = title || 'Aviso';
    messageEl.textContent = message || 'Verifique as informações da compra.';
    iconEl.setAttribute('data-lucide', icon);
    overlay.classList.add('active');
    overlay.setAttribute('aria-hidden', 'false');
    lucide.createIcons();
    okBtn.focus();
}

function fecharAvisoVitrine(event) {
    if (event && event.target && event.target.id !== 'public-notice-overlay') {
        return;
    }

    const overlay = document.getElementById('public-notice-overlay');
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden', 'true');
}

function productThumbHtml(src, size = 60) {
    const boxStyle = `width:${size}px;height:${size}px;border-radius:${size >= 64 ? 10 : 8}px;object-fit:cover;background:#f1f5f9;flex:0 0 auto;`;
    if (src) {
        return `<img src="${src}" style="${boxStyle}" onerror="this.outerHTML = productThumbHtml('', ${size}); lucide.createIcons();">`;
    }

    return `<div style="${boxStyle}display:flex;align-items:center;justify-content:center;color:#94a3b8;"><i data-lucide="package" style="width:22px;height:22px;"></i></div>`;
}

function toggleCart() {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-overlay');
    const isOpen = drawer.style.right === '0px';
    drawer.style.right = isOpen ? '-400px' : '0px';
    overlay.style.display = isOpen ? 'none' : 'block';
}

function toggleAccountModal() {
    const modal = document.getElementById('account-modal');
    modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
}

function toggleDepartments() {
    const modal = document.getElementById('departments-modal');
    modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
}

function toggleFavorites() {
    const drawer = document.getElementById('favorites-drawer');
    const overlay = document.getElementById('favorites-overlay');
    const isOpen = drawer.style.left === '0px';
    drawer.style.left = isOpen ? '-400px' : '0px';
    overlay.style.display = isOpen ? 'none' : 'block';
    if (!isOpen) renderFavorites();
}

function saveFavorites() {
    localStorage.setItem('conectados_favorites', JSON.stringify(favoriteIds));
}

function updateFavoritesCount() {
    const count = document.getElementById('favorites-count');
    if (count) count.innerText = favoriteIds.length;
}

function toggleFavorite(id) {
    id = Number(id);
    const exists = favoriteIds.includes(id);
    favoriteIds = exists ? favoriteIds.filter(item => item !== id) : [...favoriteIds, id];
    saveFavorites();
    updateFavoritesCount();
    renderFavoriteButtons();
    renderFavorites();
}

function renderFavoriteButtons() {
    document.querySelectorAll('[data-favorite-id]').forEach(button => {
        const id = Number(button.dataset.favoriteId);
        const active = favoriteIds.includes(id);
        button.style.background = active ? 'rgba(239, 68, 68, 0.12)' : '#fff';
        button.style.color = active ? '#dc2626' : '#10233f';
    });
}

function renderFavorites() {
    const container = document.getElementById('favorites-items');
    if (!container) return;
    const products = window.vitrineProducts || {};
    if (favoriteIds.length === 0) {
        container.innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:4rem 1rem;"><i data-lucide="heart" style="width:48px;opacity:0.12;margin-bottom:1rem;"></i><p>Nenhum favorito salvo.</p></div>';
        lucide.createIcons();
        return;
    }
    let html = '';
    favoriteIds.forEach(id => {
        const product = products[id];
        if (!product) return;
        html += `
            <div style="display:flex;gap:1rem;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--border);">
                ${productThumbHtml(product.imagem, 64)}
                <div style="flex:1;">
                    <h4 style="margin:0 0 0.25rem;font-size:0.92rem;">${product.nome}</h4>
                    <p style="margin:0 0 0.75rem;color:var(--primary);font-weight:800;">${product.preco}</p>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                        <a href="${product.link}" class="btn btn-outline" style="text-decoration:none;padding:8px 12px;">Ver</a>
                        <button onclick="toggleFavorite(${id})" class="btn btn-secondary" type="button" style="padding:8px 12px;">Remover</button>
                    </div>
                </div>
            </div>
        `;
    });
    container.innerHTML = html || '<div style="text-align:center;color:var(--text-muted);padding:4rem 1rem;"><p>Abra esta lista a partir da home ou catalogo para carregar os produtos.</p></div>';
    lucide.createIcons();
}

function addToCart(id, nome, preco, imagem) {
    const product = window.vitrineProducts && window.vitrineProducts[id] ? window.vitrineProducts[id] : null;
    if (product && product.disponivel === false) {
        abrirAvisoVitrine('Produto esgotado', 'Este produto esta sem estoque no momento.', 'shopping-cart');
        return;
    }

    const existing = cart.find(i => i.id === id);
    if (existing) { existing.qtd++; }
    else { cart.push({id, nome, preco : parseFloat(preco), imagem, qtd: 1}); }
    updateCart();
    if (id) toggleCart();
}

async function shareStoreContent(title, text, url, imageUrl) {
    let shareUrl = url || window.location.href;
    try {
        const parsedShareUrl = new URL(shareUrl, window.location.origin);
        if (parsedShareUrl.pathname.includes('/vitrine/produto/')) {
            parsedShareUrl.searchParams.set('preview', String(Math.floor(Date.now() / 1000)));
            shareUrl = parsedShareUrl.toString();
        }
    } catch (error) {}
    const payload = {
        title: title || document.title,
        url: shareUrl
    };

    if (navigator.share) {
        try {
            if (imageUrl) {
                try {
                    const imageResponse = await fetch(imageUrl, { cache: 'reload', credentials: 'same-origin' });
                    const imageBlob = imageResponse.ok ? await imageResponse.blob() : null;
                    if (imageBlob && imageBlob.type.startsWith('image/')) {
                        const imageExt = (imageBlob.type.split('/')[1] || 'jpg').replace('jpeg', 'jpg');
                        const imageFile = new File([imageBlob], `produto-conectados.${imageExt}`, { type: imageBlob.type });
                        const filePayload = {
                            title: payload.title,
                            text: `${title || payload.title}\n${shareUrl}`,
                            files: [imageFile]
                        };
                        if (!navigator.canShare || navigator.canShare(filePayload)) {
                            await navigator.share(filePayload);
                            return;
                        }
                    }
                } catch (error) {}
            }

            if (navigator.canShare && !navigator.canShare(payload)) {
                throw new Error('Compartilhamento indisponivel');
            }
            await navigator.share(payload);
            return;
        } catch (error) {
            if (error && error.name === 'AbortError') return;
        }
    }

    const fullText = payload.url;
    try {
        await navigator.clipboard.writeText(fullText);
        abrirAvisoVitrine('Link copiado', 'O link foi copiado para voce compartilhar.', 'share-2');
    } catch (error) {
        window.open(`https://wa.me/?text=${encodeURIComponent(fullText)}`, '_blank');
    }
}

function shareCatalogOnFacebook(url) {
    const shareUrl = url || window.location.href;
    const facebookShareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
    window.open(facebookShareUrl, '_blank', 'noopener,noreferrer,width=680,height=540');
}

function updateCart() {
    const container = document.getElementById('cart-items');
    const count = document.getElementById('cart-count');
    const total = document.getElementById('cart-total');
    const footer = document.getElementById('cart-footer');
    
    count.innerText = cart.reduce((a, b) => a + b.qtd, 0);
    
    if (cart.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 4rem 1rem;"><i data-lucide="shopping-cart" style="width: 48px; opacity: 0.1; margin-bottom: 1rem;"></i><p>Carrinho vazio</p></div>';
        footer.style.display = 'none';
        lucide.createIcons();
        return;
    }
    
    footer.style.display = 'block';
    let html = '';
    let sum = 0;
    cart.forEach((item, idx) => {
        sum += item.preco * item.qtd;
        html += `
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                ${productThumbHtml(item.imagem, 60)}
                <div style="flex: 1;">
                    <h4 style="font-size: 0.9rem; margin-bottom: 4px;">${item.nome}</h4>
                    <p style="font-weight: 700; color: var(--primary);">R$ ${(item.preco * item.qtd).toFixed(2).replace('.',',')}</p>
                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                        <button onclick="changeQtd(${idx}, -1)" style="width: 24px; height: 24px; border-radius: 4px; border: 1px solid var(--border); background: none;">-</button>
                        <span>${item.qtd}</span>
                        <button onclick="changeQtd(${idx}, 1)" style="width: 24px; height: 24px; border-radius: 4px; border: 1px solid var(--border); background: none;">+</button>
                    </div>
                </div>
                <button onclick="removeFromCart(${idx})" style="background: none; border: none; color: var(--danger);"><i data-lucide="trash-2" style="width: 16px;"></i></button>
            </div>
        `;
    });
    container.innerHTML = html;
    total.innerText = 'R$ ' + sum.toFixed(2).replace('.',',');
    lucide.createIcons();
}

function changeQtd(idx, delta) {
    cart[idx].qtd = Math.max(0, cart[idx].qtd + delta);
    if (cart[idx].qtd === 0) cart.splice(idx, 1);
    updateCart();
}

function removeFromCart(idx) {
    cart.splice(idx, 1);
    updateCart();
}

const MP_PUBLIC_KEY = <?= json_encode((string) ($settings['mercadopago_public_key'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const CHECKOUT_MP_URL = '<?= route_url('vitrine/checkoutMercadoPago') ?>';
const CHECKOUT_STATUS_URL = '<?= route_url('vitrine/statusMercadoPago') ?>';
const CSRF_TOKEN = '<?= e(csrf_token()) ?>';
const STORE_WHATSAPP = '<?= preg_replace('/\D+/', '', (string) ($settings['whatsapp'] ?? '5511999999999')) ?>';
const CHECKOUT_RETURN_STATUS = '<?= htmlspecialchars((string) ($_GET['checkout'] ?? ''), ENT_QUOTES) ?>';
let checkoutPollTimer = null;

function checkoutWhatsApp(message = '') {
    const lines = cart.map((item) => {
        const total = (item.preco * item.qtd).toFixed(2).replace('.', ',');
        return `- ${item.qtd}x ${item.nome} - R$ ${total}`;
    });
    const total = cart.reduce((sum, item) => sum + (item.preco * item.qtd), 0).toFixed(2).replace('.', ',');
    const text = [
        'Ola! Quero finalizar esta compra:',
        ...lines,
        `Total: R$ ${total}`,
        message ? `Observacao: ${message}` : ''
    ].filter(Boolean).join('\n');

    window.location.href = `https://wa.me/${STORE_WHATSAPP || '5511999999999'}?text=${encodeURIComponent(text)}`;
}

async function checkoutMP() {
    if (cart.length === 0) {
        abrirAvisoVitrine('Carrinho vazio', 'Adicione um produto antes de finalizar a compra.', 'shopping-cart');
        return;
    }

    abrirAvisoVitrine('Mercado Pago', 'Criando checkout seguro. Aguarde alguns segundos...', 'credit-card');
    const checkoutWindow = window.open('', '_blank');

    try {
        const response = await fetch(CHECKOUT_MP_URL, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN,
            },
            body: JSON.stringify({ items: cart }),
        });
        const data = await response.json().catch(() => ({}));

        if (response.ok && data.ok && data.init_point) {
            if (checkoutWindow) {
                checkoutWindow.location.href = data.init_point;
            } else {
                window.open(data.init_point, '_blank');
            }

            abrirAvisoVitrine('Aguardando pagamento', 'Finalize o Pix na aba do Mercado Pago. Esta tela vai avisar automaticamente quando o pagamento for aprovado.', 'clock');
            if (data.external_reference) {
                startMercadoPagoStatusPolling(data.external_reference);
            }
            return;
        }

        if (checkoutWindow) {
            checkoutWindow.close();
        }

        abrirAvisoVitrine('Checkout indisponivel', data.message || 'Nao foi possivel iniciar o Mercado Pago. Vamos continuar pelo WhatsApp.', 'message-circle');
        window.setTimeout(() => checkoutWhatsApp(data.message || ''), 900);
    } catch (error) {
        if (checkoutWindow) {
            checkoutWindow.close();
        }
        abrirAvisoVitrine('Checkout indisponivel', 'Nao foi possivel conectar ao Mercado Pago. Vamos continuar pelo WhatsApp.', 'message-circle');
        window.setTimeout(() => checkoutWhatsApp('Mercado Pago indisponivel no momento.'), 900);
    }
}

function startMercadoPagoStatusPolling(reference) {
    if (checkoutPollTimer) {
        window.clearInterval(checkoutPollTimer);
    }

    let attempts = 0;
    const checkStatus = async () => {
        attempts++;
        try {
            const response = await fetch(`${CHECKOUT_STATUS_URL}&ref=${encodeURIComponent(reference)}`, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json().catch(() => ({}));

            if (data.ok && data.status === 'approved') {
                window.clearInterval(checkoutPollTimer);
                checkoutPollTimer = null;
                cart = [];
                updateCart();
                abrirAvisoVitrine('Pagamento concluido', 'Pagamento aprovado pelo Mercado Pago. A loja vai separar seu pedido e entrar em contato se precisar de alguma informacao.', 'check-circle');
                return;
            }

            if (data.ok && ['rejected', 'cancelled', 'refunded', 'charged_back'].includes(data.status)) {
                window.clearInterval(checkoutPollTimer);
                checkoutPollTimer = null;
                abrirAvisoVitrine('Pagamento nao concluido', 'O Mercado Pago nao aprovou esse pagamento. Voce pode tentar novamente ou falar com a loja pelo WhatsApp.', 'circle-alert');
                return;
            }
        } catch (error) {
            console.warn('Nao foi possivel consultar o status do pagamento.', error);
        }

        if (attempts >= 180) {
            window.clearInterval(checkoutPollTimer);
            checkoutPollTimer = null;
            abrirAvisoVitrine('Pagamento em analise', 'Ainda nao recebemos a confirmacao. Se voce ja pagou, a loja sera avisada pelo Mercado Pago assim que o pagamento compensar.', 'clock');
        }
    };

    checkStatus();
    checkoutPollTimer = window.setInterval(checkStatus, 5000);
}
updateFavoritesCount();
renderFavoriteButtons();
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        fecharAvisoVitrine();
    }
});
<?php if ($accountCreated || $accountError !== ''): ?>
window.addEventListener('load', () => toggleAccountModal());
<?php endif; ?>
window.addEventListener('load', () => {
    if (CHECKOUT_RETURN_STATUS === 'success') {
        cart = [];
        updateCart();
        abrirAvisoVitrine('Pagamento concluido', 'Recebemos a confirmacao do Mercado Pago. A loja vai separar seu pedido e entrar em contato se precisar de alguma informacao.', 'check-circle');
        return;
    }

    if (CHECKOUT_RETURN_STATUS === 'pending') {
        abrirAvisoVitrine('Pagamento em analise', 'O Mercado Pago ainda esta processando o pagamento. Assim que for aprovado, a loja segue com o pedido.', 'clock');
        return;
    }

    if (CHECKOUT_RETURN_STATUS === 'failure') {
        abrirAvisoVitrine('Pagamento nao concluido', 'Nao foi possivel concluir o pagamento pelo Mercado Pago. Voce pode tentar novamente ou falar com a loja pelo WhatsApp.', 'circle-alert');
    }
});
</script>

<div class="public-container">
    <?php if (($show_public_intro ?? true) && !empty($page_title)): ?>
    <div style="margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.5rem; font-family: 'Outfit', sans-serif; margin-bottom: 0.5rem;"><?= e($page_title) ?></h1>
        <p style="color: var(--text-muted);">Confira nossos produtos e acessorios disponiveis para voce.</p>
    </div>
    <?php endif; ?>
