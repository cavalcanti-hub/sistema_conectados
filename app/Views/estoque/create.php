<?php
$item = is_array($item ?? null) ? $item : [];
$isEdit = !empty($item) && isset($item['id']);
$formAction = route_url('estoque/' . ($isEdit ? 'update' : 'store'));
$backUrl = route_url('estoque');
$categorias = array_values(array_unique(array_filter($categorias ?? [])));
require_once dirname(__DIR__) . '/layout/header.php'; ?>

<div style="margin-bottom:1.5rem;"><a href="<?= htmlspecialchars($backUrl) ?>" style="text-decoration:none;color:var(--text-muted);font-size:.85rem;display:inline-flex;align-items:center;gap:5px;"><i data-lucide="arrow-left" style="width:16px;"></i> Voltar</a></div>

<div><div class="card">
    <h3 class="brand-font" style="margin-bottom:1.5rem;"><?= $isEdit ? 'Editar Item: '.htmlspecialchars($item['nome'] ?? '') : 'Cadastrar Novo Item de Estoque' ?></h3>
    <form id="form-estoque" action="<?= e($formAction) ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if($isEdit): ?><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><?php endif; ?>

        <div class="form-group" style="margin-bottom:1.5rem;">
            <label class="form-label">Imagem do Item (Opcional)</label>
            <div style="display:flex;gap:15px;align-items:center;">
                <div id="imagem-preview-box" style="width:100px;height:100px;border:2px dashed var(--border);border-radius:12px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:var(--bg-main);">
                    <?php if($isEdit && !empty($item['imagem_url'])): ?>
                    <img id="imagem-preview" src="<?= htmlspecialchars($item['imagem_url']) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';document.getElementById('imagem-placeholder').style.display='block';lucide.createIcons();">
                    <?php else: ?>
                    <img id="imagem-preview" style="width:100%;height:100%;object-fit:cover;display:none;">
                    <i id="imagem-placeholder" data-lucide="image" style="color:var(--text-muted); opacity:0.3;"></i>
                    <?php endif; ?>
                </div>
                <?php if($isEdit && !empty($item['imagem_url'])): ?><i id="imagem-placeholder" data-lucide="image" style="display:none;"></i><?php endif; ?>
                <input type="file" name="imagem" id="imagem-input" class="form-control" accept="image/*">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
            <div class="form-group"><label class="form-label">Codigo Interno</label><input type="text" name="codigo_interno" class="form-control" value="<?= htmlspecialchars($item['codigo_interno']??'') ?>" placeholder="Ex: EST-001"></div>
            <div class="form-group"><label class="form-label">Uso do Item</label>
                <select name="tipo_item" class="form-control">
                    <option value="produto" <?= ($item['tipo'] ?? 'produto') === 'produto' ? 'selected' : '' ?>>Produto da loja</option>
                    <option value="peca" <?= ($item['tipo'] ?? '') === 'peca' ? 'selected' : '' ?>>Estoque tecnico / OS</option>
                </select>
            </div>
            <div class="form-group" style="grid-column:span 2;"><label class="form-label">Categoria</label>
                <select name="categoria" class="form-control">
                    <?php foreach($categorias as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= ($item['categoria']??'')===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="grid-column:span 2;"><label class="form-label">Nome do Item *</label><input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($item['nome']??'') ?>" placeholder="Ex: Tela iPhone 14, cola B7000, pelicula 3D"></div>
            <div class="form-group"><label class="form-label">Marca / Fabricante</label><input type="text" name="marca_compativel" class="form-control" value="<?= htmlspecialchars($item['marca_compativel']??'') ?>" placeholder="Apple, Samsung, generico..."></div>
            <div class="form-group"><label class="form-label">Modelo / Aplicacao</label><input type="text" name="modelo_compativel" class="form-control" value="<?= htmlspecialchars($item['modelo_compativel']??'') ?>" placeholder="iPhone 14 Pro, A10, uso geral..."></div>
            <div class="form-group"><label class="form-label">Quantidade Atual</label><input type="number" name="quantidade" class="form-control" value="<?= e($item['quantidade'] ?? 0) ?>" min="0" required></div>
            <div class="form-group"><label class="form-label">Estoque Minimo</label><input type="number" name="estoque_minimo" class="form-control" value="<?= e($item['estoque_minimo'] ?? 5) ?>" min="0" required></div>
            <div class="form-group"><label class="form-label">Custo Unitario (R$)</label><input type="number" step="0.01" name="custo" class="form-control" value="<?= e($item['custo'] ?? 0) ?>" min="0"></div>
            <div class="form-group"><label class="form-label">Preco de Venda (R$)</label><input type="number" step="0.01" name="preco_venda" class="form-control" value="<?= e($item['preco_venda'] ?? 0) ?>" min="0"></div>
            <div class="form-group"><label class="form-label">Fornecedor</label><input type="text" name="fornecedor" class="form-control" value="<?= htmlspecialchars($item['fornecedor']??'') ?>" placeholder="Nome do fornecedor"></div>
            <div class="form-group"><label class="form-label">Localizacao no Estoque</label><input type="text" name="localizacao" class="form-control" value="<?= htmlspecialchars($item['localizacao']??'') ?>" placeholder="Prateleira A3, gaveta 2..."></div>
        </div>
        <div style="display:flex;gap:1rem;margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary" style="flex:1;padding:12px;"><i data-lucide="save"></i> <?= $isEdit ? 'Salvar' : 'Cadastrar Item' ?></button>
            <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
<script>
(() => {
    const form = document.getElementById('form-estoque');
    const input = document.getElementById('imagem-input');
    const preview = document.getElementById('imagem-preview');
    const placeholder = document.getElementById('imagem-placeholder');

    if (!input || !preview || typeof FileReader === 'undefined') {
        if (form) {
            form.addEventListener('submit', () => {
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                }
            });
        }
        return;
    }

    input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file || !file.type.startsWith('image/')) {
            return;
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            preview.src = event.target && event.target.result ? event.target.result : '';
            preview.style.display = 'block';
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    });

    if (form) {
        form.addEventListener('submit', () => {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
            }
        });
    }
})();
</script>
</div></div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
