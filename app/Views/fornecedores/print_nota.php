<?php
$companyName = trim((string) ($company['name'] ?? '')) ?: 'Conectados';
$noteNumber = trim((string) ($nota['numero'] ?? '')) ?: '#' . (int) ($nota['id'] ?? 0);
$supplierName = trim((string) ($nota['fornecedor_nome'] ?? '')) ?: 'Nao informado';
$paymentMethod = trim((string) ($nota['forma_pagamento'] ?? '')) ?: 'Nao informado';
$status = trim((string) ($nota['status'] ?? ''));
$issuedAt = !empty($nota['data_emissao']) ? strtotime((string) $nota['data_emissao']) : time();
$dueAt = !empty($nota['data_vencimento']) ? strtotime((string) $nota['data_vencimento']) : null;
$downloadedAt = !empty($nota['baixado_at']) ? strtotime((string) $nota['baixado_at']) : null;
$notes = trim((string) ($nota['observacoes'] ?? ''));
$items = is_array($nota['itens'] ?? null) ? $nota['itens'] : [];
$attachmentName = trim((string) ($nota['anexo_original'] ?? ''));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nota de Compra <?= htmlspecialchars($noteNumber) ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #fff;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.35;
        }
        .sheet {
            width: 190mm;
            min-height: 277mm;
            margin: 0 auto;
            padding: 12mm;
        }
        .header {
            display: flex;
            justify-content: space-between;
            gap: 10mm;
            border-bottom: 2px solid #111;
            padding-bottom: 6mm;
            margin-bottom: 7mm;
        }
        .brand img {
            width: 46mm;
            height: auto;
            display: block;
            margin-bottom: 2mm;
        }
        .brand-name {
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .muted { color: #444; font-size: 11px; }
        .title {
            text-align: right;
            text-transform: uppercase;
            font-weight: 900;
            font-size: 20px;
        }
        .badge {
            display: inline-block;
            border: 1px solid #111;
            padding: 2mm 4mm;
            margin-top: 2mm;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 3mm 8mm;
            margin-bottom: 7mm;
        }
        .field {
            border-bottom: 1px solid #bbb;
            padding-bottom: 2mm;
            min-height: 10mm;
        }
        .field strong {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 1mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3mm;
        }
        th {
            text-align: left;
            text-transform: uppercase;
            font-size: 10px;
            border-bottom: 1px solid #111;
            padding: 2.5mm 1.5mm;
        }
        td {
            border-bottom: 1px solid #ddd;
            padding: 2.5mm 1.5mm;
            vertical-align: top;
        }
        .right { text-align: right; }
        .total-box {
            margin-left: auto;
            margin-top: 5mm;
            width: 70mm;
            border: 2px solid #111;
            padding: 4mm;
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 900;
        }
        .section-title {
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            margin-top: 7mm;
            border-bottom: 1px solid #111;
            padding-bottom: 1.5mm;
        }
        .text {
            white-space: pre-wrap;
            word-break: break-word;
            margin-top: 2mm;
        }
        .footer {
            margin-top: 12mm;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12mm;
            text-align: center;
            font-size: 11px;
        }
        .signature {
            border-top: 1px solid #111;
            padding-top: 2mm;
        }
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            body { margin: 0; }
            .sheet { width: 190mm; margin: 0 auto; }
        }
    </style>
</head>
<body>
    <main class="sheet">
        <header class="header">
            <div class="brand">
                <?php if (!empty($company['logo_print'])): ?>
                    <img src="<?= htmlspecialchars($company['logo_print']) ?>" alt="<?= htmlspecialchars($companyName) ?>">
                <?php endif; ?>
                <div class="brand-name"><?= htmlspecialchars($companyName) ?></div>
                <?php if (!empty($company['phone'])): ?><div class="muted"><?= htmlspecialchars($company['phone']) ?></div><?php endif; ?>
                <?php if (!empty($company['address'])): ?><div class="muted"><?= htmlspecialchars($company['address']) ?></div><?php endif; ?>
                <?php if (!empty($company['website'])): ?><div class="muted"><?= htmlspecialchars($company['website']) ?></div><?php endif; ?>
            </div>
            <div>
                <div class="title">Nota de Compra</div>
                <div class="right"><strong><?= htmlspecialchars($noteNumber) ?></strong></div>
                <?php if ($status !== ''): ?><div class="right"><span class="badge"><?= htmlspecialchars($status) ?></span></div><?php endif; ?>
            </div>
        </header>

        <section class="grid">
            <div class="field"><strong>Fornecedor</strong><?= htmlspecialchars($supplierName) ?></div>
            <div class="field"><strong>Pagamento</strong><?= htmlspecialchars($paymentMethod) ?></div>
            <div class="field"><strong>Emissao</strong><?= date('d/m/Y', $issuedAt) ?></div>
            <div class="field"><strong>Vencimento</strong><?= $dueAt ? date('d/m/Y', $dueAt) : 'Nao informado' ?></div>
            <div class="field"><strong>Baixa</strong><?= $downloadedAt ? date('d/m/Y H:i', $downloadedAt) : 'Nao baixada' ?></div>
            <div class="field"><strong>Registro interno</strong>#<?= (int) ($nota['id'] ?? 0) ?></div>
            <div class="field"><strong>Anexo</strong><?= $attachmentName !== '' ? htmlspecialchars($attachmentName) : 'Nao informado' ?></div>
        </section>

        <section>
            <div class="section-title">Itens</div>
            <table>
                <thead>
                    <tr>
                        <th>Descricao</th>
                        <th>Tipo</th>
                        <th class="right">Qtd</th>
                        <th class="right">Valor un.</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="5">Nenhum item encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <?php
                            $description = trim((string) ($item['produto_nome'] ?? '')) ?: trim((string) ($item['descricao'] ?? 'Item'));
                            $type = ($item['tipo'] ?? '') === 'produto' ? 'Produto da loja' : 'Peca tecnica';
                            ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($description) ?>
                                    <?php if (!empty($item['codigo_interno'])): ?><br><span class="muted">Cod. <?= htmlspecialchars($item['codigo_interno']) ?></span><?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($type) ?></td>
                                <td class="right"><?= (int) ($item['quantidade'] ?? 0) ?></td>
                                <td class="right">R$ <?= money_br($item['valor_unitario'] ?? 0) ?></td>
                                <td class="right">R$ <?= money_br($item['total'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="total-box">
                <span>Total</span>
                <span>R$ <?= money_br($nota['valor_total'] ?? 0) ?></span>
            </div>
        </section>

        <?php if ($notes !== ''): ?>
            <section>
                <div class="section-title">Observacoes</div>
                <div class="text"><?= htmlspecialchars($notes) ?></div>
            </section>
        <?php endif; ?>

        <footer class="footer">
            <div class="signature">Conferencia</div>
            <div class="signature">Responsavel</div>
        </footer>
    </main>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
