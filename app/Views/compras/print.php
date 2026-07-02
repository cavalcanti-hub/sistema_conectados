<?php
$items = $items ?? [];
$tipos = $tipos ?? [];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitacao de Compra</title>
    <style>
        @page { size: A4; margin: 16mm 14mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #fff;
            font-size: 12px;
        }
        .document {
            width: 100%;
            max-width: 190mm;
            margin: 0 auto;
        }
        .print-actions {
            display: flex;
            justify-content: flex-end;
            margin: 0 0 14px;
        }
        button {
            border: 0;
            border-radius: 6px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
            background: #00349a;
        }
        .letterhead {
            display: grid;
            grid-template-columns: 140px 1fr 170px;
            align-items: center;
            gap: 18px;
            border-bottom: 2px solid #00349a;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .logo {
            width: 132px;
            max-height: 70px;
            object-fit: contain;
            display: block;
        }
        .company { text-align: center; }
        .company strong {
            display: block;
            font-size: 15px;
            color: #00349a;
            margin-bottom: 4px;
        }
        .company span {
            display: block;
            color: #475569;
            line-height: 1.35;
        }
        .doc-meta {
            text-align: right;
            color: #475569;
            line-height: 1.45;
            font-size: 11px;
        }
        h1 {
            margin: 0 0 14px;
            font-size: 20px;
            color: #0f172a;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .section-title {
            margin: 0 0 8px;
            font-size: 13px;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            vertical-align: top;
        }
        .items-table th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
            font-size: 10.5px;
        }
        .center { text-align: center; }
        .note {
            color: #475569;
            font-size: 10.5px;
            line-height: 1.35;
            margin-top: 4px;
        }
        .empty {
            padding: 22px;
            border: 1px solid #cbd5e1;
            text-align: center;
            color: #475569;
        }
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 26mm;
            margin-top: 32px;
        }
        .signature {
            border-top: 1px solid #334155;
            padding-top: 7px;
            text-align: center;
            color: #475569;
            font-size: 11px;
        }
        @media print {
            .print-actions { display: none; }
            .document { max-width: none; }
            tr { break-inside: avoid; }
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
                <strong>Conectados Assistência Técnica</strong>
                <span>Solicitação de compra de peças e produtos</span>
            </div>
            <div class="doc-meta">
                Emitido em<br>
                <strong><?= date('d/m/Y H:i') ?></strong><br>
                Total de itens: <strong><?= count($items) ?></strong>
            </div>
        </header>

        <h1>Solicitação de Compra</h1>

        <div class="section-title">Produtos e peças solicitados</div>
        <?php if (empty($items)): ?>
            <div class="empty">Nenhum produto ou peça solicitado.</div>
        <?php else: ?>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Produto / peça solicitada</th>
                        <th>Tipo</th>
                        <th class="center">Qtd</th>
                        <th>Fornecedor</th>
                        <th>Prioridade</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Solicitante</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($item['item_nome']) ?></strong>
                            <?php if (!empty($item['observacoes'])): ?>
                                <div class="note"><?= nl2br(htmlspecialchars($item['observacoes'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($tipos[$item['tipo']] ?? $item['tipo']) ?></td>
                        <td class="center"><?= (int) $item['quantidade'] ?></td>
                        <td><?= htmlspecialchars($item['fornecedor'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($item['prioridade']) ?></td>
                        <td><?= htmlspecialchars($item['status']) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($item['data_solicitacao']))) ?></td>
                        <td><?= htmlspecialchars($item['usuario_nome'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="signature-grid">
            <div class="signature">Responsável pela solicitação</div>
            <div class="signature">Responsável pela compra</div>
        </div>
    </main>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
</body>
</html>
