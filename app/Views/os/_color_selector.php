<?php
$colorOptions = [
    'Preto' => '#111827',
    'Preto fosco' => '#1f2937',
    'Preto brilhante' => '#050505',
    'Branco' => '#f8fafc',
    'Prata' => '#cbd5e1',
    'Cinza' => '#6b7280',
    'Grafite' => '#374151',
    'Dourado' => '#d4af37',
    'Rose Gold' => '#b76e79',
    'Champagne' => '#f7e7ce',
    'Bege' => '#d6c4a1',
    'Azul' => '#2563eb',
    'Azul claro' => '#60a5fa',
    'Azul escuro' => '#1e3a8a',
    'Verde' => '#16a34a',
    'Verde menta' => '#86efac',
    'Verde oliva' => '#556b2f',
    'Vermelho' => '#dc2626',
    'Rosa' => '#f472b6',
    'Roxo' => '#7c3aed',
    'Lilas' => '#a78bfa',
    'Amarelo' => '#facc15',
    'Laranja' => '#f97316',
    'Marrom' => '#92400e',
    'Titanio natural' => '#b8b8ad',
    'Titanio branco' => '#e5e7eb',
    'Titanio preto' => '#27272a',
    'Titanio azul' => '#64748b',
    'Meia-noite' => '#1f2937',
    'Estelar' => '#f5f5dc',
    'Product Red' => '#b91c1c',
    'Gold' => '#d4af37',
    'Transparente' => '#f8fafc',
];

$currentColor = trim((string) ($colorValue ?? ''));
foreach ($colorOptions as $label => $_hex) {
    if ($currentColor !== '' && strcasecmp($currentColor, $label) === 0) {
        $currentColor = $label;
        break;
    }
}
$isKnownColor = $currentColor !== '' && array_key_exists($currentColor, $colorOptions);
$customColor = $currentColor !== '' && !$isKnownColor ? $currentColor : '';
?>

<div class="form-group os-color-field" data-color-selector>
    <label class="form-label">Cor</label>
    <input type="hidden" name="cor" class="os-color-value" value="<?= e($currentColor) ?>">
    <div class="os-color-row">
        <select class="form-control os-color-select">
            <option value="">Selecione a cor...</option>
            <?php foreach ($colorOptions as $label => $hex): ?>
                <option value="<?= e($label) ?>" data-color="<?= e($hex) ?>" <?= $currentColor === $label ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
            <option value="__custom__" <?= $customColor !== '' ? 'selected' : '' ?>>Outra cor...</option>
        </select>
        <span class="os-color-swatch" aria-hidden="true" hidden></span>
    </div>
    <input type="text" class="form-control os-color-custom" placeholder="Digite a cor" value="<?= e($customColor) ?>" <?= $customColor === '' ? 'hidden' : '' ?> style="margin-top:.5rem;">
</div>

<style>
    .os-color-row { display:grid;grid-template-columns:1fr;gap:.7rem;align-items:center; }
    .os-color-row.has-swatch { grid-template-columns:minmax(0,1fr) 48px; }
    .os-color-select {
        border-color: #cbd8ea;
        background:
            linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,250,252,.96)),
            var(--bg-card);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.9), 0 10px 22px -20px rgba(15,23,42,.65);
        font-weight: 650;
    }
    .os-color-select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 4px rgba(37,99,235,.11), inset 0 1px 0 rgba(255,255,255,.9);
    }
    .os-color-swatch {
        position: relative;
        width: 48px;
        height: 48px;
        border: 1px solid rgba(148,163,184,.45);
        border-radius: 14px;
        background: #f8fafc;
        box-shadow:
            inset 0 0 0 3px rgba(255,255,255,.72),
            inset 0 -12px 18px rgba(15,23,42,.12),
            0 12px 26px -18px rgba(15,23,42,.8);
        overflow: hidden;
    }
    .os-color-swatch::before {
        content: "";
        position: absolute;
        inset: 4px 5px auto 5px;
        height: 42%;
        border-radius: 999px;
        background: linear-gradient(180deg, rgba(255,255,255,.72), rgba(255,255,255,.08));
        pointer-events: none;
    }
    .os-color-swatch::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        border: 1px solid rgba(255,255,255,.55);
        pointer-events: none;
    }
    .os-color-swatch[hidden] { display:none; }
</style>

<script>
(function () {
    if (window.__osColorSelectorReady) {
        return;
    }
    window.__osColorSelectorReady = true;

    function initColorSelector(field) {
        const select = field.querySelector('.os-color-select');
        const hidden = field.querySelector('.os-color-value');
        const custom = field.querySelector('.os-color-custom');
        const swatch = field.querySelector('.os-color-swatch');
        const row = field.querySelector('.os-color-row');
        const form = field.closest('form');

        function currentValue() {
            if (select.value === '__custom__') {
                return custom.value.trim();
            }
            return select.value.trim();
        }

        function syncColor() {
            const selected = select.options[select.selectedIndex];
            const isCustom = select.value === '__custom__';
            custom.hidden = !isCustom;
            hidden.value = currentValue();

            const color = selected?.dataset?.color || '#f8fafc';
            const showSwatch = !isCustom && !!select.value;
            swatch.hidden = !showSwatch;
            row?.classList.toggle('has-swatch', showSwatch);
            swatch.style.background = isCustom ? '#f8fafc' : color;
            swatch.style.backgroundImage = isCustom
                ? 'linear-gradient(135deg, #f8fafc 0 45%, #cbd5e1 45% 55%, #f8fafc 55% 100%)'
                : 'none';
        }

        select.addEventListener('change', () => {
            syncColor();
            if (select.value === '__custom__') {
                custom.focus();
            }
        });

        custom.addEventListener('input', syncColor);
        form?.addEventListener('submit', syncColor);
        syncColor();
    }

    document.querySelectorAll('[data-color-selector]').forEach(initColorSelector);
})();
</script>
