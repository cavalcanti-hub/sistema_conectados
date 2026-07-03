<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>

<div style="margin-bottom:1.5rem;">
    <a href="<?= e(route_url('os')) ?>" style="text-decoration:none;color:var(--text-muted);display:inline-flex;align-items:center;gap:5px;font-size:.85rem;">
        <i data-lucide="arrow-left" style="width:16px;"></i> Voltar para lista de OS
    </a>
</div>

<form action="<?= e(route_url('os/store')) ?>" method="POST" id="form-nova-os" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:2rem;">
        
        <div style="display:flex;flex-direction:column;gap:1.5rem;">
            <!-- Cliente e Aparelho -->
            <div class="card">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.5rem;">
                    <div style="width:28px;height:28px;background:var(--secondary);color:white;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">1</div>
                    <h3 class="brand-font">Cliente e Aparelho</h3>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                    <div class="form-group" style="grid-column:span 2;">
                        <label class="form-label">Cliente *</label>
                        <div style="display:flex;gap:10px;">
                            <select class="form-control" name="cliente_id" required>
                                <option value="">Selecione um cliente...</option>
                                <?php foreach($clientes as $c): ?>
                                <option value="<?= (int) $c['id'] ?>"><?= e($c['nome'] ?? '') ?> - <?= e($c['whatsapp'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                            <a href="<?= e(route_url('clientes/create')) ?>" class="btn btn-secondary" style="white-space:nowrap;text-decoration:none;"><i data-lucide="user-plus" style="width:16px;"></i> Novo</a>
                        </div>
                    </div>
                    <?php
                    $deviceValues = ['marca' => '', 'modelo' => ''];
                    require __DIR__ . '/_device_selector.php';
                    ?>
                    <div class="form-group"><label class="form-label">IMEI / Nº Série</label><input type="text" name="imei" class="form-control" placeholder="15 dígitos"></div>
                    <div class="form-group"><label class="form-label">Cor</label><input type="text" name="cor" class="form-control" placeholder="Preto, Branco, Gold..."></div>
                    <div class="form-group" style="grid-column:span 2;"><label class="form-label">Senha / Padrão (informada pelo cliente)</label><input type="text" name="senha_padrao" class="form-control" placeholder="PIN ou padrão de desbloqueio"></div>
                </div>
            </div>

            <!-- Checklist -->
            <div class="card">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.5rem;">
                    <div style="width:28px;height:28px;background:var(--secondary);color:white;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">2</div>
                    <h3 class="brand-font">Checklist de Recebimento</h3>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:.75rem;margin-bottom:1.25rem;">
                    <?php $checkItems = ['Liga/Desliga','Botão Volume','Tela/Touch OK','Câmera Frontal','Câmera Traseira','Microfone','Alto-falante','Wi-Fi / Bluetooth','Sinal Operadora','Conector de Carga','Face ID / Digital','Sensor de Proximidade']; ?>
                    <?php foreach($checkItems as $item): ?>
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:10px;border:1px solid var(--border);cursor:pointer;font-size:.82rem;transition:.2s;" onmouseover="this.style.borderColor='var(--secondary)'" onmouseout="this.style.borderColor='var(--border)'">
                        <input type="checkbox" name="checklist[]" value="<?= e($item) ?>" style="accent-color:var(--success);width:16px;height:16px;">
                        <?= e($item) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado Físico e Observações na Entrada</label>
                    <textarea name="estado_fisico" class="form-control" rows="3" placeholder="Descreva riscos, trincas, mau contato, etc..."></textarea>
                </div>
                <div class="form-group" style="margin-top:1.25rem;">
                    <label class="form-label" style="display:flex;align-items:center;gap:8px;">
                        <i data-lucide="camera" style="width:16px;"></i> Fotos do Aparelho (comprovar estado na entrada)
                    </label>
                    <input type="file" name="fotos[]" class="form-control" multiple accept="image/*" style="padding:10px;">
                    <p style="font-size:.7rem;color:var(--text-muted);margin-top:5px;">Você pode selecionar várias fotos simultaneamente.</p>
                </div>
            </div>

            <!-- Serviço e Orçamento -->
            <div class="card">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.5rem;">
                    <div style="width:28px;height:28px;background:var(--secondary);color:white;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">3</div>
                    <h3 class="brand-font">Defeito e Orçamento Inicial</h3>
                </div>
                <div class="form-group">
                    <label class="form-label">Defeito Relatado pelo Cliente *</label>
                    <textarea name="problema_relatado" class="form-control" rows="3" required placeholder="Descreva o que o cliente informou sobre o problema..."></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Mão de Obra (R$)</label>
                        <input type="number" step="0.01" name="valor_mao_obra" class="form-control" placeholder="0,00" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Valor Peças (R$)</label>
                        <input type="number" step="0.01" name="valor_pecas" class="form-control" placeholder="0,00" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prazo Estimado</label>
                        <input type="date" name="prazo_estimado" class="form-control" min="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel Lateral -->
        <div style="display:flex;flex-direction:column;gap:1.5rem;">
            <div class="card" style="position:sticky;top:20px;">
                <h3 class="brand-font" style="margin-bottom:1.25rem;">Configurações da OS</h3>
                <div class="form-group">
                    <label class="form-label">Prioridade</label>
                    <select name="prioridade" class="form-control">
                        <option value="Normal">Normal</option>
                        <option value="Alta">Alta</option>
                        <option value="Urgente">🚨 Urgente</option>
                        <option value="Baixa">Baixa</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label class="form-label">Técnico Responsável</label>
                    <select name="tecnico_id" class="form-control">
                        <option value="">Aguardando triagem...</option>
                        <?php foreach($tecnicos as $t): ?>
                        <option value="<?= (int) $t['id'] ?>"><?= e($t['nome'] ?? '') ?><?= !empty($t['especialidade']) ? ' (' . e($t['especialidade']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="background:#fef9c3;border:1px solid #fde047;border-radius:10px;padding:12px;margin-bottom:1.25rem;font-size:.82rem;color:#854d0e;">
                    <strong>💡 O número da OS é gerado automaticamente.</strong><br>A OS entrará com status "Recebido".
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:1rem;">
                    <i data-lucide="save"></i> Salvar e Abrir OS
                </button>
                <button type="button" class="btn btn-secondary" style="width:100%;margin-top:.75rem;" onclick="window.open('<?= route_url('os/print', ['id' => $os['id'] ?? '', 'print' => '80']) ?>', '_blank')">
                    <i data-lucide="printer"></i> Imprimir Entrada
                </button>
            </div>
        </div>

    </div>
</form>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
