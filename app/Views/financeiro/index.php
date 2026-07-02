<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>

<?php
$periodoLabels = [
    'dia' => 'Hoje',
    'semana' => 'Semana',
    'mes' => 'Mês',
    'todos' => 'Todos',
];
$periodoAtual = $periodo ?? 'dia';
$periodoLabel = $periodoLabels[$periodoAtual] ?? 'Hoje';
$tipoAtual = $tipo ?? '';
$qtdReceitas = $qtdReceitas ?? count(array_filter($movimentacoes, static fn($m) => ($m['tipo'] ?? '') === 'Receita'));
$qtdDespesas = $qtdDespesas ?? count(array_filter($movimentacoes, static fn($m) => ($m['tipo'] ?? '') === 'Despesa'));
$canEditLancamentos = current_user_profile() === 'Administrador';
?>

<style>
    .finance-command-card {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) auto;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding: 1rem;
    }

    .finance-command-title {
        display: flex;
        align-items: center;
        gap: .8rem;
        min-width: 0;
    }

    .finance-command-icon {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        background: rgba(0, 52, 154, .1);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .finance-command-title h3 {
        margin: 0;
        font-size: 1rem;
        line-height: 1.2;
    }

    .finance-command-title p {
        margin: .15rem 0 0;
        color: var(--text-muted);
        font-size: .82rem;
    }

    .finance-command-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .65rem;
        flex-wrap: wrap;
    }

    .finance-filters {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .finance-filters .form-control {
        width: auto;
        min-width: 132px;
        height: 42px;
        padding: 8px 12px;
    }

    .finance-grid {
        display: grid;
        grid-template-columns: minmax(680px, 2fr) minmax(280px, 1fr);
        gap: 1.25rem;
        align-items: start;
        min-width: 0;
    }

    .finance-main {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        min-width: 0;
    }

    .finance-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .finance-stat {
        display: flex;
        align-items: center;
        gap: .9rem;
        padding: 1.05rem;
        min-height: 104px;
        border-bottom: 3px solid transparent;
        min-width: 0;
    }

    .finance-stat.success { border-bottom-color: var(--success); }
    .finance-stat.danger { border-bottom-color: var(--danger); }
    .finance-stat.primary { border-bottom-color: var(--primary); }
    .finance-stat.neutral { border-bottom-color: var(--border); }

    .finance-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .finance-stat p {
        color: var(--text-muted);
        font-size: .78rem;
        margin: 0 0 .35rem;
        font-weight: 700;
    }

    .finance-stat h3 {
        color: var(--primary);
        font-size: 1.2rem;
        line-height: 1.1;
        margin: 0;
        overflow-wrap: anywhere;
    }

    .finance-table-card {
        padding: 1.5rem;
        min-width: 0;
    }

    .finance-table-card .table-container {
        max-width: 100%;
    }

    .finance-table-card table {
        min-width: 760px;
    }

    .finance-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .finance-card-head h3 {
        margin: 0;
        font-size: 1.05rem;
    }

    .finance-card-head p {
        margin: .2rem 0 0;
        color: var(--text-muted);
        font-size: .78rem;
    }

    .finance-side {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-width: 0;
    }

    .today-card {
        background: var(--primary);
        color: #fff;
        border: 0;
    }

    .today-card h4,
    .side-card h4 {
        margin: 0 0 1rem;
        font-size: 1rem;
    }

    .today-row,
    .side-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .55rem 0;
        border-bottom: 1px solid rgba(255,255,255,.12);
    }

    .today-row:last-child,
    .side-row:last-child {
        border-bottom: 0;
    }

    .today-row span:first-child {
        color: rgba(255,255,255,.72);
        font-size: .84rem;
    }

    .today-row strong {
        font-size: 1rem;
    }

    .side-row {
        border-bottom-color: var(--border);
        align-items: flex-start;
    }

    .side-row span {
        color: var(--text-muted);
        font-size: .76rem;
    }

    .payment-bar {
        margin-top: .45rem;
        width: 100%;
        height: 7px;
        border-radius: 999px;
        background: rgba(148, 163, 184, .28);
        overflow: hidden;
    }

    .payment-bar > div {
        height: 100%;
        border-radius: inherit;
        background: var(--primary);
    }

    .finance-amount.receita { color: var(--success); }
    .finance-amount.despesa { color: var(--danger); }

    .fee-ref-details {
        max-width: 360px;
    }

    .fee-ref-details summary {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        cursor: pointer;
        color: var(--text-main);
        font-weight: 700;
        list-style: none;
    }

    .fee-ref-details summary::-webkit-details-marker {
        display: none;
    }

    .fee-ref-details summary small {
        color: var(--primary);
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .fee-ref-box {
        margin-top: .65rem;
        padding: .75rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #f8fafc;
        color: var(--text-main);
        display: grid;
        gap: .35rem;
        font-size: .8rem;
        line-height: 1.35;
    }

    .fee-ref-box strong {
        color: var(--primary);
    }

    .fee-ref-box a {
        color: var(--primary);
        font-weight: 800;
        text-decoration: none;
    }

    .finance-edit-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        background: transparent;
        color: var(--primary);
        cursor: pointer;
        border-radius: 8px;
        transition: background .2s, color .2s;
    }

    .finance-edit-btn:hover {
        background: rgba(0, 52, 154, .08);
        color: var(--primary-dark);
    }

    @media(max-width:1500px) {
        .finance-grid { grid-template-columns: 1fr; }
        .finance-side {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: start;
        }
    }

    @media(max-width:1180px) {
        .finance-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .finance-side { grid-template-columns: 1fr; }
    }

    @media(max-width:780px) {
        .finance-command-card { grid-template-columns: 1fr; }
        .finance-table-card { padding: 1rem; }
        .finance-command-actions,
        .finance-filters,
        .finance-command-actions .btn,
        .finance-filters .form-control {
            width: 100%;
        }
        .finance-stats { grid-template-columns: 1fr; }
    }
</style>

<div class="card finance-command-card">
    <div class="finance-command-title">
        <span class="finance-command-icon"><i data-lucide="wallet-cards"></i></span>
        <div>
            <h3 class="brand-font">Controle financeiro</h3>
            <p>Acompanhe entradas, saídas, formas de pagamento e categorias no período selecionado.</p>
        </div>
    </div>
    <div class="finance-command-actions">
        <form action="" method="GET" class="finance-filters">
            <input type="hidden" name="url" value="financeiro">
            <select name="periodo" class="form-control" onchange="this.form.submit()">
                <option value="dia" <?= $periodoAtual === 'dia' ? 'selected' : '' ?>>Hoje</option>
                <option value="semana" <?= $periodoAtual === 'semana' ? 'selected' : '' ?>>Semana</option>
                <option value="mes" <?= $periodoAtual === 'mes' ? 'selected' : '' ?>>Mês</option>
                <option value="todos" <?= $periodoAtual === 'todos' ? 'selected' : '' ?>>Todos</option>
            </select>
            <select name="tipo" class="form-control" onchange="this.form.submit()">
                <option value="" <?= $tipoAtual === '' ? 'selected' : '' ?>>Todos os tipos</option>
                <option value="Receita" <?= $tipoAtual === 'Receita' ? 'selected' : '' ?>>Receitas</option>
                <option value="Despesa" <?= $tipoAtual === 'Despesa' ? 'selected' : '' ?>>Despesas</option>
            </select>
            <button type="submit" class="btn btn-outline"><i data-lucide="filter"></i> Filtrar</button>
        </form>
        <a href="<?= route_url('financeiro/print', ['periodo' => $periodoAtual, 'tipo' => $tipoAtual]) ?>" target="_blank" class="btn btn-outline"><i data-lucide="printer"></i> Imprimir</a>
        <button onclick="openFinanceModal()" class="btn btn-primary"><i data-lucide="plus"></i> Lançar</button>
    </div>
</div>

<div class="finance-grid">
    <div class="finance-main">
        <div class="finance-stats">
            <div class="card finance-stat success">
                <div class="finance-stat-icon" style="background:rgba(16,185,129,.1);color:var(--success);"><i data-lucide="trending-up"></i></div>
                <div><p>Receitas (<?= $periodoLabel ?>)</p><h3>R$ <?= number_format($receitas,2,',','.') ?></h3></div>
            </div>
            <div class="card finance-stat danger">
                <div class="finance-stat-icon" style="background:rgba(239,68,68,.1);color:var(--danger);"><i data-lucide="trending-down"></i></div>
                <div><p>Despesas (<?= $periodoLabel ?>)</p><h3>R$ <?= number_format($despesas,2,',','.') ?></h3></div>
            </div>
            <div class="card finance-stat primary">
                <div class="finance-stat-icon" style="background:rgba(0,52,154,.1);color:var(--primary);"><i data-lucide="wallet"></i></div>
                <div><p>Resultado (<?= $periodoLabel ?>)</p><h3>R$ <?= number_format($lucro,2,',','.') ?></h3></div>
            </div>
            <div class="card finance-stat neutral">
                <div class="finance-stat-icon" style="background:rgba(100,116,139,.12);color:var(--text-muted);"><i data-lucide="list-checks"></i></div>
                <div><p>Lançamentos</p><h3><?= count($movimentacoes) ?></h3></div>
            </div>
        </div>

        <div class="card finance-table-card">
            <div class="finance-card-head">
                <div>
                    <h3 class="brand-font">Movimentações</h3>
                    <p><?= $qtdReceitas ?> receita(s), <?= $qtdDespesas ?> despesa(s) no filtro atual.</p>
                </div>
                <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
                    <a href="<?= route_url('financeiro/print', ['periodo' => $periodoAtual, 'tipo' => $tipoAtual]) ?>" target="_blank" class="btn btn-outline" style="font-size:.85rem;"><i data-lucide="printer"></i> Imprimir</a>
                    <button onclick="openFinanceModal()" class="btn btn-primary" style="font-size:.85rem;"><i data-lucide="plus"></i> Lançar</button>
                </div>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>Data</th><th>Tipo</th><th>Categoria</th><th>Descrição</th><th>Forma</th><th>Valor</th><?php if($canEditLancamentos): ?><th style="text-align:right;">Ações</th><?php endif; ?></tr></thead>
                    <tbody>
                    <?php if(empty($movimentacoes)): ?>
                        <tr><td colspan="<?= $canEditLancamentos ? 7 : 6 ?>" style="text-align:center;padding:3rem;color:var(--text-muted);">
                            <i data-lucide="receipt-text" style="width:34px;display:block;margin:0 auto .75rem;"></i>
                            Nenhuma movimentação no filtro atual.
                            <button onclick="openFinanceModal()" style="background:none;border:none;color:var(--secondary);cursor:pointer;font-weight:700;">Lançar agora</button>
                        </td></tr>
                    <?php else: foreach($movimentacoes as $m): ?>
                        <?php
                            $dataMov = $m['data_pagamento'] ?: $m['created_at'];
                            $taxaRef = $m['taxa_referencia'] ?? null;
                        ?>
                        <tr>
                            <td style="font-size:.82rem;white-space:nowrap;"><?= date('d/m/Y', strtotime($dataMov)) ?></td>
                            <td><span class="badge <?= $m['tipo']==='Receita'?'badge-green':'badge-red' ?>"><?= $m['tipo'] ?></span></td>
                            <td style="font-size:.82rem;"><?= htmlspecialchars($m['categoria'] ?: 'Sem categoria') ?></td>
                            <td style="font-size:.86rem;">
                                <?php if ($taxaRef): ?>
                                <details class="fee-ref-details">
                                    <summary>
                                        <span><?= htmlspecialchars($m['descricao']) ?></span>
                                        <small>Ver referencia</small>
                                    </summary>
                                    <div class="fee-ref-box">
                                        <strong><?= htmlspecialchars($taxaRef['label'] ?? 'Referencia') ?></strong>
                                        <span><?= htmlspecialchars($taxaRef['descricao'] ?? '') ?></span>
                                        <?php if (($taxaRef['valor'] ?? null) !== null): ?>
                                        <span>Valor original: R$ <?= number_format((float) $taxaRef['valor'], 2, ',', '.') ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($taxaRef['forma_pagamento'])): ?>
                                        <span>Forma: <?= htmlspecialchars($taxaRef['forma_pagamento']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($taxaRef['data_pagamento'])): ?>
                                        <span>Data: <?= date('d/m/Y', strtotime((string) $taxaRef['data_pagamento'])) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($taxaRef['os_id'])): ?>
                                        <a href="<?= route_url('os/viewDetail', ['id' => (int) $taxaRef['os_id']]) ?>">Abrir OS relacionada</a>
                                        <?php endif; ?>
                                    </div>
                                </details>
                                <?php else: ?>
                                <?= htmlspecialchars($m['descricao']) ?>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:.82rem;"><?= htmlspecialchars($m['forma_pagamento'] ?: '—') ?></td>
                            <td style="font-weight:800;white-space:nowrap;" class="finance-amount <?= $m['tipo']==='Receita'?'receita':'despesa' ?>"><?= $m['tipo']==='Receita'?'+':'-' ?> R$ <?= number_format($m['valor'],2,',','.') ?></td>
                            <?php if($canEditLancamentos): ?>
                            <td style="text-align:right;white-space:nowrap;">
                                <button type="button" class="finance-edit-btn" title="Editar lançamento" onclick='openFinanceModal(<?= json_attr([
                                    'id' => (int) $m['id'],
                                    'tipo' => $m['tipo'],
                                    'categoria' => $m['categoria'],
                                    'descricao' => $m['descricao'],
                                    'valor' => number_format((float) $m['valor'], 2, '.', ''),
                                    'forma_pagamento' => $m['forma_pagamento'],
                                    'data_pagamento' => $m['data_pagamento'] ?: date('Y-m-d', strtotime($m['created_at'])),
                                    'os_id' => $m['os_id'],
                                ]) ?>)'><i data-lucide="edit-2" style="width:16px;"></i></button>
                                <form action="<?= route_url('financeiro/delete') ?>" method="POST" data-confirm="Remover este lancamento financeiro?" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                    <button type="submit" class="finance-edit-btn" title="Remover lancamento" aria-label="Remover lancamento">
                                        <i data-lucide="trash-2" style="width:16px;"></i>
                                    </button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <?= render_pagination($pagination ?? [], 'financeiro', ['periodo' => $periodoAtual, 'tipo' => $tipoAtual]) ?>
        </div>
    </div>

    <aside class="finance-side">
        <div class="card today-card">
            <h4 class="brand-font">Resumo de Hoje</h4>
            <div class="today-row"><span>Receitas</span><strong style="color:#4ade80;">R$ <?= number_format($receitas_dia,2,',','.') ?></strong></div>
            <div class="today-row"><span>Despesas</span><strong style="color:#fca5a5;">R$ <?= number_format($despesas_dia,2,',','.') ?></strong></div>
            <div class="today-row"><span>Saldo</span><strong>R$ <?= number_format($saldo_dia,2,',','.') ?></strong></div>
        </div>

        <div class="card side-card">
            <h4 class="brand-font">Formas de Pagamento</h4>
            <?php if(empty($formas)): ?>
            <p style="color:var(--text-muted);font-size:.85rem;">Nenhuma receita no período selecionado.</p>
            <?php else: ?>
            <?php $totalFormas = array_sum(array_column($formas,'total')); foreach($formas as $f): $pct = $totalFormas > 0 ? ($f['total']/$totalFormas)*100 : 0; ?>
            <div style="margin-bottom:1rem;">
                <div class="side-row">
                    <div>
                        <strong><?= htmlspecialchars($f['forma_pagamento'] ?: 'Não informado') ?></strong><br>
                        <span><?= (int) $f['qtd'] ?> lançamento(s)</span>
                    </div>
                    <strong>R$ <?= number_format($f['total'],2,',','.') ?></strong>
                </div>
                <div class="payment-bar"><div style="width:<?= $pct ?>%;"></div></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card side-card">
            <h4 class="brand-font">Despesas por Categoria</h4>
            <?php if(empty($categorias_despesas)): ?>
            <p style="color:var(--text-muted);font-size:.85rem;">Nenhuma despesa no período selecionado.</p>
            <?php else: ?>
            <?php foreach($categorias_despesas as $cat): ?>
            <div class="side-row">
                <div>
                    <strong><?= htmlspecialchars($cat['categoria']) ?></strong><br>
                    <span><?= (int) $cat['qtd'] ?> lançamento(s)</span>
                </div>
                <strong style="color:var(--danger);">R$ <?= number_format($cat['total'],2,',','.') ?></strong>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>
</div>

<!-- Modal Lançamento -->
<div id="modal-fin" class="modal-overlay">
    <div class="modal-content" style="max-width:450px;">
        <h3 class="brand-font" id="finance-modal-title" style="margin-bottom:1.5rem;">Novo Lançamento</h3>
        <form action="<?= route_url('financeiro/store') ?>" method="POST" id="finance-form">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" id="finance-method" value="store">
            <input type="hidden" name="id" id="finance-id" value="">
            <div class="form-group"><label class="form-label">Tipo *</label>
                <select name="tipo" id="finance-tipo" class="form-control" required>
                    <option value="Receita">Receita</option>
                    <option value="Despesa">Despesa</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Categoria</label>
                <select name="categoria" id="finance-categoria" class="form-control">
                    <option value="Serviço">Serviço</option><option value="Venda de Peça">Venda de Peça</option><option value="Aluguel">Aluguel</option><option value="Compra de Estoque">Compra de Estoque</option><option value="Salário">Salário</option><option value="Outros">Outros</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Descrição *</label><input type="text" name="descricao" id="finance-descricao" class="form-control" required placeholder="Ex: Pagamento OS #2026-0001"></div>
            <div class="form-group"><label class="form-label">Valor (R$) *</label><input type="number" step="0.01" name="valor" id="finance-valor" class="form-control" required placeholder="0,00"></div>
            <div class="form-group"><label class="form-label">Forma de Pagamento</label>
                <select name="forma_pagamento" id="finance-forma" class="form-control">
                    <option value="Pix">Pix</option><option value="Dinheiro">Dinheiro</option><option value="Cartão de Débito">Cartão de Débito</option><option value="Cartão de Crédito">Cartão de Crédito</option><option value="Transferência">Transferência</option><option value="Boleto">Boleto</option><option value="Saldo Mercado Livre">Saldo Mercado Livre</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Data</label><input type="date" name="data_pagamento" id="finance-data" class="form-control" value="<?= date('Y-m-d') ?>"></div>
            <input type="hidden" name="os_id" id="finance-os-id" value="">
            <div style="display:flex;gap:.75rem;margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary" id="finance-submit" style="flex:1;">Lançar</button>
                <button type="button" onclick="closeFinanceModal()" class="btn btn-secondary" style="flex:1;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function setSelectValue(select, value) {
    if (!select) return;
    const normalized = value || '';
    const exists = Array.from(select.options).some((option) => option.value === normalized);
    if (!exists && normalized !== '') {
        select.add(new Option(normalized, normalized));
    }
    select.value = normalized || select.options[0]?.value || '';
}

function openFinanceModal(entry) {
    const form = document.getElementById('finance-form');
    form.reset();
    form.action = entry ? '<?= route_url('financeiro/update') ?>' : '<?= route_url('financeiro/store') ?>';
    document.getElementById('finance-method').value = entry ? 'update' : 'store';
    document.getElementById('finance-id').value = entry?.id || '';
    document.getElementById('finance-modal-title').textContent = entry ? 'Editar Lançamento' : 'Novo Lançamento';
    document.getElementById('finance-submit').textContent = entry ? 'Salvar alterações' : 'Lançar';

    if (entry) {
        setSelectValue(document.getElementById('finance-tipo'), entry.tipo);
        setSelectValue(document.getElementById('finance-categoria'), entry.categoria);
        setSelectValue(document.getElementById('finance-forma'), entry.forma_pagamento);
        document.getElementById('finance-descricao').value = entry.descricao || '';
        document.getElementById('finance-valor').value = entry.valor || '';
        document.getElementById('finance-data').value = entry.data_pagamento || '<?= date('Y-m-d') ?>';
        document.getElementById('finance-os-id').value = entry.os_id || '';
    } else {
        document.getElementById('finance-data').value = '<?= date('Y-m-d') ?>';
    }

    document.getElementById('modal-fin').style.display = 'flex';
}

function closeFinanceModal() {
    document.getElementById('modal-fin').style.display = 'none';
}
</script>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
