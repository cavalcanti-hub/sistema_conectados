<?php require_once dirname(__DIR__) . '/layout/header.php';
$statusColors = ['Recebido'=>'badge-gray','Em analise'=>'badge-blue','Aguardando aprovacao'=>'badge-yellow','Aprovado'=>'badge-purple','Reprovado'=>'badge-red','Em reparo'=>'badge-blue','Aguardando peca'=>'badge-yellow','Pronto'=>'badge-green','Entregue'=>'badge-green','Cancelado'=>'badge-red'];
$statusIcons = ['Recebido'=>'inbox','Em analise'=>'search','Aguardando aprovacao'=>'clock','Aprovado'=>'thumbs-up','Reprovado'=>'thumbs-down','Em reparo'=>'wrench','Aguardando peca'=>'package','Pronto'=>'check-circle','Entregue'=>'truck','Cancelado'=>'x-circle'];
$pagamentos = $pagamentos ?? [];
$totalPago = round((float) ($totalPago ?? 0), 2);
$saldoRestante = round(max(0, (float) ($os['valor_total'] ?? 0) - $totalPago), 2);
$statusAtual = (string) ($os['status'] ?? '');
$statusAtualLabel = trim((string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $statusAtual)) ?: $statusAtual;
$pointOrder = $pointOrder ?? null;
$pointSettings = $pointSettings ?? [];
?>

<style>
    .os-payment-summary {
        margin-top: 1rem;
        display: grid;
        gap: .4rem;
        font-size: .86rem;
    }
    .os-payment-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        min-width: 0;
    }
    .os-payment-row span {
        color: var(--text-muted);
    }
    .os-payment-value {
        white-space: nowrap;
        text-align: right;
        min-width: max-content;
        line-height: 1.2;
    }
    .point-status-box {
        margin-top: 1rem;
        padding: .9rem;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid var(--border);
        font-size: .84rem;
    }
    .point-success-modal {
        position: fixed;
        inset: 0;
        z-index: 2600;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, .66);
        backdrop-filter: blur(5px);
    }
    .point-success-modal.active {
        display: flex;
    }
    .point-success-dialog {
        width: min(440px, 100%);
        padding: 2.5rem 1.5rem;
        border-radius: 24px;
        border: 0;
        background: #ffffff;
        color: #0f172a;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(16, 185, 129, 0.1);
        animation: pointSuccessPop .4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }
    .point-success-dialog::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 6px;
        background: linear-gradient(90deg, #10b981, #34d399);
    }
    .point-success-logo {
        height: 48px;
        margin-bottom: 0.5rem;
        object-fit: contain;
    }
    .point-success-slogan {
        font-size: 0.85rem;
        color: #64748b;
        font-style: italic;
        margin-bottom: 2rem;
        font-weight: 500;
    }
    .point-success-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.2rem;
        color: #ffffff;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.5);
    }
    .point-success-icon i {
        width: 40px;
        height: 40px;
    }
    .point-success-dialog h2 {
        margin: 0 0 .5rem;
        font-family: 'Outfit', sans-serif;
        font-size: 1.75rem;
        font-weight: 800;
        color: #064e3b;
    }
    .point-success-dialog p {
        margin: 0 0 1.5rem;
        color: #475569;
        line-height: 1.5;
        font-size: 1rem;
    }
    .point-success-dialog .btn {
        width: 100%;
        min-height: 50px;
        border-radius: 12px;
        font-size: 1.05rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        background: #10b981;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
        transition: all 0.2s;
    }
    .point-success-dialog .btn:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px -1px rgba(16, 185, 129, 0.3);
    }
    @keyframes pointSuccessPop {
        from { opacity: 0; transform: scale(0.9) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>

<?php if (isset($_GET['pagamento_ok'])): ?>
<div class="alert-box alert-green">Pagamento registrado com sucesso.</div>
<?php elseif (!empty($_GET['payment_error'])):
    $paymentMessages = [
        'INVALID_PAYMENT_VALUE' => 'Informe um valor valido, positivo e com no maximo duas casas decimais.',
        'PAYMENT_METHOD_INVALID' => 'Selecione Pix ou Dinheiro para pagamento manual.',
        'PAYMENT_EXCEEDS_BALANCE' => 'O valor informado e maior que o saldo atual.',
        'ORDER_ALREADY_PAID' => 'Esta ordem de servico ja esta quitada.',
        'PAYMENT_DUPLICATE' => 'Esta solicitacao ja foi processada ou expirou. Atualize a pagina.',
        'ORDER_NOT_PAYABLE' => 'Esta ordem de servico nao aceita pagamento.',
    ]; ?>
<div class="alert-box alert-red"><?= e($paymentMessages[(string) $_GET['payment_error']] ?? 'Nao foi possivel registrar o pagamento.') ?></div>
<?php endif; ?>

<?php if (($_GET['point_sent'] ?? '') === '1'): ?>
    <div class="alert-success">
        <i data-lucide="check-circle"></i> Cobranca enviada para a Smart Point. Confira a tela da maquininha.
    </div>
<?php endif; ?>

<?php if (!empty($_GET['point_error'])): ?>
    <div class="alert-success" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;">
        <i data-lucide="alert-triangle"></i> Nao foi possivel enviar para a Smart Point: <?= e(urldecode((string) $_GET['point_error'])) ?>
    </div>
<?php endif; ?>

<?php if (($_GET['error'] ?? '') === 'delete_failed'): ?>
    <div class="alert-success" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;">
        <i data-lucide="alert-triangle"></i> Nao foi possivel excluir esta OS agora.
    </div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <a href="<?= e(route_url('os')) ?>" style="text-decoration:none;color:var(--text-muted);display:inline-flex;align-items:center;gap:5px;font-size:.85rem;">
        <i data-lucide="arrow-left" style="width:16px;"></i> Voltar para lista
    </a>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <?php 
        $wpp_msg = "*ORCAMENTO / ORDEM DE SERVICO #{$os['numero_os']}* - *CONECTADOS*\n\n" .
                   "- *Aparelho:* {$os['marca']} {$os['modelo']}\n" .
                   "- *Problema:* {$os['problema_relatado']}\n" .
                   "- *Status:* {$statusAtualLabel}\n";
        if($os['valor_total'] > 0) $wpp_msg .= "- *Valor:* R$ " . number_format($os['valor_total'], 2, ',', '.') . "\n";
        $wpp_msg .= "\n_Qualquer duvida, estamos a disposicao!_";
        ?>
        <a href="https://wa.me/55<?= preg_replace('/\D/','',$os['cliente_whatsapp'] ?? '') ?>?text=<?= urlencode($wpp_msg) ?>" target="_blank" class="btn" style="background:#25D366;color:white;text-decoration:none;">
            <svg style="width:18px;height:18px;margin-right:8px;" viewBox="0 0 448 512" fill="currentColor"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.1 0-65.6-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-5.5-2.8-23.2-8.5-44.2-27.1-16.4-14.6-27.4-32.7-30.6-38.1-3.2-5.4-.3-8.3 2.5-11.1 2.5-2.5 5.5-6.5 8.3-9.7 2.8-3.2 3.7-5.5 5.5-9.2 1.9-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 13.2 5.8 23.5 9.2 31.6 11.8 13.6 4.3 25.9 3.7 35.8 2.2 11-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg> Enviar p/ Cliente (WhatsApp)
        </a>
        <a href="<?= e(route_url('os/edit', ['id' => (int) $os['id']])) ?>" class="btn btn-secondary">
            <i data-lucide="edit-3"></i> Editar OS
        </a>
        <button onclick="window.open('<?= e(route_url('os/print', ['id' => (int) $os['id'], 'print' => 'a4'])) ?>', '_blank')" class="btn btn-secondary">
            <i data-lucide="printer"></i> Imprimir A4
        </button>
        <button onclick="window.open('<?= e(route_url('os/print', ['id' => (int) $os['id'], 'print' => 'a4', 'detalhada' => 1])) ?>', '_blank')" class="btn btn-secondary">
            <i data-lucide="file-text"></i> OS Detalhada
        </button>
        <button onclick="window.open('<?= e(route_url('os/print', ['id' => (int) $os['id'], 'print' => '80'])) ?>', '_blank')" class="btn btn-secondary">
            <i data-lucide="printer"></i> Imprimir 80mm
        </button>
        <?php if (current_user_profile() === 'Administrador'): ?>
            <form method="POST" action="<?= e(route_url('os/delete')) ?>" data-confirm="Excluir a OS #<?= e($os['numero_os'] ?? '') ?>?Esta acao remove a ordem, historico, pagamentos e fotos anexadas." onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $os['id'] ?>">
                <button type="submit" class="btn" style="background:var(--danger);color:#fff;" onclick="event.stopPropagation()">
                    <i data-lucide="trash-2"></i> Excluir OS
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        <!-- Header da OS -->
        <div class="card" style="border-left:5px solid var(--secondary);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;flex-wrap:wrap;gap:1rem;">
                <div>
                    <p style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Protocolo</p>
                    <h2 class="brand-font" style="font-size:2rem;line-height:1;">#<?= e($os['numero_os'] ?? '') ?></h2>
                    <p style="color:var(--text-muted);font-size:.85rem;">Entrada: <?= date('d/m/Y H:i', strtotime($os['created_at'])) ?></p>
                </div>
                <div style="text-align:right;">
                    <span class="badge <?= e($statusColors[$statusAtualLabel] ?? 'badge-gray') ?>" style="font-size:.85rem;padding:6px 14px;"><?= e($statusAtualLabel) ?></span>
                    <?php if($os['prioridade'] === 'Urgente'): ?>
                    <div style="margin-top:5px;font-size:.75rem;color:var(--danger);font-weight:600;">URGENTE</div>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.5rem;border-top:1px solid var(--border);padding-top:1.25rem;">
                <div>
                    <p style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:5px;">Cliente</p>
                    <p style="font-weight:600;"><?= htmlspecialchars($os['cliente_nome']) ?></p>
                    <p style="font-size:.82rem;color:var(--text-muted);"><?= e($os['cliente_whatsapp'] ?? $os['cliente_telefone'] ?? '-') ?></p>
                </div>
                <div>
                    <p style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:5px;">Aparelho</p>
                    <p style="font-weight:600;"><?= htmlspecialchars($os['marca'].' '.$os['modelo']) ?></p>
                    <p style="font-size:.82rem;color:var(--text-muted);"><?= e($os['cor'] ?? '') ?> - IMEI: <?= e($os['imei'] ?: '-') ?></p>
                </div>
                <div>
                    <p style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:5px;">Tecnico</p>
                    <p style="font-weight:600;"><?= e($os['tecnico_nome'] ?? 'Nao atribuido') ?></p>
                    <p style="font-size:.82rem;color:var(--text-muted);">Prazo: <?= $os['prazo_estimado'] ? date('d/m/Y', strtotime($os['prazo_estimado'])) : '-' ?></p>
                </div>
            </div>
        </div>

        <!-- Problema e Diagnostico -->
        <div class="card">
            <h3 class="brand-font" style="margin-bottom:1.25rem;">Informacoes Tecnicas</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                <div>
                    <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:6px;">Problema Relatado pelo Cliente</p>
                    <div class="alert-box alert-blue" style="margin:0;height:calc(100% - 20px);display:flex;align-items:center;"><?= nl2br(htmlspecialchars($os['problema_relatado'] ?? '-')) ?></div>
                </div>
                <div>
                    <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:6px;">Senha / Padrao de Desbloqueio</p>
                    <div class="alert-box alert-blue" style="margin:0;height:calc(100% - 20px);display:flex;align-items:center;font-family:monospace;font-size:1.1rem;font-weight:bold;background:#eff6ff;color:#1e40af;border-color:#bfdbfe;"><?= htmlspecialchars($os['senha_padrao'] ?: 'Sem senha') ?></div>
                </div>
            </div>
            
            <?php if(!empty($os['estado_fisico'])): ?>
            <div style="margin-bottom:1.5rem;">
                <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:8px;">Estado Fisico & Checklist de Entrada</p>
                <?php
                $estadoFisico = trim($os['estado_fisico'] ?? '');
                $checklistItems = [];
                $observacoes = '';

                if (str_starts_with($estadoFisico, "Checklist de Entrada:")) {
                    $parts = explode("\n\n", $estadoFisico, 2);
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
                    $observacoes = $estadoFisico;
                }
                ?>
                
                <?php if (!empty($checklistItems)): ?>
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.6rem; margin-bottom: 1rem;">
                    <?php 
                    $itemIcons = [
                        'Liga/Desliga' => 'power',
                        'Botao Volume' => 'volume-2',
                        'Tela/Touch' => 'smartphone',
                        'Camera Frontal' => 'camera',
                        'Camera Traseira' => 'camera',
                        'Microfone' => 'mic',
                        'Alto-falante' => 'speaker',
                        'Wi-Fi / Bluetooth' => 'wifi',
                        'Sinal Operadora' => 'signal',
                        'Conector de Carga' => 'battery-charging',
                        'Face ID / Digital' => 'fingerprint',
                        'Sensor de Proximidade' => 'maximize',
                        'Vibracao' => 'vibrate',
                        'Flash / Lanterna' => 'flashlight',
                        'Leitura de Chip' => 'cpu',
                        'Tampa Traseira' => 'smartphone',
                        'Auricular' => 'phone',
                        'Botao Home' => 'circle-dot'
                    ];
                    ?>
                    <?php foreach ($checklistItems as $item => $status): ?>
                        <?php 
                        $statusVal = strtolower($status);
                        $badgeClass = $statusVal === 'ok' ? 'badge-green' : ($statusVal === 'defeito' ? 'badge-red' : 'badge-gray');
                        $icon = $itemIcons[$item] ?? 'check-square';
                        ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border:1px solid #e2e8f0; padding:8px 12px; border-radius:8px; font-size:0.8rem;">
                            <span style="font-weight:600; display:flex; align-items:center; gap:6px; color:var(--text-main);">
                                <i data-lucide="<?= $icon ?>" style="width:14px; height:14px; color:#2563eb;"></i>
                                <?= htmlspecialchars($item) ?>
                            </span>
                            <span class="badge <?= $badgeClass ?>" style="font-size:0.68rem; padding: 3px 8px; font-weight:700; text-transform:uppercase;"><?= htmlspecialchars($status) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($observacoes !== ''): ?>
                <div style="background:#fff7ed; color:#ea580c; border:1px solid #ffedd5; padding: 12px 16px; border-radius: 8px; font-size: 0.86rem; display:flex; align-items:flex-start; gap:8px;">
                    <i data-lucide="info" style="width:18px; height:18px; flex-shrink:0; margin-top:2px;"></i>
                    <div>
                        <strong style="display:block; margin-bottom:2px;">Observações do Aparelho:</strong>
                        <span style="color:#475569;"><?= nl2br(htmlspecialchars($observacoes)) ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($os['diagnostico_tecnico'])): ?>
            <div style="margin-bottom:1.25rem;">
                <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:6px;">Diagnostico Tecnico</p>
                <div class="alert-box alert-yellow" style="margin:0;"><?= nl2br(htmlspecialchars($os['diagnostico_tecnico'])) ?></div>
            </div>
            <?php endif; ?>
            <?php if(!empty($os['servico_realizar'])): ?>
            <div>
                <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:6px;">Servico a Realizar</p>
                <div class="alert-box alert-green" style="margin:0;"><?= nl2br(htmlspecialchars($os['servico_realizar'])) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Valores -->
        <div class="card">
            <h3 class="brand-font" style="margin-bottom:1.25rem;">Orcamento e Pagamento</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                <div>
                    <table style="width:100%;">
                        <?php $rows=[['Mao de Obra',$os['valor_mao_obra']],['Pecas',$os['valor_pecas']],['Desconto',-($os['desconto'] ?? 0)]]; ?>
                        <?php foreach($rows as [$l,$v]): ?>
                        <tr>
                            <td style="padding:6px 0;color:var(--text-muted);font-size:.85rem;"><?= $l ?></td>
                            <td style="text-align:right;font-weight:500;">R$ <?= number_format($v, 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="border-top:2px solid var(--border);">
                            <td style="padding:10px 0 0;font-weight:700;">TOTAL</td>
                            <td style="text-align:right;font-weight:700;font-size:1.2rem;color:var(--secondary);padding-top:10px;">R$ <?= number_format($os['valor_total'] ?? 0, 2, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
                <div>
                    <p style="font-size:.75rem;color:var(--text-muted);margin-bottom:8px;">Pagamento</p>
                    <p style="font-weight:600;"><?= e($os['forma_pagamento'] ?: '-') ?></p>
                    <?php $spay = $os['situacao_pagamento'] ?? 'Pendente'; $cpay = $spay === 'Pago' ? 'badge-green' : ($spay === 'Parcial' ? 'badge-yellow' : 'badge-red'); ?>
                    <span class="badge <?= e($cpay) ?>" style="margin-top:8px;"><?= e($spay) ?></span>
                    <div class="os-payment-summary">
                        <div class="os-payment-row"><span>Pago</span><strong class="os-payment-value" style="color:var(--success);">R$ <?= number_format($totalPago, 2, ',', '.') ?></strong></div>
                        <div class="os-payment-row"><span>Restante</span><strong class="os-payment-value" style="color:<?= $saldoRestante > 0 ? 'var(--danger)' : 'var(--success)' ?>;">R$ <?= number_format($saldoRestante, 2, ',', '.') ?></strong></div>
                    </div>
                </div>
            </div>
            <?php if (!empty($pagamentos)): ?>
            <div style="border-top:1px solid var(--border);padding-top:1rem;">
                <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.65rem;">Pagamentos registrados</p>
                <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;">
                    <table style="width:100%;border-collapse:collapse;font-size:.86rem;">
                        <thead style="background:#f8fafc;"><tr><th style="text-align:left;padding:.7rem;">Data</th><th style="text-align:left;padding:.7rem;">Forma</th><th style="text-align:left;padding:.7rem;">Observacao</th><th style="text-align:right;padding:.7rem;">Valor</th></tr></thead>
                        <tbody>
                        <?php foreach ($pagamentos as $pagamento): ?>
                            <tr style="border-top:1px solid var(--border);">
                                <td style="padding:.7rem;"><?= date('d/m/Y', strtotime($pagamento['data_pagamento'])) ?></td>
                                <td style="padding:.7rem;"><?= htmlspecialchars($pagamento['forma_pagamento'] ?: '-') ?></td>
                                <td style="padding:.7rem;"><?= htmlspecialchars($pagamento['observacao'] ?: '-') ?></td>
                                <td style="padding:.7rem;text-align:right;font-weight:700;color:var(--success);">R$ <?= number_format((float) $pagamento['valor'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Fotos do Aparelho -->
        <?php if(!empty($os['fotos'])): ?>
        <div class="card">
            <h3 class="brand-font" style="margin-bottom:1.25rem;"><i data-lucide="camera" style="width:20px;vertical-align:middle;margin-right:8px;"></i> Fotos do Aparelho (Entrada)</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:1rem;">
                <?php foreach(json_decode($os['fotos'], true) as $foto): ?>
                <?php $foto = basename(str_replace('\\', '/', (string) $foto)); ?>
                <a href="<?= e(app_url('uploads/os/' . $foto)) ?>" target="_blank" style="border-radius:12px;overflow:hidden;border:1px solid var(--border);display:block;">
                    <img src="<?= e(app_url('uploads/os/' . $foto)) ?>" style="width:100%;height:150px;object-fit:cover;transition:.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($os['fotos_saida'])): ?>
        <div class="card">
            <h3 class="brand-font" style="margin-bottom:1.25rem;"><i data-lucide="image-up" style="width:20px;vertical-align:middle;margin-right:8px;"></i> Fotos do Aparelho (Saida)</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:1rem;">
                <?php foreach(json_decode($os['fotos_saida'], true) as $foto): ?>
                <?php $foto = basename(str_replace('\\', '/', (string) $foto)); ?>
                <a href="<?= e(app_url('uploads/os/' . $foto)) ?>" target="_blank" style="border-radius:12px;overflow:hidden;border:1px solid var(--border);display:block;">
                    <img src="<?= e(app_url('uploads/os/' . $foto)) ?>" style="width:100%;height:150px;object-fit:cover;transition:.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Timeline e Acoes -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        <div class="card">
            <h3 class="brand-font" style="margin-bottom:1.25rem;">Linha do Tempo</h3>
            <div style="position:relative;padding-left:24px;">
                <div style="position:absolute;left:8px;top:0;bottom:0;width:2px;background:var(--border);"></div>
                <?php if(empty($historico)): ?>
                <p style="font-size:.85rem;color:var(--text-muted);">Sem movimentacoes registradas.</p>
                <?php else: ?>
                <?php foreach($historico as $h): ?>
                <div style="position:relative;margin-bottom:1.25rem;">
                    <div style="position:absolute;left:-20px;top:4px;width:12px;height:12px;border-radius:50%;background:var(--secondary);border:2px solid white;box-shadow:0 0 0 1px var(--secondary);"></div>
                    <p style="font-weight:600;font-size:.85rem;"><?= htmlspecialchars($h['status_novo'] ?: 'OS Criada') ?></p>
                    <p style="font-size:.75rem;color:var(--text-muted);"><?= e(date('d/m/Y H:i', strtotime($h['created_at']))) ?> - <?= e($h['usuario_nome'] ?? 'Sistema') ?></p>
                    <?php if($h['observacao']): ?><p style="font-size:.78rem;color:var(--text-muted);background:var(--bg-main);padding:6px 10px;border-radius:6px;margin-top:4px;"><?= htmlspecialchars($h['observacao']) ?></p><?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h3 class="brand-font" style="margin-bottom:1rem;"><i data-lucide="banknote" style="width:20px;vertical-align:middle;margin-right:6px;"></i> Receber Pagamento</h3>
            <?php $pointReady = !empty($pointSettings['mercadopago_access_token']) && !empty($pointSettings['mercadopago_point_terminal_id']); ?>
            <?php if ($saldoRestante <= 0): ?>
                <div class="alert-box alert-green" style="font-size:.85rem;">Esta OS ja esta quitada.</div>
            <?php else: ?>
                <div class="form-group">
                    <label class="form-label">Valor a receber</label>
                    <input type="text" id="os_pagamento_valor" class="form-control" value="<?= e(number_format($saldoRestante, 2, ',', '.')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Forma de Pagamento</label>
                    <select id="os_forma_pagamento" class="form-control" onchange="osAtualizarFormaPagamento()">
                        <option value="Pix">Pix</option>
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="Cartao de Debito" selected>Cartao de Debito</option>
                        <option value="QR Mercado Pago">QR Mercado Pago</option>
                        <option value="Saldo Mercado Pago">Saldo Mercado Pago</option>
                        <option value="Cartao de Credito na hora">Cartao de Credito na hora</option>
                        <option value="Cartao de Credito 14 dias">Cartao de Credito 14 dias</option>
                        <option value="Cartao de Credito 30 dias">Cartao de Credito 30 dias</option>
                        <?php for ($parcelas = 2; $parcelas <= 12; $parcelas++): ?>
                        <option value="Cartao de Credito <?= $parcelas ?>x">Cartao de Credito <?= $parcelas ?>x</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Botão Smart Point (para cartão/QR/Saldo) -->
                <div id="os_point_section" style="display:none;">
                    <?php if (!$pointReady): ?>
                        <div class="alert-box alert-yellow" style="font-size:.85rem;margin-bottom:1rem;">
                            Configure o Access Token e o Terminal ID em Configuracoes > Pagamentos.
                        </div>
                    <?php else: ?>
                        <form action="<?= e(route_url('os/receberPoint')) ?>" method="POST" id="os_point_form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $os['id'] ?>">
                            <input type="hidden" name="valor" id="os_point_valor">
                            <input type="hidden" name="payment_type" id="os_point_payment_type" value="debit_card">
                            <input type="hidden" name="installments" id="os_point_installments" value="1">
                            <button type="submit" class="btn btn-primary" style="width:100%;">
                                <i data-lucide="credit-card"></i> Enviar Smart Point
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Botão Registrar Manual (para Pix/Dinheiro) -->
                <div id="os_manual_section" style="display:none;">
                    <form action="<?= e(route_url('os/registrarPagamento')) ?>" method="POST" id="os_manual_form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $os['id'] ?>">
                        <input type="hidden" name="payment_nonce" value="<?= e($paymentNonce) ?>">
                        <input type="hidden" name="valor" id="os_manual_valor">
                        <input type="hidden" name="forma_pagamento" id="os_manual_forma">
                        <div class="form-group" style="margin-bottom:.75rem;">
                            <label class="form-label">Observacao (opcional)</label>
                            <input type="text" name="observacao" class="form-control" placeholder="Ex: Pix recebido pelo Nubank">
                        </div>
                        <button type="submit" class="btn btn-success" style="width:100%;" data-payment-submit>
                            <i data-lucide="check-circle"></i> Registrar Pagamento
                        </button>
                    </form>
                </div>

                <script>
                function osFormaUsaPoint(forma) {
                    forma = forma.toLowerCase();
                    return forma.includes('cartao') || forma.includes('credito') || forma.includes('debito')
                        || forma.includes('qr mercado') || forma.includes('saldo mercado');
                }
                function osAtualizarFormaPagamento() {
                    const forma = document.getElementById('os_forma_pagamento').value;
                    const valor = document.getElementById('os_pagamento_valor').value;
                    const usaPoint = osFormaUsaPoint(forma);
                    document.getElementById('os_point_section').style.display = usaPoint ? 'block' : 'none';
                    document.getElementById('os_manual_section').style.display = usaPoint ? 'none' : 'block';

                    if (usaPoint) {
                        document.getElementById('os_point_valor').value = valor;
                        // Mapear forma para payment_type da API
                        let paymentType = 'debit_card';
                        let installments = 1;
                        const fl = forma.toLowerCase();
                        if (fl.includes('credito') || fl.includes('crédito')) {
                            paymentType = 'credit_card';
                            const match = fl.match(/(\d+)x/);
                            if (match) installments = parseInt(match[1]);
                        }
                        document.getElementById('os_point_payment_type').value = paymentType;
                        document.getElementById('os_point_installments').value = installments;
                    } else {
                        document.getElementById('os_manual_valor').value = valor;
                        document.getElementById('os_manual_forma').value = forma;
                    }
                }
                // Atualizar ao mudar o valor também
                document.getElementById('os_pagamento_valor').addEventListener('input', osAtualizarFormaPagamento);
                document.getElementById('os_manual_form').addEventListener('submit', function () {
                    const button = this.querySelector('[data-payment-submit]');
                    if (button) { button.disabled = true; button.textContent = 'Processando...'; }
                });
                // Inicializar
                osAtualizarFormaPagamento();
                </script>
            <?php endif; ?>

            <?php if (!empty($pointOrder)): ?>
                <div class="point-status-box">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;margin-bottom:.35rem;">
                        <span style="color:var(--text-muted);">Ultima cobranca</span>
                        <strong><?= e($pointOrder['status'] ?? '-') ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;gap:.75rem;margin-bottom:.35rem;">
                        <span style="color:var(--text-muted);">Valor</span>
                        <strong>R$ <?= number_format((float) ($pointOrder['amount'] ?? 0), 2, ',', '.') ?></strong>
                    </div>
                    <div style="color:var(--text-muted);overflow-wrap:anywhere;">
                        <?= e($pointOrder['mp_order_id'] ?: $pointOrder['external_reference']) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Alterar Status Rapido -->
        <div class="card">
            <h3 class="brand-font" style="margin-bottom:1rem;">Alterar Status</h3>
            <form action="<?= e(route_url('os/update')) ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $os['id'] ?>">
                <input type="hidden" name="tecnico_id" value="<?= (int) ($os['tecnico_id'] ?? 0) ?>">
                <input type="hidden" name="diagnostico_tecnico" value="<?= e($os['diagnostico_tecnico'] ?? '') ?>">
                <input type="hidden" name="servico_realizar" value="<?= e($os['servico_realizar'] ?? '') ?>">
                <input type="hidden" name="prioridade" value="<?= e($os['prioridade'] ?? 'Normal') ?>">
                <input type="hidden" name="valor_mao_obra" value="<?= e($os['valor_mao_obra'] ?? 0) ?>">
                <input type="hidden" name="valor_pecas" value="<?= e($os['valor_pecas'] ?? 0) ?>">
                <input type="hidden" name="desconto" value="<?= e($os['desconto'] ?? 0) ?>">
                <input type="hidden" name="prazo_estimado" value="<?= e($os['prazo_estimado'] ?? '') ?>">
                <input type="hidden" name="forma_pagamento" value="<?= e($os['forma_pagamento'] ?? '') ?>">
                <select name="status" class="form-control" style="margin-bottom:.75rem;">
                    <?php foreach($status_list as $s): ?>
                        <?php $statusOptionLabel = trim((string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $s)) ?: (string) $s; ?>
                        <option value="<?= e($s) ?>" <?= ($os['status'] ?? '') === $s ? 'selected' : '' ?>><?= e($statusOptionLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="obs_interna" class="form-control" rows="2" placeholder="Observacao interna (opcional)..." style="margin-bottom:.75rem;font-size:.85rem;"></textarea>
                <button type="submit" class="btn btn-primary" style="width:100%;">Atualizar Status</button>
            </form>
        </div>
        
        <!-- Espaçador para o select abrir para baixo -->
        <div style="min-height: 250px;"></div>
    </div>
</div>

<div id="point-success-modal" class="point-success-modal" aria-hidden="true" onclick="fecharPointSuccessModal(true, event)">
    <div class="point-success-dialog" role="dialog" aria-modal="true" aria-labelledby="point-success-title" aria-describedby="point-success-message" onclick="event.stopPropagation()">
        <img src="<?= app_url('assets/img/logo.png') ?>" alt="Logo" class="point-success-logo">
        <div class="point-success-slogan">Você conectado sempre</div>
        
        <div class="point-success-icon">
            <i data-lucide="check"></i>
        </div>
        <h2 id="point-success-title">Pagamento aprovado</h2>
        <p id="point-success-message">O pagamento na Smart Point foi efetuado com sucesso.</p>
        <button type="button" class="btn btn-primary" onclick="fecharPointSuccessModal(true)">
            <i data-lucide="refresh-cw"></i> Atualizar OS
        </button>
    </div>
</div>

<script>
<?php if (!empty($pointOrder) && !in_array($pointOrder['status'] ?? '', ['paid', 'approved', 'finished', 'processed'])): ?>
    const pointApprovedStatuses = new Set(['paid', 'approved', 'finished', 'processed']);
    const pointStatusUrl = <?= json_attr(route_url('os/pointStatus', ['id' => (int) $os['id']])) ?>;
    let pointPollCount = 0;
    let pointModalShown = false;

    function abrirPointSuccessModal(data) {
        if (pointModalShown) {
            return;
        }

        pointModalShown = true;
        const modal = document.getElementById('point-success-modal');
        const message = document.getElementById('point-success-message');
        if (message && data && data.payment_id) {
            message.textContent = 'Pagamento confirmado com sucesso. Codigo do pagamento: ' + data.payment_id + '.';
        }
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function fecharPointSuccessModal(reload, event) {
        if (event && event.target && event.target.id !== 'point-success-modal') {
            return;
        }

        const modal = document.getElementById('point-success-modal');
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        if (reload) {
            window.location.reload();
        }
    }

    function consultarPointStatus() {
        fetch(pointStatusUrl, {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Status HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const status = String(data.status || '').toLowerCase();
                if (data.paid || pointApprovedStatuses.has(status)) {
                    clearInterval(pointInterval);
                    abrirPointSuccessModal(data);
                }
            })
            .catch(err => console.error('Falha ao consultar Smart Point:', err));

        pointPollCount++;
        if (pointPollCount > 120) {
            clearInterval(pointInterval);
        }
    }

    const pointInterval = setInterval(consultarPointStatus, 5000);
    consultarPointStatus();

    document.addEventListener('keydown', (event) => {
        if (!pointModalShown || !['Escape', 'Enter'].includes(event.key)) {
            return;
        }
        fecharPointSuccessModal(true);
    });
<?php endif; ?>
</script>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
