<?php require_once dirname(__DIR__) . '/layout/header.php';
$statusColors = ['Recebido'=>'badge-gray','Em análise'=>'badge-blue','Aguardando aprovação'=>'badge-yellow','Aprovado'=>'badge-purple','Reprovado'=>'badge-red','Em reparo'=>'badge-blue','Aguardando peça'=>'badge-yellow','Pronto'=>'badge-green','Entregue'=>'badge-green','Cancelado'=>'badge-red'];
$statusIcons = ['Recebido'=>'inbox','Em análise'=>'search','Aguardando aprovação'=>'clock','Aprovado'=>'thumbs-up','Reprovado'=>'thumbs-down','Em reparo'=>'wrench','Aguardando peça'=>'package','Pronto'=>'check-circle','Entregue'=>'truck','Cancelado'=>'x-circle'];
$pagamentos = $pagamentos ?? [];
$totalPago = round((float) ($totalPago ?? 0), 2);
$saldoRestante = round(max(0, (float) ($os['valor_total'] ?? 0) - $totalPago), 2);
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
</style>

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
        $emojiRocket = html_entity_decode('&#x1F680;', ENT_QUOTES, 'UTF-8');
        $emojiPhone = html_entity_decode('&#x1F4F1;', ENT_QUOTES, 'UTF-8');
        $emojiWrench = html_entity_decode('&#x1F527;', ENT_QUOTES, 'UTF-8');
        $emojiStatus = html_entity_decode('&#x1F4CA;', ENT_QUOTES, 'UTF-8');
        $emojiMoney = html_entity_decode('&#x1F4B0;', ENT_QUOTES, 'UTF-8');
        $wpp_msg = "{$emojiRocket} *ORÇAMENTO / ORDEM DE SERVIÇO #{$os['numero_os']}* - *CONECTADOS*\n\n" .
                   "{$emojiPhone} *Aparelho:* {$os['marca']} {$os['modelo']}\n" .
                   "{$emojiWrench} *Problema:* {$os['problema_relatado']}\n" .
                   "{$emojiStatus} *Status:* {$os['status']}\n";
        if($os['valor_total'] > 0) $wpp_msg .= "{$emojiMoney} *Valor:* R$ " . number_format($os['valor_total'], 2, ',', '.') . "\n";
        $wpp_msg .= "\n_Qualquer dúvida, estamos à disposição!_";
        ?>
        <a href="https://wa.me/55<?= preg_replace('/\D/','',$os['cliente_whatsapp']??'') ?>?text=<?= urlencode($wpp_msg) ?>" target="_blank" class="btn" style="background:#25D366;color:white;text-decoration:none;">
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
            <form method="POST" action="<?= e(route_url('os/delete')) ?>" data-confirm="Excluir a OS #<?= e($os['numero_os'] ?? '') ?>? Esta acao remove a ordem, historico, pagamentos e fotos anexadas.">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $os['id'] ?>">
                <button type="submit" class="btn" style="background:var(--danger);color:#fff;">
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
                    <span class="badge <?= e($statusColors[$os['status']] ?? 'badge-gray') ?>" style="font-size:.85rem;padding:6px 14px;"><?= e($os['status'] ?? '') ?></span>
                    <?php if($os['prioridade'] === 'Urgente'): ?>
                    <div style="margin-top:5px;font-size:.75rem;color:var(--danger);font-weight:600;">🚨 URGENTE</div>
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
                    <p style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:5px;">Técnico</p>
                    <p style="font-weight:600;"><?= e($os['tecnico_nome'] ?? 'Nao atribuido') ?></p>
                    <p style="font-size:.82rem;color:var(--text-muted);">Prazo: <?= $os['prazo_estimado'] ? date('d/m/Y', strtotime($os['prazo_estimado'])) : '—' ?></p>
                </div>
            </div>
        </div>

        <!-- Problema e Diagnóstico -->
        <div class="card">
            <h3 class="brand-font" style="margin-bottom:1.25rem;">Informações Técnicas</h3>
            <div style="margin-bottom:1.25rem;">
                <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:6px;">Problema Relatado pelo Cliente</p>
                <div class="alert-box alert-blue"><?= nl2br(htmlspecialchars($os['problema_relatado'] ?? '—')) ?></div>
            </div>
            <?php if(!empty($os['diagnostico_tecnico'])): ?>
            <div style="margin-bottom:1.25rem;">
                <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:6px;">Diagnóstico Técnico</p>
                <div class="alert-box alert-yellow"><?= nl2br(htmlspecialchars($os['diagnostico_tecnico'])) ?></div>
            </div>
            <?php endif; ?>
            <?php if(!empty($os['servico_realizar'])): ?>
            <div>
                <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:6px;">Serviço a Realizar</p>
                <div class="alert-box alert-green"><?= nl2br(htmlspecialchars($os['servico_realizar'])) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Valores -->
        <div class="card">
            <h3 class="brand-font" style="margin-bottom:1.25rem;">Orçamento e Pagamento</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                <div>
                    <table style="width:100%;">
                        <?php $rows=[['Mão de Obra',$os['valor_mao_obra']],['Peças',$os['valor_pecas']],['Desconto',-($os['desconto']??0)]]; ?>
                        <?php foreach($rows as [$l,$v]): ?>
                        <tr>
                            <td style="padding:6px 0;color:var(--text-muted);font-size:.85rem;"><?= $l ?></td>
                            <td style="text-align:right;font-weight:500;">R$ <?= number_format($v, 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="border-top:2px solid var(--border);">
                            <td style="padding:10px 0 0;font-weight:700;">TOTAL</td>
                            <td style="text-align:right;font-weight:700;font-size:1.2rem;color:var(--secondary);padding-top:10px;">R$ <?= number_format($os['valor_total']??0, 2, ',', '.') ?></td>
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
    </div>

    <!-- Timeline e Ações -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        <div class="card">
            <h3 class="brand-font" style="margin-bottom:1.25rem;">Linha do Tempo</h3>
            <div style="position:relative;padding-left:24px;">
                <div style="position:absolute;left:8px;top:0;bottom:0;width:2px;background:var(--border);"></div>
                <?php if(empty($historico)): ?>
                <p style="font-size:.85rem;color:var(--text-muted);">Sem movimentações registradas.</p>
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

        <!-- Alterar Status Rápido -->
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
                <input type="hidden" name="situacao_pagamento" value="<?= e($os['situacao_pagamento'] ?? 'Pendente') ?>">
                <select name="status" class="form-control" style="margin-bottom:.75rem;">
                    <?php foreach($status_list as $s): ?><option value="<?= e($s) ?>" <?= ($os['status'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
                </select>
                <textarea name="obs_interna" class="form-control" rows="2" placeholder="Observação interna (opcional)..." style="margin-bottom:.75rem;font-size:.85rem;"></textarea>
                <button type="submit" class="btn btn-primary" style="width:100%;">Atualizar Status</button>
            </form>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
