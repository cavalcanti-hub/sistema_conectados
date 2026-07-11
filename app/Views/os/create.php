<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>

<style>
    .os-form-card { --form-card-accent: var(--secondary); border-left: 4px solid var(--form-card-accent) !important; }
    .os-form-card:nth-of-type(1) { --form-card-accent: #2563eb; }
    .os-form-card:nth-of-type(2) { --form-card-accent: #10b981; }
    .os-form-card:nth-of-type(3) { --form-card-accent: #f59e0b; }
    .os-side-card { --form-card-accent: #7c3aed; border-left: 4px solid var(--form-card-accent) !important; }
    .os-create-layout { display: flex; flex-direction: column; gap: .85rem; }
    .os-content-stack { display: flex; flex-direction: column; gap: .85rem; }
    .os-settings-bar { display: grid; grid-template-columns: minmax(170px, .7fr) minmax(190px, .95fr) minmax(260px, 1.2fr) max-content; gap: .8rem; align-items: end; }
    .os-settings-bar h3 { align-self: center; margin: 0; white-space: nowrap; }
    .os-settings-field { margin: 0 !important; }
    .os-settings-actions { display: flex; gap: .55rem; align-items: center; justify-content: flex-end; min-width: 0; }
    .os-settings-actions .btn { min-height: 38px; white-space: nowrap; }
    .receiving-checklist-card { border: 1px solid rgba(37, 99, 235, .14); box-shadow: 0 18px 40px -32px rgba(15, 23, 42, .55); }
    .receiving-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.4rem; }
    .receiving-title { display: flex; align-items: center; gap: .85rem; }
    .receiving-step { width: 36px; height: 36px; background: #2563eb; color: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .9rem; flex: 0 0 auto; }
    .receiving-title h3 { margin: 0; line-height: 1.1; }
    .receiving-title p { margin: .25rem 0 0; color: var(--text-muted); font-size: .82rem; }
    .receiving-badge { display: inline-flex; align-items: center; gap: .35rem; border: 1px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; border-radius: 999px; padding: .4rem .7rem; font-size: .75rem; font-weight: 700; white-space: nowrap; }
    .receiving-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: .75rem; margin-bottom: 1.3rem; }
    .chk-card { background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.6rem 0.75rem; display: flex; flex-direction: column; justify-content: space-between; gap: 0.6rem; transition: border-color 0.18s ease; }
    .chk-card:hover { border-color: #94a3b8; }
    .chk-header { display: flex; align-items: center; gap: 0.5rem; font-weight: 600; font-size: 0.82rem; color: var(--text-main); }
    .chk-header svg { width: 15px; height: 15px; color: #2563eb; flex-shrink: 0; }
    .chk-group { display: flex; background: #f1f5f9; border-radius: 6px; padding: 2px; }
    .chk-opt { flex: 1; text-align: center; }
    .chk-opt input { display: none; }
    .chk-opt span { 
        position: relative;
        display: block; 
        padding: 5px 8px; 
        font-size: 0.72rem; 
        font-weight: 700; 
        color: #64748b; 
        border-radius: 4px; 
        cursor: pointer; 
        transition: all 0.15s ease; 
        user-select: none; 
        text-transform: uppercase; 
        border: 1.2px solid transparent;
        overflow: hidden;
    }
    .chk-opt span::before {
        content: '';
        position: absolute;
        top: 1px;
        left: 2px;
        right: 2px;
        height: 40%;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.45) 0%, rgba(255, 255, 255, 0.05) 100%);
        border-radius: 3px 3px 1px 1px;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .chk-opt input:checked + span::before {
        opacity: 1;
    }
    .chk-opt input[value="OK"]:checked + span { 
        background: linear-gradient(180deg, #4ade80 0%, #16a34a 50%, #15803d 50%, #14532d 100%); 
        border-color: #22c55e;
        color: #ffffff; 
        box-shadow: 0 2px 6px rgba(22, 163, 74, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.3);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    }
    .chk-opt input[value="Defeito"]:checked + span { 
        background: linear-gradient(180deg, #f87171 0%, #dc2626 50%, #b91c1c 50%, #7f1d1d 100%); 
        border-color: #ef4444;
        color: #ffffff; 
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.3);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    }
    .chk-opt input[value="N/A"]:checked + span { 
        background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 50%, #e2e8f0 50%, #cbd5e1 100%); 
        border-color: #b4c6e7;
        color: #0f172a; 
        box-shadow: 0 2px 6px rgba(148, 163, 184, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }
    .receiving-details { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr); gap: 1rem; align-items: stretch; }
    .receiving-field { border: 1px solid #dbe4ef; border-radius: 8px; background: #f8fafc; padding: 1rem; }
    .receiving-field.form-group { margin: 0 !important; }
    .receiving-field .form-label { display: flex; align-items: center; gap: .5rem; margin-bottom: .65rem; }
    .receiving-field textarea { min-height: 132px; resize: vertical; background: #fff; }
    .receiving-upload { position: relative; min-height: 132px; display: flex; flex-direction: column; justify-content: center; gap: .75rem; border: 1.5px dashed #93c5fd; border-radius: 8px; background: #eff6ff; padding: 1rem; }
    .receiving-upload input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .receiving-upload-main { display: flex; align-items: center; gap: .8rem; color: #1e3a8a; font-weight: 800; }
    .receiving-upload-icon { width: 40px; height: 40px; border-radius: 8px; background: #dbeafe; color: #2563eb; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
    .receiving-upload small { color: #475569; line-height: 1.35; }
    .receiving-file-status { display: inline-flex; width: fit-content; max-width: 100%; border-radius: 999px; background: rgba(255, 255, 255, .86); border: 1px solid #bfdbfe; padding: .35rem .6rem; color: #1e40af; font-size: .76rem; font-weight: 700; }
    .receiving-checklist-card .form-group { border: 1px solid #dbe4ef; border-radius: 8px; background: #f8fafc; padding: 1rem; }
    .receiving-checklist-card .form-group + .form-group { margin-top: 1rem !important; }
    .receiving-checklist-card .form-label { font-weight: 800; }
    .receiving-checklist-card textarea.form-control { min-height: 132px; background: #fff; resize: vertical; }
    .receiving-photo-proxy { position: relative; min-height: 132px; display: flex; flex-direction: column; justify-content: center; gap: .75rem; border: 1.5px dashed #93c5fd; border-radius: 8px; background: #eff6ff; padding: 1rem; margin-top: .65rem; overflow: hidden; }
    .receiving-photo-proxy input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .receiving-photo-proxy-main { display: flex; align-items: center; gap: .8rem; color: #1e3a8a; font-weight: 800; }
    .receiving-photo-proxy-icon { width: 40px; height: 40px; border-radius: 8px; background: #dbeafe; color: #2563eb; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
    .receiving-photo-proxy small { color: #475569; line-height: 1.35; }
    @media (max-width: 1280px) { .os-settings-bar { grid-template-columns: minmax(180px, .8fr) minmax(220px, 1fr) minmax(320px, max-content); } .os-settings-bar h3 { grid-column: 1 / -1; } .os-settings-actions { justify-content: flex-end; } }
    @media (max-width: 980px) { .os-settings-bar { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: stretch; } .os-settings-bar h3 { grid-column: span 2; } .os-settings-actions { grid-column: span 2; justify-content: stretch; } .os-settings-actions .btn { flex: 1; } }
    @media (max-width: 980px) { .receiving-head { flex-direction: column; } .receiving-details { grid-template-columns: 1fr; } }
    @media (max-width: 720px) { .os-settings-bar { grid-template-columns: 1fr; } .os-settings-bar h3 { grid-column: auto; } .os-settings-actions { flex-direction: column; } .os-settings-actions .btn { width: 100%; } }
    @media (max-width: 640px) { .receiving-grid { grid-template-columns: 1fr; } }
    
    .btn-gel-blue {
        position: relative;
        background: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 50%, #1e40af 50%, #1e3a8a 100%);
        border: 1.5px solid #2563eb;
        border-radius: 30px;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 10px 24px;
        box-shadow: 0 4px 15px rgba(29, 78, 216, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.3);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.6);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
        overflow: hidden;
    }
    .btn-gel-blue::before {
        content: '';
        position: absolute;
        top: 1px;
        left: 2px;
        right: 2px;
        height: 40%;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.35) 0%, rgba(255, 255, 255, 0.05) 100%);
        border-radius: 30px 30px 10px 10px;
        pointer-events: none;
    }
    .btn-gel-blue:hover {
        background: linear-gradient(180deg, #60a5fa 0%, #2563eb 50%, #1d4ed8 50%, #1e40af 100%);
        box-shadow: 0 6px 18px rgba(29, 78, 216, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.4);
    }
    .btn-gel-blue:active {
        transform: translateY(1px);
        box-shadow: 0 2px 8px rgba(29, 78, 216, 0.3);
    }
    .btn-gel-white {
        position: relative;
        background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 50%, #e2e8f0 50%, #cbd5e1 100%);
        border: 1.5px solid #b4c6e7;
        border-radius: 30px;
        color: #0f172a;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 10px 24px;
        box-shadow: 0 4px 10px rgba(148, 163, 184, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.9);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
        overflow: hidden;
    }
    .btn-gel-white::before {
        content: '';
        position: absolute;
        top: 1px;
        left: 2px;
        right: 2px;
        height: 40%;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0.1) 100%);
        border-radius: 30px 30px 10px 10px;
        pointer-events: none;
    }
    .btn-gel-white:hover {
        background: linear-gradient(180deg, #ffffff 0%, #e2e8f0 50%, #cbd5e1 50%, #94a3b8 100%);
        box-shadow: 0 6px 14px rgba(148, 163, 184, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }
    .btn-gel-white:active {
        transform: translateY(1px);
        box-shadow: 0 2px 6px rgba(148, 163, 184, 0.2);
    }
</style>

<div style="margin-bottom:1.5rem;">
    <a href="<?= e(route_url('os')) ?>" style="text-decoration:none;color:var(--text-muted);display:inline-flex;align-items:center;gap:5px;font-size:.85rem;">
        <i data-lucide="arrow-left" style="width:16px;"></i> Voltar para lista de OS
    </a>
</div>

<form action="<?= e(route_url('os/store')) ?>" method="POST" id="form-nova-os" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="os-create-layout">
        <div class="card os-side-card">
            <div class="os-settings-bar">
                <h3 class="brand-font">Configuracoes da OS</h3>
                <div class="form-group os-settings-field">
                    <label class="form-label">Prioridade</label>
                    <select name="prioridade" class="form-control">
                        <option value="Normal">Normal</option>
                        <option value="Alta">Alta</option>
                        <option value="Urgente">Urgente</option>
                        <option value="Baixa">Baixa</option>
                    </select>
                </div>
                <div class="form-group os-settings-field">
                    <label class="form-label">Tecnico Responsavel</label>
                    <select name="tecnico_id" class="form-control">
                        <option value="">Aguardando triagem...</option>
                        <?php foreach($tecnicos as $t): ?>
                        <option value="<?= (int) $t['id'] ?>"><?= e($t['nome'] ?? '') ?><?= !empty($t['especialidade']) ? ' (' . e($t['especialidade']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="os-settings-actions">
                    <button type="submit" class="btn-gel-blue">
                        <i data-lucide="save"></i> Salvar e Abrir OS
                    </button>
                    <button type="button" class="btn-gel-white" onclick="window.open('<?= route_url('os/print', ['id' => $os['id'] ?? '', 'print' => '80']) ?>', '_blank')">
                        <i data-lucide="printer"></i> Imprimir Entrada
                    </button>
                </div>
            </div>
        </div>

        <div class="os-content-stack">
            <!-- Cliente e Aparelho -->
            <div class="card os-form-card">
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
                            <a href="<?= e(route_url('clientes/create')) ?>" class="btn-gel-white" style="white-space:nowrap;text-decoration:none;padding:8px 18px;font-size:0.85rem;"><i data-lucide="user-plus" style="width:16px;"></i> Novo</a>
                        </div>
                    </div>
                    <?php
                    $deviceValues = ['marca' => '', 'modelo' => ''];
                    require __DIR__ . '/_device_selector.php';
                    ?>
                    <div class="form-group"><label class="form-label">IMEI / No Serie</label><input type="text" name="imei" class="form-control" placeholder="15 digitos"></div>
                    <?php
                    $colorValue = '';
                    require __DIR__ . '/_color_selector.php';
                    ?>
                    <div class="form-group" style="grid-column:span 2;"><label class="form-label">Senha / Padrao (informada pelo cliente)</label><input type="text" name="senha_padrao" class="form-control" placeholder="PIN ou padrao de desbloqueio"></div>
                </div>
            </div>

            <!-- Checklist -->
            <div class="card os-form-card receiving-checklist-card">
                <div class="receiving-head">
                    <div class="receiving-title">
                        <div class="receiving-step">2</div>
                        <div>
                            <h3 class="brand-font">Checklist de Recebimento</h3>
                            <p>Registre os testes feitos na entrada e documente o estado do aparelho.</p>
                        </div>
                    </div>
                    <span class="receiving-badge"><i data-lucide="clipboard-check" style="width:15px;"></i> Entrada</span>
                </div>
                <div style="display:flex; justify-content:flex-end; margin-bottom: 0.75rem;">
                    <button type="button" class="btn-gel-white" id="btn-check-all-ok" style="font-size:0.75rem; padding: 6px 14px; min-height: auto;">
                        <i data-lucide="check-check" style="width:14px;height:14px;"></i> Marcar todos como OK
                    </button>
                </div>
                <div class="receiving-grid">
                    <?php 
                    $checkItems = [
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
                    <?php foreach($checkItems as $item => $icon): ?>
                    <div class="chk-card">
                        <div class="chk-header">
                            <i data-lucide="<?= $icon ?>"></i>
                            <span><?= e($item) ?></span>
                        </div>
                        <div class="chk-group">
                            <label class="chk-opt">
                                <input type="radio" name="checklist[<?= e($item) ?>]" value="OK">
                                <span>OK</span>
                            </label>
                            <label class="chk-opt">
                                <input type="radio" name="checklist[<?= e($item) ?>]" value="Defeito">
                                <span>Defeito</span>
                            </label>
                            <label class="chk-opt">
                                <input type="radio" name="checklist[<?= e($item) ?>]" value="N/A" checked>
                                <span>N/A</span>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado Fisico e Observacoes na Entrada</label>
                    <textarea name="estado_fisico" class="form-control" rows="3" placeholder="Descreva riscos, trincas, mau contato, etc..."></textarea>
                </div>
                <div class="form-group" style="margin-top:1.25rem;">
                    <label class="form-label" style="display:flex;align-items:center;gap:8px;">
                        <i data-lucide="camera" style="width:16px;"></i> Fotos do Aparelho (comprovar estado na entrada)
                    </label>
                    <input type="file" name="fotos[]" class="form-control" multiple accept="image/*" style="padding:10px;">
                    <p style="font-size:.7rem;color:var(--text-muted);margin-top:5px;">Voce pode selecionar varias fotos simultaneamente.</p>
                </div>
            </div>

            <!-- Servico e Orcamento -->
            <div class="card os-form-card">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.5rem;">
                    <div style="width:28px;height:28px;background:var(--secondary);color:white;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">3</div>
                    <h3 class="brand-font">Defeito e Orcamento Inicial</h3>
                </div>
                <div class="form-group">
                    <label class="form-label">Defeito Relatado pelo Cliente *</label>
                    <textarea name="problema_relatado" class="form-control" rows="3" required placeholder="Descreva o que o cliente informou sobre o problema..."></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Mao de Obra (R$)</label>
                        <input type="number" step="0.01" name="valor_mao_obra" class="form-control" placeholder="0,00" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Valor Pecas (R$)</label>
                        <input type="number" step="0.01" name="valor_pecas" class="form-control" placeholder="0,00" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prazo Estimado</label>
                        <input type="date" name="prazo_estimado" class="form-control" min="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checklistCard = document.querySelector('.receiving-checklist-card');
    if (checklistCard && !checklistCard.querySelector('.receiving-details')) {
        const groups = Array.from(checklistCard.querySelectorAll(':scope > .form-group'));
        if (groups.length >= 2) {
            const details = document.createElement('div');
            details.className = 'receiving-details';
            checklistCard.insertBefore(details, groups[0]);
            groups.slice(0, 2).forEach((group) => {
                group.classList.add('receiving-field');
                details.appendChild(group);
            });
        }
    }

    const fileInput = document.querySelector('.receiving-checklist-card input[type="file"][name="fotos[]"]');
    if (!fileInput || fileInput.closest('.receiving-photo-proxy')) {
        return;
    }

    const proxy = document.createElement('label');
    proxy.className = 'receiving-photo-proxy';
    proxy.innerHTML = `
        <span class="receiving-photo-proxy-main">
            <span class="receiving-photo-proxy-icon"><i data-lucide="image-plus" style="width:21px;"></i></span>
            Adicionar fotos da entrada
        </span>
        <small>Use fotos para comprovar tela, traseira, laterais, conectores e marcas de uso.</small>
        <span class="receiving-file-status" data-photo-status>Nenhuma foto selecionada</span>
    `;

    fileInput.parentNode.insertBefore(proxy, fileInput);
    proxy.appendChild(fileInput);

    const status = proxy.querySelector('[data-photo-status]');
    fileInput.addEventListener('change', () => {
        const count = fileInput.files ? fileInput.files.length : 0;
        status.textContent = count === 0
            ? 'Nenhuma foto selecionada'
            : `${count} foto${count > 1 ? 's' : ''} selecionada${count > 1 ? 's' : ''}`;
    });

    const btnAllOk = document.getElementById('btn-check-all-ok');
    if (btnAllOk) {
        btnAllOk.addEventListener('click', () => {
            document.querySelectorAll('.chk-group input[value="OK"]').forEach(radio => {
                radio.checked = true;
            });
        });
    }

    if (window.lucide) {
        window.lucide.createIcons();
    }
});
</script>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<style>
    .ts-wrapper { flex: 1; }
    .ts-control { border-radius: 8px; border: 1px solid #dbe4ef; padding: 10px 14px; font-family: 'Inter', sans-serif; font-size: .95rem; box-shadow: none; }
    .ts-control.focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const clienteSelect = document.querySelector('select[name="cliente_id"]');
    if (clienteSelect) {
        new TomSelect(clienteSelect, {
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            create: false,
            placeholder: "Digite para pesquisar um cliente...",
            load: function(query, callback) {
                if(!query.length || query.length < 2) return callback();
                fetch("<?= app_url('clientes/searchJson') ?>?q=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => {
                        callback(json);
                    }).catch(()=>{
                        callback();
                    });
            }
        });
    }
});
</script>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
