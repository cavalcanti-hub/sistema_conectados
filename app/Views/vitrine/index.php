<?php require_once dirname(__DIR__) . '/layout/public_header.php'; ?>

<?php
$empresa = trim($settings['nome_empresa'] ?? '') ?: 'Conectados';
$whats = trim($settings['whatsapp'] ?? '') ?: '5511999999999';
$destaques = array_slice($produtos, 0, 24);
$lancamentos = $lancamentos ?? [];
$mapQuery = trim($settings['endereco'] ?? '');
$mapLink = $mapQuery !== '' ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapQuery) : '';
$mapEmbedUrl = trim((string) ($settings['vitrine_mapa_embed_url'] ?? ''));
if ($mapEmbedUrl !== '' && !str_contains($mapEmbedUrl, 'output=embed') && !str_contains($mapEmbedUrl, '/maps/embed')) {
    $mapEmbedUrl = '';
}
if ($mapEmbedUrl === '' && $mapQuery !== '') {
    $mapEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($mapQuery) . '&output=embed';
}
$cfg = function ($key, $default = '') use ($settings) {
    $value = trim((string) ($settings[$key] ?? ''));
    return $value !== '' ? $value : $default;
};
$servicos = [
    ['icon' => 'smartphone', 'title' => $cfg('vitrine_servico_1_titulo', 'Troca de tela'), 'text' => $cfg('vitrine_servico_1_texto', 'Reparo rapido para telas quebradas ou sem toque.')],
    ['icon' => 'battery-charging', 'title' => $cfg('vitrine_servico_2_titulo', 'Troca de bateria'), 'text' => $cfg('vitrine_servico_2_texto', 'Mais autonomia para o aparelho no dia a dia.')],
    ['icon' => 'shield-check', 'title' => $cfg('vitrine_servico_3_titulo', 'Peliculas e protecao'), 'text' => $cfg('vitrine_servico_3_texto', 'Aplicacao e acessorios para proteger seu investimento.')],
    ['icon' => 'settings-2', 'title' => $cfg('vitrine_servico_4_titulo', 'Configuracao e suporte'), 'text' => $cfg('vitrine_servico_4_texto', 'Ajuda com apps, contas, backup e ajustes do aparelho.')],
];
$depoimentos = [
    ['name' => $cfg('vitrine_depoimento_1_nome', 'Mariana S.'), 'text' => $cfg('vitrine_depoimento_1_texto', 'Atendimento rapido, explicaram tudo direitinho e o celular ficou excelente.')],
    ['name' => $cfg('vitrine_depoimento_2_nome', 'Carlos R.'), 'text' => $cfg('vitrine_depoimento_2_texto', 'Comprei acessorios e fui muito bem atendido. Loja organizada e confiavel.')],
    ['name' => $cfg('vitrine_depoimento_3_nome', 'Fernanda L.'), 'text' => $cfg('vitrine_depoimento_3_texto', 'Resolveram meu problema no mesmo dia e ainda deram suporte pelo WhatsApp.')],
];

$categoryCounts = [];
$categoryPreview = [];
foreach ($produtos as $produto) {
    $cat = trim($produto['categoria'] ?? '') ?: 'Geral';
    if (!isset($categoryCounts[$cat])) {
        $categoryCounts[$cat] = 0;
    }
    $categoryCounts[$cat]++;
    if (!isset($categoryPreview[$cat])) {
        $categoryPreview[$cat] = [
            'nome' => (string) ($produto['nome'] ?? ''),
            'imagem' => (string) ($produto['imagem_url'] ?? ''),
        ];
    }
}

$resolveIcon = function ($category) {
    $map = [
        'tela' => 'smartphone',
        'capa' => 'shield',
        'pelicula' => 'scan-search',
        'fone' => 'headphones',
        'cabo' => 'cable',
        'carregador' => 'battery-charging',
        'bateria' => 'battery-medium',
        'camera' => 'camera',
        'som' => 'speaker',
        'geral' => 'package',
    ];

    $lower = strtolower($category);
    foreach ($map as $needle => $icon) {
        if (strpos($lower, $needle) !== false) {
            return $icon;
        }
    }

    return 'package';
};
?>

<style>
    /* ============================================
       VITRINE — DESIGN SYSTEM V2
    ============================================ */
    @keyframes heroFloat {
        0%, 100% { transform: rotate(7deg) translateY(0); }
        50% { transform: rotate(7deg) translateY(-12px); }
    }
    @keyframes orbitSpin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @keyframes meshMove {
        0% { background-position: 0 0; }
        100% { background-position: 60px 60px; }
    }
    @keyframes pulse-glow {
        0%, 100% { opacity: 0.55; }
        50% { opacity: 0.85; }
    }
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .shop-home {
        display: flex;
        flex-direction: column;
        gap: 0;
        padding-bottom: 2rem;
    }
    .hero-banner {
        position: relative;
        width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        min-height: clamp(340px, 34vw, 480px);
        overflow: hidden;
        background:
            linear-gradient(135deg,
                #020818 0%,
                #071550 28%,
                #0a2380 55%,
                #1a3fbb 78%,
                #2d2dff 100%);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        border-bottom: 1px solid rgba(0, 40, 120, 0.3);
    }
    /* Animated dot-mesh overlay */
    .hero-banner::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background-image:
            radial-gradient(circle at 14% 18%, rgba(120, 180, 255, 0.28) 0, transparent 18rem),
            radial-gradient(circle at 78% 30%, rgba(100, 220, 255, 0.25) 0, transparent 22rem),
            radial-gradient(circle at 50% 90%, rgba(60, 60, 255, 0.22) 0, transparent 18rem),
            radial-gradient(circle 1px at center, rgba(255,255,255,.35) 0, transparent 1px);
        background-size: auto, auto, auto, 28px 28px;
        animation: meshMove 8s linear infinite;
        z-index: 0;
    }
    /* Diagonal highlight sweep */
    .hero-banner::after {
        content: "";
        position: absolute;
        right: -15vw;
        top: -40%;
        width: min(720px, 58vw);
        height: 180%;
        border-radius: 999px;
        background: linear-gradient(135deg,
            rgba(255,255,255,.14) 0%,
            rgba(100,180,255,.10) 50%,
            rgba(255,255,255,0) 100%);
        transform: rotate(-16deg);
        pointer-events: none;
        z-index: 0;
    }
    /* Floating glow orbs */
    .hero-glow-1, .hero-glow-2, .hero-glow-3 {
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
        z-index: 0;
        animation: pulse-glow 4s ease-in-out infinite;
    }
    .hero-glow-1 {
        width: 380px; height: 380px;
        left: -60px; top: -80px;
        background: radial-gradient(circle, rgba(80, 130, 255, 0.32), transparent 70%);
        animation-delay: 0s;
    }
    .hero-glow-2 {
        width: 480px; height: 480px;
        right: 20%; bottom: -160px;
        background: radial-gradient(circle, rgba(45, 120, 255, 0.2), transparent 70%);
        animation-delay: 1.8s;
    }
    .hero-glow-3 {
        width: 260px; height: 260px;
        right: 6%; top: -40px;
        background: radial-gradient(circle, rgba(120, 200, 255, 0.25), transparent 70%);
        animation-delay: 0.9s;
    }
    .hero-banner-layout {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(280px, 0.58fr);
        align-items: center;
        gap: clamp(1rem, 4vw, 5rem);
        width: 100%;
        max-width: 1480px;
        min-height: clamp(340px, 34vw, 480px);
        margin: 0 auto;
        padding: clamp(2rem, 3.5vw, 3.5rem) clamp(1rem, 2vw, 1.75rem);
        animation: fadeSlideUp 0.7s ease both;
    }
    .hero-banner-content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-start;
        gap: 0.78rem;
        max-width: 680px;
        color: #fff;
    }
    .hero-banner-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        min-height: 32px;
        padding: 0 1rem;
        border: 1px solid rgba(120, 200, 255, 0.38);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #93c5fd;
        box-shadow: 0 0 0 1px rgba(120, 200, 255, 0.12) inset;
    }
    .hero-banner-kicker::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #60c8ff;
        box-shadow: 0 0 8px #60c8ff;
        flex: 0 0 auto;
    }
    .hero-banner-logo {
        width: clamp(178px, 20vw, 300px);
        height: auto;
        margin: .2rem 0 .1rem;
        filter:
            brightness(1.35)
            saturate(1.15)
            drop-shadow(0 0 8px rgba(255, 255, 255, 0.45))
            drop-shadow(0 16px 30px rgba(0, 0, 0, 0.28));
    }
    .hero-banner h2 {
        display: block;
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: clamp(2rem, 3.4vw, 3.4rem);
        line-height: 1.03;
        max-width: 660px;
        text-shadow: 0 2px 40px rgba(0, 0, 0, 0.35);
        letter-spacing: -0.02em;
    }
    .hero-title-accent {
        background: linear-gradient(90deg, #60c8ff 0%, #a78bfa 60%, #60c8ff 100%);
        background-size: 200% 100%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: shimmerText 4s linear infinite;
    }
    @keyframes shimmerText {
        from { background-position: 0% 0%; }
        to { background-position: 200% 0%; }
    }
    .hero-banner p {
        display: block;
        margin: 0;
        max-width: 560px;
        font-size: clamp(1rem, 1.18vw, 1.12rem);
        line-height: 1.65;
        color: rgba(200, 220, 255, 0.9);
    }
    .hero-banner-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.72rem;
        width: 100%;
        margin-top: .5rem;
    }
    .hero-banner-actions .featured-btn,
    .hero-banner-secondary {
        min-height: 50px;
        width: auto;
        flex: none;
        gap: 0.55rem;
        padding: 0 1.08rem;
        border-radius: 14px;
        font-size: 0.95rem;
        line-height: 1;
        font-weight: 850;
        text-shadow: 0 1px 10px rgba(0,0,0,0.22);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.22),
            0 18px 32px -22px rgba(0,0,0,0.72);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        transform: translateY(0);
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease, background .22s ease;
    }
    .hero-banner-actions .featured-btn {
        grid-column: auto;
        background: linear-gradient(135deg, #075dff 0%, #00349a 62%, #001f63 100%);
        border: 1px solid rgba(142, 205, 255, 0.62);
        color: #fff;
    }
    .hero-banner-actions .featured-btn:hover,
    .hero-banner-secondary:hover {
        transform: translateY(-2px);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.28),
            0 22px 36px -22px rgba(0,0,0,0.82),
            0 0 22px rgba(58, 178, 255, 0.24);
    }
    .hero-banner-actions svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
        stroke-width: 2.4;
    }
    .hero-banner-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border: 1px solid rgba(255,255,255,0.34);
        background: rgba(4, 26, 70, 0.56);
        color: #fff;
        font: inherit;
        font-weight: 850;
        cursor: pointer;
    }
    .hero-banner-secondary:hover {
        border-color: rgba(125, 217, 255, 0.92);
        background: rgba(4, 52, 123, 0.72);
    }
    .hero-share-action {
        grid-column: auto;
    }
    .hero-banner-art {
        position: relative;
        min-height: 260px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hero-orbit {
        position: absolute;
        width: min(440px, 36vw);
        aspect-ratio: 1;
        border-radius: 999px;
        border: 1px solid rgba(100, 180, 255, 0.22);
        background:
            radial-gradient(circle, rgba(255,255,255,.06) 0 2px, transparent 3px),
            linear-gradient(135deg, rgba(100,180,255,.07), rgba(255,255,255,0));
        box-shadow:
            inset 0 0 100px rgba(80, 160, 255, 0.12),
            0 0 60px rgba(60, 140, 255, 0.08);
        animation: orbitSpin 28s linear infinite;
        opacity: .85;
    }
    .hero-orbit-inner {
        position: absolute;
        width: min(310px, 26vw);
        aspect-ratio: 1;
        border-radius: 999px;
        border: 1px dashed rgba(120, 200, 255, 0.18);
        animation: orbitSpin 18s linear infinite reverse;
    }
    /* Floating mini badges on device art */
    .hero-float-badge {
        position: absolute;
        display: flex;
        align-items: center;
        gap: 0.42rem;
        padding: 0.45rem 0.75rem;
        border-radius: 12px;
        font-size: 0.72rem;
        font-weight: 800;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        white-space: nowrap;
        z-index: 5;
        animation: fadeSlideUp 0.7s ease both;
        box-shadow: 0 8px 24px -8px rgba(0,0,0,0.35);
    }
    .hero-float-badge.badge-green {
        background: rgba(16, 185, 129, 0.18);
        border: 1px solid rgba(52, 211, 153, 0.35);
        color: #6ee7b7;
        top: 14%;
        left: -28%;
        animation-delay: 0.4s;
    }
    .hero-float-badge.badge-purple {
        background: rgba(139, 92, 246, 0.18);
        border: 1px solid rgba(167, 139, 250, 0.35);
        color: #c4b5fd;
        bottom: 20%;
        left: -32%;
        animation-delay: 0.6s;
    }
    .hero-device {
        position: relative;
        width: min(240px, 21vw);
        min-width: 195px;
        aspect-ratio: 10 / 17;
        border-radius: 36px;
        padding: 0.65rem;
        background: linear-gradient(155deg, #0a1628, #0d2356 45%, #1433a0);
        border: 1px solid rgba(100, 180, 255, 0.3);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.2),
            inset 0 -1px 0 rgba(0,0,0,.4),
            0 40px 80px -30px rgba(0,0,0,.85),
            0 0 0 8px rgba(255,255,255,.04),
            0 0 50px rgba(60, 120, 255, 0.2);
        animation: heroFloat 5s ease-in-out infinite;
    }
    /* Camera notch */
    .hero-device::before {
        content: "";
        position: absolute;
        top: .55rem;
        left: 50%;
        width: 62px;
        height: 7px;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(255,255,255,.25), rgba(255,255,255,.45), rgba(255,255,255,.25));
        transform: translateX(-50%);
        box-shadow: 0 0 8px rgba(120, 200, 255, 0.3);
        z-index: 2;
    }
    /* Side button highlight */
    .hero-device::after {
        content: "";
        position: absolute;
        right: -2px;
        top: 20%;
        width: 3px;
        height: 14%;
        border-radius: 2px 0 0 2px;
        background: rgba(255,255,255,.22);
    }
    .hero-device-screen {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 0.7rem;
        padding: 1.8rem 0.75rem 0.85rem;
        border-radius: 28px;
        /* Dark OLED screen */
        background: linear-gradient(175deg, #0a0a12 0%, #080c1e 55%, #06091a 100%);
        color: #fff;
        overflow: hidden;
        position: relative;
    }
    /* Subtle inner glow on the screen */
    .hero-device-screen::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse at 50% 30%, rgba(60, 100, 255, 0.18) 0, transparent 65%),
            radial-gradient(ellipse at 50% 100%, rgba(30, 60, 200, 0.12) 0, transparent 60%);
        pointer-events: none;
    }
    /* Status bar */
    .device-status-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 0.25rem;
        position: relative;
        z-index: 1;
        flex: 0 0 auto;
    }
    /* Logo area */
    .device-logo-area {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex: 1;
        position: relative;
        z-index: 1;
        gap: 0.45rem;
    }
    .device-logo-glow {
        position: absolute;
        width: 120px;
        height: 60px;
        background: radial-gradient(ellipse, rgba(80, 150, 255, 0.4) 0, transparent 70%);
        filter: blur(14px);
        pointer-events: none;
    }
    .device-logo-img {
        width: min(130px, 85%);
        height: auto;
        object-fit: contain;
        filter:
            brightness(1.5)
            saturate(1.2)
            drop-shadow(0 0 6px rgba(100, 180, 255, 0.6))
            drop-shadow(0 0 18px rgba(80, 140, 255, 0.35));
        position: relative;
        z-index: 1;
    }
    .device-tagline {
        margin: 0;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(120, 180, 255, 0.75);
        position: relative;
        z-index: 1;
    }
    .hero-device-list {
        display: grid;
        gap: .45rem;
    }
    .hero-device-list div {
        display: flex;
        align-items: center;
        gap: .45rem;
        min-height: 32px;
        padding: 0 .6rem;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.9);
        font-size: .75rem;
        font-weight: 600;
        backdrop-filter: blur(8px);
        transition: background 0.2s ease, border-color 0.2s ease;
        position: relative;
        z-index: 1;
    }
    .hero-device-list div:hover {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(100, 180, 255, 0.4);
    }
    .hero-device-list i {
        color: #60c8ff;
    }
    .benefits-strip {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1.25rem;
    }
    .benefit-card {
        background: rgba(255, 255, 255, 0.75);
        border: 1px solid rgba(100, 160, 255, 0.18);
        border-radius: 22px;
        padding: 1rem 1.15rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        box-shadow:
            0 18px 36px -28px rgba(15, 23, 42, 0.3),
            inset 0 1px 0 rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }
    .benefit-card:hover {
        transform: translateY(-3px);
        box-shadow:
            0 24px 42px -24px rgba(0, 52, 154, 0.25),
            inset 0 1px 0 rgba(255,255,255,0.95);
        border-color: rgba(80, 140, 255, 0.3);
    }
    .benefit-icon {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(0, 52, 200, 0.1), rgba(80, 140, 255, 0.08));
        color: var(--primary);
        flex: 0 0 auto;
        box-shadow: 0 6px 16px -8px rgba(0, 52, 200, 0.25);
    }
    .benefit-card strong {
        display: block;
        font-size: 0.93rem;
        margin-bottom: 0.12rem;
        color: #0f172a;
    }
    .benefit-card span {
        color: var(--text-muted);
        font-size: 0.83rem;
    }
    .section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.1rem;
    }
    .section-head h2 {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: clamp(1.5rem, 2.8vw, 2.15rem);
        color: #071b37;
        letter-spacing: -0.02em;
    }
    .section-head h2 span {
        display: inline-block;
        background: linear-gradient(90deg, #0a2380, #2d2dff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .section-head p {
        margin: 0.32rem 0 0;
        color: var(--text-muted);
        font-size: 0.93rem;
    }
    .content-section {
        margin-top: 2.4rem;
    }
    .section-link {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        text-decoration: none;
        color: var(--primary);
        font-weight: 800;
    }
    .category-bar {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    .category-arrow {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        border: 1px solid rgba(0, 52, 154, 0.12);
        background: #fff;
        color: #092347;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex: 0 0 auto;
    }
    .category-carousel {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(190px, 230px);
        gap: 1.15rem;
        overflow-x: auto;
        scrollbar-width: none;
        scroll-snap-type: x mandatory;
        padding: .2rem .15rem .85rem;
    }
    .category-carousel::-webkit-scrollbar {
        display: none;
    }
    .category-tile {
        position: relative;
        scroll-snap-align: start;
        min-height: 170px;
        border-radius: 30px;
        padding: 1rem;
        text-decoration: none;
        color: #071b37;
        background: transparent;
        border: 0;
        box-shadow: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        text-align: center;
        overflow: hidden;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }
    .category-tile::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.0), rgba(255,255,255,.65) 45%, rgba(255,255,255,0));
        transform: translateX(-120%);
        transition: transform .5s ease;
        pointer-events: none;
    }
    .category-tile:hover {
        transform: translateY(-5px);
        border-color: transparent;
        box-shadow: none;
    }
    .category-tile:hover::after {
        transform: translateX(120%);
    }
    .category-icon {
        width: 132px;
        height: 132px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef5ff;
        color: var(--primary);
        border: 8px solid #fff;
        box-shadow: 0 18px 34px -26px rgba(0, 35, 90, 0.8);
        overflow: hidden;
        margin-bottom: .85rem;
        transition: transform .25s ease;
    }
    .category-tile:hover .category-icon {
        transform: scale(1.06);
    }
    .category-preview-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .35s ease;
    }
    .category-tile:hover .category-preview-img {
        transform: scale(1.1);
    }
    .category-tile h3 {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        line-height: 1.2;
        min-height: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .featured-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.1rem;
    }
    .featured-card {
        background: #fff;
        border: 1px solid rgba(180, 210, 255, 0.35);
        border-radius: 20px;
        overflow: hidden;
        box-shadow:
            0 8px 24px -16px rgba(0, 40, 120, 0.25),
            0 2px 8px -4px rgba(0, 0, 0, 0.08);
        display: flex;
        flex-direction: column;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .featured-card:hover {
        transform: translateY(-5px);
        box-shadow:
            0 28px 48px -24px rgba(0, 52, 180, 0.3),
            0 8px 20px -10px rgba(0, 0, 0, 0.1);
        border-color: rgba(60, 120, 255, 0.3);
    }
    .featured-image {
        position: relative;
        aspect-ratio: 1 / 0.72;
        background: linear-gradient(180deg, #f8fbff 0%, #f1f6fd 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: var(--primary);
    }
    .featured-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        transition: transform 0.35s ease;
        padding: 0.75rem;
    }
    .featured-card:hover .featured-image img {
        transform: scale(1.04);
    }
    .favorite-toggle {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: none;
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(8px);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 2;
        color: #1e293b;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .favorite-toggle:hover {
        background: #fff;
        color: #ef4444;
        transform: scale(1.1);
    }
    .featured-body {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        flex: 1;
    }
    .featured-tag {
        display: inline-flex;
        width: fit-content;
        padding: 3px 8px;
        background: rgba(0, 52, 154, 0.05);
        color: var(--primary);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.035em;
        text-transform: uppercase;
        border-radius: 99px;
    }
    .featured-body h3 {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: 0.98rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.32;
        min-height: 3.85rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .featured-price-row {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.6rem;
        margin-top: auto;
        padding-top: 0.8rem;
        border-top: 1px solid rgba(0,0,0,0.04);
    }
    .featured-actions {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: nowrap;
        justify-content: space-between;
        flex: 0 0 auto;
        min-width: 0;
    }
    .featured-price {
        font-size: 1.08rem;
        font-weight: 800;
        color: #00349a;
        line-height: 1.1;
        white-space: nowrap;
    }
    .featured-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        min-height: 40px;
        padding: 0 0.75rem;
        border-radius: 10px;
        text-decoration: none;
        border: none;
        background: #002d85;
        color: #fff;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.3s ease;
        flex: 1 1 auto;
    }
    .featured-btn:hover {
        background: #001f5c;
        transform: translateY(-2px);
    }
    .featured-icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: 1px solid rgba(0, 52, 154, 0.12);
        background: #fff;
        color: #10233f;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    .featured-icon-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }
    .featured-icon-btn.is-whatsapp:hover {
        border-color: #22c55e;
        color: #16a34a;
        background: #f0fff4;
    }
    .featured-whatsapp-icon {
        width: 21px;
        height: 21px;
        display: block;
    }
    .service-grid,
    .testimonial-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.1rem;
    }
    .service-card,
    .testimonial-card,
    .location-card {
        background: rgba(255,255,255,0.82);
        border: 1px solid rgba(160, 200, 255, 0.2);
        border-radius: 26px;
        box-shadow:
            0 16px 32px -24px rgba(0, 40, 120, 0.2),
            inset 0 1px 0 rgba(255,255,255,0.95);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }
    .service-card:hover,
    .testimonial-card:hover {
        transform: translateY(-3px);
        box-shadow:
            0 24px 44px -22px rgba(0, 52, 180, 0.22),
            inset 0 1px 0 rgba(255,255,255,0.95);
    }
    .service-card {
        padding: 1.25rem;
    }
    .service-card h3,
    .testimonial-card h3,
    .location-card h3 {
        margin: 0 0 0.45rem;
        font-family: 'Outfit', sans-serif;
        font-size: 1.1rem;
        color: #071b37;
    }
    .service-card p,
    .testimonial-card p,
    .location-card p {
        margin: 0;
        color: var(--text-muted);
    }
    .service-icon {
        width: 52px;
        height: 52px;
        margin-bottom: 1rem;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 52, 154, 0.08);
        color: var(--primary);
    }
    .testimonial-card {
        padding: 1.35rem;
    }
    .testimonial-stars {
        display: flex;
        gap: 0.3rem;
        color: #f59e0b;
        margin-bottom: 0.95rem;
    }
    .testimonial-name {
        margin-top: 1rem;
        font-weight: 800;
        color: #071b37;
    }
    .location-card {
        display: grid;
        grid-template-columns: minmax(320px, 0.45fr) minmax(420px, 0.55fr);
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(0, 52, 154, 0.1);
        border-radius: 28px;
        box-shadow: 0 24px 70px -48px rgba(0, 35, 90, 0.65);
    }
    .location-copy {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 1.15rem;
        background:
            linear-gradient(180deg, rgba(0, 52, 154, 0.045), rgba(255, 255, 255, 0) 46%),
            #fff;
    }
    .location-copy h3 {
        margin: 0;
        font-size: 1.5rem;
        color: #071b37;
    }
    .location-copy p {
        margin: 0;
        color: #334155;
        line-height: 1.65;
    }
    .location-meta {
        display: grid;
        gap: 0.75rem;
    }
    .location-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #1f3657;
        padding: 0.75rem 0.85rem;
        border-radius: 16px;
        background: #f8fbff;
        border: 1px solid rgba(0, 52, 154, 0.08);
        min-width: 0;
    }
    .location-row i {
        color: var(--primary);
    }
    .location-row span {
        min-width: 0;
        overflow-wrap: anywhere;
    }
    .location-map {
        min-height: 420px;
        background: #dbeafe;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        color: #0b2e67;
        position: relative;
    }
    .location-map-inner {
        text-align: center;
        padding: 2rem;
    }
    .location-map iframe {
        width: 100%;
        height: 100%;
        min-height: 420px;
        border: 0;
        display: block;
    }
    .location-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .pill-link {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.82rem 1.05rem;
        border-radius: 999px;
        background: var(--primary);
        color: #fff;
        text-decoration: none;
        font-weight: 800;
        box-shadow: 0 16px 26px -20px rgba(0, 52, 154, 0.75);
    }
    .pill-link.secondary {
        background: #eaf1ff;
        color: var(--primary);
        box-shadow: none;
    }
    .shop-panel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.4rem 1.5rem;
        border-radius: 28px;
        background: linear-gradient(90deg, #071b37, #0d2f63 60%, #1d60b3);
        color: #fff;
        box-shadow: 0 26px 46px -34px rgba(7, 27, 55, 0.82);
    }
    .shop-panel h3 {
        margin: 0 0 0.3rem;
        font-family: 'Outfit', sans-serif;
        font-size: 1.45rem;
    }
    .shop-panel p {
        margin: 0;
        color: rgba(255,255,255,0.78);
    }
    .empty-state {
        padding: 2rem;
        border-radius: 24px;
        background: #fff;
        border: 1px solid rgba(0, 52, 154, 0.08);
        color: var(--text-muted);
    }
    @media (max-width: 1200px) {
        .featured-grid,
        .benefits-strip,
        .service-grid,
        .testimonial-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .location-card {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 720px) {
        .hero-banner {
            min-height: 0;
        }
        .hero-banner-layout {
            grid-template-columns: 1fr;
            min-height: 0;
            padding-top: 1.35rem;
            padding-bottom: 1.45rem;
        }
        .hero-banner-content {
            max-width: none;
            align-items: center;
            text-align: center;
            width: 100%;
        }
        .hero-banner-logo {
            width: min(230px, 68vw);
        }
        .hero-banner h2 {
            width: 100%;
            max-width: 340px;
            overflow-wrap: anywhere;
            text-wrap: balance;
            font-size: clamp(1.45rem, 6vw, 1.82rem);
        }
        .hero-banner p {
            max-width: calc(100vw - 3rem);
            overflow-wrap: break-word;
            font-size: 0.95rem;
        }
        .hero-banner-art {
            display: none;
        }
        .shop-panel {
            flex-direction: column;
            align-items: stretch;
        }
        .hero-banner-actions {
            grid-template-columns: 1fr;
            width: 100%;
        }
        .hero-banner-actions .featured-btn,
        .hero-banner-secondary,
        .hero-share-action {
            width: 100%;
            min-width: 0;
            justify-content: center;
        }
        .featured-btn {
            width: auto;
        }
        .featured-actions {
            width: auto;
        }
        .benefits-strip,
        .featured-grid,
        .service-grid,
        .testimonial-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 420px) {
        .featured-price-row {
            grid-template-columns: 1fr;
            gap: 0.6rem;
        }
        .featured-price {
            font-size: 1.08rem;
        }
        .featured-actions {
            justify-content: flex-start;
        }
        .featured-btn {
            min-width: 122px;
            padding: 0 0.85rem;
        }
        .featured-icon-btn {
            width: 40px;
            height: 40px;
        }
    }
</style>

<main class="shop-home">
    <section class="hero-banner">
        <!-- Animated glow orbs -->
        <div class="hero-glow-1" aria-hidden="true"></div>
        <div class="hero-glow-2" aria-hidden="true"></div>
        <div class="hero-glow-3" aria-hidden="true"></div>
        <div class="hero-banner-layout">
            <div class="hero-banner-content">
                <span class="hero-banner-kicker"><?= htmlspecialchars($cfg('vitrine_hero_kicker', 'Tecnologia conectada')) ?></span>
                <img class="hero-banner-logo" src="<?= app_url('assets/img/logo.png') ?>" alt="<?= htmlspecialchars($empresa) ?>">
                <h2><?= htmlspecialchars($cfg('vitrine_hero_titulo', 'Celulares, games e')) ?> <span class="hero-title-accent"><?= htmlspecialchars($cfg('vitrine_hero_titulo_accent', 'acessorios')) ?></span> <?= htmlspecialchars($cfg('vitrine_hero_titulo_fim', 'em destaque')) ?></h2>
                <p><?= htmlspecialchars($cfg('vitrine_hero_texto', 'Escolha produtos com visual premium e atendimento direto pelo WhatsApp da loja.')) ?></p>
                <div class="hero-banner-actions">
                    <a class="featured-btn" href="<?= route_url('vitrine/catalogo') ?>">
                        <i data-lucide="shopping-bag" style="width: 18px; height: 18px;"></i>
                        Ver catalogo
                    </a>
                    <a class="hero-banner-secondary" href="https://wa.me/<?= htmlspecialchars($whats) ?>" target="_blank">
                        <i data-lucide="message-circle" style="width: 18px; height: 18px;"></i>
                        Falar com a loja
                    </a>
                    <button class="hero-banner-secondary hero-share-action" type="button" onclick="shareStoreContent('Catalogo Conectados', 'Veja o catalogo de produtos da Conectados.', '<?= app_url('') ?>')">
                        <i data-lucide="share-2" style="width: 18px; height: 18px;"></i>
                        Compartilhar catalogo
                    </button>
                </div>
            </div>
            <div class="hero-banner-art" aria-hidden="true">
                <div class="hero-orbit"></div>
                <div class="hero-orbit-inner"></div>
                <div class="hero-device">
                    <!-- Floating badges -->
                    <div class="hero-float-badge badge-green">
                        <i data-lucide="shield-check" style="width: 13px; height: 13px;"></i>
                        Compra segura
                    </div>
                    <div class="hero-float-badge badge-purple">
                        <i data-lucide="zap" style="width: 13px; height: 13px;"></i>
                        Suporte rapido
                    </div>
                    <div class="hero-device-screen">
                        <!-- Dark OLED-style screen with logo -->
                        <div class="device-status-bar">
                            <span style="font-size:.6rem;font-weight:800;letter-spacing:0.04em;color:rgba(255,255,255,0.6);">9:41</span>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <i data-lucide="wifi" style="width:10px;height:10px;color:rgba(255,255,255,0.6);"></i>
                                <i data-lucide="battery" style="width:10px;height:10px;color:rgba(255,255,255,0.6);"></i>
                            </div>
                        </div>
                        <div class="device-logo-area">
                            <div class="device-logo-glow"></div>
                            <img src="<?= app_url('assets/img/logo.png') ?>" alt="<?= htmlspecialchars($empresa) ?>" class="device-logo-img">
                            <p class="device-tagline">Tecnologia conectada</p>
                        </div>
                        <div class="hero-device-list">
                            <div><i data-lucide="smartphone" style="width: 13px; height: 13px;"></i> Assistencia tecnica</div>
                            <div><i data-lucide="headphones" style="width: 13px; height: 13px;"></i> Acessorios</div>
                            <div><i data-lucide="message-circle" style="width: 13px; height: 13px;"></i> WhatsApp direto</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="benefits-strip">
        <article class="benefit-card">
            <div class="benefit-icon"><i data-lucide="truck" style="width: 22px; height: 22px;"></i></div>
            <div>
                <strong><?= htmlspecialchars($cfg('vitrine_benefit_1_titulo', 'Entrega e retirada')) ?></strong>
                <span><?= htmlspecialchars($cfg('vitrine_benefit_1_texto', 'Consulta rapida pelo WhatsApp.')) ?></span>
            </div>
        </article>
        <article class="benefit-card">
            <div class="benefit-icon"><i data-lucide="shield-check" style="width: 22px; height: 22px;"></i></div>
            <div>
                <strong><?= htmlspecialchars($cfg('vitrine_benefit_2_titulo', 'Loja especializada')) ?></strong>
                <span><?= htmlspecialchars($cfg('vitrine_benefit_2_texto', 'Celulares, audio e acessorios.')) ?></span>
            </div>
        </article>
        <article class="benefit-card">
            <div class="benefit-icon"><i data-lucide="wallet-cards" style="width: 22px; height: 22px;"></i></div>
            <div>
                <strong><?= htmlspecialchars($cfg('vitrine_benefit_3_titulo', 'Pagamento facil')) ?></strong>
                <span><?= htmlspecialchars($cfg('vitrine_benefit_3_texto', 'Compra mais direta e moderna.')) ?></span>
            </div>
        </article>
        <article class="benefit-card">
            <div class="benefit-icon"><i data-lucide="badge-check" style="width: 22px; height: 22px;"></i></div>
            <div>
                <strong><?= htmlspecialchars($cfg('vitrine_benefit_4_titulo', 'Atendimento humano')) ?></strong>
                <span><?= htmlspecialchars($cfg('vitrine_benefit_4_texto', 'Suporte rapido com a loja.')) ?></span>
            </div>
        </article>
    </section>

    <section class="content-section">
        <div class="section-head">
            <div>
                <h2>Categorias em destaque</h2>
                <p><?= htmlspecialchars($cfg('vitrine_categorias_texto', 'Explore os principais grupos de produtos da loja.')) ?></p>
            </div>
            <div class="category-bar">
                <button class="category-arrow" type="button" onclick="scrollCategories(-1)" aria-label="Voltar categorias">
                    <i data-lucide="chevron-left" style="width: 18px; height: 18px;"></i>
                </button>
                <button class="category-arrow" type="button" onclick="scrollCategories(1)" aria-label="Avancar categorias">
                    <i data-lucide="chevron-right" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
        </div>

        <?php if (!empty($categoryCounts)): ?>
        <div class="category-carousel" id="category-carousel">
            <?php foreach ($categoryCounts as $cat => $count): ?>
            <?php $preview = $categoryPreview[$cat] ?? ['nome' => '', 'imagem' => '']; ?>
            <a class="category-tile" href="<?= route_url('vitrine/catalogo', ['categoria' => $cat]) ?>">
                <div class="category-icon">
                    <?php if (!empty($preview['imagem'])): ?>
                    <img class="category-preview-img" src="<?= htmlspecialchars($preview['imagem']) ?>" alt="<?= htmlspecialchars($preview['nome'] ?: $cat) ?>">
                    <?php else: ?>
                    <i data-lucide="<?= htmlspecialchars($resolveIcon($cat)) ?>" style="width: 22px; height: 22px;"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <h3><?= htmlspecialchars($cat) ?></h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">Cadastre produtos para exibir as categorias aqui.</div>
        <?php endif; ?>
    </section>

    <section class="content-section">
        <div class="section-head">
            <div>
                <h2><?= htmlspecialchars($cfg('vitrine_lancamentos_titulo', 'Lancamentos')) ?></h2>
                <p><?= htmlspecialchars($cfg('vitrine_lancamentos_texto', 'Produtos adicionados recentemente na vitrine da loja.')) ?></p>
            </div>
            <a class="section-link" href="<?= route_url('vitrine/catalogo') ?>">
                ver catalogo
                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
            </a>
        </div>

        <?php if (!empty($lancamentos)): ?>
        <div class="featured-grid">
            <?php foreach ($lancamentos as $p): ?>
            <?php $soldOut = (int) ($p['quantidade'] ?? 0) <= 0; ?>
            <article class="featured-card <?= $soldOut ? 'is-sold-out' : '' ?>">
                <div class="featured-image">
                    <?php if ($soldOut): ?>
                    <span class="sold-out-ribbon">Esgotado</span>
                    <?php endif; ?>
                    <button class="favorite-toggle" type="button" onclick="toggleFavorite(<?= (int) $p['id'] ?>)" data-favorite-id="<?= (int) $p['id'] ?>" aria-label="Favoritar produto">
                        <i data-lucide="heart" style="width: 18px; height: 18px;"></i>
                    </button>
                    <a href="<?= route_url('vitrine/produto/' . $p['id']) ?>" style="display:block;width:100%;height:100%;">
                        <?php if (!empty($p['imagem_url'])): ?>
                        <img src="<?= htmlspecialchars($p['imagem_url']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='block';lucide.createIcons();">
                        <i data-lucide="<?= htmlspecialchars($resolveIcon($p['categoria'] ?? 'geral')) ?>" style="display:none;width:58px;height:58px;"></i>
                        <?php else: ?>
                        <i data-lucide="<?= htmlspecialchars($resolveIcon($p['categoria'] ?? 'geral')) ?>" style="width: 58px; height: 58px;"></i>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="featured-body">
                    <span class="featured-tag"><?= htmlspecialchars(trim($p['categoria'] ?? '') ?: 'Geral') ?></span>
                    <a href="<?= route_url('vitrine/produto/' . $p['id']) ?>" style="text-decoration:none;color:inherit;">
                        <h3><?= htmlspecialchars($p['nome']) ?></h3>
                    </a>
                    <div class="featured-price-row">
                        <span class="featured-price">R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></span>
                        <div class="featured-actions">
                            <?php if ($soldOut): ?>
                            <button class="featured-btn btn-sold-out" type="button" disabled aria-disabled="true">
                                Esgotado
                                <i data-lucide="circle-x" style="width: 18px; height: 18px;"></i>
                            </button>
                            <?php else: ?>
                            <button class="featured-btn" type="button" onclick="addToCart(<?= (int) $p['id'] ?>, <?= e(json_attr($p['nome'])) ?>, <?= (float) $p['preco_venda'] ?>, <?= e(json_attr($p['imagem_url'] ?? '')) ?>)">
                                Adicionar
                                <i data-lucide="shopping-cart" style="width: 18px; height: 18px;"></i>
                            </button>
                            <?php endif; ?>
                            <a class="featured-icon-btn is-whatsapp" href="https://wa.me/<?= htmlspecialchars($whats) ?>?text=<?= urlencode('Ola! Tenho interesse em ' . $p['nome']) ?>" target="_blank" title="Chamar no WhatsApp" aria-label="Chamar no WhatsApp">
                                <img class="featured-whatsapp-icon" src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="">
                            </a>
                            <button class="featured-icon-btn" type="button" onclick="shareStoreContent(<?= e(json_attr($p['nome'])) ?>, <?= e(json_attr('Veja este produto da Conectados: ' . $p['nome'])) ?>, <?= e(json_attr(absolute_route_url('vitrine/produto/' . $p['id']))) ?>, <?= e(json_attr($p['imagem_url'] ?? '')) ?>)" title="Compartilhar produto" aria-label="Compartilhar produto">
                                <i data-lucide="share-2" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">Cadastre produtos para preencher a area de lancamentos.</div>
        <?php endif; ?>
    </section>

    <section class="content-section">
        <div class="section-head">
            <div>
                <h2><?= htmlspecialchars($cfg('vitrine_mais_procurados_titulo', 'Mais procurados')) ?></h2>
                <p><?= htmlspecialchars($cfg('vitrine_mais_procurados_texto', 'Cards limpos sem usar fotos de teste do sistema.')) ?></p>
            </div>
            <a class="section-link" href="<?= route_url('vitrine/catalogo') ?>">
                ver catalogo
                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
            </a>
        </div>

        <?php if (!empty($destaques)): ?>
        <div class="featured-grid">
            <?php foreach ($destaques as $p): ?>
            <?php $soldOut = (int) ($p['quantidade'] ?? 0) <= 0; ?>
            <article class="featured-card <?= $soldOut ? 'is-sold-out' : '' ?>">
                <div class="featured-image">
                    <?php if ($soldOut): ?>
                    <span class="sold-out-ribbon">Esgotado</span>
                    <?php endif; ?>
                    <button class="favorite-toggle" type="button" onclick="toggleFavorite(<?= (int) $p['id'] ?>)" data-favorite-id="<?= (int) $p['id'] ?>" aria-label="Favoritar produto">
                        <i data-lucide="heart" style="width: 18px; height: 18px;"></i>
                    </button>
                    <a href="<?= route_url('vitrine/produto/' . $p['id']) ?>" style="display:block;width:100%;height:100%;">
                        <?php if (!empty($p['imagem_url'])): ?>
                        <img src="<?= htmlspecialchars($p['imagem_url']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='block';lucide.createIcons();">
                        <i data-lucide="<?= htmlspecialchars($resolveIcon($p['categoria'] ?? 'geral')) ?>" style="display:none;width:58px;height:58px;"></i>
                        <?php else: ?>
                        <i data-lucide="<?= htmlspecialchars($resolveIcon($p['categoria'] ?? 'geral')) ?>" style="width: 58px; height: 58px;"></i>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="featured-body">
                    <span class="featured-tag"><?= htmlspecialchars(trim($p['categoria'] ?? '') ?: 'Geral') ?></span>
                    <a href="<?= route_url('vitrine/produto/' . $p['id']) ?>" style="text-decoration:none;color:inherit;">
                        <h3><?= htmlspecialchars($p['nome']) ?></h3>
                    </a>
                    <div class="featured-price-row">
                        <span class="featured-price">R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></span>
                        <div class="featured-actions">
                            <?php if ($soldOut): ?>
                            <button class="featured-btn btn-sold-out" type="button" disabled aria-disabled="true">
                                Esgotado
                                <i data-lucide="circle-x" style="width: 18px; height: 18px;"></i>
                            </button>
                            <?php else: ?>
                            <button class="featured-btn" type="button" onclick="addToCart(<?= (int) $p['id'] ?>, <?= e(json_attr($p['nome'])) ?>, <?= (float) $p['preco_venda'] ?>, <?= e(json_attr($p['imagem_url'] ?? '')) ?>)">
                                Adicionar
                                <i data-lucide="shopping-cart" style="width: 18px; height: 18px;"></i>
                            </button>
                            <?php endif; ?>
                            <a class="featured-icon-btn is-whatsapp" href="https://wa.me/<?= htmlspecialchars($whats) ?>?text=<?= urlencode('Ola! Tenho interesse em ' . $p['nome']) ?>" target="_blank" title="Chamar no WhatsApp" aria-label="Chamar no WhatsApp">
                                <img class="featured-whatsapp-icon" src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="">
                            </a>
                            <button class="featured-icon-btn" type="button" onclick="shareStoreContent(<?= e(json_attr($p['nome'])) ?>, <?= e(json_attr('Veja este produto da Conectados: ' . $p['nome'])) ?>, <?= e(json_attr(absolute_route_url('vitrine/produto/' . $p['id']))) ?>, <?= e(json_attr($p['imagem_url'] ?? '')) ?>)" title="Compartilhar produto" aria-label="Compartilhar produto">
                                <i data-lucide="share-2" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">Adicione produtos para mostrar os destaques da home.</div>
        <?php endif; ?>
    </section>

    <section class="content-section">
        <div class="section-head">
            <div>
                <h2><?= htmlspecialchars($cfg('vitrine_servicos_titulo', 'Servicos da loja')) ?></h2>
                <p><?= htmlspecialchars($cfg('vitrine_servicos_texto', 'Assistencia e suporte para deixar o cliente mais seguro na compra.')) ?></p>
            </div>
        </div>
        <div class="service-grid">
            <?php foreach ($servicos as $servico): ?>
            <article class="service-card">
                <div class="service-icon">
                    <i data-lucide="<?= htmlspecialchars($servico['icon']) ?>" style="width: 24px; height: 24px;"></i>
                </div>
                <h3><?= htmlspecialchars($servico['title']) ?></h3>
                <p><?= htmlspecialchars($servico['text']) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="content-section">
        <div class="section-head">
            <div>
                <h2><?= htmlspecialchars($cfg('vitrine_depoimentos_titulo', 'Quem compra recomenda')) ?></h2>
                <p><?= htmlspecialchars($cfg('vitrine_depoimentos_texto', 'Comentarios curtos para reforcar confianca e atendimento.')) ?></p>
            </div>
        </div>
        <div class="testimonial-grid">
            <?php foreach ($depoimentos as $depoimento): ?>
            <article class="testimonial-card">
                <div class="testimonial-stars">
                    <i data-lucide="star" style="width: 16px; height: 16px; fill: currentColor;"></i>
                    <i data-lucide="star" style="width: 16px; height: 16px; fill: currentColor;"></i>
                    <i data-lucide="star" style="width: 16px; height: 16px; fill: currentColor;"></i>
                    <i data-lucide="star" style="width: 16px; height: 16px; fill: currentColor;"></i>
                    <i data-lucide="star" style="width: 16px; height: 16px; fill: currentColor;"></i>
                </div>
                <h3><?= htmlspecialchars($depoimento['name']) ?></h3>
                <p><?= htmlspecialchars($depoimento['text']) ?></p>
                <div class="testimonial-name"><?= htmlspecialchars($empresa) ?></div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="content-section">
        <div class="section-head">
            <div>
                <h2><?= htmlspecialchars($cfg('vitrine_localizacao_titulo', 'Onde estamos')) ?></h2>
                <p><?= htmlspecialchars($cfg('vitrine_localizacao_texto', 'Facilite retirada, visita presencial e contato com a loja.')) ?></p>
            </div>
        </div>
        <div class="location-card">
            <div class="location-copy">
                <h3><?= htmlspecialchars($empresa) ?></h3>
                <p><?= htmlspecialchars($cfg('vitrine_localizacao_descricao', 'Visite a loja para compras, retirada e suporte tecnico presencial.')) ?></p>
                <div class="location-meta">
                    <div class="location-row">
                        <i data-lucide="map-pin" style="width: 18px; height: 18px; flex: 0 0 auto;"></i>
                        <span><?= htmlspecialchars($settings['endereco'] ?? 'Cadastre o endereco da loja para exibir aqui.') ?></span>
                    </div>
                    <div class="location-row">
                        <i data-lucide="message-circle" style="width: 18px; height: 18px; flex: 0 0 auto;"></i>
                        <span><?= htmlspecialchars($settings['whatsapp'] ?? '') ?></span>
                    </div>
                </div>
                <div class="location-actions">
                <?php if ($mapLink !== ''): ?>
                <a class="pill-link" href="<?= htmlspecialchars($mapLink) ?>" target="_blank">
                    <i data-lucide="navigation" style="width: 18px; height: 18px;"></i>
                    Abrir no mapa
                </a>
                <?php endif; ?>
                <a class="pill-link secondary" href="https://wa.me/<?= htmlspecialchars($whats) ?>" target="_blank">
                    <i data-lucide="message-circle" style="width: 18px; height: 18px;"></i>
                    Chamar loja
                </a>
                </div>
            </div>
            <div class="location-map">
                <?php if ($mapEmbedUrl !== ''): ?>
                <iframe src="<?= htmlspecialchars($mapEmbedUrl) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                <?php else: ?>
                <div class="location-map-inner">
                    <i data-lucide="map" style="width: 42px; height: 42px; margin-bottom: 0.85rem;"></i>
                    <h3><?= htmlspecialchars($cfg('vitrine_localizacao_mapa_titulo', 'Retirada e atendimento')) ?></h3>
                    <p><?= htmlspecialchars($cfg('vitrine_localizacao_mapa_texto', 'Use o endereco cadastrado para receber clientes com clareza.')) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="content-section shop-panel">
        <div>
            <h3><?= htmlspecialchars($empresa) ?></h3>
            <p><?= htmlspecialchars($cfg('vitrine_cta_texto', 'Se quiser ajuda para escolher produto, falar de conserto ou confirmar disponibilidade, chame no WhatsApp.')) ?></p>
        </div>
        <div>
            <a class="featured-btn" href="https://wa.me/<?= htmlspecialchars($whats) ?>" target="_blank">
                <i data-lucide="message-circle" style="width: 18px; height: 18px;"></i>
                Falar no WhatsApp
            </a>
        </div>
    </section>
</main>

<?php
$produtosJson = [];
if (!empty($produtos)) {
    foreach ($produtos as $p) {
        $id = (int)($p['id'] ?? 0);
        if ($id > 0) {
            $produtosJson[$id] = [
                'nome' => $p['nome'],
                'preco' => 'R$ ' . number_format((float)($p['preco_venda'] ?? 0), 2, ',', '.'),
                'imagem' => $p['imagem_url'] ?? '',
                'link' => route_url('vitrine/produto/' . $id),
                'disponivel' => (int) ($p['quantidade'] ?? 0) > 0
            ];
        }
    }
}
?>
<script>
window.vitrineProducts = <?php echo json_encode($produtosJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
function scrollCategories(direction) {
    const track = document.getElementById('category-carousel');
    if (!track) return;

    const amount = Math.max(track.clientWidth * 0.75, 220);
    track.scrollBy({
        left: amount * direction,
        behavior: 'smooth'
    });
}
</script>

<?php require_once dirname(__DIR__) . '/layout/public_footer.php'; ?>
