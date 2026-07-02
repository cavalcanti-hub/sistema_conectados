<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>

<style>
    .page-header-actions {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.25rem;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1rem;
        box-shadow: var(--shadow);
    }
    .filter-group {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) auto minmax(190px, 260px) 52px;
        gap: 0;
        width: 100%;
        background: #f8fafc;
        padding: 6px;
        border-radius: 14px;
        border: 1px solid var(--border);
        box-shadow: none;
    }
    .filter-input {
        border: none;
        background: transparent;
        padding: 10px 14px;
        outline: none;
        color: var(--text-main);
        font-size: 0.92rem;
        min-width: 0;
    }
    .filter-divider {
        width: 1px;
        background: var(--border);
        margin: 7px 0;
    }
    .page-header-actions .btn-primary {
        min-height: 44px;
        border-radius: 12px;
    }
    .stock-add-btn {
        padding: 0 22px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 14px 24px rgba(0, 52, 154, .16);
    }
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .modern-stat-card {
        background: var(--bg-card);
        padding: 1.5rem;
        border-radius: 24px;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: 0.3s;
    }
    .modern-stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .stat-icon.blue { background: #eff6ff; color: #3b82f6; }
    .stat-icon.red { background: #fef2f2; color: #ef4444; }
    .stat-icon.green { background: #f0fdf4; color: #22c55e; }

    .data-card {
        background: var(--bg-card);
        border-radius: 28px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }
    .modern-table th {
        background: #f8fafc;
        padding: 1.25rem 1rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-muted);
    }
    .modern-table td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
    }
    .product-thumb {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        object-fit: cover;
        background: #f1f5f9;
    }
    .btn-circle {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        background: white;
        color: var(--text-muted);
        transition: 0.2s;
    }
    .btn-circle:hover { background: var(--primary); color: white; border-color: var(--primary); }
    .btn-circle.danger:hover { background: var(--danger); border-color: var(--danger); }
    @media(max-width:900px){
        .page-header-actions{grid-template-columns:1fr;}
        .filter-group{grid-template-columns:1fr;}
        .filter-divider{display:none;}
        .stock-add-btn{width:100%;justify-content:center;}
    }
</style>

<div class="page-header-actions">
    <form action="" method="GET" class="filter-group">
        <input type="hidden" name="url" value="estoque">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="filter-input" placeholder="Buscar item, codigo ou categoria...">
        <div class="filter-divider"></div>
        <select name="categoria" class="filter-input" onchange="this.form.submit()">
            <option value="">Todas Categorias</option>
            <?php foreach($categorias as $cat): ?><option value="<?= e($cat) ?>" <?= $categoria===$cat?'selected':'' ?>><?= e($cat) ?></option><?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary" style="padding:0;"><i data-lucide="search" style="width:18px;"></i></button>
    </form>
    <a href="<?= route_url('estoque/create') ?>" class="btn btn-primary stock-add-btn">
        <i data-lucide="plus-circle" style="width:20px;"></i> Novo Item
    </a>
</div>

<div class="stats-container">
    <div class="modern-stat-card">
        <div class="stat-icon blue"><i data-lucide="package" style="width:28px;height:28px;"></i></div>
        <div class="stat-content">
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Itens cadastrados</p>
            <h3 style="font-size: 1.5rem; font-weight: 800;"><?= $total ?></h3>
        </div>
    </div>
    <div class="modern-stat-card">
        <div class="stat-icon red"><i data-lucide="alert-triangle" style="width:28px;height:28px;"></i></div>
        <div class="stat-content">
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Estoque critico</p>
            <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--danger);"><?= count($baixo) ?></h3>
        </div>
    </div>
    <div class="modern-stat-card">
        <div class="stat-icon green"><i data-lucide="trending-up" style="width:28px;height:28px;"></i></div>
        <div class="stat-content">
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Valor em estoque</p>
            <h3 style="font-size: 1.5rem; font-weight: 800; color: #16a34a;">R$ <?= number_format($totalValor, 2, ',', '.') ?></h3>
        </div>
    </div>
</div>

<div class="data-card fade-in">
    <div class="table-container">
        <table class="modern-table">
            <thead>
                <tr>
                    <th style="width: 60px;">IMG</th>
                    <th>Item</th>
                    <th>Tipo</th>
                    <th>Categoria</th>
                    <th>Aplicacao</th>
                    <th>Quantidade</th>
                    <th>Preco Venda</th>
                    <th>Status</th>
                    <th style="text-align:right;">Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($items)): ?>
                <tr><td colspan="9" style="text-align:center;padding:4rem;color:var(--text-muted);">
                    <i data-lucide="inbox" style="width:48px;height:48px;opacity:0.2;margin-bottom:1rem;"></i><br>
                    Nenhum item encontrado.
                </td></tr>
                <?php else: foreach($items as $p): $baixoEstoque = $p['quantidade'] <= $p['estoque_minimo']; ?>
                <tr>
                    <td>
                        <?php if(!empty($p['imagem_url'])): ?>
                        <img src="<?= htmlspecialchars($p['imagem_url']) ?>" class="product-thumb" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';lucide.createIcons();">
                        <div class="product-thumb" style="display:none;align-items:center;justify-content:center;opacity:0.3;"><i data-lucide="package" style="width:20px;"></i></div>
                        <?php else: ?>
                        <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;opacity:0.3;"><i data-lucide="package" style="width:20px;"></i></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight:700;"><?= htmlspecialchars($p['nome']) ?></div>
                        <div style="font-size:0.7rem;color:var(--text-muted);"><?= htmlspecialchars($p['codigo_interno'] ?? '') ?></div>
                    </td>
                    <td>
                        <?= ($p['tipo'] ?? '') === 'produto'
                            ? '<span class="badge badge-blue">Produto Loja</span>'
                            : '<span class="badge badge-purple">Estoque Tecnico</span>' ?>
                    </td>
                    <td><span class="badge badge-gray"><?= htmlspecialchars($p['categoria'] ?? '') ?></span></td>
                    <td>
                        <div style="font-size:0.8rem;"><?= htmlspecialchars($p['marca_compativel'] ?? '') ?></div>
                        <div style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($p['modelo_compativel'] ?? '') ?></div>
                    </td>
                    <td>
                        <div style="font-weight:800;font-size:1.1rem;color:<?= $baixoEstoque?'var(--danger)':'inherit' ?>;"><?= (int) $p['quantidade'] ?></div>
                        <div style="font-size:0.65rem;color:var(--text-muted);">Min: <?= (int) $p['estoque_minimo'] ?></div>
                    </td>
                    <td style="font-weight:700;color:var(--primary);">R$ <?= number_format((float) $p['preco_venda'],2,',','.') ?></td>
                    <td><?= $baixoEstoque ? '<span class="badge badge-red">Reposicao</span>' : '<span class="badge badge-green">Estavel</span>' ?></td>
                    <td style="text-align:right;">
                        <a href="<?= route_url('estoque/edit', ['id' => $p['id']]) ?>" class="btn-circle" title="Editar"><i data-lucide="edit-3" style="width:16px;"></i></a>
                        <button onclick="abrirMovimentacao(<?= (int) $p['id'] ?>, '<?= htmlspecialchars($p['nome'], ENT_QUOTES) ?>')" class="btn-circle" title="Movimentar"><i data-lucide="arrow-up-down" style="width:16px;"></i></button>
                        <form action="<?= e(route_url('estoque/delete')) ?>" method="POST" style="display:inline;" data-confirm="Excluir este item do estoque?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" class="btn-circle danger" title="Excluir"><i data-lucide="trash-2" style="width:16px;"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div style="padding:0 1rem 1rem;">
        <?= render_pagination($pagination ?? [], 'estoque', ['search' => $search, 'categoria' => $categoria]) ?>
    </div>
</div>

<div id="modal-movimentacao" class="modal-overlay">
    <div class="modal-content" style="border-radius:24px; max-width:400px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
            <h3 class="brand-font">Movimentar Estoque</h3>
            <button onclick="fecharModal()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);"><i data-lucide="x"></i></button>
        </div>
        <p id="modal-produto-nome" style="color:var(--primary);margin-bottom:1.5rem;font-weight:700;font-size:1rem;"></p>
        <form action="<?= e(route_url('estoque/movimentar')) ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="produto_id" id="modal-produto-id">
            <div class="form-group" style="margin-bottom:1rem;"><label class="form-label">Tipo de Movimento</label>
                <select name="tipo" class="form-control">
                    <option value="entrada">Entrada (Compra/Ajuste+)</option>
                    <option value="saida">Saida (Uso/Ajuste-)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:1rem;"><label class="form-label">Quantidade</label><input type="number" name="quantidade" class="form-control" min="1" value="1" required></div>
            <div class="form-group" style="margin-bottom:2rem;"><label class="form-label">Motivo</label><input type="text" name="motivo" class="form-control" placeholder="Ex: Compra lote 05, uso em OS, ajuste mensal..."></div>
            <div style="display:flex;gap:.75rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;padding:12px;border-radius:12px;font-weight:700;">Confirmar Ajuste</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirMovimentacao(id, nome) {
    document.getElementById('modal-produto-id').value = id;
    document.getElementById('modal-produto-nome').textContent = nome;
    document.getElementById('modal-movimentacao').style.display = 'flex';
    lucide.createIcons();
}
function fecharModal() { document.getElementById('modal-movimentacao').style.display = 'none'; }
</script>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
