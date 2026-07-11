<?php
/**
 * @var string $title
 * @var string|null $periodo
 * @var string|null $tipo
 * @var array $movimentacoes
 * @var float $receitas
 * @var float $despesas
 * @var float $lucro
 * @var array $formas
 * @var array $categorias_despesas
 */

$periodoLabels = [
    'dia' => 'Hoje',
    'semana' => 'Semana atual',
    'mes' => 'Mes atual',
    'todos' => 'Todos os lancamentos',
];

if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodo ?? '')) {
    $periodoLabel = 'Dia ' . date('d/m/Y', strtotime($periodo));
} else {
    $periodoLabel = $periodoLabels[$periodo ?? 'dia'] ?? 'Hoje';
}
$totalLancamentos = count($movimentacoes ?? []);
$resultado = (float) $lucro;
$logoUrl = asset_url('assets/img/logo-print.png?v=20260702-banner');
$generatedAt = date('d/m/Y H:i');
$receitasCount = count(array_filter($movimentacoes ?? [], static fn($item) => ($item['tipo'] ?? '') === 'Receita'));
$despesasCount = count(array_filter($movimentacoes ?? [], static fn($item) => ($item['tipo'] ?? '') === 'Despesa'));
$resultadoLabel = $resultado >= 0 ? 'saldo positivo' : 'saldo negativo';
$resultadoTexto = 'No periodo ' . strtolower($periodoLabel) . ', foram registradas entradas de R$ '
    . number_format((float) $receitas, 2, ',', '.')
    . ' e saidas de R$ ' . number_format((float) $despesas, 2, ',', '.')
    . ', resultando em ' . $resultadoLabel . ' de R$ ' . number_format(abs($resultado), 2, ',', '.') . '.';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatorio Financeiro</title>
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
        @page {
            size: A4 landscape;
            margin: 10mm 12mm;
        }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
            background: #fff;
            font-size: 12px;
            line-height: 1.42;
        }
        .sheet {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 14px 28px 8px;
        }
        .print-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 18px;
        }
        button {
            border: 0;
            border-radius: 4px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
            background: var(--brand);
        }
        .letterhead {
            display: grid;
            grid-template-columns: minmax(240px, 1fr) minmax(420px, 1.4fr);
            gap: 24px;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--brand);
        }
        .brand img {
            max-width: 235px;
            max-height: 72px;
            object-fit: contain;
            display: block;
        }
        .document-title {
            text-align: right;
        }
        h1 {
            margin: 0 0 5px;
            font-size: 25px;
            line-height: 1.1;
            letter-spacing: 0;
            color: var(--brand);
            text-transform: uppercase;
        }
        .document-title p,
        .meta-line {
            margin: 0;
            color: var(--muted);
            font-size: 11px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: minmax(420px, 2fr) minmax(160px, .8fr) minmax(190px, .9fr);
            border: 1px solid var(--line);
            border-top: 0;
            margin-bottom: 14px;
            background: #fff;
        }
        .meta-cell {
            padding: 6px 10px;
            border-right: 1px solid var(--line);
            min-height: 30px;
        }
        .meta-cell:last-child { border-right: 0; }
        .label {
            display: block;
            color: var(--muted);
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .value {
            display: block;
            font-weight: 700;
            color: var(--ink);
            font-size: 11px;
            line-height: 1.2;
        }
        .section-title {
            margin: 14px 0 7px;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--line);
            color: var(--brand);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0;
        }
        .executive-note {
            border: 1px solid var(--line);
            border-left: 4px solid var(--brand);
            padding: 9px 11px;
            margin: 10px 0 12px;
            background: #fbfcfe;
            font-size: 12px;
        }
        .executive-note strong {
            color: var(--brand);
        }
        .summary-table,
        .report-table,
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table th,
        .summary-table td,
        .report-table th,
        .report-table td,
        .breakdown-table th,
        .breakdown-table td {
            border: 1px solid var(--line);
            padding: 7px 10px;
            vertical-align: top;
        }
        .summary-table th,
        .report-table th,
        .breakdown-table th {
            background: var(--soft);
            color: #263248;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .summary-table td {
            font-size: 14px;
            font-weight: 800;
        }
        .report-table {
            font-size: 11px;
        }
        .report-table tbody tr:nth-child(even) {
            background: #fbfcfe;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .receita { color: var(--green); font-weight: 800; }
        .despesa { color: var(--red); font-weight: 800; }
        .resultado { color: <?= $resultado >= 0 ? 'var(--green)' : 'var(--red)' ?>; }
        .empty {
            padding: 28px;
            border: 1px dashed #b8c4d3;
            text-align: center;
            color: var(--muted);
            background: #fbfcfe;
        }
        .footer-note {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 10px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        @media print {
            body { background: #fff; }
            .sheet { max-width: none; padding: 0; }
            .print-actions { display: none; }
            .letterhead,
            .meta-grid,
            .summary-table,
            .report-table tr,
            .footer-note {
                break-inside: avoid;
            }
        }
        @media(max-width: 760px) {
            .sheet { padding: 18px; }
            .letterhead { grid-template-columns: 1fr; }
            .document-title { text-align: left; }
            .meta-grid { grid-template-columns: 1fr; }
            .meta-cell { border-right: 0; border-bottom: 1px solid var(--line); min-height: 0; }
            .meta-cell:last-child { border-bottom: 0; }
        }
    </style>
</head>
<body>
    <main class="sheet">
        <div class="print-actions">
            <button type="button" onclick="window.print()">Imprimir relatorio</button>
        </div>

        <header class="letterhead">
            <div class="brand">
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Conectados">
            </div>
            <div class="document-title">
                <h1>Relatorio Financeiro</h1>
                <p>Resumo gerencial para acompanhamento de caixa</p>
            </div>
        </header>

        <section class="meta-grid" aria-label="Dados do relatorio">
            <div class="meta-cell">
                <span class="label">Empresa</span>
                <span class="value">Conectados Assistencia Tecnica</span>
            </div>
            <div class="meta-cell">
                <span class="label">Periodo</span>
                <span class="value"><?= htmlspecialchars($periodoLabel) ?></span>
            </div>
            <div class="meta-cell">
                <span class="label">Emissao</span>
                <span class="value"><?= htmlspecialchars($generatedAt) ?></span>
            </div>
        </section>

        <h2 class="section-title">Resumo Executivo</h2>
        <div class="executive-note">
            <strong>Leitura rapida:</strong> <?= htmlspecialchars($resultadoTexto) ?>
        </div>

        <table class="summary-table">
            <thead>
                <tr>
                    <th>Entradas recebidas</th>
                    <th>Saidas / despesas</th>
                    <th>Saldo do periodo</th>
                    <th>Lancamentos analisados</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="receita">R$ <?= number_format((float) $receitas, 2, ',', '.') ?></td>
                    <td class="despesa">R$ <?= number_format((float) $despesas, 2, ',', '.') ?></td>
                    <td class="resultado">R$ <?= number_format($resultado, 2, ',', '.') ?></td>
                    <td><?= (int) $totalLancamentos ?> <span class="meta-line">(<?= $receitasCount ?> receita(s), <?= $despesasCount ?> despesa(s))</span></td>
                </tr>
            </tbody>
        </table>

        <h2 class="section-title">Lancamentos Detalhados</h2>
        <?php if (empty($movimentacoes)): ?>
            <div class="empty">Nenhum lancamento encontrado para o filtro selecionado.</div>
        <?php else: ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width:78px;">Data</th>
                        <th style="width:76px;">Natureza</th>
                        <th style="width:128px;">Categoria</th>
                        <th>Descricao</th>
                        <th style="width:118px;">Forma</th>
                        <th class="right" style="width:112px;">Impacto no caixa</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($movimentacoes as $m): ?>
                    <?php $dataMov = $m['data_pagamento'] ?: $m['created_at']; ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($dataMov)) ?></td>
                        <td><?= htmlspecialchars($m['tipo']) ?></td>
                        <td><?= htmlspecialchars($m['categoria'] ?: 'Sem categoria') ?></td>
                        <td><?= htmlspecialchars($m['descricao']) ?></td>
                        <td><?= htmlspecialchars($m['forma_pagamento'] ?: '-') ?></td>
                        <td class="right <?= $m['tipo'] === 'Receita' ? 'receita' : 'despesa' ?>">
                            <?= $m['tipo'] === 'Receita' ? '+' : '-' ?> R$ <?= number_format((float) $m['valor'], 2, ',', '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <footer class="footer-note">
            <span>Conectados Assistencia Tecnica</span>
            <span>Relatorio gerado pelo sistema em <?= htmlspecialchars($generatedAt) ?></span>
        </footer>
    </main>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
</body>
</html>
