<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>
<?php
$pagamentos = $pagamentos ?? [];
$totalPago = (float) ($totalPago ?? 0);
$valorTotalOs = (float) ($os['valor_total'] ?? 0);
$saldoRestante = max(0, $valorTotalOs - $totalPago);
?>

<div style="margin-bottom:1.5rem;">
    <a href="<?= e(route_url('os')) ?>" style="text-decoration:none;color:var(--text-muted);font-size:.85rem;display:inline-flex;align-items:center;gap:5px;">
        <i data-lucide="arrow-left" style="width:16px;"></i> Voltar
    </a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:2rem;">
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        <div class="card">
            <h3 class="brand-font" style="margin-bottom:1.5rem;">Editar OS #<?= e($os['numero_os'] ?? '') ?></h3>
            <form action="<?= e(route_url('os/update')) ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $os['id'] ?>">
            
            <div style="background:var(--bg-main);padding:1.25rem;border-radius:12px;margin-bottom:1.5rem;border:1px solid var(--border);">
                <h4 class="brand-font" style="margin-bottom:1rem;color:var(--secondary);display:flex;align-items:center;gap:8px;">
                    <i data-lucide="smartphone" style="width:18px;"></i> Dados do Aparelho
                </h4>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                    <?php
                    $deviceValues = [
                        'marca' => $os['marca'] ?? '',
                        'modelo' => $os['modelo'] ?? '',
                    ];
                    require __DIR__ . '/_device_selector.php';
                    ?>
                    <div class="form-group"><label class="form-label">IMEI / Nº Série</label><input type="text" name="imei" class="form-control" value="<?= htmlspecialchars($os['imei']??'') ?>"></div>
                    <?php
                    $colorValue = $os['cor'] ?? '';
                    require __DIR__ . '/_color_selector.php';
                    ?>
                    <div class="form-group" style="grid-column:span 2;"><label class="form-label">Senha / Padrão</label><input type="text" name="senha_padrao" class="form-control" value="<?= htmlspecialchars($os['senha_padrao']??'') ?>"></div>
                    <div class="form-group" style="grid-column:span 2;"><label class="form-label">Estado Físico na Entrada</label><textarea name="estado_fisico" class="form-control" rows="2"><?= htmlspecialchars($os['estado_fisico']??'') ?></textarea></div>
                    <div class="form-group" style="grid-column:span 2;">
                        <label class="form-label"><i data-lucide="camera" style="width:16px;"></i> Adicionar mais fotos</label>
                        <input type="file" name="fotos[]" class="form-control" multiple accept="image/*" style="padding:10px;">
                        <?php if(!empty($os['fotos'])): ?>
                        <div style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;">
                            <?php foreach(json_decode($os['fotos'], true) as $foto): ?>
                            <div style="position:relative;">
                                <?php $foto = basename(str_replace('\\', '/', (string) $foto)); ?>
                                <img src="<?= e(app_url('uploads/os/' . $foto)) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group" style="grid-column:span 2;">
                        <label class="form-label"><i data-lucide="image-up" style="width:16px;"></i> Fotos de saida do aparelho</label>
                        <input type="file" name="fotos_saida[]" class="form-control" multiple accept="image/*" style="padding:10px;">
                        <p style="font-size:.75rem;color:var(--text-muted);margin-top:6px;">Registre o estado do aparelho antes da entrega ao cliente.</p>
                        <?php if(!empty($os['fotos_saida'])): ?>
                        <div style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;">
                            <?php foreach(json_decode($os['fotos_saida'], true) as $foto): ?>
                            <div style="position:relative;">
                                <?php $foto = basename(str_replace('\\', '/', (string) $foto)); ?>
                                <img src="<?= e(app_url('uploads/os/' . $foto)) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-control" required>
                        <?php foreach($status_list as $s): ?><option value="<?= e($s) ?>" <?= $os['status']===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Prioridade</label>
                    <select name="prioridade" class="form-control">
                        <?php foreach(['Normal','Alta','Urgente','Baixa'] as $p): ?><option value="<?= e($p) ?>" <?= $os['prioridade']===$p?'selected':'' ?>><?= e($p) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Técnico Responsável</label>
                    <select name="tecnico_id" class="form-control">
                        <option value="">Sem técnico</option>
                        <?php foreach($tecnicos as $t): ?><option value="<?= (int) $t['id'] ?>" <?= $os['tecnico_id']==$t['id']?'selected':'' ?>><?= e($t['nome'] ?? '') ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Diagnóstico Técnico</label>
                    <textarea name="diagnostico_tecnico" class="form-control" rows="3"><?= htmlspecialchars($os['diagnostico_tecnico']??'') ?></textarea>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Serviço a Realizar</label>
                    <textarea name="servico_realizar" class="form-control" rows="2"><?= htmlspecialchars($os['servico_realizar']??'') ?></textarea>
                </div>
                <div class="form-group"><label class="form-label">Mão de Obra (R$)</label><input type="number" step="0.01" name="valor_mao_obra" class="form-control" value="<?= e($os['valor_mao_obra'] ?? 0) ?>"></div>
                <div class="form-group"><label class="form-label">Peças (R$)</label><input type="number" step="0.01" name="valor_pecas" class="form-control" value="<?= e($os['valor_pecas'] ?? 0) ?>"></div>
                <div class="form-group"><label class="form-label">Desconto (R$)</label><input type="number" step="0.01" name="desconto" class="form-control" value="<?= e($os['desconto'] ?? 0) ?>"></div>
                <div class="form-group"><label class="form-label">Prazo de Entrega</label><input type="date" name="prazo_estimado" class="form-control" value="<?= e($os['prazo_estimado'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Forma de Pagamento</label>
                    <select name="forma_pagamento" class="form-control">
                        <?php foreach(['Dinheiro','Pix','Cartao de Debito','QR Mercado Pago','Saldo Mercado Pago','Cartao de Credito na hora','Cartao de Credito 14 dias','Cartao de Credito 30 dias','Transferencia'] as $f): ?><option value="<?= e($f) ?>" <?= ($os['forma_pagamento']??'')===$f?'selected':'' ?>><?= e($f) ?></option><?php endforeach; ?>
                        <?php for ($parcelas = 2; $parcelas <= 12; $parcelas++): ?>
                            <?php $f = 'Cartao de Credito ' . $parcelas . 'x'; ?>
                            <option value="<?= e($f) ?>" <?= ($os['forma_pagamento']??'')===$f?'selected':'' ?>><?= e($f) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Situação do Pagamento</label>
                    <select name="situacao_pagamento" class="form-control">
                        <?php foreach(['Pendente','Parcial','Pago'] as $sp): ?><option value="<?= e($sp) ?>" <?= ($os['situacao_pagamento']??'Pendente')===$sp?'selected':'' ?>><?= e($sp) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div style="grid-column:span 2;background:var(--bg-main);border:1px solid var(--border);border-radius:12px;padding:1rem;">
                    <h4 class="brand-font" style="margin-bottom:.85rem;color:var(--secondary);display:flex;align-items:center;gap:8px;">
                        <i data-lucide="wallet" style="width:18px;"></i> Pagamentos da OS
                    </h4>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1rem;">
                        <div style="background:white;border:1px solid var(--border);border-radius:10px;padding:.75rem;">
                            <p style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Total da OS</p>
                            <strong>R$ <?= number_format($valorTotalOs, 2, ',', '.') ?></strong>
                        </div>
                        <div style="background:white;border:1px solid var(--border);border-radius:10px;padding:.75rem;">
                            <p style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Pago</p>
                            <strong style="color:var(--success);">R$ <?= number_format($totalPago, 2, ',', '.') ?></strong>
                        </div>
                        <div style="background:white;border:1px solid var(--border);border-radius:10px;padding:.75rem;">
                            <p style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Restante</p>
                            <strong style="color:<?= $saldoRestante > 0 ? 'var(--danger)' : 'var(--success)' ?>;">R$ <?= number_format($saldoRestante, 2, ',', '.') ?></strong>
                        </div>
                    </div>
                    <?php if (!empty($pagamentos)): ?>
                    <div style="margin-bottom:1rem;border:1px solid var(--border);border-radius:10px;overflow:hidden;background:white;">
                        <table style="width:100%;border-collapse:collapse;font-size:.84rem;">
                            <thead style="background:#f8fafc;"><tr><th style="text-align:left;padding:.65rem;">Data</th><th style="text-align:left;padding:.65rem;">Forma</th><th style="text-align:right;padding:.65rem;">Valor</th></tr></thead>
                            <tbody>
                            <?php foreach ($pagamentos as $pagamento): ?>
                                <tr style="border-top:1px solid var(--border);">
                                    <td style="padding:.65rem;"><?= date('d/m/Y', strtotime($pagamento['data_pagamento'])) ?></td>
                                    <td style="padding:.65rem;"><?= htmlspecialchars($pagamento['forma_pagamento'] ?: '-') ?></td>
                                    <td style="padding:.65rem;text-align:right;font-weight:700;color:var(--success);">R$ <?= number_format((float) $pagamento['valor'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.85rem;">
                        <div class="form-group"><label class="form-label">Adicionar pagamento (R$)</label><input type="number" step="0.01" min="0" name="pagamento_valor" class="form-control" placeholder="0,00"></div>
                        <div class="form-group"><label class="form-label">Forma</label>
                            <select name="pagamento_forma" class="form-control">
                                <?php foreach(['Dinheiro','Pix','Cartao de Debito','QR Mercado Pago','Saldo Mercado Pago','Cartao de Credito na hora','Cartao de Credito 14 dias','Cartao de Credito 30 dias','Transferencia'] as $f): ?><option value="<?= e($f) ?>"><?= e($f) ?></option><?php endforeach; ?>
                                <?php for ($parcelas = 2; $parcelas <= 12; $parcelas++): ?>
                                    <option value="<?= e('Cartao de Credito ' . $parcelas . 'x') ?>"><?= e('Cartao de Credito ' . $parcelas . 'x') ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label">Data</label><input type="date" name="pagamento_data" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                        <div class="form-group" style="grid-column:span 3;"><label class="form-label">Observacao do pagamento</label><input type="text" name="pagamento_observacao" class="form-control" placeholder="Ex: entrada, segunda parcela, restante pago no Pix"></div>
                    </div>
                </div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Observação Interna</label><textarea name="obs_interna" class="form-control" rows="2" placeholder="Nota interna sobre esta alteração..."></textarea></div>
            </div>
            </div>
        </div>

        <!-- Painel Lateral -->
        <div style="display:flex;flex-direction:column;gap:1.5rem;">
            <div class="card" style="position:sticky;top:20px;">
                <h3 class="brand-font" style="margin-bottom:1.25rem;">Ações da OS</h3>
                <div style="background:rgba(0,52,154,0.05);border:1px solid var(--border);border-radius:12px;padding:15px;margin-bottom:1.5rem;">
                    <p style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:5px;">Protocolo</p>
                    <p style="font-weight:700;font-size:1.1rem;color:var(--primary);">#<?= e($os['numero_os'] ?? '') ?></p>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:1rem;">
                    <i data-lucide="save"></i> Salvar Alterações
                </button>
                
                <a href="<?= e(route_url('os/viewDetail', ['id' => (int) $os['id']])) ?>" class="btn" style="width:100%;margin-top:.75rem;border:1px solid var(--border);background:var(--bg-card);text-decoration:none;">
                    Cancelar
                </a>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid var(--border);">

                <button type="button" class="btn btn-secondary" style="width:100%;" onclick="window.open('<?= e(route_url('os/print', ['id' => (int) $os['id'], 'print' => '80'])) ?>', '_blank')">
                    <i data-lucide="printer"></i> Imprimir OS
                </button>
                <button type="button" class="btn btn-secondary" style="width:100%;margin-top:.75rem;" onclick="window.open('<?= e(route_url('os/print', ['id' => (int) $os['id'], 'print' => 'a4', 'detalhada' => 1])) ?>', '_blank')">
                    <i data-lucide="file-text"></i> OS Detalhada
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
