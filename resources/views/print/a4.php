<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($documentTitle ?? 'Impressao') ?></title>
    <style>
        :root {
            --ink: #111827;
            --muted: #6b7280;
            --line: #d1d5db;
            --soft: #f3f4f6;
        }
        * { box-sizing: border-box; }
        html {
            background: #2f2f2f;
        }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #eef2f7;
            color: var(--ink);
            font-size: 12px;
            line-height: 1.35;
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
            cursor: pointer;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--ink);
            break-inside: avoid;
        }
        .brand {
            display: flex;
            gap: 12px;
            align-items: center;
            min-width: 0;
        }
        .brand img {
            max-height: 48px;
            max-width: 165px;
            object-fit: contain;
            filter: brightness(0);
            flex: 0 0 auto;
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
            margin: 0 0 6px;
            font-size: 22px;
            line-height: 1.05;
        }
        .meta {
            text-align: right;
            min-width: 180px;
            max-width: 45%;
        }
        .meta strong {
            display: block;
            font-size: 21px;
            margin-bottom: 5px;
        }
        .section {
            margin-top: 12px;
            break-inside: avoid;
        }
        .section-title {
            margin: 0 0 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--line);
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: .08em;
            color: var(--ink);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .panel {
            border: 1px solid var(--line);
            padding: 10px 12px;
            background: #fff;
            min-width: 0;
            break-inside: avoid;
        }
        .field {
            margin-bottom: 6px;
            font-size: 12px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .field:last-child { margin-bottom: 0; }
        .label {
            display: inline-block;
            min-width: 105px;
            font-weight: 700;
        }
        .text-block {
            min-height: 46px;
            border: 1px solid var(--line);
            background: #fff;
            padding: 9px 12px;
            white-space: pre-wrap;
            line-height: 1.4;
            overflow-wrap: anywhere;
            break-inside: avoid;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            break-inside: avoid;
        }
        th, td {
            padding: 6px 8px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            font-size: 12px;
            line-height: 1.3;
        }
        th:last-child, td:last-child {
            text-align: right;
        }
        .total-row td {
            font-size: 15px;
            font-weight: 700;
            border-top: 2px solid var(--ink);
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
            font-size: 12px;
            color: var(--muted);
            break-inside: avoid;
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
                <div class="text-block"><?= htmlspecialchars(implode("\n", $printData['services'] ?? [])) ?></div>
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
                <div class="text-block"><?= htmlspecialchars(trim(($printData['notes'] ?? '') ?: ($os['problema_relatado'] ?? 'Sem observacoes.'))) ?></div>
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
