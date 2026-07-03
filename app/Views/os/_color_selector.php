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
        <span class="os-color-swatch" aria-hidden="true"></span>
    </div>
    <input type="text" class="form-control os-color-custom" placeholder="Digite a cor" value="<?= e($customColor) ?>" <?= $customColor === '' ? 'hidden' : '' ?> style="margin-top:.5rem;">
</div>

<style>
    .os-color-row { display:grid;grid-template-columns:minmax(0,1fr) 46px;gap:.5rem;align-items:center; }
    .os-color-swatch { width:46px;height:46px;border:1px solid var(--border);border-radius:8px;background:#f8fafc;box-shadow:inset 0 0 0 1px rgba(255,255,255,.35); }
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
