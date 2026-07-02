<?php
$isEdit = !empty($item) && isset($item['id']);
$categorias = array_values(array_unique(array_filter($categorias ?? [])));
$formAction = route_url('produtos/' . ($isEdit ? 'update' : 'store'));
$backUrl = route_url('produtos');
$successUrl = route_url('produtos', ['success' => 1]);
require_once dirname(__DIR__) . '/layout/header.php'; ?>

<div style="margin-bottom:1.5rem;"><a href="<?= htmlspecialchars($backUrl) ?>" style="text-decoration:none;color:var(--text-muted);font-size:.85rem;display:inline-flex;align-items:center;gap:5px;"><i data-lucide="arrow-left" style="width:16px;"></i> Voltar</a></div>

<div class="card">
    <h3 class="brand-font" style="margin-bottom:1.5rem;"><?= $isEdit ? 'Editar Produto: '.htmlspecialchars($item['nome']) : 'Cadastrar Novo Produto para Vitrine' ?></h3>
    <form id="produto-form" action="<?= htmlspecialchars($formAction) ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="_form_token" value="<?= htmlspecialchars($formToken ?? '') ?>">
        <?php if($isEdit): ?><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><?php endif; ?>
        
        <div style="display:grid;grid-template-columns:300px 1fr;gap:2rem;">
            <div>
                <label class="form-label">Foto do Produto (Principal)</label>
                <div id="produto-imagem-preview-box" style="width:100%;height:300px;border:2px dashed var(--border);border-radius:20px;display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;background:var(--bg-main);position:relative;">
                    <?php if($isEdit && !empty($item['imagem_url'])): ?>
                    <img id="produto-imagem-preview" src="<?= htmlspecialchars($item['imagem_url']) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';document.getElementById('produto-imagem-placeholder').style.display='block';document.getElementById('produto-imagem-help').style.display='block';lucide.createIcons();">
                    <?php else: ?>
                    <img id="produto-imagem-preview" style="width:100%;height:100%;object-fit:cover;display:none;">
                    <i id="produto-imagem-placeholder" data-lucide="image" style="color:var(--text-muted); opacity:0.3; width:48px;height:48px;"></i>
                    <p id="produto-imagem-help" style="font-size:.7rem;color:var(--text-muted);margin-top:10px;">Clique acima para escolher</p>
                    <?php endif; ?>
                    <?php if($isEdit && !empty($item['imagem_url'])): ?>
                    <i id="produto-imagem-placeholder" data-lucide="image" style="display:none;"></i>
                    <p id="produto-imagem-help" style="display:none;"></p>
                    <?php endif; ?>
                    <input id="produto-imagem-input" type="file" name="imagem" style="position:absolute;inset:0;opacity:0;cursor:pointer;" accept="image/jpeg,image/png,image/gif,image/webp">
                </div>
                <p style="font-size:.7rem;color:var(--text-muted);margin-top:10px;text-align:center;">Recomendado: Imagens quadradas (1:1)</p>

                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label">Fotos extras da galeria</label>
                    <label for="produto-galeria-input" style="display:flex;align-items:center;justify-content:center;gap:.5rem;border:1px dashed var(--border);border-radius:14px;padding:13px;background:#f8fafc;cursor:pointer;color:var(--text-muted);font-weight:700;">
                        <i data-lucide="images" style="width:18px;height:18px;"></i>
                        Adicionar fotos
                    </label>
                    <input id="produto-galeria-input" type="file" name="galeria_imagens[]" multiple accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;">
                    <small style="display:block;color:var(--text-muted);margin-top:.5rem;">Voce pode enviar ate 8 fotos extras por vez.</small>
                    <div id="produto-galeria-preview" style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-top:.75rem;"></div>
                </div>

                <?php if ($isEdit && !empty($galleryImages)): ?>
                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label">Galeria atual</label>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.65rem;">
                        <?php foreach ($galleryImages as $galleryImage): ?>
                        <label style="border:1px solid var(--border);border-radius:12px;padding:.45rem;background:white;display:grid;gap:.4rem;cursor:pointer;">
                            <img src="<?= htmlspecialchars($galleryImage['imagem_url']) ?>" alt="Foto extra do produto" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:8px;background:#f8fafc;">
                            <span style="display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:#b91c1c;font-weight:700;">
                                <input type="checkbox" name="remover_galeria[]" value="<?= (int) $galleryImage['id'] ?>">
                                Remover
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Nome Comercial do Produto *</label><input id="produto-nome-input" type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($item['nome']??'') ?>" placeholder="Ex: iPhone 14 Pro Max 256GB Grafite" autocomplete="off"></div>
                
                <div class="form-group"><label class="form-label">Categoria Comercial</label>
                    <select name="categoria" class="form-control">
                        <?php foreach($categorias as $c): ?>
                        <option value="<?= e($c) ?>" <?= ($item['categoria']??'')===$c?'selected':'' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Código (SKU)</label><input type="text" name="codigo_interno" class="form-control" value="<?= htmlspecialchars($item['codigo_interno']??'') ?>" placeholder="Opcional"></div>
                
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Breve Descrição (Opcional)</label><textarea name="localizacao" class="form-control" style="height:80px;"><?= htmlspecialchars($item['localizacao']??'') ?></textarea></div>
                
                <div class="form-group"><label class="form-label">Preço de Venda (R$) *</label><input type="number" step="0.01" name="preco_venda" class="form-control" required value="<?= e($item['preco_venda'] ?? 0) ?>"></div>
                <div class="form-group"><label class="form-label">Custo (Opcional)</label><input type="number" step="0.01" name="custo" class="form-control" value="<?= e($item['custo'] ?? 0) ?>"></div>
                
                <div class="form-group"><label class="form-label">Quantidade Inicial</label><input type="number" name="quantidade" class="form-control" value="<?= e($item['quantidade'] ?? 0) ?>"></div>
                <div class="form-group"><label class="form-label">Marca</label><input type="text" name="marca_compativel" class="form-control" value="<?= htmlspecialchars($item['marca_compativel']??'') ?>" placeholder="Samsung, Apple..."></div>
                <div class="form-group"><label class="form-label">Modelo</label><input type="text" name="modelo_compativel" class="form-control" value="<?= htmlspecialchars($item['modelo_compativel']??'') ?>" placeholder="iPhone 14, Galaxy A32..."></div>

                <div class="form-group">
                    <label class="form-label">Categoria Mercado Livre</label>
                    <input type="text" name="mercado_livre_category_id" class="form-control" value="<?= htmlspecialchars($item['mercado_livre_category_id'] ?? '') ?>" placeholder="Ex: MLB1714">
                    <small style="color:var(--text-muted);">Informe uma categoria final do Mercado Livre. Exemplos: mouse MLB1714, microfone para celular MLB61283.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de Anuncio</label>
                    <select name="mercado_livre_listing_type_id" class="form-control">
                        <?php $mlListing = $item['mercado_livre_listing_type_id'] ?? ''; ?>
                        <option value="" <?= $mlListing === '' ? 'selected' : '' ?>>Usar padrao</option>
                        <option value="gold_special" <?= $mlListing === 'gold_special' ? 'selected' : '' ?>>Classico</option>
                        <option value="gold_pro" <?= $mlListing === 'gold_pro' ? 'selected' : '' ?>>Premium</option>
                        <option value="free" <?= $mlListing === 'free' ? 'selected' : '' ?>>Gratis</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Condicao Mercado Livre</label>
                    <?php $mlCondition = $item['mercado_livre_condition'] ?? ''; ?>
                    <select name="mercado_livre_condition" class="form-control">
                        <option value="" <?= $mlCondition === '' ? 'selected' : '' ?>>Usar padrao</option>
                        <option value="new" <?= $mlCondition === 'new' ? 'selected' : '' ?>>Novo</option>
                        <option value="used" <?= $mlCondition === 'used' ? 'selected' : '' ?>>Usado</option>
                        <option value="not_specified" <?= $mlCondition === 'not_specified' ? 'selected' : '' ?>>Nao especificado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Anuncio Mercado Livre</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($item['mercado_livre_item_id'] ?? 'Ainda nao publicado') ?>" readonly>
                </div>
                <?php
                    $mlAttributesRaw = (string) ($item['mercado_livre_attributes'] ?? '');
                    $mlGtin = '';
                    $mlColor = '';
                    $mlAnatel = '';
                    $mlPartNumber = '';
                    $mlSaleFormat = '';
                    $mlWarrantyType = 'Sem garantia';
                    $mlAvailabilityDays = '0';
                    $mlAdvancedLines = [];
                    foreach (preg_split('/\r\n|\r|\n/u', $mlAttributesRaw) ?: [] as $mlAttributeLine) {
                        $mlAttributeLine = trim($mlAttributeLine);
                        if ($mlAttributeLine === '' || !preg_match('/^([A-Z0-9_:-]+)\s*[=:]\s*(.+)$/i', $mlAttributeLine, $mlAttributeMatch)) {
                            continue;
                        }
                        $mlAttributeId = strtoupper(trim($mlAttributeMatch[1]));
                        $mlAttributeValue = trim($mlAttributeMatch[2]);
                        if ($mlAttributeId === 'GTIN') {
                            $mlGtin = $mlAttributeValue;
                        } elseif ($mlAttributeId === 'COLOR') {
                            $mlColor = $mlAttributeValue;
                        } elseif ($mlAttributeId === 'ANATEL_HOMOLOGATION_NUMBER') {
                            $mlAnatel = $mlAttributeValue;
                        } elseif ($mlAttributeId === 'PART_NUMBER') {
                            $mlPartNumber = $mlAttributeValue;
                        } elseif ($mlAttributeId === 'SALE_FORMAT') {
                            $mlSaleFormat = $mlAttributeValue;
                        } elseif ($mlAttributeId === 'WARRANTY_TYPE') {
                            $mlWarrantyType = $mlAttributeValue;
                        } elseif ($mlAttributeId === 'MANUFACTURING_TIME') {
                            $mlAvailabilityDays = preg_replace('/\D+/', '', $mlAttributeValue) ?: '0';
                        } else {
                            $mlAdvancedLines[] = $mlAttributeId . '=' . $mlAttributeValue;
                        }
                    }
                ?>
                <div class="form-group">
                    <label class="form-label">Codigo de barras</label>
                    <input type="text" name="mercado_livre_gtin" class="form-control" value="<?= htmlspecialchars($mlGtin) ?>" placeholder="Opcional">
                    <small style="color:var(--text-muted);">EAN/GTIN da embalagem, se o produto tiver.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Cor principal</label>
                    <input type="text" name="mercado_livre_color" class="form-control" value="<?= htmlspecialchars($mlColor) ?>" placeholder="Ex: Preto">
                </div>
                <div class="form-group">
                    <label class="form-label">Homologacao Anatel No</label>
                    <input type="text" name="mercado_livre_anatel" class="form-control" value="<?= htmlspecialchars($mlAnatel) ?>" placeholder="Ex: 12345678901">
                    <small style="color:var(--text-muted);">Obrigatorio em algumas categorias de celulares, tablets e eletronicos.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Numero de peca</label>
                    <input type="text" name="mercado_livre_part_number" class="form-control" value="<?= htmlspecialchars($mlPartNumber) ?>" placeholder="Ex: KMF4-A">
                    <small style="color:var(--text-muted);">Campo exigido em algumas categorias, como microfones para celular.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Formato de venda</label>
                    <input type="text" name="mercado_livre_sale_format" class="form-control" value="<?= htmlspecialchars($mlSaleFormat) ?>" placeholder="Unidade">
                </div>
                <div class="form-group">
                    <label class="form-label">Garantia</label>
                    <select name="mercado_livre_warranty_type" class="form-control">
                        <?php foreach (['Sem garantia', 'Garantia do vendedor', 'Garantia de fabrica'] as $mlWarrantyOption): ?>
                        <option value="<?= htmlspecialchars($mlWarrantyOption) ?>" <?= $mlWarrantyType === $mlWarrantyOption ? 'selected' : '' ?>><?= htmlspecialchars($mlWarrantyOption) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Prazo para entrega</label>
                    <input type="number" min="0" name="mercado_livre_availability_days" class="form-control" value="<?= htmlspecialchars($mlAvailabilityDays) ?>">
                    <small style="color:var(--text-muted);">Dias corridos para o produto ficar pronto para envio.</small>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <details>
                        <summary style="cursor:pointer;color:var(--text-muted);font-size:.85rem;font-weight:700;">Campos avancados do Mercado Livre</summary>
                        <textarea name="mercado_livre_attributes" class="form-control" style="height:76px;margin-top:.75rem;" placeholder="Somente se o suporte pedir"><?= htmlspecialchars(implode("\n", $mlAdvancedLines)) ?></textarea>
                    </details>
                </div>
            </div>
        </div>

        <?php if ($isEdit): ?>
        <div class="card" style="margin-top:1.5rem;background:#f8fafc;border:1px solid var(--border);box-shadow:none;">
            <h4 class="brand-font" style="margin-bottom:1rem;">Diagnostico Mercado Livre</h4>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;">
                <div>
                    <label class="form-label">Status</label>
                    <div class="form-control" style="background:white;"><?= htmlspecialchars($item['mercado_livre_status'] ?? 'Nao publicado') ?></div>
                </div>
                <div>
                    <label class="form-label">Item ID</label>
                    <div class="form-control" style="background:white;"><?= htmlspecialchars($item['mercado_livre_item_id'] ?? '-') ?></div>
                </div>
                <div>
                    <label class="form-label">Ultima sincronizacao</label>
                    <div class="form-control" style="background:white;"><?= htmlspecialchars($item['mercado_livre_last_sync_at'] ?? '-') ?></div>
                </div>
                <div>
                    <label class="form-label">Categoria usada</label>
                    <div class="form-control" style="background:white;"><?= htmlspecialchars($item['mercado_livre_category_id'] ?? 'Padrao do sistema') ?></div>
                </div>
                <div style="grid-column:1/-1;">
                    <label class="form-label">URL publica da imagem</label>
                    <div style="display:flex;gap:.75rem;align-items:center;">
                        <input type="text" class="form-control" readonly value="<?= htmlspecialchars($mercadoLivreImageUrl ?? '') ?>" placeholder="Sem imagem publica">
                        <?php if (!empty($mercadoLivreImageUrl)): ?>
                        <a href="<?= htmlspecialchars($mercadoLivreImageUrl) ?>" target="_blank" rel="noopener" class="btn btn-secondary" style="text-decoration:none;white-space:nowrap;">Testar imagem</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($item['mercado_livre_last_error'])): ?>
                <div style="grid-column:1/-1;">
                    <label class="form-label">Ultimo erro</label>
                    <textarea class="form-control" readonly style="min-height:90px;background:white;"><?= htmlspecialchars($item['mercado_livre_last_error']) ?></textarea>
                </div>
                <?php endif; ?>
            </div>
            <div style="margin-top:1rem;display:flex;gap:1rem;justify-content:flex-end;">
                <button type="submit" form="ml-publish-form" class="btn btn-primary"><i data-lucide="send"></i> Tentar publicar/sincronizar</button>
            </div>
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:1rem;margin-top:2rem;justify-content:flex-end;">
            <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-secondary" style="padding:12px 30px;">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding:12px 40px;"><i data-lucide="save"></i> Salvar Produto</button>
        </div>
    </form>
    <?php if ($isEdit): ?>
    <form id="ml-publish-form" action="<?= e(route_url('mercadolivre/publish/' . (int) $item['id'])) ?>" method="POST" data-confirm="Publicar ou sincronizar este produto no Mercado Livre?"><?= csrf_field() ?></form>
    <?php endif; ?>
</div>

<script>
document.getElementById('produto-imagem-input')?.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) return;

    const preview = document.getElementById('produto-imagem-preview');
    const placeholder = document.getElementById('produto-imagem-placeholder');
    const help = document.getElementById('produto-imagem-help');

    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
    if (help) help.style.display = 'none';
});

document.getElementById('produto-galeria-input')?.addEventListener('change', function () {
    const preview = document.getElementById('produto-galeria-preview');
    if (!preview) return;

    preview.innerHTML = '';
    Array.from(this.files || []).slice(0, 8).forEach((file) => {
        if (!file.type.startsWith('image/')) return;

        const item = document.createElement('div');
        item.style.cssText = 'aspect-ratio:1/1;border:1px solid var(--border);border-radius:10px;overflow:hidden;background:#f8fafc;';

        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = file.name;
        image.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        item.appendChild(image);
        preview.appendChild(item);
    });
});

const produtoForm = document.getElementById('produto-form');
const produtoNomeInput = document.getElementById('produto-nome-input');
const produtoImagemInput = document.getElementById('produto-imagem-input');
let produtoImagemOtimizada = null;

function normalizeRepeatedProductName(value) {
    const text = String(value || '').replace(/\s+/g, ' ').trim();
    if (text.length < 8 || text.length % 2 !== 0) {
        return text;
    }

    const half = text.length / 2;
    const first = text.slice(0, half).trim();
    const second = text.slice(half).trim();
    return first && first.toLocaleLowerCase() === second.toLocaleLowerCase() ? first : text;
}

produtoNomeInput?.addEventListener('paste', () => {
    window.setTimeout(() => {
        produtoNomeInput.value = normalizeRepeatedProductName(produtoNomeInput.value);
    }, 0);
});

produtoNomeInput?.addEventListener('blur', () => {
    produtoNomeInput.value = normalizeRepeatedProductName(produtoNomeInput.value);
});

async function optimizeProductImageForUpload() {
    const file = produtoImagemInput?.files && produtoImagemInput.files[0];
    if (!file || produtoImagemOtimizada === file || !file.type.startsWith('image/')) {
        return file || null;
    }

    if (file.type === 'image/gif' || file.type === 'image/webp') {
        return file;
    }

    if (file.size <= 900 * 1024) {
        return file;
    }

    const imageUrl = URL.createObjectURL(file);
    const image = new Image();

    try {
        await new Promise((resolve, reject) => {
            image.onload = resolve;
            image.onerror = reject;
            image.src = imageUrl;
        });

        const maxSize = 1400;
        const ratio = Math.min(1, maxSize / Math.max(image.naturalWidth || image.width, image.naturalHeight || image.height));
        const width = Math.max(1, Math.round((image.naturalWidth || image.width) * ratio));
        const height = Math.max(1, Math.round((image.naturalHeight || image.height) * ratio));
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const context = canvas.getContext('2d');
        if (!context) return file;

        context.drawImage(image, 0, 0, width, height);
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.82));
        if (!blob || blob.size >= file.size) {
            return file;
        }

        const optimizedFile = new File(
            [blob],
            (file.name || 'produto').replace(/\.[^.]+$/, '') + '.jpg',
            { type: 'image/jpeg', lastModified: Date.now() }
        );
        produtoImagemOtimizada = optimizedFile;
        return optimizedFile;
    } catch (error) {
        console.warn('Nao foi possivel otimizar a imagem antes do envio.', error);
        return file;
    } finally {
        URL.revokeObjectURL(imageUrl);
    }
}

async function submitProductFormWithFetch(optimizedFile) {
    const formData = new FormData(produtoForm);
    const originalFile = produtoImagemInput?.files && produtoImagemInput.files[0];

    if (optimizedFile && originalFile && optimizedFile !== originalFile) {
        formData.set('imagem', optimizedFile, optimizedFile.name);
    }

    const response = await fetch(produtoForm.action, {
        method: produtoForm.method || 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': formData.get('_csrf_token') || '',
            'X-FORM-TOKEN': formData.get('_form_token') || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        cache: 'no-store',
    });

    if (response.ok || response.redirected) {
        window.location.href = '<?= htmlspecialchars($successUrl, ENT_QUOTES) ?>';
        return;
    }

    const contentType = response.headers.get('content-type') || '';
    const message = contentType.includes('application/json')
        ? ((await response.json()).message || '')
        : await response.text();
    produtoForm.dataset.submitting = '0';
    const submitButton = produtoForm.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = false;
        submitButton.style.opacity = '';
    }
    alert(message || ('Nao foi possivel salvar o produto. HTTP ' + response.status + '.'));
}

produtoForm?.addEventListener('submit', async (event) => {
    if (produtoForm.dataset.submitting === '1') {
        event.preventDefault();
        return;
    }

    event.preventDefault();
    produtoForm.dataset.submitting = '1';

    if (produtoNomeInput) {
        produtoNomeInput.value = normalizeRepeatedProductName(produtoNomeInput.value);
    }

    const submitButton = produtoForm.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.style.opacity = '.75';
    }

    try {
        const optimizedFile = await optimizeProductImageForUpload();

        await submitProductFormWithFetch(optimizedFile);
    } catch (error) {
        produtoForm.dataset.submitting = '0';
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.style.opacity = '';
        }
        alert(error?.message || 'Nao foi possivel salvar o produto.');
    }
});
</script>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
