<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>

<?php
$items = $items ?? [];
$categorias = $categorias ?? [];
$search = (string) ($search ?? '');
$categoria = (string) ($categoria ?? '');
$total = (int) ($total ?? 0);
$totalValor = (float) ($totalValor ?? 0);
$publishedCount = 0;
$errorCount = 0;
$lowStockCount = 0;
foreach ($items as $product) {
    if (!empty($product['mercado_livre_item_id'])) {
        $publishedCount++;
    } elseif (!empty($product['mercado_livre_last_error'])) {
        $errorCount++;
    }
    if ((int) ($product['quantidade'] ?? 0) <= (int) ($product['estoque_minimo'] ?? 0)) {
        $lowStockCount++;
    }
}
?>

<style>
    .catalog-toolbar {
        display:grid;
        grid-template-columns:minmax(280px,1fr) auto;
        gap:1rem;
        align-items:center;
        margin-bottom:1rem;
    }
    .catalog-filter {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:14px;
        box-shadow:var(--shadow-sm);
        padding:.7rem;
        display:grid;
        grid-template-columns:minmax(220px,1fr) 220px auto auto;
        gap:.65rem;
        align-items:center;
    }
    .catalog-filter .form-control { min-height:44px; }
    .catalog-actions { display:flex;justify-content:flex-end;gap:.7rem;flex-wrap:wrap; }
    .catalog-actions .btn { min-height:46px;border-radius:12px;text-decoration:none; }
    .catalog-metrics {
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:1rem;
        margin-bottom:1rem;
    }
    .catalog-metric {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:14px;
        box-shadow:var(--shadow-sm);
        padding:1rem;
        display:flex;
        align-items:center;
        gap:.85rem;
        min-height:92px;
    }
    .metric-icon {
        width:44px;height:44px;border-radius:12px;
        display:flex;align-items:center;justify-content:center;flex:0 0 auto;
        background:#eef4ff;color:var(--primary);
    }
    .metric-icon.green { background:#ecfdf5;color:#059669; }
    .metric-icon.yellow { background:#fffbeb;color:#b45309; }
    .metric-icon.red { background:#fef2f2;color:#dc2626; }
    .catalog-metric span { display:block;color:var(--text-muted);font-size:.76rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em; }
    .catalog-metric strong { display:block;color:var(--text-main);font-size:1.25rem;font-weight:900;margin-top:.18rem; }
    .products-panel {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:16px;
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }
    .products-panel-head {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:1rem;
        padding:1rem 1.1rem;
        border-bottom:1px solid var(--border);
        background:#f8fafc;
    }
    .products-panel-head h3 { margin:0;font-size:1rem;font-family:'Outfit',sans-serif; }
    .products-panel-head p { margin:.2rem 0 0;color:var(--text-muted);font-size:.82rem; }
    .products-table-wrap { overflow:auto; }
    .products-table { width:100%;border-collapse:collapse;min-width:980px; }
    .products-table th {
        text-align:left;
        padding:.85rem 1rem;
        color:#475569;
        background:#fff;
        border-bottom:1px solid var(--border);
        font-size:.72rem;
        text-transform:uppercase;
        letter-spacing:.04em;
    }
    .products-table td {
        padding:.95rem 1rem;
        border-bottom:1px solid #eef2f7;
        vertical-align:middle;
        font-size:.9rem;
    }
    .product-cell { display:flex;align-items:center;gap:.85rem;min-width:320px; }
    .product-thumb {
        width:64px;height:64px;border-radius:12px;object-fit:cover;
        background:#f8fafc;border:1px solid var(--border);flex:0 0 auto;
    }
    .product-title { color:var(--text-main);font-weight:850;font-size:.98rem;line-height:1.25;margin-bottom:.25rem; }
    .product-sub { display:flex;gap:.45rem;flex-wrap:wrap;color:var(--text-muted);font-size:.76rem; }
    .product-stock strong { font-size:1rem; }
    .stock-low { color:#b45309; }
    .stock-out { color:#dc2626; }
    .ml-status { display:grid;gap:.25rem; }
    .ml-detail { color:var(--text-muted);font-size:.74rem;max-width:220px;line-height:1.3; }
    .price-main { color:var(--primary);font-weight:900;font-size:1.05rem; }
    .row-actions { display:flex;align-items:center;justify-content:flex-end;gap:.45rem; }
    .icon-btn {
        width:38px;height:38px;border-radius:10px;border:1px solid var(--border);background:#fff;color:#334155;
        display:inline-flex;align-items:center;justify-content:center;cursor:pointer;text-decoration:none;transition:.18s;
    }
    .icon-btn:hover { background:var(--primary);border-color:var(--primary);color:#fff; }
    .icon-btn.ml:hover { background:#fff159;border-color:#fff159;color:#2d3277; }
    .icon-btn.danger:hover { background:var(--danger);border-color:var(--danger);color:#fff; }
    .empty-products {
        padding:3rem 1rem;
        text-align:center;
        color:var(--text-muted);
    }
    .empty-products i { width:46px;height:46px;opacity:.35;margin-bottom:.8rem; }
    @media(max-width:1180px){
        .catalog-toolbar { grid-template-columns:1fr; }
        .catalog-actions { justify-content:flex-start; }
        .catalog-metrics { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media(max-width:760px){
        .catalog-filter { grid-template-columns:1fr; }
        .catalog-actions .btn { width:100%;justify-content:center; }
        .catalog-metrics { grid-template-columns:1fr; }
        .products-panel-head { align-items:flex-start;flex-direction:column; }
    }
</style>

<?php $mlError = (string) ($_SESSION['ml_error'] ?? ($_GET['ml_error'] ?? '')); unset($_SESSION['ml_error']); ?>
<?php if ($mlError !== ''): ?>
<div class="alert-success system-flash" style="background:#fef2f2;border-color:#fecaca;color:#991b1b;">
    <i data-lucide="alert-triangle" style="width:18px;"></i>
    Mercado Livre: <?= htmlspecialchars($mlError) ?>
</div>
<?php endif; ?>

<div class="catalog-toolbar">
    <form action="<?= route_url() ?>" method="GET" class="catalog-filter">
        <input type="hidden" name="url" value="produtos">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Buscar por nome ou SKU">
        <select name="categoria" class="form-control">
            <option value="">Todas as categorias</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Buscar</button>
        <?php if ($search !== '' || $categoria !== ''): ?>
            <a href="<?= route_url('produtos') ?>" class="btn btn-secondary" style="text-decoration:none;"><i data-lucide="x"></i> Limpar</a>
        <?php endif; ?>
    </form>
    <div class="catalog-actions">
        <a href="<?= route_url('vitrine') ?>" target="_blank" class="btn btn-secondary"><i data-lucide="external-link"></i> Ver vitrine</a>
        <a href="<?= route_url('produtos/print', ['search' => $search, 'categoria' => $categoria]) ?>" target="_blank" class="btn btn-secondary"><i data-lucide="printer"></i> Imprimir catalogo</a>
        <a href="<?= route_url('produtos/create') ?>" class="btn btn-primary"><i data-lucide="plus-circle"></i> Novo produto</a>
    </div>
</div>

<div class="catalog-metrics">
    <div class="catalog-metric">
        <div class="metric-icon"><i data-lucide="shopping-bag"></i></div>
        <div><span>Produtos cadastrados</span><strong><?= $total ?></strong></div>
    </div>
    <div class="catalog-metric">
        <div class="metric-icon green"><i data-lucide="circle-dollar-sign"></i></div>
        <div><span>Venda potencial</span><strong>R$ <?= number_format($totalValor, 2, ',', '.') ?></strong></div>
    </div>
    <div class="catalog-metric">
        <div class="metric-icon yellow"><i data-lucide="megaphone"></i></div>
        <div><span>Publicados na pagina</span><strong><?= $total ?></strong></div>
    </div>
    <div class="catalog-metric">
        <div class="metric-icon red"><i data-lucide="triangle-alert"></i></div>
        <div><span>Atencao na pagina</span><strong><?= $lowStockCount + $errorCount ?></strong></div>
    </div>
</div>

<div class="products-panel">
    <div class="products-panel-head">
        <div>
            <h3>Produtos da vitrine</h3>
            <p><?= (int) ($pagination['total'] ?? count($items)) ?> produto(s) encontrado(s)</p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <span class="badge badge-green"><?= $publishedCount ?> Mercado Livre</span>
            <span class="badge badge-red"><?= $errorCount ?> com erro ML</span>
            <span class="badge badge-yellow"><?= $lowStockCount ?> estoque baixo</span>
        </div>
    </div>

    <div class="products-table-wrap">
        <table class="products-table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Estoque</th>
                    <th>Mercado Livre</th>
                    <th>Preco</th>
                    <th style="text-align:right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-products">
                                <i data-lucide="package-search"></i>
                                <div style="font-weight:800;color:var(--text-main);margin-bottom:.25rem;">Nenhum produto encontrado</div>
                                <div>Cadastre um produto novo ou ajuste os filtros de busca.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: foreach ($items as $p): ?>
                    <?php
                        $stock = (int) ($p['quantidade'] ?? 0);
                        $minStock = (int) ($p['estoque_minimo'] ?? 0);
                        $stockClass = $stock <= 0 ? 'stock-out' : ($stock <= $minStock ? 'stock-low' : '');
                        $hasMl = !empty($p['mercado_livre_item_id']);
                        $hasMlError = !$hasMl && !empty($p['mercado_livre_last_error']);
                    ?>
                    <tr>
                        <td>
                            <div class="product-cell">
                                <?php if (!empty($p['imagem_url'])): ?>
                                    <img src="<?= htmlspecialchars($p['imagem_url']) ?>" class="product-thumb" alt="<?= htmlspecialchars($p['nome']) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';lucide.createIcons();">
                                    <div class="product-thumb" style="display:none;align-items:center;justify-content:center;color:#94a3b8;"><i data-lucide="image-off" style="width:22px;"></i></div>
                                <?php else: ?>
                                    <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;color:#94a3b8;"><i data-lucide="image-off" style="width:22px;"></i></div>
                                <?php endif; ?>
                                <div>
                                    <div class="product-title"><?= htmlspecialchars($p['nome']) ?></div>
                                    <div class="product-sub">
                                        <span>SKU: <?= htmlspecialchars($p['codigo_interno'] ?: 'sem SKU') ?></span>
                                        <?php if (!empty($p['fornecedor'])): ?><span>Fornecedor: <?= htmlspecialchars($p['fornecedor']) ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-purple"><?= htmlspecialchars($p['categoria'] ?: 'Sem categoria') ?></span></td>
                        <td class="product-stock">
                            <strong class="<?= $stockClass ?>"><?= $stock ?></strong> <small>un.</small>
                            <?php if ($stock <= $minStock): ?>
                                <div class="ml-detail">mínimo <?= $minStock ?> un.</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="ml-status">
                                <?php if ($hasMl): ?>
                                    <a href="<?= htmlspecialchars($p['mercado_livre_permalink'] ?? '#') ?>" target="_blank" rel="noopener" class="badge badge-green" style="text-decoration:none;width:max-content;">
                                        <?= htmlspecialchars($p['mercado_livre_status'] ?: 'publicado') ?>
                                    </a>
                                    <span class="ml-detail"><?= htmlspecialchars($p['mercado_livre_item_id']) ?></span>
                                <?php elseif ($hasMlError): ?>
                                    <span class="badge badge-red" style="width:max-content;">erro</span>
                                    <span class="ml-detail"><?= htmlspecialchars($p['mercado_livre_last_error']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-gray" style="width:max-content;">não publicado</span>
                                    <span class="ml-detail">Pronto para configurar e publicar.</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><span class="price-main">R$ <?= number_format((float) $p['preco_venda'], 2, ',', '.') ?></span></td>
                        <td>
                            <div class="row-actions">
                                <a href="<?= route_url('vitrine/produto/' . (int) $p['id']) ?>" target="_blank" class="icon-btn" title="Ver na vitrine" aria-label="Ver na vitrine"><i data-lucide="eye" style="width:16px;"></i></a>
                                <form action="<?= route_url('mercadolivre/publish/' . (int) $p['id']) ?>" method="POST" data-confirm="<?= $hasMl ? 'Sincronizar preço e estoque com o Mercado Livre?' : 'Publicar este produto no Mercado Livre?' ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="icon-btn ml" title="<?= $hasMl ? 'Sincronizar Mercado Livre' : 'Publicar no Mercado Livre' ?>" aria-label="<?= $hasMl ? 'Sincronizar Mercado Livre' : 'Publicar no Mercado Livre' ?>"><i data-lucide="<?= $hasMl ? 'refresh-cw' : 'send' ?>" style="width:16px;"></i></button>
                                </form>
                                <a href="<?= route_url('produtos/edit/' . (int) $p['id']) ?>" class="icon-btn" title="Editar produto" aria-label="Editar produto"><i data-lucide="edit-2" style="width:16px;"></i></a>
                                <form action="<?= route_url('produtos/delete') ?>" method="POST" data-confirm="Remover este produto da loja?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="icon-btn danger" title="Excluir produto" aria-label="Excluir produto"><i data-lucide="trash-2" style="width:16px;"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <div style="padding:0 1rem 1rem;">
        <?= render_pagination($pagination ?? [], 'produtos', ['search' => $search, 'categoria' => $categoria]) ?>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
