<?php
require_once dirname(__DIR__) . '/layout/header.php';

$fornecedores = $fornecedores ?? [];
$fornecedoresAtivos = $fornecedoresAtivos ?? [];
$notas = $notas ?? [];
$produtos = $produtos ?? [];
$filters = $filters ?? ['search' => '', 'status' => '', 'fornecedor_id' => 0];
$formasPagamento = $formasPagamento ?? [];
$tipos = $tipos ?? ['peca' => 'Peca tecnica', 'produto' => 'Produto da loja'];
$error = (string) ($_SESSION['fornecedores_error'] ?? '');
unset($_SESSION['fornecedores_error']);
$statusClass = ['Aberta' => 'badge-yellow', 'Baixada' => 'badge-green', 'Cancelada' => 'badge-gray'];
$totalAberto = 0.0;
$totalBaixado = 0.0;
foreach ($notas as $notaResumo) {
    if (($notaResumo['status'] ?? '') === 'Aberta') {
        $totalAberto += (float) ($notaResumo['valor_total'] ?? 0);
    } elseif (($notaResumo['status'] ?? '') === 'Baixada') {
        $totalBaixado += (float) ($notaResumo['valor_total'] ?? 0);
    }
}
?>

<style>
    .supplier-page { display:grid;gap:1.25rem; }
    .supplier-hero {
        display:flex;align-items:center;justify-content:space-between;gap:1rem;
        background:linear-gradient(135deg,#09245c,#0b63ce);color:#fff;border-radius:18px;
        padding:1.35rem 1.5rem;box-shadow:0 20px 48px -34px rgba(15,47,111,.85);
    }
    .supplier-hero h2 { font-family:'Outfit',sans-serif;font-size:1.35rem;margin:0 0 .25rem; }
    .supplier-hero p { margin:0;color:rgba(255,255,255,.78);font-size:.9rem; }
    .supplier-grid { display:grid;grid-template-columns:minmax(330px,.75fr) minmax(0,1.25fr);gap:1.25rem;align-items:start; }
    .supplier-panel { background:var(--bg-card);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow-sm);padding:1.2rem; }
    .supplier-panel h3 { font-family:'Outfit',sans-serif;font-size:1.05rem;margin:0 0 1rem;color:var(--text-main); }
    .supplier-stats { display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.8rem; }
    .supplier-stat { background:#fff;border:1px solid var(--border);border-radius:14px;padding:.95rem;box-shadow:var(--shadow-sm); }
    .supplier-stat span { display:block;color:var(--text-muted);font-size:.72rem;text-transform:uppercase;font-weight:800;letter-spacing:.03em; }
    .supplier-stat strong { display:block;margin-top:.22rem;font-family:'Outfit',sans-serif;font-size:1.25rem;color:var(--primary); }
    .supplier-form-grid { display:grid;grid-template-columns:1fr 1fr;gap:.8rem; }
    .span-2 { grid-column:1/-1; }
    .supplier-list { display:grid;gap:.65rem;max-height:540px;overflow:auto;padding-right:.2rem; }
    .supplier-card { border:1px solid var(--border);border-radius:13px;padding:.85rem;background:#fff;display:grid;gap:.35rem; }
    .supplier-card-head { display:flex;align-items:flex-start;justify-content:space-between;gap:.7rem; }
    .supplier-card strong { color:var(--text-main);font-size:.96rem; }
    .supplier-card small { color:var(--text-muted);display:block;line-height:1.35; }
    .notes-toolbar { display:grid;grid-template-columns:minmax(180px,1fr) 170px 220px auto;gap:.75rem;align-items:end;margin-bottom:1rem; }
    .note-form-grid { display:grid;grid-template-columns:1.2fr 1fr 1fr 1fr;gap:.8rem; }
    .note-items { display:grid;gap:.6rem;margin-top:.85rem; }
    .note-item-row { display:grid;grid-template-columns:1.15fr 1.1fr .7fr .55fr .75fr 36px;gap:.55rem;align-items:end;padding:.7rem;border:1px solid var(--border);border-radius:12px;background:#f8fafc; }
    .note-item-row .form-group { margin:0; }
    .note-table-wrap { overflow:auto;border:1px solid var(--border);border-radius:14px; }
    .note-table { width:100%;border-collapse:collapse;min-width:980px;background:#fff; }
    .note-table th { background:#f8fafc;color:#475569;font-size:.74rem;text-align:left;text-transform:uppercase;letter-spacing:.03em;padding:.85rem; }
    .note-table td { padding:.85rem;border-top:1px solid var(--border);vertical-align:top;font-size:.88rem; }
    .note-number { font-weight:850;color:var(--text-main);display:block;margin-bottom:.25rem; }
    .note-items-list { margin:.45rem 0 0;padding-left:1rem;color:var(--text-muted);font-size:.78rem;line-height:1.45; }
    .actions-row { display:flex;gap:.45rem;align-items:center;justify-content:flex-end;flex-wrap:wrap; }
    .icon-action {
        width:36px;height:36px;border-radius:10px;border:1px solid var(--border);background:#fff;color:var(--primary);
        display:inline-flex;align-items:center;justify-content:center;cursor:pointer;text-decoration:none;
    }
    .icon-action.green { color:#059669; }
    .icon-action.red { color:var(--danger); }
    .empty-state { padding:2rem;text-align:center;color:var(--text-muted); }
    @media(max-width:1180px){
        .supplier-grid { grid-template-columns:1fr; }
        .supplier-stats { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .notes-toolbar,.note-form-grid,.note-item-row { grid-template-columns:1fr; }
    }
    @media(max-width:720px){
        .supplier-form-grid,.supplier-stats { grid-template-columns:1fr; }
    }
</style>

<?php if ($error !== ''): ?>
<div class="alert-success system-flash" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;">
    <i data-lucide="alert-triangle" style="width:18px;"></i>
    <?= htmlspecialchars($error) ?>
</div>
<?php elseif (isset($_GET['nota_success'])): ?>
<div class="alert-success system-flash"><i data-lucide="check-circle"></i> Nota lancada com sucesso.</div>
<?php elseif (isset($_GET['baixada'])): ?>
<div class="alert-success system-flash"><i data-lucide="check-circle"></i> Nota baixada, estoque e financeiro atualizados.</div>
<?php elseif (isset($_GET['updated'])): ?>
<div class="alert-success system-flash"><i data-lucide="check-circle"></i> Fornecedor atualizado.</div>
<?php endif; ?>

<section class="supplier-page">
    <div class="supplier-hero">
        <div>
            <h2>Fornecedores e notas de compra</h2>
            <p>Cadastre fornecedores, lance notas e faca a baixa para atualizar estoque e financeiro.</p>
        </div>
        <i data-lucide="truck" style="width:48px;height:48px;opacity:.85;"></i>
    </div>

    <div class="supplier-stats">
        <div class="supplier-stat"><span>Fornecedores</span><strong><?= count($fornecedores) ?></strong></div>
        <div class="supplier-stat"><span>Notas filtradas</span><strong><?= count($notas) ?></strong></div>
        <div class="supplier-stat"><span>Aberto</span><strong>R$ <?= number_format($totalAberto, 2, ',', '.') ?></strong></div>
        <div class="supplier-stat"><span>Baixado</span><strong>R$ <?= number_format($totalBaixado, 2, ',', '.') ?></strong></div>
    </div>

    <div class="supplier-grid">
        <div class="supplier-panel">
            <h3>Cadastro de fornecedor</h3>
            <form action="<?= e(route_url('fornecedores/store')) ?>" method="POST" id="supplier-form">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="supplier-id" value="">
                <div class="supplier-form-grid">
                    <div class="form-group span-2">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" id="supplier-nome" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">CNPJ/CPF</label>
                        <input type="text" name="documento" id="supplier-documento" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" id="supplier-telefone" class="form-control">
                    </div>
                    <div class="form-group span-2">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" id="supplier-email" class="form-control">
                    </div>
                    <div class="form-group span-2">
                        <label class="form-label">Endereco</label>
                        <input type="text" name="endereco" id="supplier-endereco" class="form-control">
                    </div>
                    <div class="form-group span-2">
                        <label class="form-label">Observacoes</label>
                        <textarea name="observacoes" id="supplier-observacoes" class="form-control" rows="2"></textarea>
                    </div>
                    <label class="span-2" style="display:flex;align-items:center;gap:.5rem;font-weight:700;">
                        <input type="checkbox" name="ativo" id="supplier-ativo" value="1" checked> Ativo
                    </label>
                </div>
                <div style="display:flex;gap:.75rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary" id="supplier-submit"><i data-lucide="save"></i> Salvar fornecedor</button>
                    <button type="button" class="btn btn-secondary" onclick="resetSupplierForm()">Limpar</button>
                </div>
            </form>

            <hr style="border:0;border-top:1px solid var(--border);margin:1.2rem 0;">
            <h3>Fornecedores cadastrados</h3>
            <div class="supplier-list">
                <?php if (empty($fornecedores)): ?>
                    <div class="empty-state">Nenhum fornecedor cadastrado.</div>
                <?php endif; ?>
                <?php foreach ($fornecedores as $fornecedor): ?>
                    <?php $supplierJson = htmlspecialchars(json_encode($fornecedor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES); ?>
                    <article class="supplier-card">
                        <div class="supplier-card-head">
                            <div>
                                <strong><?= htmlspecialchars($fornecedor['nome']) ?></strong>
                                <small><?= htmlspecialchars($fornecedor['telefone'] ?: 'Sem telefone') ?><?= !empty($fornecedor['email']) ? ' | ' . htmlspecialchars($fornecedor['email']) : '' ?></small>
                            </div>
                            <button type="button" class="icon-action" onclick="editSupplier(JSON.parse(this.dataset.supplier))" data-supplier="<?= $supplierJson ?>" title="Editar fornecedor">
                                <i data-lucide="edit-2" style="width:16px;"></i>
                            </button>
                        </div>
                        <small><?= (int) $fornecedor['notas_total'] ?> nota(s) | R$ <?= number_format((float) $fornecedor['valor_total'], 2, ',', '.') ?></small>
                        <span class="badge <?= !empty($fornecedor['ativo']) ? 'badge-green' : 'badge-gray' ?>" style="width:max-content;"><?= !empty($fornecedor['ativo']) ? 'ativo' : 'inativo' ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="supplier-panel">
            <h3>Lancar nota de compra</h3>
            <form action="<?= e(route_url('fornecedores/storeNota')) ?>" method="POST" id="note-form">
                <?= csrf_field() ?>
                <div class="note-form-grid">
                    <div class="form-group">
                        <label class="form-label">Fornecedor</label>
                        <select name="fornecedor_id" class="form-control">
                            <option value="">Nao informado</option>
                            <?php foreach ($fornecedoresAtivos as $fornecedor): ?>
                                <option value="<?= (int) $fornecedor['id'] ?>"><?= htmlspecialchars($fornecedor['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Numero da nota</label>
                        <input type="text" name="numero" class="form-control" placeholder="NF, pedido ou recibo">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Emissao</label>
                        <input type="date" name="data_emissao" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pagamento</label>
                        <select name="forma_pagamento" class="form-control">
                            <?php foreach ($formasPagamento as $forma): ?><option value="<?= htmlspecialchars($forma) ?>"><?= htmlspecialchars($forma) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label class="form-label">Vencimento</label>
                        <input type="date" name="data_vencimento" class="form-control">
                    </div>
                    <div class="form-group span-2">
                        <label class="form-label">Observacoes</label>
                        <input type="text" name="observacoes" class="form-control" placeholder="Ex: compra Mercado Livre, frete incluso, boleto a vencer...">
                    </div>
                </div>

                <div class="note-items" id="note-items"></div>
                <div style="display:flex;gap:.75rem;margin-top:1rem;justify-content:space-between;align-items:center;flex-wrap:wrap;">
                    <button type="button" class="btn btn-secondary" onclick="addNoteItem()"><i data-lucide="plus"></i> Adicionar item</button>
                    <div style="display:flex;gap:.75rem;align-items:center;">
                        <strong>Total: <span id="note-total">R$ 0,00</span></strong>
                        <button type="submit" class="btn btn-primary"><i data-lucide="file-plus"></i> Lancar nota</button>
                    </div>
                </div>
            </form>

            <hr style="border:0;border-top:1px solid var(--border);margin:1.25rem 0;">
            <div style="display:flex;justify-content:space-between;gap:1rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem;">
                <h3 style="margin:0;">Notas de compra</h3>
                <form class="notes-toolbar" action="<?= e(route_url()) ?>" method="GET">
                    <input type="hidden" name="url" value="fornecedores">
                    <div class="form-group">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Nota, fornecedor ou observacao">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach (['Aberta', 'Baixada', 'Cancelada'] as $status): ?>
                                <option value="<?= $status ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= $status ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fornecedor</label>
                        <select name="fornecedor_id" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($fornecedores as $fornecedor): ?>
                                <option value="<?= (int) $fornecedor['id'] ?>" <?= (int) ($filters['fornecedor_id'] ?? 0) === (int) $fornecedor['id'] ? 'selected' : '' ?>><?= htmlspecialchars($fornecedor['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary"><i data-lucide="filter"></i> Filtrar</button>
                </form>
            </div>

            <div class="note-table-wrap">
                <table class="note-table">
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Fornecedor</th>
                            <th>Data</th>
                            <th>Itens</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th style="text-align:right;">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($notas)): ?>
                        <tr><td colspan="7"><div class="empty-state">Nenhuma nota encontrada.</div></td></tr>
                    <?php endif; ?>
                    <?php foreach ($notas as $nota): ?>
                        <tr>
                            <td>
                                <span class="note-number"><?= htmlspecialchars($nota['numero'] ?: '#' . $nota['id']) ?></span>
                                <small><?= htmlspecialchars($nota['forma_pagamento'] ?: '-') ?></small>
                            </td>
                            <td><?= htmlspecialchars($nota['fornecedor_nome'] ?: 'Nao informado') ?></td>
                            <td>
                                <?= date('d/m/Y', strtotime($nota['data_emissao'])) ?>
                                <?php if (!empty($nota['data_vencimento'])): ?><br><small>Venc. <?= date('d/m/Y', strtotime($nota['data_vencimento'])) ?></small><?php endif; ?>
                            </td>
                            <td>
                                <strong><?= (int) $nota['itens_total'] ?> item(ns)</strong>
                                <?php if (!empty($nota['itens'])): ?>
                                <ul class="note-items-list">
                                    <?php foreach (array_slice($nota['itens'], 0, 4) as $item): ?>
                                        <li><?= (int) $item['quantidade'] ?>x <?= htmlspecialchars($item['produto_nome'] ?: $item['descricao']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </td>
                            <td><strong>R$ <?= number_format((float) $nota['valor_total'], 2, ',', '.') ?></strong></td>
                            <td><span class="badge <?= $statusClass[$nota['status']] ?? 'badge-gray' ?>"><?= htmlspecialchars($nota['status']) ?></span></td>
                            <td>
                                <div class="actions-row">
                                    <?php if ($nota['status'] === 'Aberta'): ?>
                                    <form action="<?= e(route_url('fornecedores/baixarNota')) ?>" method="POST" data-confirm="Baixar esta nota? O estoque dos itens vinculados e o financeiro serao atualizados.">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $nota['id'] ?>">
                                        <button type="submit" class="icon-action green" title="Dar baixa"><i data-lucide="check-circle" style="width:16px;"></i></button>
                                    </form>
                                    <form action="<?= e(route_url('fornecedores/cancelarNota')) ?>" method="POST" data-confirm="Cancelar esta nota de compra?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $nota['id'] ?>">
                                        <button type="submit" class="icon-action red" title="Cancelar"><i data-lucide="x-circle" style="width:16px;"></i></button>
                                    </form>
                                    <?php elseif (!empty($nota['baixado_at'])): ?>
                                        <small>Baixada em <?= date('d/m/Y H:i', strtotime($nota['baixado_at'])) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<template id="note-item-template">
    <div class="note-item-row">
        <div class="form-group">
            <label class="form-label">Vincular estoque</label>
            <select name="item_produto_id[]" class="form-control item-product" onchange="fillItemFromProduct(this)">
                <option value="">Sem vinculo</option>
                <?php foreach ($produtos as $produto): ?>
                    <option value="<?= (int) $produto['id'] ?>" data-name="<?= htmlspecialchars($produto['nome'], ENT_QUOTES) ?>" data-type="<?= htmlspecialchars($produto['tipo'] ?? 'peca', ENT_QUOTES) ?>">
                        <?= htmlspecialchars($produto['nome']) ?> (<?= htmlspecialchars($produto['tipo'] ?? 'peca') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Descricao *</label>
            <input type="text" name="item_descricao[]" class="form-control item-description" placeholder="Item da nota">
        </div>
        <div class="form-group">
            <label class="form-label">Tipo</label>
            <select name="item_tipo[]" class="form-control item-type">
                <?php foreach ($tipos as $value => $label): ?><option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Qtd</label>
            <input type="number" name="item_quantidade[]" class="form-control item-qty" min="1" step="1" value="1" oninput="updateNoteTotal()">
        </div>
        <div class="form-group">
            <label class="form-label">Valor un.</label>
            <input type="text" name="item_valor_unitario[]" class="form-control item-unit" placeholder="0,00" inputmode="decimal" oninput="updateNoteTotal()">
        </div>
        <button type="button" class="icon-action red" onclick="removeNoteItem(this)" title="Remover item"><i data-lucide="trash-2" style="width:16px;"></i></button>
    </div>
</template>

<script>
function parseMoney(value) {
    value = String(value || '').trim();
    if (!value) return 0;
    if (value.includes(',') && value.includes('.')) value = value.replace(/\./g, '');
    return Number(value.replace(',', '.')) || 0;
}

function formatMoney(value) {
    return 'R$ ' + value.toFixed(2).replace('.', ',');
}

function addNoteItem() {
    const template = document.getElementById('note-item-template');
    const container = document.getElementById('note-items');
    container.appendChild(template.content.cloneNode(true));
    lucide.createIcons();
    updateNoteTotal();
}

function removeNoteItem(button) {
    button.closest('.note-item-row')?.remove();
    updateNoteTotal();
}

function fillItemFromProduct(select) {
    const option = select.selectedOptions[0];
    const row = select.closest('.note-item-row');
    if (!row || !option) return;
    const name = option.dataset.name || '';
    const type = option.dataset.type || 'peca';
    if (name) row.querySelector('.item-description').value = name;
    row.querySelector('.item-type').value = type;
}

function updateNoteTotal() {
    let total = 0;
    document.querySelectorAll('#note-items .note-item-row').forEach((row) => {
        const qty = Number(row.querySelector('.item-qty')?.value || 0) || 0;
        const unit = parseMoney(row.querySelector('.item-unit')?.value || 0);
        total += qty * unit;
    });
    document.getElementById('note-total').textContent = formatMoney(total);
}

function editSupplier(supplier) {
    const form = document.getElementById('supplier-form');
    form.action = '<?= route_url('fornecedores/update') ?>';
    document.getElementById('supplier-id').value = supplier.id || '';
    document.getElementById('supplier-nome').value = supplier.nome || '';
    document.getElementById('supplier-documento').value = supplier.documento || '';
    document.getElementById('supplier-telefone').value = supplier.telefone || '';
    document.getElementById('supplier-email').value = supplier.email || '';
    document.getElementById('supplier-endereco').value = supplier.endereco || '';
    document.getElementById('supplier-observacoes').value = supplier.observacoes || '';
    document.getElementById('supplier-ativo').checked = Number(supplier.ativo || 0) === 1;
    document.getElementById('supplier-submit').innerHTML = '<i data-lucide="save"></i> Salvar alteracoes';
    lucide.createIcons();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetSupplierForm() {
    const form = document.getElementById('supplier-form');
    form.reset();
    form.action = '<?= route_url('fornecedores/store') ?>';
    document.getElementById('supplier-id').value = '';
    document.getElementById('supplier-ativo').checked = true;
    document.getElementById('supplier-submit').innerHTML = '<i data-lucide="save"></i> Salvar fornecedor';
    lucide.createIcons();
}

addNoteItem();
</script>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
