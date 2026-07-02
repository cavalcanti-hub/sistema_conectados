<?php
$statusCount = is_array($statusCount ?? null) ? $statusCount : [];
$baixo = is_array($baixo ?? null) ? $baixo : [];
$lucroDia = (float) $receitas_dia - (float) $despesas_dia;
$lucroSemana = (float) $receitas_semana - (float) $despesas_semana;
$lucroMes = (float) $receitas_mes - (float) $despesas_mes;
$indicadores = [
    ['Resultado Hoje', $lucroDia],
    ['Resultado Semana', $lucroSemana],
    ['Resultado Mês', $lucroMes],
    ['Faturamento OS Mês', (float) $fat_mes],
    ['Receitas Hoje', (float) $receitas_dia],
    ['Despesas Hoje', (float) $despesas_dia],
    ['Receitas Mês', (float) $receitas_mes],
    ['Despesas Mês', (float) $despesas_mes],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatorio Gerencial</title>
    <style>
        @page { size: A4; margin: 16mm 14mm; }
        * { box-sizing: border-box; }
        body { margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;color:#111827;background:#fff;font-size:12px; }
        .document { width:100%;max-width:190mm;margin:0 auto; }
        .print-actions { display:flex;justify-content:flex-end;margin:0 0 14px; }
        button { border:0;border-radius:6px;padding:10px 14px;font-weight:700;cursor:pointer;color:#fff;background:#00349a; }
        .letterhead { display:grid;grid-template-columns:140px 1fr 170px;align-items:center;gap:18px;border-bottom:2px solid #00349a;padding-bottom:14px;margin-bottom:18px; }
        .logo { width:132px;max-height:70px;object-fit:contain;display:block; }
        .company { text-align:center; }
        .company strong { display:block;font-size:15px;color:#00349a;margin-bottom:4px; }
        .company span { display:block;color:#475569;line-height:1.35; }
        .doc-meta { text-align:right;color:#475569;line-height:1.45;font-size:11px; }
        h1 { margin:0 0 14px;font-size:20px;color:#0f172a;text-align:center;text-transform:uppercase;letter-spacing:.04em; }
        .section-title { margin:18px 0 8px;font-size:13px;color:#0f172a;text-transform:uppercase;letter-spacing:.03em; }
        table { width:100%;border-collapse:collapse;font-size:11.5px; }
        th, td { border:1px solid #cbd5e1;padding:7px 8px;vertical-align:top; }
        th { background:#f1f5f9;color:#0f172a;font-weight:700;text-align:left;text-transform:uppercase;font-size:10.5px; }
        .right { text-align:right; }
        .center { text-align:center; }
        .positive { color:#047857;font-weight:700; }
        .negative { color:#b91c1c;font-weight:700; }
        .empty { padding:18px;border:1px solid #cbd5e1;text-align:center;color:#475569; }
        @media print {
            .print-actions { display:none; }
            .document { max-width:none; }
            tr { break-inside:avoid; }
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
                <span>Relatório gerencial do sistema</span>
            </div>
            <div class="doc-meta">
                Emitido em<br>
                <strong><?= date('d/m/Y H:i') ?></strong>
            </div>
        </header>

        <h1>Relatório Gerencial</h1>

        <div class="section-title">Indicadores financeiros e operacionais</div>
        <table>
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th class="right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($indicadores as [$label, $value]): ?>
                    <tr>
                        <td><?= htmlspecialchars($label) ?></td>
                        <td class="right <?= $value < 0 ? 'negative' : 'positive' ?>">R$ <?= number_format((float) $value, 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="section-title">Ordens de serviço por status</div>
        <?php if (empty($statusCount)): ?>
            <div class="empty">Nenhuma ordem de serviço encontrada.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th class="center">Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($statusCount as $status => $total): ?>
                        <tr>
                            <td><?= htmlspecialchars($status) ?></td>
                            <td class="center"><?= (int) $total ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="section-title">Estoque crítico</div>
        <?php if (empty($baixo)): ?>
            <div class="empty">Nenhum item com estoque crítico.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="center">Quantidade atual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($baixo as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nome'] ?? 'Item sem nome') ?></td>
                            <td class="center"><?= (int) ($item['quantidade'] ?? 0) ?> un.</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
</body>
</html>
