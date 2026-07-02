<?php require_once dirname(__DIR__) . '/layout/public_header.php'; ?>
<?php
$galleryImages = $galleryImages ?? [];
$soldOut = (int) ($produto['quantidade'] ?? 0) <= 0;
$productGallery = [];
if (!empty($produto['imagem_url'])) {
    $productGallery[] = [
        'url' => $produto['imagem_url'],
        'alt' => $produto['nome'],
    ];
}
foreach ($galleryImages as $galleryImage) {
    if (!empty($galleryImage['imagem_url'])) {
        $productGallery[] = [
            'url' => $galleryImage['imagem_url'],
            'alt' => $produto['nome'] . ' - foto extra',
        ];
    }
}
$seenGalleryUrls = [];
$productGallery = array_values(array_filter($productGallery, static function (array $galleryItem) use (&$seenGalleryUrls): bool {
    $url = (string) ($galleryItem['url'] ?? '');
    if ($url === '' || isset($seenGalleryUrls[$url])) {
        return false;
    }

    $seenGalleryUrls[$url] = true;
    return true;
}));
$thumbGallery = array_slice($productGallery, 0, 4);
?>

<style>
    .product-page {
        padding: 0.65rem 0 2rem;
    }
    .product-details-grid {
        display: grid;
        grid-template-columns: minmax(0, 0.9fr) minmax(340px, 0.7fr);
        gap: 1.45rem;
        align-items: start;
    }
    .product-gallery-wrap {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .product-gallery {
        position: relative;
        background: linear-gradient(180deg, #ffffff 0%, #f6f9fd 100%);
        border-radius: 18px;
        padding: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 285px;
        border: 1px solid rgba(0, 52, 154, 0.08);
        box-shadow: 0 14px 30px -24px rgba(15, 23, 42, 0.34);
        overflow: hidden;
    }
    .product-gallery.is-zoomable {
        cursor: zoom-in;
    }
    .product-gallery img {
        max-width: 100%;
        max-height: 275px;
        object-fit: contain;
        border-radius: 14px;
    }
    .product-magnifier-lens {
        position: absolute;
        z-index: 3;
        width: 132px;
        height: 132px;
        border-radius: 50%;
        border: 3px solid #fff;
        background-color: #fff;
        background-repeat: no-repeat;
        box-shadow: 0 16px 34px -18px rgba(15, 23, 42, 0.62);
        pointer-events: none;
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.96);
        transition: opacity 0.15s ease, transform 0.15s ease;
    }
    .product-magnifier-lens.active {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    .product-zoom-trigger {
        position: absolute;
        top: 14px;
        right: 14px;
        z-index: 4;
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.94);
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 16px 28px -22px rgba(15, 23, 42, 0.75);
        transition: transform 0.2s, background 0.2s;
    }
    .product-zoom-trigger:hover {
        transform: scale(1.06);
        background: #fff;
    }
    .product-thumbs {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 82px));
        gap: 0.5rem;
        justify-content: center;
    }
    .product-thumb {
        border: 2px solid transparent;
        background: #fff;
        border-radius: 10px;
        padding: 3px;
        aspect-ratio: 1 / 1;
        cursor: pointer;
        box-shadow: 0 10px 24px -18px rgba(15, 23, 42, 0.45);
        transition: border-color 0.2s, transform 0.2s;
    }
    .product-thumb:hover,
    .product-thumb.active {
        border-color: #00349a;
        transform: translateY(-1px);
    }
    .product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px;
        display: block;
    }
    .product-zoom-modal {
        position: fixed;
        inset: 0;
        z-index: 2600;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        background: rgba(15, 23, 42, 0.82);
        backdrop-filter: blur(4px);
    }
    .product-zoom-modal.active {
        display: flex;
    }
    .product-zoom-content {
        position: relative;
        width: min(980px, 100%);
        max-height: 92vh;
        background: #fff;
        border-radius: 22px;
        padding: 1rem;
        box-shadow: 0 34px 90px -40px rgba(0, 0, 0, 0.75);
    }
    .product-zoom-content img {
        width: 100%;
        max-height: calc(92vh - 2rem);
        object-fit: contain;
        display: block;
        border-radius: 16px;
        background: #f8fbff;
    }
    .product-zoom-close {
        position: absolute;
        top: -14px;
        right: -14px;
        width: 42px;
        height: 42px;
        border: 0;
        border-radius: 999px;
        background: #fff;
        color: #0f172a;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 16px 30px -20px rgba(0, 0, 0, 0.7);
    }
    .product-info-panel {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        padding-top: 0.15rem;
    }
    .product-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-bottom: 0.55rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .product-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }
    .product-breadcrumb a:hover {
        color: var(--primary);
    }
    .product-badge {
        display: inline-flex;
        padding: 4px 10px;
        background: rgba(0, 52, 154, 0.06);
        color: var(--primary);
        font-size: 0.66rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 99px;
        width: fit-content;
    }
    .product-title-h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.72rem;
        line-height: 1.08;
        margin: 0;
        color: #0f172a;
        letter-spacing: 0;
    }
    .product-price-large {
        font-size: 2.08rem;
        font-weight: 800;
        color: #00349a;
        margin: 0.2rem 0 0.3rem;
        line-height: 1;
    }
    .product-meta-list {
        display: grid;
        gap: 0.45rem;
        padding: 0.72rem 0;
        border-top: 1px solid rgba(0,0,0,0.06);
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    .product-meta-item {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        font-size: 0.86rem;
        line-height: 1.25;
    }
    .product-meta-label {
        color: var(--text-muted);
    }
    .product-meta-value {
        font-weight: 700;
        color: #0f172a;
        text-align: right;
    }
    .product-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.55rem;
        margin-top: 0.2rem;
    }
    .product-description-box {
        border-top: 1px solid rgba(0,0,0,0.06);
        border-bottom: 1px solid rgba(0,0,0,0.06);
        padding: 0.72rem 0;
        color: #334155;
        font-size: 0.88rem;
        line-height: 1.55;
    }
    .product-description-title {
        display: block;
        color: var(--text-muted);
        font-size: 0.78rem;
        font-weight: 700;
        margin-bottom: 0.28rem;
    }
    .product-description-text {
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        white-space: pre-line;
    }
    .product-description-box.expanded .product-description-text {
        display: block;
        overflow: visible;
    }
    .product-description-toggle {
        margin-top: 0.42rem;
        padding: 0;
        border: 0;
        background: transparent;
        color: var(--primary);
        font: inherit;
        font-size: 0.82rem;
        font-weight: 800;
        cursor: pointer;
    }
    .btn-buy-now {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        min-height: 48px;
        padding: 0.75rem 0.9rem;
        background: #002d85;
        color: #fff;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 800;
        font-size: 0.94rem;
        transition: 0.3s;
        grid-column: 1 / -1;
    }
    .btn-buy-now:hover {
        background: #001f5c;
        transform: translateY(-3px);
        box-shadow: 0 15px 30px -10px rgba(0, 52, 154, 0.4);
    }
    .btn-whatsapp-now {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        min-height: 44px;
        padding: 0.65rem 0.75rem;
        background: #fff;
        color: #10233f;
        border: 1px solid rgba(0, 52, 154, 0.15);
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.86rem;
        transition: 0.3s;
    }
    .btn-whatsapp-now:hover {
        background: #f0fff4;
        border-color: #22c55e;
    }
    .btn-share-now {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        min-height: 44px;
        padding: 0.65rem 0.75rem;
        background: #fff;
        color: #10233f;
        border: 1px solid rgba(0, 52, 154, 0.15);
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.86rem;
        cursor: pointer;
        transition: 0.3s;
    }
    .btn-share-now:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: #f8fbff;
    }
    .related-section {
        margin-top: 1.7rem;
        border-top: 1px solid rgba(0,0,0,0.05);
        padding-top: 1.55rem;
    }
    .related-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        margin-bottom: 1rem;
        text-align: center;
        color: #0f172a;
    }
    /* Grid de Sugestões */
    .featured-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
    }
    .featured-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 18px 38px -34px rgba(15, 23, 42, 0.55);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .featured-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 26px 42px -28px rgba(15, 23, 42, 0.5);
        border-color: rgba(0, 52, 154, 0.22);
    }
    .featured-image {
        aspect-ratio: 1 / 0.72;
        height: auto;
        background: linear-gradient(180deg, #f8fbff 0%, #f1f6fd 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .favorite-toggle {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #fff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        z-index: 10;
        color: #64748b;
        transition: 0.3s;
    }
    .favorite-toggle:hover {
        transform: scale(1.1);
        color: #ef4444;
    }
    .favorite-toggle.active {
        background: #ef4444;
        color: #fff;
    }
    .featured-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 0.75rem;
        transition: transform 0.35s ease;
    }
    .featured-card:hover .featured-image img {
        transform: scale(1.04);
    }
    .featured-body {
        padding: 1rem;
    }
    .featured-tag {
        font-size: 0.62rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--primary);
        margin-bottom: 0.5rem;
        display: block;
    }
    .featured-body h3 {
        font-size: 0.98rem;
        margin: 0.45rem 0;
        color: #0f172a;
        line-height: 1.32;
        min-height: 2.6rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        white-space: normal;
        overflow: hidden;
    }
    .featured-price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.6rem;
        margin-top: 0.8rem;
        padding-top: 0.75rem;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
    }
    .featured-price {
        font-weight: 800;
        color: var(--primary);
        font-size: 1.08rem;
    }
    .featured-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        min-height: 40px;
        padding: 0 0.75rem;
        background: rgba(0, 52, 154, 0.05);
        color: var(--primary);
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        transition: 0.2s;
    }
    .featured-btn:hover {
        background: var(--primary);
        color: #fff;
    }

    @media (max-width: 960px) {
        .product-details-grid {
            grid-template-columns: 1fr;
            gap: 1.2rem;
        }
        .product-gallery {
            min-height: 270px;
        }
        .product-gallery img {
            max-height: 260px;
        }
        .product-title-h1 {
            font-size: 1.5rem;
        }
        .product-price-large {
            font-size: 1.85rem;
        }
        .product-actions {
            grid-template-columns: 1fr;
        }
        .product-magnifier-lens {
            display: none;
        }
    }
</style>

<main class="product-page">
    <div class="commerce-shell">
        <nav class="product-breadcrumb">
            <a href="<?= route_url('vitrine') ?>">Início</a>
            <i data-lucide="chevron-right" style="width: 14px;"></i>
            <a href="<?= route_url('vitrine/catalogo') ?>">Catálogo</a>
            <i data-lucide="chevron-right" style="width: 14px;"></i>
            <span><?= htmlspecialchars($produto['nome']) ?></span>
        </nav>

        <div class="product-details-grid">
            <!-- Galeria -->
            <div class="product-gallery-wrap">
                <div class="product-gallery <?= !empty($productGallery) ? 'is-zoomable' : '' ?> <?= $soldOut ? 'is-sold-out' : '' ?>" onclick="openProductZoom()">
                    <?php if ($soldOut): ?>
                    <span class="sold-out-ribbon">Esgotado</span>
                    <?php endif; ?>
                    <?php if (!empty($productGallery)): ?>
                    <img id="product-gallery-main" src="<?= htmlspecialchars($productGallery[0]['url']) ?>" alt="<?= htmlspecialchars($productGallery[0]['alt']) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='block';this.closest('.product-gallery').classList.remove('is-zoomable');lucide.createIcons();">
                    <i data-lucide="package" style="display:none;width:120px;height:120px;opacity:0.1;"></i>
                    <div class="product-magnifier-lens" id="product-magnifier-lens" aria-hidden="true"></div>
                    <button class="product-zoom-trigger" type="button" onclick="event.stopPropagation(); openProductZoom()" aria-label="Ampliar imagem">
                        <i data-lucide="search" style="width:22px;height:22px;"></i>
                    </button>
                    <?php else: ?>
                    <i data-lucide="package" style="width:120px;height:120px;opacity:0.1;"></i>
                    <?php endif; ?>
                </div>

                <?php if (count($productGallery) > 1): ?>
                <div class="product-thumbs" aria-label="Fotos do produto">
                    <?php foreach ($thumbGallery as $index => $galleryItem): ?>
                    <button type="button" class="product-thumb <?= $index === 0 ? 'active' : '' ?>" data-gallery-src="<?= htmlspecialchars($galleryItem['url']) ?>" data-gallery-alt="<?= htmlspecialchars($galleryItem['alt']) ?>" aria-label="Ver foto <?= $index + 1 ?>">
                        <img src="<?= htmlspecialchars($galleryItem['url']) ?>" alt="<?= htmlspecialchars($galleryItem['alt']) ?>">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Informações -->
            <div class="product-info-panel">
                <span class="product-badge"><?= htmlspecialchars($produto['categoria'] ?? 'Geral') ?></span>
                <h1 class="product-title-h1"><?= htmlspecialchars($produto['nome']) ?></h1>
                
                <div class="product-price-large">
                    R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?>
                </div>

                <div class="product-meta-list">
                    <?php if (!empty($produto['marca_compativel'])): ?>
                    <div class="product-meta-item">
                        <span class="product-meta-label">Marca Compatível</span>
                        <span class="product-meta-value"><?= htmlspecialchars($produto['marca_compativel']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($produto['modelo_compativel'])): ?>
                    <div class="product-meta-item">
                        <span class="product-meta-label">Modelo Compatível</span>
                        <span class="product-meta-value"><?= htmlspecialchars($produto['modelo_compativel']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="product-meta-item">
                        <span class="product-meta-label">Disponibilidade</span>
                        <span class="product-meta-value" style="color: <?= $soldOut ? '#dc2626' : '#059669' ?>;"><?= $soldOut ? 'Esgotado' : 'Em estoque' ?></span>
                    </div>
                </div>

                <?php $productDescription = trim((string) ($produto['localizacao'] ?? '')); ?>
                <?php if ($productDescription !== ''): ?>
                <div class="product-description-box" id="product-description-box">
                    <span class="product-description-title">Descri&ccedil;&atilde;o</span>
                    <p class="product-description-text" id="product-description-text"><?= htmlspecialchars($productDescription) ?></p>
                    <button type="button" class="product-description-toggle" id="product-description-toggle" onclick="toggleProductDescription()">Ver mais</button>
                </div>
                <?php endif; ?>

                <div class="product-actions">
                    <?php if ($soldOut): ?>
                    <span class="btn-buy-now btn-sold-out" aria-disabled="true">
                        <i data-lucide="circle-x"></i> Produto esgotado
                    </span>
                    <?php else: ?>
                    <a href="javascript:void(0)" onclick="addToCart(<?= (int) $produto['id'] ?>, <?= e(json_attr($produto['nome'])) ?>, <?= (float) $produto['preco_venda'] ?>, <?= e(json_attr($produto['imagem_url'] ?? '')) ?>)" class="btn-buy-now">
                        <i data-lucide="shopping-cart"></i> Adicionar ao carrinho
                    </a>
                    <?php endif; ?>
                    <a href="https://wa.me/<?= htmlspecialchars($settings['whatsapp'] ?? '5511999999999') ?>?text=<?= urlencode('Olá! Vi no site e tenho interesse no produto: ' . $produto['nome']) ?>" target="_blank" class="btn-whatsapp-now">
                        <i data-lucide="message-circle"></i> Falar com Vendedor
                    </a>
                    <button type="button" class="btn-share-now" onclick="shareStoreContent(<?= e(json_attr($produto['nome'])) ?>, <?= e(json_attr('Veja este produto da Conectados: ' . $produto['nome'])) ?>, <?= e(json_attr(absolute_route_url('vitrine/produto/' . $produto['id']))) ?>, <?= e(json_attr($produto['imagem_url'] ?? '')) ?>)">
                        <i data-lucide="share-2"></i> Compartilhar Produto
                    </button>
                </div>
            </div>
        </div>

        <!-- Seção Relacionados -->
        <section class="related-section">
            <h2 class="related-title">Quem viu este, também gostou</h2>
            <div class="featured-grid">
                <?php foreach ($relacionados as $p): ?>
                <?php if ($p['id'] != $produto['id']): ?>
                <?php $relatedSoldOut = (int) ($p['quantidade'] ?? 0) <= 0; ?>
                <article class="featured-card <?= $relatedSoldOut ? 'is-sold-out' : '' ?>">
                    <div class="featured-image">
                        <?php if ($relatedSoldOut): ?>
                        <span class="sold-out-ribbon">Esgotado</span>
                        <?php endif; ?>
                        <button class="favorite-toggle" type="button" onclick="toggleFavorite(<?= (int) $p['id'] ?>)" data-favorite-id="<?= (int) $p['id'] ?>" aria-label="Favoritar produto">
                            <i data-lucide="heart" style="width: 18px; height: 18px;"></i>
                        </button>
                        <a href="<?= route_url('vitrine/produto/' . $p['id']) ?>" style="display:block;width:100%;height:100%;">
                            <?php if (!empty($p['imagem_url'])): ?>
                            <img src="<?= htmlspecialchars($p['imagem_url']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='block';lucide.createIcons();">
                            <i data-lucide="package" style="display:none;width:58px;height:58px;"></i>
                            <?php else: ?>
                            <i data-lucide="package" style="width: 58px; height: 58px;"></i>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="featured-body">
                        <span class="featured-tag"><?= htmlspecialchars(trim($p['categoria'] ?? '') ?: 'Geral') ?></span>
                        <a href="<?= route_url('vitrine/produto/' . $p['id']) ?>" style="text-decoration:none;">
                            <h3><?= htmlspecialchars($p['nome']) ?></h3>
                        </a>
                        <div class="featured-price-row">
                            <span class="featured-price">R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></span>
                            <?php if ($relatedSoldOut): ?>
                            <span class="featured-btn btn-sold-out" aria-disabled="true">
                                Esgotado
                                <i data-lucide="circle-x" style="width: 18px; height: 18px;"></i>
                            </span>
                            <?php else: ?>
                            <a class="featured-btn" href="javascript:void(0)" onclick="addToCart(<?= (int) $p['id'] ?>, <?= e(json_attr($p['nome'])) ?>, <?= (float) $p['preco_venda'] ?>, <?= e(json_attr($p['imagem_url'] ?? '')) ?>)">
                                Adicionar
                                <i data-lucide="shopping-cart" style="width: 18px; height: 18px;"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php if (!empty($productGallery)): ?>
<div class="product-zoom-modal" id="product-zoom-modal" aria-hidden="true" onclick="closeProductZoom(event)">
    <div class="product-zoom-content" role="dialog" aria-modal="true" aria-label="Imagem ampliada do produto" onclick="event.stopPropagation()">
        <button class="product-zoom-close" type="button" onclick="closeProductZoom()" aria-label="Fechar imagem ampliada">
            <i data-lucide="x" style="width:22px;height:22px;"></i>
        </button>
        <img id="product-zoom-image" src="<?= htmlspecialchars($productGallery[0]['url']) ?>" alt="<?= htmlspecialchars($productGallery[0]['alt']) ?>">
    </div>
</div>
<?php endif; ?>

<script>
// Garantir que os produtos da página de detalhes também estejam no window.vitrineProducts para o carrinho/favoritos
if (!window.vitrineProducts) window.vitrineProducts = {};
window.vitrineProducts[<?= (int) $produto['id'] ?>] = {
    nome: '<?= addslashes($produto['nome']) ?>',
    preco: 'R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?>',
    imagem: '<?= addslashes($produto['imagem_url'] ?? '') ?>',
    link: '<?= route_url('vitrine/produto', ['id' => $produto['id']]) ?>',
    disponivel: <?= $soldOut ? 'false' : 'true' ?>
};

document.querySelectorAll('.product-thumbs .product-thumb').forEach((button) => {
    button.addEventListener('click', () => {
        const mainImage = document.getElementById('product-gallery-main');
        const zoomImage = document.getElementById('product-zoom-image');
        const lens = document.getElementById('product-magnifier-lens');
        if (!mainImage) return;

        mainImage.src = button.dataset.gallerySrc || '';
        mainImage.alt = button.dataset.galleryAlt || '<?= addslashes($produto['nome']) ?>';
        if (zoomImage) {
            zoomImage.src = mainImage.src;
            zoomImage.alt = mainImage.alt;
        }
        if (lens) {
            lens.classList.remove('active');
            lens.style.backgroundImage = '';
        }
        mainImage.style.display = 'block';
        document.querySelectorAll('.product-thumbs .product-thumb').forEach((thumb) => thumb.classList.remove('active'));
        button.classList.add('active');
    });
});

function setupProductMagnifier() {
    const gallery = document.querySelector('.product-gallery.is-zoomable');
    const image = document.getElementById('product-gallery-main');
    const lens = document.getElementById('product-magnifier-lens');
    if (!gallery || !image || !lens) return;

    const zoomLevel = 2.4;
    const hideLens = () => lens.classList.remove('active');

    const moveLens = (event) => {
        if (window.matchMedia('(max-width: 960px)').matches || image.style.display === 'none') {
            hideLens();
            return;
        }

        const imageRect = image.getBoundingClientRect();
        const galleryRect = gallery.getBoundingClientRect();
        const x = event.clientX - imageRect.left;
        const y = event.clientY - imageRect.top;

        if (x < 0 || y < 0 || x > imageRect.width || y > imageRect.height) {
            hideLens();
            return;
        }

        const lensSize = lens.offsetWidth || 132;
        const imageUrl = (image.currentSrc || image.src).replace(/"/g, '%22');

        lens.style.left = `${event.clientX - galleryRect.left}px`;
        lens.style.top = `${event.clientY - galleryRect.top}px`;
        lens.style.backgroundImage = `url("${imageUrl}")`;
        lens.style.backgroundSize = `${imageRect.width * zoomLevel}px ${imageRect.height * zoomLevel}px`;
        lens.style.backgroundPosition = `${-(x * zoomLevel - lensSize / 2)}px ${-(y * zoomLevel - lensSize / 2)}px`;
        lens.classList.add('active');
    };

    gallery.addEventListener('mousemove', moveLens);
    gallery.addEventListener('mouseleave', hideLens);
    window.addEventListener('resize', hideLens);
}

setupProductMagnifier();

function setupProductDescriptionToggle() {
    const box = document.getElementById('product-description-box');
    const text = document.getElementById('product-description-text');
    const toggle = document.getElementById('product-description-toggle');
    if (!box || !text || !toggle) return;

    const isClamped = text.scrollHeight > text.clientHeight + 2;
    if (!isClamped) {
        toggle.style.display = 'none';
    }
}

function toggleProductDescription() {
    const box = document.getElementById('product-description-box');
    const toggle = document.getElementById('product-description-toggle');
    if (!box || !toggle) return;

    const expanded = box.classList.toggle('expanded');
    toggle.textContent = expanded ? 'Ver menos' : 'Ver mais';
}

setupProductDescriptionToggle();

function openProductZoom() {
    const modal = document.getElementById('product-zoom-modal');
    const mainImage = document.getElementById('product-gallery-main');
    const zoomImage = document.getElementById('product-zoom-image');
    if (!modal || !mainImage || !zoomImage || !mainImage.src) return;

    zoomImage.src = mainImage.src;
    zoomImage.alt = mainImage.alt;
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
}

function closeProductZoom(event) {
    if (event && event.target !== event.currentTarget) return;
    const modal = document.getElementById('product-zoom-modal');
    if (!modal) return;
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeProductZoom();
});
</script>

<?php require_once dirname(__DIR__) . '/layout/public_footer.php'; ?>
