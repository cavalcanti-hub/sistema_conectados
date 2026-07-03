<?php
$deviceBrandModels = is_array($deviceBrandModels ?? null) ? $deviceBrandModels : [];
$deviceValues = is_array($deviceValues ?? null) ? $deviceValues : [];
$currentBrand = trim((string) ($deviceValues['marca'] ?? ''));
$currentModel = trim((string) ($deviceValues['modelo'] ?? ''));

if ($currentBrand !== '' && !isset($deviceBrandModels[$currentBrand])) {
    $deviceBrandModels[$currentBrand] = [];
}

if ($currentBrand !== '' && $currentModel !== '' && !in_array($currentModel, $deviceBrandModels[$currentBrand] ?? [], true)) {
    $deviceBrandModels[$currentBrand][] = $currentModel;
}

$brandOptions = array_keys($deviceBrandModels);
natcasesort($brandOptions);
$brandOptions = array_values($brandOptions);
$modelOptions = $currentBrand !== '' ? ($deviceBrandModels[$currentBrand] ?? []) : [];
?>

<div class="form-group os-device-field" data-device-selector data-models="<?= e(json_encode($deviceBrandModels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>" data-save-url="<?= e(route_url('os/storeModelo')) ?>">
    <label class="form-label">Marca</label>
    <input type="hidden" name="marca" class="os-device-brand-value" value="<?= e($currentBrand) ?>">
    <select class="form-control os-device-brand-select">
        <option value="">Selecione a marca...</option>
        <?php foreach ($brandOptions as $brand): ?>
            <option value="<?= e($brand) ?>" <?= $currentBrand === $brand ? 'selected' : '' ?>><?= e($brand) ?></option>
        <?php endforeach; ?>
        <option value="__custom__">Outra marca...</option>
    </select>
    <input type="text" class="form-control os-device-brand-custom" placeholder="Digite a marca" value="<?= e(!in_array($currentBrand, $brandOptions, true) ? $currentBrand : '') ?>" hidden style="margin-top:.5rem;">
</div>

<div class="form-group os-device-field" data-device-model-field>
    <label class="form-label">Modelo *</label>
    <div class="os-device-model-row">
        <select name="modelo" class="form-control os-device-model-select" required>
            <option value="">Selecione o modelo...</option>
            <?php foreach ($modelOptions as $model): ?>
                <option value="<?= e($model) ?>" <?= $currentModel === $model ? 'selected' : '' ?>><?= e($model) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="btn btn-secondary os-device-add-toggle" title="Cadastrar modelo" aria-label="Cadastrar modelo">
            <i data-lucide="plus" style="width:16px;"></i>
        </button>
    </div>
    <div class="os-device-new-model" hidden>
        <input type="text" class="form-control os-device-new-input" placeholder="Nome do modelo">
        <button type="button" class="btn btn-primary os-device-new-save">
            <i data-lucide="plus" style="width:16px;"></i> Cadastrar
        </button>
    </div>
</div>

<style>
    .os-device-model-row { display:grid;grid-template-columns:minmax(0,1fr) 46px;gap:.5rem;align-items:center; }
    .os-device-add-toggle { width:46px;height:46px;padding:0;display:inline-flex;align-items:center;justify-content:center; }
    .os-device-new-model { display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.5rem;margin-top:.65rem;align-items:center; }
    .os-device-new-model[hidden] { display:none; }
    .os-device-new-save { min-height:44px;white-space:nowrap; }
    @media(max-width:720px) {
        .os-device-new-model { grid-template-columns:1fr; }
        .os-device-new-save { width:100%; }
    }
</style>

<script>
(function () {
    if (window.__osDeviceSelectorReady) {
        return;
    }
    window.__osDeviceSelectorReady = true;

    function sortText(values) {
        return Array.from(new Set(values.filter(Boolean))).sort((a, b) => a.localeCompare(b, 'pt-BR', { numeric: true, sensitivity: 'base' }));
    }

    function postModel(saveUrl, csrfToken, brand, model) {
        const payload = new URLSearchParams();
        payload.set('_csrf_token', csrfToken || '');
        payload.set('marca', brand);
        payload.set('modelo', model);

        return fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: payload.toString()
        });
    }

    function initSelector(brandField) {
        const wrapper = brandField.closest('form') || document;
        const modelField = wrapper.querySelector('[data-device-model-field]');
        if (!modelField) {
            return;
        }

        let modelsByBrand = {};
        try {
            modelsByBrand = JSON.parse(brandField.dataset.models || '{}') || {};
        } catch (error) {
            modelsByBrand = {};
        }

        const saveUrl = brandField.dataset.saveUrl || '';
        const brandSelect = brandField.querySelector('.os-device-brand-select');
        const brandHidden = brandField.querySelector('.os-device-brand-value');
        const brandCustom = brandField.querySelector('.os-device-brand-custom');
        const modelSelect = modelField.querySelector('.os-device-model-select');
        const addToggle = modelField.querySelector('.os-device-add-toggle');
        const newPanel = modelField.querySelector('.os-device-new-model');
        const newInput = modelField.querySelector('.os-device-new-input');
        const newSave = modelField.querySelector('.os-device-new-save');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function currentBrand() {
            if (brandSelect.value === '__custom__') {
                return brandCustom.value.trim();
            }
            return brandSelect.value.trim();
        }

        function setBrandValue() {
            brandHidden.value = currentBrand();
        }

        function renderModels(selectedValue) {
            const brand = currentBrand();
            const models = sortText(modelsByBrand[brand] || []);
            const selected = selectedValue !== undefined ? selectedValue : modelSelect.value;
            modelSelect.innerHTML = '';

            const empty = document.createElement('option');
            empty.value = '';
            empty.textContent = brand ? 'Selecione o modelo...' : 'Selecione uma marca primeiro...';
            modelSelect.appendChild(empty);

            models.forEach((model) => {
                const option = document.createElement('option');
                option.value = model;
                option.textContent = model;
                modelSelect.appendChild(option);
            });

            if (selected && !models.includes(selected)) {
                const option = document.createElement('option');
                option.value = selected;
                option.textContent = selected;
                modelSelect.appendChild(option);
            }

            modelSelect.value = selected || '';
        }

        function showNewPanel() {
            setBrandValue();
            if (!brandHidden.value) {
                brandSelect.focus();
                return;
            }

            newPanel.hidden = false;
            newInput.value = '';
            newInput.focus();
        }

        function addModel() {
            setBrandValue();
            const brand = brandHidden.value;
            const model = newInput.value.trim();
            if (!brand || !model) {
                return;
            }

            if (!modelsByBrand[brand]) {
                modelsByBrand[brand] = [];
            }
            modelsByBrand[brand].push(model);
            modelsByBrand[brand] = sortText(modelsByBrand[brand]);
            renderModels(model);
            newPanel.hidden = true;

            if (saveUrl) {
                postModel(saveUrl, csrfToken, brand, model).catch(() => {});
            }
        }

        if (brandHidden.value && !Array.from(brandSelect.options).some((option) => option.value === brandHidden.value)) {
            brandSelect.value = '__custom__';
            brandCustom.hidden = false;
        }

        brandSelect.addEventListener('change', () => {
            const custom = brandSelect.value === '__custom__';
            brandCustom.hidden = !custom;
            if (custom) {
                brandCustom.focus();
            }
            setBrandValue();
            renderModels('');
        });

        brandCustom.addEventListener('input', () => {
            setBrandValue();
            renderModels('');
        });

        addToggle.addEventListener('click', showNewPanel);
        newSave.addEventListener('click', addModel);
        newInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                addModel();
            }
            if (event.key === 'Escape') {
                newPanel.hidden = true;
            }
        });

        wrapper.addEventListener('submit', setBrandValue);
        renderModels(modelSelect.value);
    }

    document.querySelectorAll('[data-device-selector]').forEach(initSelector);
})();
</script>
