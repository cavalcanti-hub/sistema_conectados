<?php
$periodoLabels = [
    'dia' => 'Hoje',
    'semana' => 'Semana atual',
    'mes' => 'Mes atual',
    'todos' => 'Todos os lancamentos',
];
$periodoLabel = $periodoLabels[$periodo ?? 'mes'] ?? 'Mes atual';
$tipoLabel = $tipo !== '' ? $tipo : 'Todos';
$formatMoney = static fn($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
$logoUrl = asset_url('assets/img/logo-print.png?v=20260702-banner');
$generatedAt = date('d/m/Y H:i');
$saldo = (float) ($saldo ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatorio de Gastos Pessoais</title>
    <style>
        * { box-sizing: border-box; }
        :root {
            --ink: #111827;
            --muted: #64748b;
            --line: #d7dee8;
            --soft: #f5f7fb;
            --brand: #00349a;
            --green: #047857;
            --red: #b91c1c;
        }
        @page { size: A4 landscape; margin: 10mm 12mm; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; color: var(--ink); background: #fff; font-size: 12px; line-height: 1.42; }
        .sheet { width: 100%; max-width: 1280px; margin: 0 auto; padding: 14px 28px 8px; }
        .print-actions { display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 18px; }
        button { border: 0; border-radius: 4px; padding: 10px 14px; font-weight: 700; cursor: pointer; color: #fff; background: var(--brand); }
        .letterhead { display: grid; grid-template-columns: minmax(240px, 1fr) minmax(420px, 1.4fr); gap: 24px; align-items: center; padding-bottom: 10px; border-bottom: 3px solid var(--brand); }
        .brand img { max-width: 235px; max-height: 72px; object-fit: contain; display: block; }
        .document-title { text-align: right; }
        h1 { margin: 0 0 5px; font-size: 25px; line-height: 1.1; color: var(--brand); text-transform: uppercase; }
        .document-title p, .footer-note { margin: 0; color: var(--muted); font-size: 11px; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; border: 1px solid var(--line); border-top: 0; margin-bottom: 14px; }
        .meta-cell { padding: 7px 10px; border-right: 1px solid var(--line); }
        .meta-cell:last-child { border-right: 0; }
        .label { display: block; color: var(--muted); font-size: 9px; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
        .value { display: block; font-weight: 700; font-size: 11px; }
        .section-title { margin: 14px 0 7px; padding-bottom: 5px; border-bottom: 1px solid var(--line); color: var(--brand); font-size: 13px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid var(--line); padding: 7px 10px; vertical-align: top; }
        th { background: var(--soft); color: #263248; text-align: left; font-size: 10px; text-transform: uppercase; }
        tbody tr:nth-child(even) { background: #fbfcfe; }
        .summary td { font-size: 14px; font-weight: 800; }
        .right { text-align: right; }
        .receita { color: var(--green); font-weight: 800; }
        .despesa { color: var(--red); font-weight: 800; }
        .saldo { color: <?= $saldo >= 0 ? 'var(--green)' : 'var(--red)' ?>; }
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .empty { padding: 28px; border: 1px dashed #b8c4d3; text-align: center; color: var(--muted); background: #fbfcfe; }
        .footer-note { margin-top: 20px; padding-top: 10px; border-top: 1px solid var(--line); display: flex; justify-content: space-between; gap: 20px; }
        @media print {
            .sheet { max-width: none; padding: 0; }
            .print-actions { display: none; }
            .letterhead, .meta-grid, tr, .footer-note { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="print-actions">
            <button type="button" onclick="window.print()">Imprimir</button>
            <button type="button" onclick="window.close()">Fechar</button>
        </div>

        <div class="letterhead">
            <div class="brand"><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Conectados"></div>
            <div class="document-title">
                <h1>Gastos Pessoais</h1>
                <p>Relatorio privado do proprietario</p>
            </div>
        </div>
        <div class="meta-grid">
            <div class="meta-cell"><span class="label">Periodo</span><span class="value"><?= htmlspecialchars($periodoLabel) ?></span></div>
            <div class="meta-cell"><span class="label">Tipo</span><span class="value"><?= htmlspecialchars($tipoLabel) ?></span></div>
            <div class="meta-cell"><span class="label">Gerado em</span><span class="value"><?= htmlspecialchars($generatedAt) ?></span></div>
        </div>

        <div class="section-title">Resumo</div>
        <table class="summary">
            <tr>
                <th>Entradas</th>
                <th>Despesas</th>
                <th>Saldo pessoal</th>
                <th>Quantidade de lancamentos</th>
            </tr>
            <tr>
                <td class="receita"><?= $formatMoney($entradas ?? 0) ?></td>
                <td class="despesa"><?= $formatMoney($despesas ?? 0) ?></td>
                <td class="saldo"><?= $formatMoney($saldo) ?></td>
                <td><?= count($lancamentos ?? []) ?></td>
            </tr>
        </table>

        <div class="two-col">
            <div>
                <div class="section-title">Categorias</div>
                <?php if (empty($categorias)): ?>
                <div class="empty">Sem despesas por categoria no periodo.</div>
                <?php else: ?>
                <table>
                    <tr><th>Categoria</th><th class="right">Qtd</th><th class="right">Total</th></tr>
                    <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['categoria']) ?></td>
                        <td class="right"><?= (int) $cat['qtd'] ?></td>
                        <td class="right"><?= $formatMoney($cat['total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
            <div>
                <div class="section-title">Formas de pagamento</div>
                <?php if (empty($formas)): ?>
                <div class="empty">Sem formas de pagamento no periodo.</div>
                <?php else: ?>
                <table>
                    <tr><th>Forma</th><th class="right">Qtd</th><th class="right">Total</th></tr>
                    <?php foreach ($formas as $forma): ?>
                    <tr>
                        <td><?= htmlspecialchars($forma['forma_pagamento']) ?></td>
                        <td class="right"><?= (int) $forma['qtd'] ?></td>
                        <td class="right"><?= $formatMoney($forma['total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="section-title">Lancamentos</div>
        <?php if (empty($lancamentos)): ?>
        <div class="empty">Nenhum lancamento encontrado para o filtro selecionado.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descricao</th>
                    <th>Categoria</th>
                    <th>Forma</th>
                    <th>Tipo</th>
                    <th class="right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lancamentos as $item): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($item['data_lancamento'])) ?></td>
                    <td><?= htmlspecialchars($item['descricao']) ?><?= !empty($item['recorrente']) ? ' (recorrente)' : '' ?></td>
                    <td><?= htmlspecialchars($item['categoria'] ?: 'Sem categoria') ?></td>
                    <td><?= htmlspecialchars($item['forma_pagamento'] ?: 'Nao informado') ?></td>
                    <td><?= htmlspecialchars($item['tipo']) ?></td>
                    <td class="right <?= $item['tipo'] === 'Receita' ? 'receita' : 'despesa' ?>"><?= $formatMoney($item['valor']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="footer-note">
            <span>Conectados - Controle de gastos pessoais</span>
            <span>Documento gerado automaticamente pelo sistema.</span>
        </div>
    </div>
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 350));</script>
</body>
</html>
