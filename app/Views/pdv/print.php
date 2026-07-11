<?php
$companyName = trim((string) ($company['name'] ?? '')) ?: 'Conectados';
$saleNumber = trim((string) ($venda['numero_venda'] ?? ''));
$issuedAt = !empty($venda['created_at']) ? strtotime((string) $venda['created_at']) : time();
$customerName = trim((string) ($venda['cliente_nome'] ?? '')) ?: 'Consumidor Final';
$paymentMethod = trim((string) ($venda['forma_pagamento'] ?? '')) ?: 'Nao informado';
$operatorName = trim((string) ($venda['operador_nome'] ?? ''));
$osNumber = trim((string) ($venda['numero_os_vinculada'] ?? ''));
$notes = trim((string) ($venda['observacoes'] ?? ''));
$discount = (float) ($venda['desconto'] ?? 0);
$cardFee = (float) ($venda['taxa_cartao_valor'] ?? 0);
$qtyLabel = static function ($value): string {
    $qty = (float) $value;
    if (abs($qty - round($qty)) < 0.001) {
        return (string) (int) round($qty);
    }

    return rtrim(rtrim(number_format($qty, 3, ',', '.'), '0'), ',');
};
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Venda #<?= htmlspecialchars($saleNumber) ?></title>
    <style>
        * { box-sizing: border-box; }
        html,
        body {
            margin: 0;
            background: #fff;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.28;
            width: 80mm;
            max-width: 80mm;
            overflow-x: hidden;
        }
        .sheet {
            width: 74mm;
            max-width: 74mm;
            margin: 0 auto;
            padding: 3mm 2mm 2mm;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .brand {
            text-align: center;
            padding-bottom: 2mm;
        }
        .brand img {
            width: 42mm;
            height: auto;
            max-width: 42mm;
            max-height: none;
            object-fit: contain;
            display: block;
            margin: 0 auto 1mm;
        }
        .brand-name {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .muted {
            color: #111;
            font-size: 10px;
        }
        .sep {
            border-top: 1px dashed #000;
            margin: 2.2mm 0;
            height: 0;
        }
        .solid-sep {
            border-top: 1px solid #000;
            margin: 2.2mm 0;
            height: 0;
        }
        .title {
            text-align: center;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            margin: 1mm 0 1.5mm;
        }
        .badge {
            display: inline-block;
            border: 1px solid #000;
            padding: 1mm 2mm;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            margin-top: 1mm;
        }
        .section {
            margin: 2mm 0;
            break-inside: avoid;
        }
        .section-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: .8mm;
            margin-bottom: 1.4mm;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 3mm;
            margin: 1mm 0;
            align-items: flex-start;
        }
        .row span:first-child {
            flex: 1 1 auto;
            min-width: 0;
            word-break: break-word;
        }
        .row span:last-child {
            flex: 0 0 auto;
            text-align: right;
            font-weight: 700;
            white-space: nowrap;
        }
        .field {
            margin: .8mm 0;
            word-break: break-word;
        }
        .field strong {
            display: inline-block;
            min-width: 18mm;
            font-size: 10px;
            text-transform: uppercase;
        }
        .item {
            padding: 1.3mm 0;
            border-bottom: 1px dashed #777;
            break-inside: avoid;
        }
        .item:last-child {
            border-bottom: 0;
        }
        .item-name {
            font-weight: 800;
            word-break: break-word;
        }
        .item-meta {
            display: flex;
            justify-content: space-between;
            gap: 3mm;
            margin-top: .7mm;
            font-size: 10px;
        }
        .item-meta span:last-child {
            font-weight: 800;
            white-space: nowrap;
        }
        .total {
            border-top: 1px solid #000;
            margin-top: 1.5mm;
            padding-top: 1.5mm;
            font-size: 14px;
            font-weight: 900;
        }
        .grand-total {
            border: 1px solid #000;
            padding: 1.6mm 2mm;
            margin-top: 2mm;
            font-size: 15px;
            font-weight: 900;
        }
        .text {
            white-space: pre-wrap;
            word-break: break-word;
        }
        .signature {
            margin-top: 7mm;
            padding-top: 2mm;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 10px;
        }
        .cut-tail {
            display: block;
            height: 12mm;
            color: transparent;
            font-size: 1px;
            overflow: hidden;
        }
        @page {
            size: 80mm auto;
            margin: 0;
        }
        @media print {
            html,
            body {
                width: 80mm;
                max-width: 80mm;
                margin: 0;
                padding: 0;
            }
            .sheet {
                width: 74mm;
                max-width: 74mm;
                margin: 0 auto;
                padding: 3mm 2mm 2mm;
            }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <header class="brand">
            <?php if (!empty($company['logo_print'])): ?>
                <img src="<?= htmlspecialchars($company['logo_print']) ?>" alt="<?= htmlspecialchars($companyName) ?>">
            <?php endif; ?>
            <div class="brand-name"><?= htmlspecialchars($companyName) ?></div>
            <?php if (!empty($company['phone'])): ?><div class="muted"><?= htmlspecialchars($company['phone']) ?></div><?php endif; ?>
            <?php if (!empty($company['address'])): ?><div class="muted"><?= htmlspecialchars($company['address']) ?></div><?php endif; ?>
        </header>

        <div class="sep"></div>

        <div class="title">Recibo de Venda</div>
        <div class="center">
            <strong>#<?= htmlspecialchars($saleNumber) ?></strong><br>
            <span class="badge">Comprovante PDV</span>
        </div>

        <div class="sep"></div>

        <section class="section">
            <div class="row"><span>Emissao</span><span><?= date('d/m/Y H:i', $issuedAt) ?></span></div>
            <div class="row"><span>Pagamento</span><span><?= htmlspecialchars($paymentMethod) ?></span></div>
            <?php if ($operatorName !== ''): ?>
                <div class="row"><span>Operador</span><span><?= htmlspecialchars($operatorName) ?></span></div>
            <?php endif; ?>
        </section>

        <section class="section">
            <div class="section-title">Cliente</div>
            <div class="field"><strong>Nome</strong><?= htmlspecialchars($customerName) ?></div>
            <?php if ($osNumber !== ''): ?>
                <div class="field"><strong>OS</strong>#<?= htmlspecialchars($osNumber) ?></div>
            <?php endif; ?>
        </section>

        <section class="section">
            <div class="section-title">Itens da Venda</div>
            <?php if (empty($itens)): ?>
                <div class="field center">Nenhum item encontrado.</div>
            <?php else: ?>
                <?php foreach ($itens as $item): ?>
                    <?php
                    $qty = $qtyLabel($item['quantidade'] ?? 1);
                    $description = trim((string) ($item['descricao'] ?? $item['produto_nome'] ?? 'Item'));
                    $unitPrice = (float) ($item['preco_unitario'] ?? 0);
                    $lineTotal = (float) ($item['total'] ?? ($unitPrice * (float) ($item['quantidade'] ?? 1)));
                    ?>
                    <div class="item">
                        <div class="item-name"><?= htmlspecialchars($description) ?></div>
                        <div class="item-meta">
                            <span><?= htmlspecialchars($qty) ?> x R$ <?= money_br($unitPrice) ?></span>
                            <span>R$ <?= money_br($lineTotal) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="section">
            <div class="section-title">Resumo</div>
            <div class="row"><span>Subtotal</span><span>R$ <?= money_br($venda['subtotal'] ?? 0) ?></span></div>
            <?php if ($discount > 0): ?>
                <div class="row"><span>Desconto</span><span>- R$ <?= money_br($discount) ?></span></div>
            <?php endif; ?>
            <?php if ($cardFee > 0): ?>
                <div class="row"><span>Taxa maquininha (<?= money_br($venda['taxa_cartao_percentual'] ?? 0) ?>%)</span><span>R$ <?= money_br($cardFee) ?></span></div>
                <div class="row"><span>Liquido da venda</span><span>R$ <?= money_br($venda['total_liquido'] ?? 0) ?></span></div>
            <?php endif; ?>
            <div class="row grand-total">
                <span>Total pago</span>
                <span>R$ <?= money_br($venda['total'] ?? 0) ?></span>
            </div>
        </section>

        <?php if ($notes !== ''): ?>
        <section class="section">
            <div class="section-title">Observacoes</div>
            <div class="text"><?= htmlspecialchars($notes) ?></div>
        </section>
        <?php endif; ?>

        <div class="solid-sep"></div>
        <div class="muted center">Documento nao fiscal. Guarde este comprovante para trocas, garantia ou conferencia.</div>
        <div class="signature">Assinatura / Conferencia</div>
        <div class="center" style="margin-top:3mm;font-weight:800;">Obrigado pela preferencia</div>
        <?php if (!empty($company['website'])): ?><div class="muted center"><?= htmlspecialchars($company['website']) ?></div><?php endif; ?>
        <div class="cut-tail">&nbsp;</div>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
        window.onafterprint = function () {
            window.close();
        };
    </script>
</body>
</html>
