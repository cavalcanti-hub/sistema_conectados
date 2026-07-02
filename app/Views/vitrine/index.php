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
    .shop-home {
        display: flex;
        flex-direction: column;
        gap: 0;
        padding-bottom: 2rem;
    }
    .hero-banner {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        height: auto;
        min-height: 0;
        border-radius: 0;
        overflow: hidden;
        background: #020509;
        box-shadow: none;
    }
    .hero-banner::after {
        display: none;
    }
    .hero-banner img {
        position: relative;
        width: 100%;
        height: clamp(420px, 44vw, 620px);
        object-fit: contain;
        display: block;
        background: #020509;
    }
    .hero-banner-content {
        position: relative;
        inset: auto;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.85rem;
        padding: 1rem clamp(1rem, 3vw, 2rem) 1.1rem;
        max-width: none;
        width: 100%;
        background: linear-gradient(180deg, #061126 0%, #03102a 100%);
        border-top: 1px solid rgba(82, 169, 255, 0.22);
        color: #fff;
    }
    .hero-banner-kicker {
        display: none;
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #4de2ff;
    }
    .hero-banner h2 {
        display: none;
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: clamp(2rem, 3.1vw, 2.8rem);
        line-height: 1.02;
    }
    .hero-banner p {
        display: none;
        margin: 0;
        font-size: 1rem;
        color: rgba(255,255,255,0.82);
    }
    .hero-banner-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        align-items: stretch;
        gap: 0.72rem;
        width: min(100%, 860px);
        margin-top: 0;
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
    .benefits-strip {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    .benefit-card {
        background: #fff;
        border: 1px solid rgba(0, 52, 154, 0.08);
        border-radius: 22px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        box-shadow: 0 18px 30px -28px rgba(15, 23, 42, 0.35);
    }
    .benefit-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 52, 154, 0.08);
        color: var(--primary);
        flex: 0 0 auto;
    }
    .benefit-card strong {
        display: block;
        font-size: 0.95rem;
        margin-bottom: 0.15rem;
    }
    .benefit-card span {
        color: var(--text-muted);
        font-size: 0.86rem;
    }
    .section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.9rem;
    }
    .section-head h2 {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: clamp(1.45rem, 2.8vw, 2.1rem);
        color: #071b37;
    }
    .section-head p {
        margin: 0.35rem 0 0;
        color: var(--text-muted);
    }
    .content-section {
        margin-top: 2.2rem;
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
        gap: 1rem;
    }
    .featured-card {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 18px 38px -34px rgba(15, 23, 42, 0.55);
        display: flex;
        flex-direction: column;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .featured-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 26px 42px -28px rgba(15, 23, 42, 0.5);
        border-color: rgba(0, 52, 154, 0.22);
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
        gap: 1rem;
    }
    .service-card,
    .testimonial-card,
    .location-card {
        background: #fff;
        border: 1px solid rgba(0, 52, 154, 0.08);
        border-radius: 26px;
        box-shadow: 0 20px 34px -30px rgba(15, 23, 42, 0.45);
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
            height: auto;
            min-height: 0;
        }
        .hero-banner img {
            height: auto;
        }
        .hero-banner::after {
            display: none;
        }
        .hero-banner-content {
            max-width: none;
            justify-content: center;
            padding: 0.85rem 1rem 1rem;
            width: 100%;
        }
        .hero-banner h2 {
            max-width: calc(100vw - 3rem);
            overflow-wrap: break-word;
            font-size: clamp(1.75rem, 7.2vw, 2rem);
        }
        .hero-banner p {
            max-width: calc(100vw - 3rem);
            overflow-wrap: break-word;
            font-size: 0.95rem;
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
        <img src="<?= app_url('assets/img/banner.jpeg') ?>" alt="Banner Conectados Assistencia Tecnica">
        <div class="hero-banner-content">
            <span class="hero-banner-kicker"><?= htmlspecialchars($cfg('vitrine_hero_kicker', 'Tecnologia conectada')) ?></span>
            <h2><?= htmlspecialchars($cfg('vitrine_hero_titulo', 'Celulares, games e acessorios em destaque')) ?></h2>
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
