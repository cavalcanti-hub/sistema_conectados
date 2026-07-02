<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($documentTitle ?? 'Impressao 80mm') ?></title>
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
            max-width: 46mm;
            max-height: 16mm;
            object-fit: contain;
            filter: grayscale(1) contrast(1.3);
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
        .row {
            display: flex;
            justify-content: space-between;
            gap: 3mm;
            margin: 1mm 0;
            align-items: flex-start;
        }
        .row span:first-child {
            flex: 1 1 auto;
        }
        .row span:last-child {
            flex: 0 0 auto;
            text-align: right;
            font-weight: 700;
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
        .text {
            white-space: pre-wrap;
            word-break: break-word;
        }
        .item {
            margin: 1mm 0;
            padding-left: 3mm;
            position: relative;
        }
        .item::before {
            content: "-";
            position: absolute;
            left: 0;
        }
        .total {
            border-top: 1px solid #000;
            margin-top: 1.5mm;
            padding-top: 1.5mm;
            font-size: 14px;
            font-weight: 900;
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
                <img src="<?= htmlspecialchars($company['logo_print']) ?>" alt="Logo">
            <?php endif; ?>
            <div class="brand-name"><?= htmlspecialchars($company['name'] ?? 'Conectados') ?></div>
            <?php if (!empty($company['phone'])): ?><div class="muted"><?= htmlspecialchars($company['phone']) ?></div><?php endif; ?>
            <?php if (!empty($company['address'])): ?><div class="muted"><?= htmlspecialchars($company['address']) ?></div><?php endif; ?>
        </header>

        <div class="sep"></div>

        <?php if (($printContext ?? '') === 'agenda'): ?>
            <div class="title">Agenda Operacional</div>
            <div class="row"><span>Emitido</span><span><?= date('d/m/Y H:i') ?></span></div>
            <div class="row"><span>Total de OS</span><span><?= count($printData['items'] ?? []) ?></span></div>
            <div class="sep"></div>
            <?php if (empty($printData['items'])): ?>
                <div class="field center">Nenhuma OS em andamento.</div>
            <?php else: ?>
                <?php foreach (($printData['items'] ?? []) as $item): ?>
                    <section class="section">
                        <div class="row"><span>#<?= htmlspecialchars($item['numero_os'] ?? '') ?></span><span><?= htmlspecialchars($item['status'] ?? '') ?></span></div>
                        <div class="field"><?= htmlspecialchars($item['cliente_nome'] ?? '') ?></div>
                        <div class="field"><?= htmlspecialchars(trim(($item['aparelho_marca'] ?? '') . ' ' . ($item['aparelho_modelo'] ?? ''))) ?></div>
                        <div class="row"><span>Entrada</span><span><?= !empty($item['created_at']) ? date('d/m H:i', strtotime($item['created_at'])) : '-' ?></span></div>
                        <div class="row"><span>Total</span><span>R$ <?= money_br($item['valor_total'] ?? 0) ?></span></div>
                    </section>
                    <div class="sep"></div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <div class="title">Ordem de Servico</div>
            <div class="center">
                <strong>#<?= htmlspecialchars($printData['number'] ?? '') ?></strong><br>
                <span class="badge"><?= htmlspecialchars($printData['status'] ?? 'Aberta') ?></span>
            </div>

            <div class="sep"></div>

            <section class="section">
                <div class="row"><span>Entrada</span><span><?= !empty($printData['created_at']) ? date('d/m/Y H:i', strtotime($printData['created_at'])) : date('d/m/Y H:i') ?></span></div>
                <div class="row"><span>Emissao</span><span><?= date('d/m/Y H:i') ?></span></div>
            </section>

            <section class="section">
                <div class="section-title">Cliente</div>
                <div class="field"><strong>Nome</strong><?= htmlspecialchars($printData['customer_name'] ?? '') ?></div>
                <?php if (!empty($printData['customer_phone'])): ?><div class="field"><strong>Contato</strong><?= htmlspecialchars($printData['customer_phone']) ?></div><?php endif; ?>
                <?php if (!empty($printData['address_short'])): ?><div class="field"><strong>End.</strong><?= htmlspecialchars($printData['address_short']) ?></div><?php endif; ?>
            </section>

            <section class="section">
                <div class="section-title">Aparelho</div>
                <div class="field"><strong>Equip.</strong><?= htmlspecialchars($printData['equipment'] ?? '') ?></div>
                <?php if (!empty($os['imei'])): ?><div class="field"><strong>IMEI</strong><?= htmlspecialchars($os['imei']) ?></div><?php endif; ?>
                <?php if (!empty($os['cor'])): ?><div class="field"><strong>Cor</strong><?= htmlspecialchars($os['cor']) ?></div><?php endif; ?>
            </section>

            <section class="section">
                <div class="section-title">Servico / Problema</div>
                <?php foreach (($printData['services'] ?? []) as $service): ?>
                    <div class="item"><?= htmlspecialchars($service) ?></div>
                <?php endforeach; ?>
            </section>

            <section class="section">
                <div class="section-title">Valores</div>
                <?php foreach (($printData['values'] ?? []) as $value): ?>
                    <div class="row">
                        <span><?= htmlspecialchars($value['label'] ?? '') ?></span>
                        <span>R$ <?= money_br($value['amount'] ?? 0) ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="row total">
                    <span>Total</span>
                    <span>R$ <?= money_br($printData['total'] ?? 0) ?></span>
                </div>
            </section>

            <section class="section">
                <div class="section-title">Pagamento</div>
                <div class="row"><span>Forma</span><span><?= htmlspecialchars(($printData['payment_method'] ?? '') ?: 'Nao informada') ?></span></div>
                <?php if ((float) ($printData['paid_total'] ?? 0) > 0): ?>
                    <div class="row"><span>Pago</span><span>R$ <?= money_br($printData['paid_total'] ?? 0) ?></span></div>
                    <div class="row"><span>Restante</span><span>R$ <?= money_br($printData['remaining_total'] ?? 0) ?></span></div>
                <?php endif; ?>
                <?php if (!empty($printData['payments'])): ?>
                    <div class="sep"></div>
                    <?php foreach ($printData['payments'] as $payment): ?>
                        <div class="row">
                            <span><?= !empty($payment['data_pagamento']) ? date('d/m', strtotime($payment['data_pagamento'])) : '-' ?> <?= htmlspecialchars($payment['forma_pagamento'] ?? '') ?></span>
                            <span>R$ <?= money_br($payment['valor'] ?? 0) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <?php if (!empty($printData['notes'])): ?>
            <section class="section">
                <div class="section-title">Observacoes</div>
                <div class="text"><?= htmlspecialchars($printData['notes']) ?></div>
            </section>
            <?php endif; ?>

            <div class="sep"></div>
            <div class="muted center">Guarde este comprovante para acompanhamento da OS.</div>
            <div class="signature">Assinatura do cliente</div>
            <div class="center" style="margin-top:3mm;font-weight:800;"><?= htmlspecialchars($printData['final_message'] ?? 'Obrigado pela preferencia') ?></div>
        <?php endif; ?>

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
