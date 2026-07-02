<?php require_once dirname(__DIR__) . '/layout/public_header.php'; ?>

<style>
    .vitrine-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.15rem;
    }
    .product-card {
        background: var(--bg-card);
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 16px;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        display: flex;
        flex-direction: column;
        background: #fff;
        box-shadow: 0 18px 38px -34px rgba(15, 23, 42, 0.55);
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 26px 42px -28px rgba(15, 23, 42, 0.5);
        border-color: rgba(0, 52, 154, 0.22);
    }
    .product-image-container {
        width: 100%;
        aspect-ratio: 1 / 0.72;
        position: relative;
        overflow: hidden;
        background: linear-gradient(180deg, #f8fbff 0%, #f1f6fd 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .product-image-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 0.75rem;
        transition: transform 0.35s ease;
    }
    .catalog-favorite {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.65);
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 2;
        box-shadow: 0 16px 26px -20px rgba(15, 23, 42, 0.4);
    }
    .product-card:hover .product-image-container img {
        transform: scale(1.04);
    }
    .product-info {
        padding: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .product-cat {
        font-size: 0.62rem;
        color: var(--primary);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.035em;
        margin-bottom: 0.45rem;
        opacity: 0.82;
    }
    .product-title {
        font-family: 'Outfit', sans-serif;
        font-size: 0.98rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.32;
        color: var(--text-main);
        min-height: 3.85rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-price-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin: 0.8rem 0;
        padding-top: 0.75rem;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
    }
    .product-price {
        font-size: 1.08rem;
        font-weight: 800;
        color: var(--primary);
    }
    .action-row {
        display: flex;
        gap: 0.45rem;
        margin-top: auto;
    }
    .btn-checkout {
        flex: 1;
        background: var(--primary);
        color: white;
        border: none;
        min-height: 40px;
        padding: 0 0.75rem;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: 0.3s;
    }
    .btn-checkout:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
    .btn-whats-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: 0.3s;
        border: 1px solid var(--border);
    }
    .btn-whats-icon:hover {
        background: #f0fff4;
        border-color: #22c55e;
        transform: translateY(-2px);
    }
    .btn-share-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #10233f;
        border: 1px solid var(--border);
        cursor: pointer;
        transition: 0.3s;
    }
    .btn-share-icon:hover {
        color: var(--primary);
        border-color: var(--primary);
        transform: translateY(-2px);
    }
    .filter-bar {
        background: var(--bg-card);
        padding: 1.25rem;
        border-radius: 16px;
        border: 1px solid var(--border);
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 2.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .catalog-header-actions {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
    }
</style>

<div class="catalog-header-actions">
    <button class="btn btn-outline" type="button" onclick="shareStoreContent('Catalogo Conectados', 'Veja o catalogo de produtos da Conectados.', '<?= app_url('') ?>')">
        <i data-lucide="share-2" style="width: 18px;"></i>
        Compartilhar catalogo
    </button>
</div>

<div class="filter-bar">
    <div style="flex: 1; min-width: 250px; position: relative;">
        <i data-lucide="search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); width: 20px; color: var(--text-muted);"></i>
        <input type="text" id="cat-search" class="form-control" style="padding-left: 45px; border-radius: 10px;" placeholder="O que voce esta procurando?" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" oninput="filtrarCatalogo()">
    </div>
    <select id="cat-select" class="form-control" style="width: 220px; border-radius: 10px;" onchange="filtrarCatalogo()">
        <option value="">Todas as categorias</option>
        <?php foreach ($categorias as $cat): ?>
        <option value="<?= htmlspecialchars(strtolower($cat)) ?>" <?= strtolower($filters['categoria'] ?? '') === strtolower($cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="vitrine-grid" id="catalogo-container">
    <?php foreach ($produtos as $p): ?>
    <?php $soldOut = (int) ($p['quantidade'] ?? 0) <= 0; ?>
    <div class="product-card <?= $soldOut ? 'is-sold-out' : '' ?>" data-nome="<?= strtolower($p['nome']) ?>" data-cat="<?= strtolower($p['categoria']) ?>">
        <div class="product-image-container">
            <?php if ($soldOut): ?>
            <span class="sold-out-ribbon">Esgotado</span>
            <?php endif; ?>
            <button class="catalog-favorite" type="button" onclick="toggleFavorite(<?= (int) $p['id'] ?>)" data-favorite-id="<?= (int) $p['id'] ?>" aria-label="Favoritar produto">
                <i data-lucide="heart" style="width: 18px;"></i>
            </button>
            <a href="<?= route_url('vitrine/produto/' . $p['id']) ?>" style="display:block;width:100%;height:100%;">
                <?php if (!empty($p['imagem_url'])): ?>
                <img src="<?= htmlspecialchars($p['imagem_url']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='block';lucide.createIcons();">
                <i data-lucide="package" style="display:none;width:48px;opacity:0.1;"></i>
                <?php else: ?>
                <i data-lucide="package" style="width: 48px; opacity: 0.1;"></i>
                <?php endif; ?>
            </a>
        </div>
        <div class="product-info">
            <span class="product-cat"><?= htmlspecialchars($p['categoria']) ?></span>
            <a href="<?= route_url('vitrine/produto/' . $p['id']) ?>" style="text-decoration:none;color:inherit;">
                <h3 class="product-title"><?= htmlspecialchars($p['nome']) ?></h3>
            </a>
            
            <div class="product-price-row">
                <div class="product-price">R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></div>
            </div>
            
            <div class="action-row">
                <?php if ($soldOut): ?>
                <button type="button" class="btn-checkout btn-sold-out" disabled aria-disabled="true">
                    <i data-lucide="circle-x" style="width: 18px;"></i> Esgotado
                </button>
                <?php else: ?>
                <button onclick="addToCart(<?= (int) $p['id'] ?>, <?= e(json_attr($p['nome'])) ?>, <?= (float) $p['preco_venda'] ?>, <?= e(json_attr($p['imagem_url'] ?? '')) ?>)" class="btn-checkout">
                    <i data-lucide="shopping-cart" style="width: 18px;"></i> Adicionar
                </button>
                <?php endif; ?>
                <a href="https://wa.me/<?= $settings['whatsapp'] ?? '5511999999999' ?>?text=<?= urlencode('Ola! Vi no site e tenho interesse em: ' . $p['nome']) ?>" target="_blank" class="btn-whats-icon" title="WhatsApp">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" style="width: 24px; height: 24px;">
                </a>
                <button class="btn-share-icon" type="button" onclick="shareStoreContent(<?= e(json_attr($p['nome'])) ?>, <?= e(json_attr('Veja este produto da Conectados: ' . $p['nome'])) ?>, <?= e(json_attr(absolute_route_url('vitrine/produto/' . $p['id']))) ?>, <?= e(json_attr($p['imagem_url'] ?? '')) ?>)" title="Compartilhar produto" aria-label="Compartilhar produto">
                    <i data-lucide="share-2" style="width: 18px;"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($produtos)): ?>
<div style="text-align: center; padding: 5rem; color: var(--text-muted);">
    <h3>Nenhum produto em destaque no momento.</h3>
    <p>Entre em contato para saber mais.</p>
</div>
<?php endif; ?>

<div id="no-results" style="display: none; text-align: center; padding: 4rem; color: var(--text-muted);">
    <i data-lucide="search-x" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.35;"></i>
    <h3>Ops! Nao encontramos esse produto.</h3>
    <p>Tente buscar por outro termo ou trocar a categoria.</p>
</div>

<script>
window.vitrineProducts = <?php echo json_encode(array_reduce($produtos, function ($carry, $product) {
    $id = (int) ($product['id'] ?? 0);
    if ($id <= 0) {
        return $carry;
    }
    $carry[$id] = [
        'nome' => $product['nome'],
        'preco' => 'R$ ' . number_format((float) $product['preco_venda'], 2, ',', '.'),
        'imagem' => $product['imagem_url'] ?? '',
        'link' => route_url('vitrine/produto/' . $id),
        'disponivel' => (int) ($product['quantidade'] ?? 0) > 0
    ];
    return $carry;
}, []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
function filtrarCatalogo() {
    const q = document.getElementById('cat-search').value.toLowerCase();
    const cat = document.getElementById('cat-select').value.toLowerCase();
    const cards = document.querySelectorAll('.product-card');
    let hasResults = false;
    
    cards.forEach(card => {
        const nomeMatch = card.dataset.nome.includes(q);
        const catMatch = !cat || card.dataset.cat === cat;
        
        if (nomeMatch && catMatch) {
            card.style.display = 'flex';
            hasResults = true;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('no-results').style.display = hasResults ? 'none' : 'block';
    document.getElementById('catalogo-container').style.display = hasResults ? 'grid' : 'none';
}
</script>

<?php require_once dirname(__DIR__) . '/layout/public_footer.php'; ?>
