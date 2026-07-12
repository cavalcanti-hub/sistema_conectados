<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($documentTitle ?? 'Impressao') ?></title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #475569;
            --line: #cbd5e1;
            --soft: #f8fafc;
            --primary: #2563eb;
        }
        * { box-sizing: border-box; }
        html {
            background: #1e293b;
        }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #f1f5f9;
            color: var(--ink);
            font-size: 11px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .print-shell {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            background: #fff;
        }
        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px;
        }
        .toolbar button {
            border: 1px solid #cbd5e1;
            background: #fff;
            padding: 10px 14px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--primary);
            break-inside: avoid;
        }
        .brand {
            display: flex;
            gap: 12px;
            align-items: center;
            min-width: 0;
        }
        .brand img {
            width: 42mm;
            height: auto;
            max-width: 42mm;
            max-height: none;
            object-fit: contain;
            flex: 0 0 auto;
            display: block;
        }
        .brand > div {
            min-width: 0;
        }
        .brand div div,
        .meta div {
            overflow-wrap: anywhere;
        }
        .brand h1,
        .page-title {
            margin: 0 0 4px;
            font-size: 20px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1.05;
        }
        .meta {
            text-align: right;
            min-width: 180px;
            max-width: 45%;
        }
        .meta strong {
            display: block;
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 3px;
            color: var(--primary);
        }
        .section {
            margin-top: 14px;
            break-inside: avoid;
        }
        .section-title {
            margin: 0 0 8px;
            padding-bottom: 4px;
            border-bottom: 1.5px solid var(--line);
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            color: var(--primary);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .panel {
            border: 1px solid var(--line);
            padding: 10px 12px;
            background: #fff;
            border-radius: 8px;
            min-width: 0;
            break-inside: avoid;
        }
        .field {
            margin-bottom: 5px;
            font-size: 11px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }
        .field:last-child { margin-bottom: 0; }
        .label {
            display: inline-block;
            min-width: 105px;
            font-weight: 700;
            color: var(--muted);
        }
        .text-block {
            min-height: 46px;
            border: 1px solid var(--line);
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 8px;
            line-height: 1.4;
            overflow-wrap: anywhere;
            break-inside: avoid;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            break-inside: avoid;
        }
        th {
            background: var(--soft);
            font-weight: 700;
            color: var(--muted);
            border-bottom: 1.5px solid var(--line);
        }
        th, td {
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            line-height: 1.3;
        }
        td {
            border-bottom: 1px solid var(--line);
        }
        tr:last-child td {
            border-bottom: none;
        }
        th:last-child, td:last-child {
            text-align: right;
        }
        .total-row td {
            font-size: 13px;
            font-weight: 700;
            background: var(--soft);
            border-top: 1.5px solid var(--line);
        }
        .agenda-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--line);
        }
        .agenda-item:last-child {
            border-bottom: 0;
        }
        .agenda-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-weight: 700;
        }
        .footer-note {
            margin-top: 14px;
            text-align: center;
            font-size: 10px;
            color: var(--muted);
            break-inside: avoid;
        }
        .customer-stub {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px dashed #6b7280;
            break-inside: avoid;
        }
        .stub-box {
            border: 1px solid var(--primary);
            padding: 12px 14px;
            border-radius: 8px;
            background: #fff;
        }
        .stub-head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            padding-bottom: 7px;
            margin-bottom: 8px;
            border-bottom: 1px solid var(--line);
            font-weight: 700;
        }
        .stub-brand {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }
        .stub-brand img {
            width: 26mm;
            max-width: 26mm;
            height: auto;
            object-fit: contain;
            flex: 0 0 auto;
        }
        .stub-brand div {
            min-width: 0;
            overflow-wrap: anywhere;
        }
        .stub-head strong {
            font-size: 13px;
            display: block;
            margin-bottom: 2px;
            color: var(--primary);
        }
        .stub-number {
            flex: 0 0 auto;
            text-align: right;
            font-size: 12px;
            color: var(--primary);
            font-weight: bold;
        }
        .stub-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px 12px;
        }
        .stub-field {
            font-size: 11px;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }
        .stub-field span {
            display: block;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .stub-message {
            margin-top: 8px;
            padding-top: 7px;
            border-top: 1px solid var(--line);
            font-size: 9px;
            color: var(--muted);
        }
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
        @media print {
            html {
                background: #fff;
            }
            body {
                background: #fff;
                width: auto;
                min-width: 0;
            }
            .toolbar {
                display: none;
            }
            .print-shell {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
<?php if (normalize_os_status((string) ($printData['status'] ?? '')) === 'Cancelado'): ?><div style="background:#fee2e2;color:#991b1b;border:2px solid #dc2626;padding:8px;text-align:center;font-size:20px;font-weight:900;">CANCELADA</div><?php endif; ?>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Imprimir</button>
        <button type="button" onclick="window.close()">Fechar</button>
    </div>

    <div class="print-shell">
        <?php if (($printContext ?? '') === 'agenda'): ?>
            <div class="header">
                <div class="brand">
                    <img src="<?= htmlspecialchars($company['logo_print'] ?? asset_url('assets/img/logo-print.png?v=20260702-banner')) ?>" alt="Logo">
                    <div>
                        <h1><?= htmlspecialchars($company['name'] ?? 'Empresa') ?></h1>
                        <div><?= htmlspecialchars($company['phone'] ?? '') ?></div>
                        <div><?= htmlspecialchars($company['address'] ?? '') ?></div>
                    </div>
                </div>
                <div class="meta">
                    <strong>Agenda</strong>
                    <div>Gerado em: <?= date('d/m/Y H:i', strtotime($printData['generated_at'] ?? 'now')) ?></div>
                    <div>Total de itens: <?= count($printData['items'] ?? []) ?></div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">Agenda Operacional</h2>
                <div class="panel">
                    <?php if (empty($printData['items'])): ?>
                        <div class="field">Nenhuma OS em andamento para impressao.</div>
                    <?php else: ?>
                        <?php foreach (($printData['items'] ?? []) as $item): ?>
                            <div class="agenda-item">
                                <div class="agenda-top">
                                    <span>#<?= htmlspecialchars($item['numero_os'] ?? '') ?></span>
                                    <span><?= htmlspecialchars($item['status'] ?? '') ?></span>
                                </div>
                                <div class="field"><span class="label">Cliente</span><?= htmlspecialchars($item['cliente_nome'] ?? '') ?></div>
                                <div class="field"><span class="label">Equipamento</span><?= htmlspecialchars(trim(($item['aparelho_marca'] ?? '') . ' ' . ($item['aparelho_modelo'] ?? ''))) ?></div>
                                <div class="field"><span class="label">Entrada</span><?= !empty($item['created_at']) ? date('d/m/Y H:i', strtotime($item['created_at'])) : '-' ?></div>
                                <div class="field"><span class="label">Total</span>R$ <?= money_br($item['valor_total'] ?? 0) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="header">
                <div class="brand">
                    <img src="<?= htmlspecialchars($company['logo_print'] ?? asset_url('assets/img/logo-print.png?v=20260702-banner')) ?>" alt="Logo">
                    <div>
                        <h1><?= htmlspecialchars($company['name'] ?? 'Empresa') ?></h1>
                        <div><?= htmlspecialchars($company['phone'] ?? '') ?></div>
                        <div><?= htmlspecialchars($company['address'] ?? '') ?></div>
                        <div><?= htmlspecialchars($company['email'] ?? '') ?></div>
                    </div>
                </div>
                <div class="meta">
                    <strong>OS #<?= htmlspecialchars($printData['number'] ?? '') ?></strong>
                    <div>Emissao: <?= date('d/m/Y H:i') ?></div>
                    <div>Status: <?= htmlspecialchars($printData['status'] ?? '') ?></div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">Dados do Cliente</h2>
                <div class="grid">
                    <div class="panel">
                        <div class="field"><span class="label">Nome</span><?= htmlspecialchars($os['cliente_nome'] ?? '') ?></div>
                        <div class="field"><span class="label">Telefone</span><?= htmlspecialchars($printData['customer_phone'] ?? '') ?></div>
                        <div class="field"><span class="label">Endereco</span><?= htmlspecialchars($os['cliente_endereco'] ?? '') ?></div>
                    </div>
                    <div class="panel">
                        <div class="field"><span class="label">CPF/CNPJ</span><?= htmlspecialchars($os['cliente_cpf_cnpj'] ?? '') ?></div>
                        <div class="field"><span class="label">E-mail</span><?= htmlspecialchars($os['cliente_email'] ?? '') ?></div>
                        <div class="field"><span class="label">Data de entrada</span><?= !empty($os['created_at']) ? date('d/m/Y H:i', strtotime($os['created_at'])) : '-' ?></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">Dados da Ordem de Servico</h2>
                <div class="grid">
                    <div class="panel">
                        <div class="field"><span class="label">Numero</span>#<?= htmlspecialchars($printData['number'] ?? '') ?></div>
                        <div class="field"><span class="label">Status</span><?= htmlspecialchars($printData['status'] ?? '') ?></div>
                        <div class="field"><span class="label">Prazo</span><?= !empty($os['prazo_estimado']) ? date('d/m/Y', strtotime($os['prazo_estimado'])) : '-' ?></div>
                        <div class="field"><span class="label">Pagamento</span><?= htmlspecialchars(($printData['payment_method'] ?? '') ?: 'Nao informado') ?></div>
                    </div>
                    <div class="panel">
                        <div class="field"><span class="label">Equipamento</span><?= htmlspecialchars($printData['equipment'] ?? '') ?></div>
                        <div class="field"><span class="label">IMEI/Serie</span><?= htmlspecialchars($os['imei'] ?? '') ?></div>
                        <div class="field"><span class="label">Tecnico</span><?= htmlspecialchars($os['tecnico_nome'] ?? '') ?></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">Servicos Executados</h2>
                <div class="text-block" style="white-space: pre-wrap;"><?= htmlspecialchars(implode("\n", $printData['services'] ?? [])) ?></div>
            </div>

            <div class="section">
                <h2 class="section-title">Valores</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Descricao</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($printData['values'] ?? []) as $value): ?>
                            <tr>
                                <td><?= htmlspecialchars($value['label'] ?? '') ?></td>
                                <td>R$ <?= money_br($value['amount'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td>Total</td>
                            <td>R$ <?= money_br($printData['total'] ?? 0) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h2 class="section-title">Observacoes</h2>
                <div class="text-block" style="white-space: normal; min-height: auto;">
                    <?php
                    $notes = trim(($printData['notes'] ?? '') ?: ($os['problema_relatado'] ?? 'Sem observacoes.'));
                    $checklistItems = [];
                    $observacoes = '';

                    if (str_starts_with($notes, "Checklist de Entrada:")) {
                        $parts = explode("\n\n", $notes, 2);
                        $checklistText = $parts[0];
                        $observacoes = trim($parts[1] ?? '');
                        
                        $lines = explode("\n", $checklistText);
                        array_shift($lines); // Remove "Checklist de Entrada:"
                        foreach ($lines as $line) {
                            $line = trim($line, "- \t\n\r");
                            if ($line === '') continue;
                            $subParts = explode(":", $line, 2);
                            if (count($subParts) === 2) {
                                $checklistItems[trim($subParts[0])] = trim($subParts[1]);
                            }
                        }
                    } else {
                        $observacoes = $notes;
                    }
                    ?>

                    <?php if (!empty($checklistItems)): ?>
                        <div style="font-weight: 700; margin-bottom: 8px; font-size: 10px; text-transform: uppercase; color: var(--muted); letter-spacing: 0.04em;">Checklist de Entrada:</div>
                        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-bottom: 12px;">
                            <?php foreach ($checklistItems as $item => $status): ?>
                                <?php 
                                $isOk = strtolower($status) === 'ok';
                                $color = $isOk ? '#16a34a' : '#dc2626';
                                $bg = $isOk ? '#f0fdf4' : '#fef2f2';
                                $border = $isOk ? '#bbf7d0' : '#fecaca';
                                ?>
                                <div style="display:flex; justify-content:space-between; align-items:center; background:<?= $bg ?>; border:1px solid <?= $border ?>; padding:5px 8px; border-radius:5px; font-size:10px; color:#1e293b;">
                                    <span style="font-weight: 500;"><?= htmlspecialchars($item) ?></span>
                                    <strong style="color:<?= $color ?>; font-size:9px; text-transform:uppercase;"><?= htmlspecialchars($status) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($observacoes !== ''): ?>
                        <?php if (!empty($checklistItems)): ?>
                            <div style="font-weight: 700; margin-top: 10px; margin-bottom: 6px; font-size: 10px; text-transform: uppercase; color: var(--muted); letter-spacing: 0.04em;">Observações do Aparelho:</div>
                        <?php endif; ?>
                        <div style="line-height: 1.4; color: #334155; white-space: pre-wrap;"><?= htmlspecialchars($observacoes) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="customer-stub">
                <div class="stub-box">
                    <div class="stub-head">
                        <div class="stub-brand">
                            <img src="<?= htmlspecialchars($company['logo_print'] ?? asset_url('assets/img/logo-print.png?v=20260702-banner')) ?>" alt="Logo">
                            <div>
                                <strong>Comprovante de Ordem de Servico</strong>
                                <div><?= htmlspecialchars($company['name'] ?? 'Empresa') ?></div>
                            </div>
                        </div>
                        <div class="stub-number">OS #<?= htmlspecialchars($printData['number'] ?? '') ?></div>
                    </div>
                    <div class="stub-grid">
                        <div class="stub-field"><span>Cliente</span><?= htmlspecialchars($os['cliente_nome'] ?? '') ?></div>
                        <div class="stub-field"><span>Telefone</span><?= htmlspecialchars($printData['customer_phone'] ?? '') ?></div>
                        <div class="stub-field"><span>Entrada</span><?= !empty($os['created_at']) ? date('d/m/Y H:i', strtotime($os['created_at'])) : '-' ?></div>
                        <div class="stub-field"><span>Equipamento</span><?= htmlspecialchars($printData['equipment'] ?? '') ?></div>
                        <div class="stub-field"><span>Prazo</span><?= !empty($os['prazo_estimado']) ? date('d/m/Y', strtotime($os['prazo_estimado'])) : '-' ?></div>
                        <div class="stub-field"><span>Total</span>R$ <?= money_br($printData['total'] ?? 0) ?></div>
                    </div>
                    <div class="stub-message">
                        Apresente este comprovante na retirada. Contato: <?= htmlspecialchars($company['phone'] ?? '') ?><?php if (!empty($company['website'])): ?> | <?= htmlspecialchars($company['website']) ?><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="footer-note">
            <?= htmlspecialchars($company['name'] ?? 'Empresa') ?><?php if (!empty($company['website'])): ?> | <?= htmlspecialchars($company['website']) ?><?php endif; ?>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
