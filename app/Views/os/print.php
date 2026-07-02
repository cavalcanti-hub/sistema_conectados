<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Imprimir OS #<?= $os['numero_os'] ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; padding: 20px; color: #333; font-size: 12px; }
        .print-container { max-width: 800px; margin: 0 auto; border: 1px solid #eee; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; }
        .os-info { text-align: right; }
        .os-number { font-size: 20px; font-weight: bold; color: #000; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; font-size: 10px; color: #666; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .field { margin-bottom: 10px; }
        .label { font-weight: bold; color: #000; }
        .value { color: #333; }
        .footer { margin-top: 50px; border-top: 1px solid #ddd; padding-top: 20px; text-align: center; font-size: 10px; }
        .signature-box { margin-top: 60px; display: flex; justify-content: space-between; }
        .signature-line { border-top: 1px solid #000; width: 45%; text-align: center; padding-top: 5px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .print-container { border: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="text-align: right; margin-bottom: 10px;">
        <button onclick="window.print()">Imprimir</button>
        <button onclick="window.history.back()">Voltar</button>
    </div>

    <div class="print-container">
        <div class="header">
            <div class="logo">
                <img src="<?= app_url('assets/img/logo.png') ?>" alt="Conectados" style="height: 60px; max-width: 250px; filter: brightness(0);">
            </div>
            <div class="os-info">
                <div class="os-number">ORDEM DE SERVIÇO #<?= $os['numero_os'] ?></div>
                <div>Emissão: <?= date('d/m/Y H:i') ?></div>
            </div>
        </div>

        <div class="grid">
            <div class="section">
                <div class="section-title">Dados do Cliente</div>
                <div class="field"><span class="label">Nome:</span> <span class="value"><?= htmlspecialchars($os['cliente_nome']) ?></span></div>
                <div class="field"><span class="label">CPF/CNPJ:</span> <span class="value"><?= $os['cliente_cpf_cnpj'] ?? '---' ?></span></div>
                <div class="field"><span class="label">WhatsApp/Tel:</span> <span class="value"><?= $os['cliente_whatsapp'] ?? $os['cliente_telefone'] ?></span></div>
                <div class="field"><span class="label">E-mail:</span> <span class="value"><?= $os['cliente_email'] ?? '---' ?></span></div>
            </div>
            <div class="section">
                <div class="section-title">Dados do Aparelho</div>
                <div class="field"><span class="label">Aparelho:</span> <span class="value"><?= htmlspecialchars($os['marca'] . ' ' . $os['modelo']) ?></span></div>
                <div class="field"><span class="label">IMEI/Série:</span> <span class="value"><?= $os['imei'] ?: '---' ?></span></div>
                <div class="field"><span class="label">Cor:</span> <span class="value"><?= $os['cor'] ?: '---' ?></span></div>
                <div class="field"><span class="label">Senha:</span> <span class="value"><?= $os['senha_padrao'] ?: '---' ?></span></div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Problema Relatado / Observações de Entrada</div>
            <div style="background: #f9f9f9; padding: 10px; border-radius: 5px;">
                <?= nl2br(htmlspecialchars($os['problema_relatado'])) ?>
                <br><br>
                <strong>Estado Físico:</strong> <?= nl2br(htmlspecialchars($os['estado_fisico'] ?: 'Nenhuma observação informada.')) ?>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Orçamento Estimado</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 5px; border-bottom: 1px dashed #eee;">Mão de Obra</td>
                    <td style="padding: 5px; border-bottom: 1px dashed #eee; text-align: right;">R$ <?= number_format($os['valor_mao_obra'], 2, ',', '.') ?></td>
                </tr>
                <tr>
                    <td style="padding: 5px; border-bottom: 1px dashed #eee;">Peças</td>
                    <td style="padding: 5px; border-bottom: 1px dashed #eee; text-align: right;">R$ <?= number_format($os['valor_pecas'], 2, ',', '.') ?></td>
                </tr>
                <tr>
                    <td style="padding: 5px; font-weight: bold; font-size: 14px;">TOTAL ESTIMADO</td>
                    <td style="padding: 5px; font-weight: bold; font-size: 14px; text-align: right;">R$ <?= number_format($os['valor_total'], 2, ',', '.') ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Termos e Condições</div>
            <div style="font-size: 8px; color: #777;">
                1. O prazo para retirada do aparelho é de 90 dias após a conclusão do serviço. Após este período, o aparelho poderá ser vendido para cobrir custos.<br>
                2. A garantia é de 90 dias sobre o serviço realizado e peças trocadas, não cobrindo danos por mau uso, quedas ou contato com líquidos.<br>
                3. Não nos responsabilizamos por perda de dados. Recomenda-se backup antes do envio para reparo.
            </div>
        </div>

        <div class="signature-box">
            <div class="signature-line">Assinatura da Assistência</div>
            <div class="signature-line">Assinatura do Cliente</div>
        </div>

        <div class="footer">
            Conectados - Assistência Técnica Especializada<br>
            Rua Exemplo, 123 - Centro | (11) 99999-9999 | conectadosassistencia.com.br
        </div>
    </div>
</body>
</html>
