<?php
$items = is_array($items ?? null) ? $items : [];
$grouped = is_array($grouped ?? null) ? $grouped : [];
$search = (string) ($search ?? '');
$categoria = (string) ($categoria ?? '');
$totalValor = (float) ($totalValor ?? 0);
$publishedMl = 0;
$outOfStock = 0;
$lowStock = 0;
foreach ($items as $item) {
    if (!empty($item['mercado_livre_item_id'])) {
        $publishedMl++;
    }
    $stock = (int) ($item['quantidade'] ?? 0);
    $minStock = (int) ($item['estoque_minimo'] ?? 0);
    if ($stock <= 0) {
        $outOfStock++;
    } elseif ($stock <= $minStock) {
        $lowStock++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogo de Produtos</title>
    <style>
        @page { size: A4; margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;color:#111827;background:#fff;font-size:11px; }
        .document { width:100%;max-width:195mm;margin:0 auto; }
        .print-actions { display:flex;justify-content:flex-end;margin:0 0 12px; }
        button { border:0;border-radius:6px;padding:10px 14px;font-weight:700;cursor:pointer;color:#fff;background:#00349a; }
        .letterhead { display:grid;grid-template-columns:130px 1fr 170px;align-items:center;gap:16px;border-bottom:2px solid #00349a;padding-bottom:12px;margin-bottom:14px; }
        .logo { width:124px;max-height:64px;object-fit:contain;display:block; }
        .company { text-align:center; }
        .company strong { display:block;font-size:15px;color:#00349a;margin-bottom:4px; }
        .company span { display:block;color:#475569;line-height:1.35; }
        .doc-meta { text-align:right;color:#475569;line-height:1.45;font-size:10.5px; }
        h1 { margin:0 0 10px;font-size:19px;color:#0f172a;text-align:center;text-transform:uppercase;letter-spacing:.04em; }
        .filters { margin:0 0 12px;color:#475569;text-align:center;font-size:10.5px; }
        .summary { display:grid;grid-template-columns:repeat(5,1fr);gap:6px;margin-bottom:12px; }
        .summary-card { border:1px solid #cbd5e1;border-radius:8px;padding:7px;background:#f8fafc; }
        .summary-card span { display:block;color:#64748b;font-size:9px;text-transform:uppercase;font-weight:700;letter-spacing:.03em; }
        .summary-card strong { display:block;color:#0f172a;font-size:13px;margin-top:3px; }
        .category-block { margin-top:14px; break-inside:avoid; }
        .category-title { display:flex;justify-content:space-between;align-items:center;gap:12px;background:#0f172a;color:#fff;padding:7px 9px;border-radius:7px 7px 0 0;font-weight:800;text-transform:uppercase;letter-spacing:.03em; }
        .category-title small { font-weight:700;color:#dbeafe;text-transform:none;letter-spacing:0; }
        table { width:100%;border-collapse:collapse;font-size:10.4px; }
        th, td { border:1px solid #cbd5e1;padding:6px 6px;vertical-align:top; }
        th { background:#f1f5f9;color:#0f172a;font-weight:800;text-align:left;text-transform:uppercase;font-size:9.2px; }
        .right { text-align:right; }
        .center { text-align:center; }
        .product-name { font-weight:800;color:#0f172a;line-height:1.25; }
        .muted { color:#64748b;font-size:9.5px;line-height:1.25;margin-top:2px; }
        .status { display:inline-block;border-radius:999px;padding:2px 7px;font-weight:800;font-size:9px;white-space:nowrap; }
        .status-ok { background:#dcfce7;color:#166534; }
        .status-warn { background:#fef3c7;color:#92400e; }
        .status-bad { background:#fee2e2;color:#991b1b; }
        .status-gray { background:#e2e8f0;color:#334155; }
        .empty { padding:22px;border:1px solid #cbd5e1;text-align:center;color:#475569; }
        .footer-note { margin-top:14px;color:#64748b;font-size:9.5px;text-align:center; }
        @media print {
            .print-actions { display:none; }
            .document { max-width:none; }
            tr { break-inside:avoid; }
            .category-block { break-inside:auto; }
        }
    </style>
</head>
<body>
    <main class="document">
        <div class="print-actions">
            <button type="button" onclick="window.print()">Imprimir ou salvar PDF</button>
        </div>

        <header class="letterhead">
            <img class="logo" src="<?= app_url('assets/img/logo-print.png') ?>" alt="Conectados">
            <div class="company">
                <strong>Conectados Assistencia Tecnica</strong>
                <span>Catalogo interno de produtos da loja</span>
            </div>
            <div class="doc-meta">
                Emitido em<br>
                <strong><?= date('d/m/Y H:i') ?></strong><br>
                Categorias: <strong><?= count($grouped) ?></strong>
            </div>
        </header>

        <h1>Catalogo de Produtos</h1>
        <?php if ($search !== '' || $categoria !== ''): ?>
            <div class="filters">
                Filtros aplicados:
                <?= $search !== '' ? 'busca "' . htmlspecialchars($search) . '"' : '' ?>
                <?= $categoria !== '' ? ($search !== '' ? ' | ' : '') . 'categoria "' . htmlspecialchars($categoria) . '"' : '' ?>
            </div>
        <?php endif; ?>

        <section class="summary">
            <div class="summary-card"><span>Total</span><strong><?= count($items) ?></strong></div>
            <div class="summary-card"><span>Categorias</span><strong><?= count($grouped) ?></strong></div>
            <div class="summary-card"><span>Mercado Livre</span><strong><?= $publishedMl ?></strong></div>
            <div class="summary-card"><span>Sem estoque</span><strong><?= $outOfStock ?></strong></div>
            <div class="summary-card"><span>Valor estoque</span><strong>R$ <?= number_format($totalValor, 2, ',', '.') ?></strong></div>
        </section>

        <?php if (empty($items)): ?>
            <div class="empty">Nenhum produto encontrado para impressao.</div>
        <?php else: ?>
            <?php foreach ($grouped as $categoryName => $categoryItems): ?>
                <section class="category-block">
                    <div class="category-title">
                        <span><?= htmlspecialchars($categoryName) ?></span>
                        <small><?= count($categoryItems) ?> produto(s)</small>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width:42%;">Produto</th>
                                <th style="width:14%;">SKU</th>
                                <th class="center" style="width:12%;">Estoque</th>
                                <th class="right" style="width:14%;">Preco</th>
                                <th style="width:18%;">Mercado Livre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categoryItems as $product): ?>
                                <?php
                                    $stock = (int) ($product['quantidade'] ?? 0);
                                    $minStock = (int) ($product['estoque_minimo'] ?? 0);
                                    $stockStatus = $stock <= 0 ? 'status-bad' : ($stock <= $minStock ? 'status-warn' : 'status-ok');
                                    $mlStatus = !empty($product['mercado_livre_item_id'])
                                        ? 'Publicado'
                                        : (!empty($product['mercado_livre_last_error']) ? 'Erro' : 'Nao publicado');
                                    $mlClass = !empty($product['mercado_livre_item_id'])
                                        ? 'status-ok'
                                        : (!empty($product['mercado_livre_last_error']) ? 'status-bad' : 'status-gray');
                                ?>
                                <tr>
                                    <td>
                                        <div class="product-name"><?= htmlspecialchars($product['nome'] ?? 'Produto sem nome') ?></div>
                                        <?php if (!empty($product['marca_compativel']) || !empty($product['modelo_compativel'])): ?>
                                            <div class="muted">
                                                <?= htmlspecialchars(trim(($product['marca_compativel'] ?? '') . ' ' . ($product['modelo_compativel'] ?? ''))) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['codigo_interno'] ?: '-') ?></td>
                                    <td class="center">
                                        <span class="status <?= $stockStatus ?>"><?= $stock ?> un.</span>
                                        <?php if ($stock <= $minStock): ?><div class="muted">min. <?= $minStock ?></div><?php endif; ?>
                                    </td>
                                    <td class="right">R$ <?= number_format((float) ($product['preco_venda'] ?? 0), 2, ',', '.') ?></td>
                                    <td>
                                        <span class="status <?= $mlClass ?>"><?= $mlStatus ?></span>
                                        <?php if (!empty($product['mercado_livre_item_id'])): ?>
                                            <div class="muted"><?= htmlspecialchars($product['mercado_livre_item_id']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="footer-note">Relatorio gerado pelo sistema Conectados para conferencia interna do catalogo.</div>
    </main>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
</body>
</html>
