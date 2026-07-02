<?php require_once dirname(__DIR__) . '/layout/public_header.php'; ?>

<?php
$whats = preg_replace('/\D+/', '', (string) ($settings['whatsapp'] ?? '5511999999999'));
$whats = $whats !== '' ? $whats : '5511999999999';
?>

<style>
    .stationery-hero {
        width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        background:
            linear-gradient(120deg, rgba(0, 52, 154, 0.92), rgba(13, 148, 136, 0.74)),
            radial-gradient(circle at 82% 24%, rgba(255,255,255,0.28), transparent 28%),
            #08356d;
        color: #fff;
    }
    .stationery-hero-inner {
        max-width: 1180px;
        margin: 0 auto;
        min-height: 310px;
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(260px, 0.9fr);
        align-items: center;
        gap: 2rem;
        padding: 2.4rem 1.5rem;
    }
    .stationery-kicker {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #bff8ee;
        margin-bottom: .8rem;
    }
    .stationery-hero h2 {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: clamp(2rem, 4vw, 3.4rem);
        line-height: 1.02;
        max-width: 680px;
    }
    .stationery-hero p {
        max-width: 620px;
        color: rgba(255,255,255,.86);
        margin: .9rem 0 0;
        font-size: 1rem;
        line-height: 1.55;
    }
    .stationery-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        margin-top: 1.35rem;
    }
    .stationery-action {
        min-height: 46px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        padding: 0 1rem;
        font-weight: 800;
        text-decoration: none;
        border: 1px solid rgba(255,255,255,.22);
        color: #fff;
        background: rgba(255,255,255,.11);
    }
    .stationery-action.primary {
        background: #fff;
        color: #08356d;
    }
    .stationery-visual {
        min-height: 220px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,.24);
        background: rgba(255,255,255,.12);
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
        padding: .85rem;
        box-shadow: 0 24px 55px -35px rgba(0,0,0,.65);
    }
    .stationery-chip {
        border-radius: 14px;
        background: rgba(255,255,255,.9);
        color: #0f2442;
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .9rem;
        font-weight: 800;
    }
    .stationery-chip i {
        color: var(--primary);
        flex: 0 0 auto;
    }
    .stationery-toolbar {
        margin: 1.6rem 0 1.2rem;
        display: flex;
        align-items: center;
        gap: .85rem;
        flex-wrap: wrap;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 14px;
        padding: 1rem;
        box-shadow: 0 18px 32px -30px rgba(15, 23, 42, .45);
    }
    .stationery-toolbar label {
        position: relative;
        flex: 1 1 260px;
    }
    .stationery-toolbar input,
    .stationery-toolbar select {
        width: 100%;
        min-height: 44px;
        border-radius: 10px;
        border: 1px solid var(--border);
        padding: 0 .85rem;
        font: inherit;
    }
    .stationery-toolbar label input {
        padding-left: 2.65rem;
    }
    .stationery-toolbar label i {
        position: absolute;
        left: .9rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }
    .stationery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
        padding-bottom: 2rem;
    }
    .stationery-card {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 14px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 18px 38px -34px rgba(15, 23, 42, .55);
    }
    .stationery-image {
        aspect-ratio: 1 / .74;
        background: #f4f8fc;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .stationery-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: .75rem;
    }
    .stationery-body {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .stationery-category {
        color: var(--primary);
        font-size: .68rem;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: .45rem;
    }
    .stationery-title {
        min-height: 3.2rem;
        margin: 0;
        color: var(--text-main);
        font-family: 'Outfit', sans-serif;
        font-size: 1rem;
        line-height: 1.28;
    }
    .stationery-price {
        margin: .85rem 0;
        padding-top: .8rem;
        border-top: 1px solid rgba(15, 23, 42, .07);
        color: var(--primary);
        font-weight: 900;
        font-size: 1.08rem;
    }
    .stationery-card-actions {
        margin-top: auto;
        display: flex;
        gap: .45rem;
    }
    .stationery-card-actions .btn-add {
        flex: 1;
        min-height: 40px;
        border: 0;
        border-radius: 10px;
        background: var(--primary);
        color: #fff;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
    }
    .stationery-card-actions .icon-action {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        color: #0f2442;
    }
    .stationery-empty {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 14px;
        padding: 3rem 1.5rem;
        text-align: center;
        color: var(--text-muted);
        margin-bottom: 2rem;
    }
    @media (max-width: 860px) {
        .stationery-hero-inner {
            grid-template-columns: 1fr;
        }
        .stationery-visual {
            min-height: 0;
        }
    }
</style>

<section class="stationery-hero">
    <div class="stationery-hero-inner">
        <div>
            <span class="stationery-kicker"><i data-lucide="notebook-tabs" style="width:16px;height:16px;"></i> Papelaria Conectados</span>
            <h2>Materiais para escola, escritorio e rotina.</h2>
            <p>Confira itens de papelaria disponiveis na loja e fale direto pelo WhatsApp para separar seu pedido.</p>
            <div class="stationery-actions">
                <a class="stationery-action primary" href="https://wa.me/<?= e($whats) ?>?text=<?= urlencode('Ola! Quero ver produtos de papelaria da Conectados.') ?>" target="_blank">
                    <i data-lucide="message-circle" style="width:18px;height:18px;"></i>
                    Pedir pelo WhatsApp
                </a>
                <a class="stationery-action" href="<?= route_url('vitrine/catalogo') ?>">
                    <i data-lucide="shopping-bag" style="width:18px;height:18px;"></i>
                    Ver catalogo completo
                </a>
            </div>
        </div>
        <div class="stationery-visual" aria-hidden="true">
            <div class="stationery-chip"><i data-lucide="book-open"></i> Cadernos</div>
            <div class="stationery-chip"><i data-lucide="pen-line"></i> Canetas</div>
            <div class="stationery-chip"><i data-lucide="briefcase-business"></i> Escritorio</div>
            <div class="stationery-chip"><i data-lucide="palette"></i> Criatividade</div>
        </div>
    </div>
</section>

<div class="stationery-toolbar">
    <label>
        <i data-lucide="search" style="width:18px;height:18px;"></i>
        <input type="text" id="stationery-search" placeholder="Buscar na papelaria" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" oninput="filterStationery()">
    </label>
    <select id="stationery-category" onchange="filterStationery()">
        <option value="">Todas as categorias</option>
        <?php foreach ($categorias as $cat): ?>
        <option value="<?= htmlspecialchars(strtolower($cat)) ?>" <?= strtolower($filters['categoria'] ?? '') === strtolower($cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<?php if (!empty($produtos)): ?>
<div class="stationery-grid" id="stationery-grid">
    <?php foreach ($produtos as $p): ?>
    <?php $soldOut = (int) ($p['quantidade'] ?? 0) <= 0; ?>
    <article class="stationery-card" data-name="<?= htmlspecialchars(strtolower((string) $p['nome'])) ?>" data-cat="<?= htmlspecialchars(strtolower((string) $p['categoria'])) ?>">
        <a class="stationery-image" href="<?= route_url('vitrine/produto/' . (int) $p['id']) ?>">
            <?php if ($soldOut): ?>
            <span class="sold-out-ribbon">Esgotado</span>
            <?php endif; ?>
            <?php if (!empty($p['imagem_url'])): ?>
            <img src="<?= htmlspecialchars($p['imagem_url']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" loading="lazy">
            <?php else: ?>
            <i data-lucide="package" style="width:48px;opacity:.14;"></i>
            <?php endif; ?>
        </a>
        <div class="stationery-body">
            <span class="stationery-category"><?= htmlspecialchars($p['categoria'] ?: 'Papelaria') ?></span>
            <a href="<?= route_url('vitrine/produto/' . (int) $p['id']) ?>" style="text-decoration:none;color:inherit;">
                <h3 class="stationery-title"><?= htmlspecialchars($p['nome']) ?></h3>
            </a>
            <div class="stationery-price">R$ <?= number_format((float) $p['preco_venda'], 2, ',', '.') ?></div>
            <div class="stationery-card-actions">
                <?php if ($soldOut): ?>
                <button class="btn-add" type="button" disabled><i data-lucide="circle-x" style="width:18px;"></i> Esgotado</button>
                <?php else: ?>
                <button class="btn-add" type="button" onclick="addToCart(<?= (int) $p['id'] ?>, <?= e(json_attr($p['nome'])) ?>, <?= (float) $p['preco_venda'] ?>, <?= e(json_attr($p['imagem_url'] ?? '')) ?>)">
                    <i data-lucide="shopping-cart" style="width:18px;"></i> Adicionar
                </button>
                <?php endif; ?>
                <a class="icon-action" href="https://wa.me/<?= e($whats) ?>?text=<?= urlencode('Ola! Vi na pagina de papelaria e tenho interesse em: ' . $p['nome']) ?>" target="_blank" title="WhatsApp">
                    <i data-lucide="message-circle" style="width:18px;"></i>
                </a>
                <button class="icon-action" type="button" onclick="shareStoreContent(<?= e(json_attr($p['nome'])) ?>, <?= e(json_attr('Veja este produto de papelaria da Conectados: ' . $p['nome'])) ?>, <?= e(json_attr(absolute_route_url('vitrine/produto/' . (int) $p['id']))) ?>, <?= e(json_attr($p['imagem_url'] ?? '')) ?>)" title="Compartilhar">
                    <i data-lucide="share-2" style="width:18px;"></i>
                </button>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="stationery-empty" id="stationery-empty" style="<?= empty($produtos) ? '' : 'display:none;' ?>">
    <i data-lucide="notebook-tabs" style="width:48px;height:48px;opacity:.28;margin-bottom:.85rem;"></i>
    <h3 style="margin:0 0 .35rem;color:var(--text-main);font-family:'Outfit',sans-serif;">Nenhum item de papelaria encontrado.</h3>
    <p style="margin:0;">Cadastre produtos com categoria Papelaria ou nomes como caderno, caneta, agenda, lapis e papel para aparecerem aqui.</p>
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

function filterStationery() {
    const query = (document.getElementById('stationery-search').value || '').toLowerCase();
    const category = (document.getElementById('stationery-category').value || '').toLowerCase();
    const cards = document.querySelectorAll('.stationery-card');
    let visible = false;

    cards.forEach((card) => {
        const matchName = card.dataset.name.includes(query);
        const matchCategory = !category || card.dataset.cat === category;
        const show = matchName && matchCategory;
        card.style.display = show ? 'flex' : 'none';
        visible = visible || show;
    });

    const grid = document.getElementById('stationery-grid');
    const empty = document.getElementById('stationery-empty');
    if (grid) {
        grid.style.display = visible ? 'grid' : 'none';
    }
    if (empty) {
        empty.style.display = visible ? 'none' : 'block';
    }
}
</script>

<?php require_once dirname(__DIR__) . '/layout/public_footer.php'; ?>
