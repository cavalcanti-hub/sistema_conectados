<?php
$termo = $termo ?? [];
$company = $company ?? [];

$fmtDate = static function ($date): string {
    if (empty($date)) {
        return '';
    }

    return date('d/m/Y', strtotime((string) $date));
};

$check = static fn($value): string => !empty($value) ? 'X' : '&nbsp;';
$line = static fn($value): string => e((string) ($value ?: ''));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termo de Compra e Venda <?= e($termo['numero_termo'] ?? '') ?></title>
    <style>
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            background: #fff;
            font-size: 10px;
        }
        .print-actions {
            max-width: 190mm;
            margin: 0 auto 10px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .print-actions button {
            border: 0;
            border-radius: 6px;
            padding: 9px 12px;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
            background: #00349a;
        }
        .document {
            width: 190mm;
            min-height: 277mm;
            margin: 0 auto;
            padding: 6mm 7mm 5mm;
            border: 2px solid #1f2937;
            outline: 1px solid #1f2937;
            outline-offset: -5px;
            position: relative;
            overflow: hidden;
        }
        .watermark {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: .055;
            pointer-events: none;
            z-index: 0;
        }
        .watermark img { width: 118mm; max-height: 118mm; object-fit: contain; }
        .content { position: relative; z-index: 1; }
        .header {
            display: grid;
            grid-template-columns: 34mm 1fr 36mm;
            gap: 4mm;
            align-items: center;
            border-bottom: 2px solid #111;
            padding: 0 1mm 3mm;
        }
        .device-mark {
            width: 22mm;
            height: 40mm;
            margin: 0 auto;
            position: relative;
        }
        .device-frame {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .device-logo {
            position: absolute;
            left: 50%;
            top: 48%;
            width: 15mm;
            max-height: 12mm;
            object-fit: contain;
            transform: translateX(-50%);
            opacity: 1;
        }
        .title-block { text-align: center; }
        .title-block h1 {
            margin: 0;
            font-size: 22px;
            line-height: 1;
            font-weight: 900;
            font-style: italic;
            text-transform: uppercase;
        }
        .title-block h2 {
            margin: 2mm 0 0;
            font-size: 17px;
            line-height: 1.1;
            font-weight: 900;
            font-style: italic;
        }
        .term-meta {
            text-align: right;
            color: #374151;
            line-height: 1.35;
            font-size: 9px;
        }
        .term-meta strong { display: block; color: #111; font-size: 10px; }
        .section {
            border-top: 1px solid #111;
            padding-top: 3mm;
            margin-top: 3mm;
        }
        .section-label {
            width: max-content;
            margin: -6mm auto 2.5mm;
            background: #d9dde5;
            color: #111;
            border-radius: 2px;
            padding: 1mm 4mm;
            text-align: center;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .row {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 2mm;
            margin-bottom: 2.5mm;
            align-items: end;
        }
        .field { min-height: 5.5mm; }
        .span-2 { grid-column: span 2; }
        .span-3 { grid-column: span 3; }
        .span-4 { grid-column: span 4; }
        .span-5 { grid-column: span 5; }
        .span-6 { grid-column: span 6; }
        .span-7 { grid-column: span 7; }
        .span-8 { grid-column: span 8; }
        .span-9 { grid-column: span 9; }
        .span-12 { grid-column: span 12; }
        .label { font-size: 8.2px; color: #111; margin-bottom: .7mm; }
        .value {
            border-bottom: 1px solid #111;
            min-height: 4.2mm;
            padding: 0 .8mm .7mm;
            font-size: 10px;
            line-height: 1.2;
            word-break: break-word;
        }
        .buyer-grid {
            display: grid;
            grid-template-columns: 1fr 39mm;
            gap: 4mm;
            align-items: stretch;
        }
        .stamp-box {
            border: 2px solid #111;
            min-height: 38mm;
            padding: 2mm;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            font-size: 8px;
        }
        .stamp-box strong { align-self:flex-end;font-size:9px; }
        .terms {
            border-top: 1px solid #111;
            margin-top: 3mm;
            padding-top: 2mm;
            font-size: 8.2px;
            line-height: 1.28;
        }
        .terms-title { font-weight: 900; margin-bottom: 1mm; }
        .terms ol { margin: 0; padding-left: 4mm; }
        .terms li { margin-bottom: .7mm; }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14mm;
            margin-top: 8mm;
        }
        .signature {
            border-top: 1px solid #111;
            padding-top: 1.2mm;
            text-align: center;
            font-size: 9px;
        }
        .annex-note {
            text-align: center;
            font-size: 8px;
            font-style: italic;
            margin-top: 3mm;
        }
        @media print {
            body { background: #fff; }
            .print-actions { display: none; }
            .document { margin: 0 auto; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button type="button" onclick="window.print()">Imprimir ou salvar PDF</button>
    </div>

    <main class="document">
        <div class="watermark">
            <img src="<?= e(app_url('assets/img/logo-print.png')) ?>" alt="">
        </div>

        <div class="content">
            <header class="header">
                <div class="device-mark" aria-hidden="true">
                    <img class="device-frame" src="<?= e(app_url('assets/img/termo-phone-frame.webp')) ?>" alt="">
                    <img class="device-logo" src="<?= e(app_url('assets/img/logo-print.png')) ?>" alt="">
                </div>
                <div class="title-block">
                    <h1>Termo de Compra e Venda</h1>
                    <h2>Smartphones/ Tablet/ Notebook</h2>
                </div>
                <div class="term-meta">
                    <strong><?= e($termo['numero_termo'] ?? '') ?></strong>
                    Emissao: <?= e(date('d/m/Y H:i')) ?><br>
                    Entrada: <?= e($fmtDate($termo['data_entrada'] ?? '')) ?>
                </div>
            </header>

            <section class="section">
                <div class="section-label">Dados vendedor</div>
                <div class="row">
                    <div class="field span-6"><div class="label">Nome:</div><div class="value"><?= $line($termo['vendedor_nome'] ?? '') ?></div></div>
                    <div class="field span-3"><div class="label">Data Entrada:</div><div class="value"><?= e($fmtDate($termo['data_entrada'] ?? '')) ?></div></div>
                    <div class="field span-3"><div class="label">Contato:</div><div class="value"><?= $line($termo['vendedor_contato'] ?? '') ?></div></div>
                </div>
                <div class="row">
                    <div class="field span-4"><div class="label">CPF:</div><div class="value"><?= $line($termo['vendedor_cpf'] ?? '') ?></div></div>
                    <div class="field span-4"><div class="label">RG:</div><div class="value"><?= $line($termo['vendedor_rg'] ?? '') ?></div></div>
                    <div class="field span-4"><div class="label">End.:</div><div class="value"><?= $line($termo['vendedor_endereco'] ?? '') ?></div></div>
                </div>
            </section>

            <section class="section">
                <div class="section-label">Informações do equipamento e venda</div>
                <div class="row">
                    <div class="field span-4"><div class="label">Equipamento:</div><div class="value"><?= $line($termo['equipamento_tipo'] ?? '') ?></div></div>
                    <div class="field span-8"><div class="label">Marca/ Modelo:</div><div class="value"><?= $line($termo['marca_modelo'] ?? '') ?></div></div>
                </div>
                <div class="row">
                    <div class="field span-6"><div class="label">IMEI 1:</div><div class="value"><?= $line($termo['imei1'] ?? '') ?></div></div>
                    <div class="field span-6"><div class="label">IMEI 2:</div><div class="value"><?= $line($termo['imei2'] ?? '') ?></div></div>
                </div>
                <div class="row">
                    <div class="field span-8"><div class="label">Acessórios:</div><div class="value"><?= $line($termo['acessorios'] ?? '') ?></div></div>
                    <div class="field span-4"><div class="label">Valor Compra: R$</div><div class="value"><?= e(money_br($termo['valor_compra'] ?? 0)) ?></div></div>
                </div>
                <div class="row">
                    <div class="field span-12"><div class="label">Estado do aparelho / observações:</div><div class="value"><?= nl2br($line($termo['estado_aparelho'] ?? '')) ?></div></div>
                </div>
            </section>

            <section class="section">
                <div class="section-label">Dados comprador</div>
                <div class="buyer-grid">
                    <div>
                        <div class="row">
                            <div class="field span-7"><div class="label">Nome:</div><div class="value"><?= $line($termo['comprador_nome'] ?? '') ?></div></div>
                            <div class="field span-5"><div class="label">Contato:</div><div class="value"><?= $line($termo['comprador_contato'] ?? '') ?></div></div>
                        </div>
                        <div class="row">
                            <div class="field span-5"><div class="label">CPF/CNPJ:</div><div class="value"><?= $line($termo['comprador_documento'] ?? '') ?></div></div>
                            <div class="field span-7"><div class="label">End.:</div><div class="value"><?= $line($termo['comprador_endereco'] ?? '') ?></div></div>
                        </div>
                    </div>
                    <div class="stamp-box">
                        <span>Autenticação</span>
                        <strong>CARIMBO</strong>
                    </div>
                </div>
            </section>

            <section class="terms">
                <div class="terms-title">LEIA COM ATENÇÃO:</div>
                <ol>
                    <li>Declaro ter vendido esse aparelho celular, supracitado, nas condições negociadas com o comprador.</li>
                    <li>Declaro também que o aparelho celular era de total responsabilidade minha, sendo dono legítimo, de uso pessoal, e não houve por meios ilícitos e ilegais diante da lei.</li>
                    <li>Chip, cartao de memoria, SIM card ou midia removivel, quando encontrados, sao retirados e devolvidos ao vendedor no ato da conferencia; nao integram a compra e nao ficam sob responsabilidade do comprador.</li>
                    <li>Estou ciente que após fechar as negociações, que todos os dados salvos no aparelho serão deletados, não sendo possível recuperá-los. E está autorizado o acesso quando tiver senha, para a formatação.</li>
                    <li>Diante disso, dou fé e assino.</li>
                </ol>
                <?php if (!empty($termo['observacoes'])): ?>
                    <div style="margin-top:2mm;"><strong>Observações:</strong> <?= nl2br($line($termo['observacoes'])) ?></div>
                <?php endif; ?>
            </section>

            <div class="signatures">
                <div class="signature">Assinatura Vendedor</div>
                <div class="signature">Assinatura Comprador</div>
            </div>
            <div class="annex-note">Em anexo Documento com foto do vendedor</div>
        </div>
    </main>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
</body>
</html>
