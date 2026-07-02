<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>
<?php
$periodoLabels = [
    'dia' => 'Hoje',
    'semana' => 'Semana',
    'mes' => 'Mes',
    'todos' => 'Todos',
];
$periodoAtual = $periodo ?? 'mes';
$tipoAtual = $tipo ?? '';
$saldoClass = ($saldo ?? 0) >= 0 ? 'success' : 'danger';
$formatMoney = static fn($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
$categoriasCadastro = $categoriasCadastro ?? [];
$totalLancamentos = (int) ($pagination['total'] ?? count($lancamentos ?? []));
$ticketMedio = $totalLancamentos > 0 ? ((float) ($despesas ?? 0) / max(1, $totalLancamentos)) : 0;
?>

<style>
    .personal-command {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
        padding: 1rem;
        margin-bottom: 1.25rem;
    }
    .personal-command h3 {
        margin: 0;
        font-size: 1.05rem;
    }
    .personal-command p {
        margin: .2rem 0 0;
        color: var(--text-muted);
        font-size: .84rem;
    }
    .personal-filters {
        display: flex;
        gap: .6rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .personal-command-actions {
        display: flex;
        align-items: center;
        gap: .7rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .personal-filters .form-control {
        width: auto;
        min-width: 132px;
        height: 42px;
        padding: 8px 12px;
    }
    .personal-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(300px, .85fr);
        gap: 1.25rem;
        align-items: start;
    }
    .personal-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .personal-stat {
        display: flex;
        align-items: center;
        gap: .9rem;
        padding: 1.05rem;
        min-height: 104px;
        border-bottom: 3px solid var(--border);
    }
    .personal-stat.success { border-bottom-color: var(--success); }
    .personal-stat.danger { border-bottom-color: var(--danger); }
    .personal-stat.primary { border-bottom-color: var(--primary); }
    .personal-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .personal-stat p {
        margin: 0 0 .35rem;
        color: var(--text-muted);
        font-size: .78rem;
        font-weight: 700;
    }
    .personal-stat h3 {
        margin: 0;
        color: var(--primary);
        font-size: 1.25rem;
        overflow-wrap: anywhere;
    }
    .personal-table-card,
    .personal-form-card,
    .personal-side-card {
        padding: 1.4rem;
    }
    .personal-table-head,
    .personal-form-head {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    .personal-table-head h3,
    .personal-form-head h3,
    .personal-side-card h3 {
        margin: 0;
        font-size: 1.02rem;
    }
    .personal-table-head p,
    .personal-form-head p {
        margin: .2rem 0 0;
        color: var(--text-muted);
        font-size: .78rem;
    }
    .personal-table-card table {
        min-width: 780px;
    }
    .personal-amount.receita { color: var(--success); }
    .personal-amount.despesa { color: var(--danger); }
    .personal-actions {
        display: inline-flex;
        gap: .25rem;
        align-items: center;
    }
    .personal-icon-btn {
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
    }
    .personal-icon-btn:hover {
        background: rgba(0, 52, 154, .08);
    }
    .personal-icon-btn.danger {
        color: var(--danger);
    }
    .personal-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .9rem;
    }
    .personal-form-grid .span-2 {
        grid-column: 1 / -1;
    }
    .personal-side-list {
        display: flex;
        flex-direction: column;
        gap: .65rem;
    }
    .personal-side-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .8rem;
        align-items: center;
        padding: .65rem 0;
        border-bottom: 1px solid var(--border);
    }
    .personal-side-row:last-child {
        border-bottom: 0;
    }
    .personal-side-row span {
        color: var(--text-muted);
        font-size: .78rem;
    }
    .personal-bar {
        width: 100%;
        height: 7px;
        background: rgba(148, 163, 184, .25);
        border-radius: 999px;
        margin-top: .4rem;
        overflow: hidden;
    }
    .personal-bar > div {
        height: 100%;
        border-radius: inherit;
        background: var(--primary);
    }
    @media(max-width: 1100px) {
        .personal-grid,
        .personal-command {
            grid-template-columns: 1fr;
        }
        .personal-stats {
            grid-template-columns: 1fr;
        }
    }
    @media(max-width: 640px) {
        .personal-form-grid {
            grid-template-columns: 1fr;
        }
        .personal-filters .form-control,
        .personal-filters .btn {
            width: 100%;
        }
    }
    .personal-command {
        grid-template-columns: minmax(0, 1fr) minmax(420px, auto);
        padding: 1.15rem 1.25rem;
        border: 1px solid rgba(0, 52, 154, .12);
        background: linear-gradient(135deg, #fff 0%, #f8fbff 100%);
    }
    .personal-command-kpis { display:flex;gap:.55rem;flex-wrap:wrap;margin-top:.8rem; }
    .personal-chip { display:inline-flex;align-items:center;gap:.4rem;padding:.38rem .62rem;border-radius:999px;background:#fff;border:1px solid rgba(0,52,154,.1);color:#334155;font-size:.76rem;font-weight:800; }
    .personal-grid { grid-template-columns:minmax(0,1.55fr) minmax(360px,.9fr); align-items:start; }
    .personal-command-actions .btn { min-height:42px;white-space:nowrap; }
    .personal-stat { position:relative;overflow:hidden;padding:1.15rem; }
    .personal-stat:after { content:"";position:absolute;inset:auto -34px -42px auto;width:104px;height:104px;border-radius:999px;background:rgba(0,52,154,.045); }
    .personal-stat h3 { font-size:1.42rem; }
    .personal-stat small { display:block;margin-top:.35rem;color:var(--text-muted);font-size:.72rem;font-weight:700; }
    .personal-form-card { position:relative; z-index:1; }
    .personal-grid aside { position:relative; z-index:1; }
    .personal-grid aside > * + * { margin-top:.95rem; }
    .personal-table-card table { border-collapse:separate;border-spacing:0;overflow:hidden; }
    .personal-table-card thead th { position:sticky;top:0;z-index:1;background:#eef4ff;color:#334155;font-size:.72rem;letter-spacing:.03em; }
    .personal-table-card tbody tr:hover { background:#f8fbff; }
    .personal-table-card td,.personal-table-card th { vertical-align:middle; }
    .personal-desc { display:flex;align-items:center;gap:.6rem;min-width:0; }
    .personal-desc-icon { width:34px;height:34px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;background:rgba(0,52,154,.08);color:var(--primary);flex:0 0 auto; }
    .personal-desc strong { display:block;line-height:1.2; }
    .personal-details { border:1px solid rgba(0,52,154,.1);border-radius:14px;background:#f8fbff;padding:.85rem; }
    .personal-details summary { cursor:pointer;list-style:none;display:flex;align-items:center;justify-content:space-between;gap:1rem;font-weight:900;color:#071b37; }
    .personal-details summary::-webkit-details-marker { display:none; }
    .personal-details summary:after { content:"+";width:24px;height:24px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;background:#fff;color:var(--primary);border:1px solid rgba(0,52,154,.12); }
    .personal-details[open] summary:after { content:"-"; }
    @media(max-width:1100px){ .personal-command,.personal-grid{grid-template-columns:1fr;} }
    @media(max-width:640px){ .personal-command-actions,.personal-filters{width:100%;} .personal-command-actions .btn{width:100%;} }
</style>

<div class="card personal-command fade-in">
    <div>
        <h3 class="brand-font">Controle de gastos pessoais</h3>
        <p>Area privada do proprietario para acompanhar entradas, despesas, formas de pagamento e categorias.</p>
        <div class="personal-command-kpis">
            <span class="personal-chip"><i data-lucide="receipt-text" style="width:14px;"></i><?= $totalLancamentos ?> lancamento(s)</span>
            <span class="personal-chip"><i data-lucide="calendar-days" style="width:14px;"></i><?= htmlspecialchars($periodoLabels[$periodoAtual] ?? 'Mes') ?></span>
            <span class="personal-chip"><i data-lucide="gauge" style="width:14px;"></i>Media: <?= $formatMoney($ticketMedio) ?></span>
        </div>
    </div>
    <div class="personal-command-actions">
        <form class="personal-filters" method="GET" action="<?= route_url() ?>">
            <input type="hidden" name="url" value="gastos_pessoais">
            <select name="periodo" class="form-control">
                <?php foreach ($periodoLabels as $key => $label): ?>
                <option value="<?= $key ?>" <?= $periodoAtual === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <select name="tipo" class="form-control">
                <option value="" <?= $tipoAtual === '' ? 'selected' : '' ?>>Todos</option>
                <option value="Receita" <?= $tipoAtual === 'Receita' ? 'selected' : '' ?>>Entradas</option>
                <option value="Despesa" <?= $tipoAtual === 'Despesa' ? 'selected' : '' ?>>Despesas</option>
            </select>
            <button class="btn btn-primary" type="submit"><i data-lucide="filter"></i> Filtrar</button>
        </form>
        <a href="<?= route_url('gastos_pessoais/print', ['periodo' => $periodoAtual, 'tipo' => $tipoAtual]) ?>" target="_blank" class="btn btn-outline" style="text-decoration:none;">
            <i data-lucide="printer"></i> Imprimir
        </a>
        <button type="button" class="btn btn-secondary" onclick="prepararNovoGasto()">
            <i data-lucide="plus"></i> Novo
        </button>
    </div>
</div>

<div class="personal-stats">
    <div class="card personal-stat success">
        <div class="personal-stat-icon" style="background:rgba(16,185,129,.12);color:var(--success);"><i data-lucide="trending-up"></i></div>
        <div>
            <p>Entradas <?= htmlspecialchars($periodoLabels[$periodoAtual] ?? '') ?></p>
            <h3><?= $formatMoney($entradas ?? 0) ?></h3>
            <small>Valores pessoais recebidos</small>
        </div>
    </div>
    <div class="card personal-stat danger">
        <div class="personal-stat-icon" style="background:rgba(239,68,68,.12);color:var(--danger);"><i data-lucide="trending-down"></i></div>
        <div>
            <p>Gastos <?= htmlspecialchars($periodoLabels[$periodoAtual] ?? '') ?></p>
            <h3><?= $formatMoney($despesas ?? 0) ?></h3>
            <small>Saidas registradas no periodo</small>
        </div>
    </div>
    <div class="card personal-stat <?= $saldoClass ?>">
        <div class="personal-stat-icon" style="background:rgba(0,52,154,.1);color:var(--primary);"><i data-lucide="wallet"></i></div>
        <div>
            <p>Saldo pessoal</p>
            <h3><?= $formatMoney($saldo ?? 0) ?></h3>
            <small><?= ($saldo ?? 0) >= 0 ? 'Periodo positivo' : 'Periodo negativo' ?></small>
        </div>
    </div>
</div>

<div class="card personal-form-card" id="novo-lancamento-card" style="margin-bottom:1.25rem;">
    <div class="personal-form-head">
        <div>
            <h3 class="brand-font" id="gasto-form-title">Novo lancamento</h3>
            <p>Registre uma despesa ou entrada pessoal.</p>
        </div>
    </div>
    <form action="<?= e(route_url('gastos_pessoais/store')) ?>" method="POST" id="gasto-pessoal-form">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="gasto-id">
        <div class="personal-form-grid">
            <div class="form-group">
                <label class="form-label">Tipo</label>
                <select class="form-control" name="tipo" id="gasto-tipo">
                    <option value="Despesa">Despesa</option>
                    <option value="Receita">Receita</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Data</label>
                <input class="form-control" type="date" name="data_lancamento" id="gasto-data" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group span-2">
                <label class="form-label">Descricao</label>
                <input class="form-control" type="text" name="descricao" id="gasto-descricao" placeholder="Ex: mercado, escola, combustivel">
            </div>
            <div class="form-group">
                <label class="form-label">Categoria</label>
                <input class="form-control" type="text" name="categoria" id="gasto-categoria" list="categorias-pessoais" placeholder="Moradia, Alimentacao...">
                <datalist id="categorias-pessoais">
                    <?php foreach ($categoriasCadastro as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['nome']) ?>">
                    <?php endforeach; ?>
                    <option value="Alimentacao">
                    <option value="Moradia">
                    <option value="Transporte">
                    <option value="Saude">
                    <option value="Familia">
                    <option value="Lazer">
                    <option value="Cartao">
                    <option value="Investimento">
                </datalist>
            </div>
            <div class="form-group">
                <label class="form-label">Valor</label>
                <input class="form-control" type="text" name="valor" id="gasto-valor" placeholder="0,00" inputmode="decimal" required>
            </div>
            <div class="form-group">
                <label class="form-label">Forma</label>
                <input class="form-control" type="text" name="forma_pagamento" id="gasto-forma" list="formas-pessoais" placeholder="Pix, debito, credito">
                <datalist id="formas-pessoais">
                    <option value="Pix">
                    <option value="Dinheiro">
                    <option value="Debito">
                    <option value="Credito">
                    <option value="Boleto">
                    <option value="Transferencia">
                    <option value="Saldo Mercado Livre">
                </datalist>
            </div>
            <div class="form-group" style="display:flex;align-items:end;">
                <label style="display:flex;align-items:center;gap:.5rem;font-weight:700;color:var(--text-main);">
                    <input type="checkbox" name="recorrente" id="gasto-recorrente" value="1"> Recorrente
                </label>
            </div>
            <div class="form-group span-2">
                <label class="form-label">Observacoes</label>
                <textarea class="form-control" name="observacoes" id="gasto-observacoes" rows="2" placeholder="Detalhes opcionais"></textarea>
            </div>
            <div class="span-2" style="display:flex;gap:.65rem;flex-wrap:wrap;">
                <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Salvar</button>
                <button class="btn btn-secondary" type="button" onclick="limparFormularioGasto()">Cancelar</button>
            </div>
        </div>
    </form>
</div>

<div class="personal-grid">
    <div class="personal-main">
        <div class="card personal-table-card">
            <div class="personal-table-head">
                <div>
                    <h3 class="brand-font">Lancamentos</h3>
                    <p><?= (int) ($pagination['total'] ?? count($lancamentos ?? [])) ?> registro(s) no filtro atual</p>
                </div>
                <button type="button" class="btn btn-outline" onclick="prepararNovoGasto()"><i data-lucide="plus"></i> Novo lancamento</button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Descricao</th>
                            <th>Categoria</th>
                            <th>Forma</th>
                            <th>Tipo</th>
                            <th style="text-align:right;">Valor</th>
                            <th style="text-align:right;">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lancamentos)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">Nenhum gasto pessoal cadastrado neste filtro.</td>
                        </tr>
                        <?php endif; ?>
                        <?php foreach (($lancamentos ?? []) as $item): ?>
                        <?php $entryJson = json_attr($item); ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($item['data_lancamento'])) ?></td>
                            <td>
                                <div class="personal-desc">
                                    <span class="personal-desc-icon"><i data-lucide="<?= $item['tipo'] === 'Receita' ? 'arrow-down-left' : 'arrow-up-right' ?>" style="width:16px;"></i></span>
                                    <div>
                                        <strong><?= htmlspecialchars($item['descricao']) ?></strong>
                                        <?php if (!empty($item['recorrente'])): ?>
                                        <span class="badge badge-purple" style="margin-top:.3rem;">recorrente</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($item['categoria'] ?: 'Sem categoria') ?></td>
                            <td><?= htmlspecialchars($item['forma_pagamento'] ?: 'Nao informado') ?></td>
                            <td><span class="badge <?= $item['tipo'] === 'Receita' ? 'badge-green' : 'badge-red' ?>"><?= htmlspecialchars($item['tipo']) ?></span></td>
                            <td style="text-align:right;"><strong class="personal-amount <?= $item['tipo'] === 'Receita' ? 'receita' : 'despesa' ?>"><?= $formatMoney($item['valor']) ?></strong></td>
                            <td style="text-align:right;">
                                <div class="personal-actions">
                                    <button type="button" class="personal-icon-btn" onclick="editarGastoPessoal(<?= e($entryJson) ?>)" title="Editar">
                                        <i data-lucide="pencil" style="width:16px;"></i>
                                    </button>
                                    <form action="<?= e(route_url('gastos_pessoais/delete')) ?>" method="POST" data-confirm="Remover este lancamento pessoal?" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button type="submit" class="personal-icon-btn danger" title="Remover">
                                            <i data-lucide="trash-2" style="width:16px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= render_pagination($pagination ?? [], 'gastos_pessoais', ['periodo' => $periodoAtual, 'tipo' => $tipoAtual]) ?>
        </div>
    </div>

    <aside style="display:flex;flex-direction:column;gap:1rem;">
        <details class="card personal-side-card personal-details">
            <summary>Criar categorias</summary>
            <form action="<?= e(route_url('gastos_pessoais/addCategoria')) ?>" method="POST" style="display:grid;grid-template-columns:minmax(0,1fr) 130px;gap:.65rem;margin-top:.85rem;">
                <?= csrf_field() ?>
                <input class="form-control" type="text" name="nome" placeholder="Nova categoria" required>
                <select class="form-control" name="tipo">
                    <option value="Despesa">Despesa</option>
                    <option value="Receita">Receita</option>
                    <option value="Ambos">Ambos</option>
                </select>
                <button class="btn btn-primary" type="submit" style="grid-column:1 / -1;"><i data-lucide="plus"></i> Adicionar categoria</button>
            </form>
            <div class="personal-side-list" style="margin-top:1rem;">
                <?php if (empty($categoriasCadastro)): ?>
                <p style="color:var(--text-muted);font-size:.85rem;margin:0;">Nenhuma categoria personalizada ainda.</p>
                <?php endif; ?>
                <?php foreach ($categoriasCadastro as $cat): ?>
                <div class="personal-side-row">
                    <div>
                        <strong><?= htmlspecialchars($cat['nome']) ?></strong>
                        <span><?= htmlspecialchars($cat['tipo']) ?></span>
                    </div>
                    <form action="<?= e(route_url('gastos_pessoais/deleteCategoria')) ?>" method="POST" data-confirm="Remover esta categoria pessoal? Os lancamentos antigos nao serao apagados.">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                        <button type="submit" class="personal-icon-btn danger" title="Remover categoria"><i data-lucide="trash-2" style="width:16px;"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </details>

        <details class="card personal-side-card personal-details" open>
            <summary>Categorias de gastos</summary>
            <div class="personal-side-list" style="margin-top:.75rem;">
                <?php $maxCategoria = max(array_map(static fn($r) => (float) $r['total'], $categorias ?: [['total' => 0]])); ?>
                <?php if (empty($categorias)): ?>
                <p style="color:var(--text-muted);font-size:.85rem;margin:0;">Sem despesas no periodo.</p>
                <?php endif; ?>
                <?php foreach (($categorias ?? []) as $cat): ?>
                <?php $pct = $maxCategoria > 0 ? min(100, ((float) $cat['total'] / $maxCategoria) * 100) : 0; ?>
                <div class="personal-side-row">
                    <div>
                        <strong><?= htmlspecialchars($cat['categoria']) ?></strong>
                        <span><?= (int) $cat['qtd'] ?> lancamento(s)</span>
                        <div class="personal-bar"><div style="width:<?= $pct ?>%;"></div></div>
                    </div>
                    <strong><?= $formatMoney($cat['total']) ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
        </details>

        <details class="card personal-side-card personal-details">
            <summary>Formas de pagamento</summary>
            <div class="personal-side-list" style="margin-top:.75rem;">
                <?php if (empty($formas)): ?>
                <p style="color:var(--text-muted);font-size:.85rem;margin:0;">Sem dados no periodo.</p>
                <?php endif; ?>
                <?php foreach (($formas ?? []) as $forma): ?>
                <div class="personal-side-row">
                    <div>
                        <strong><?= htmlspecialchars($forma['forma_pagamento']) ?></strong>
                        <span><?= (int) $forma['qtd'] ?> lancamento(s)</span>
                    </div>
                    <strong><?= $formatMoney($forma['total']) ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
        </details>
    </aside>
</div>

<script>
function moedaParaInput(value) {
    const number = Number(value || 0);
    return number.toFixed(2).replace('.', ',');
}

function limparFormularioGasto() {
    const form = document.getElementById('gasto-pessoal-form');
    form.action = '<?= route_url('gastos_pessoais/store') ?>';
    form.reset();
    document.getElementById('gasto-id').value = '';
    document.getElementById('gasto-data').value = '<?= date('Y-m-d') ?>';
    document.getElementById('gasto-form-title').textContent = 'Novo lancamento';
}

function prepararNovoGasto() {
    limparFormularioGasto();
    document.getElementById('novo-lancamento-card')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function editarGastoPessoal(entry) {
    const form = document.getElementById('gasto-pessoal-form');
    form.action = '<?= route_url('gastos_pessoais/update') ?>';
    document.getElementById('gasto-form-title').textContent = 'Editar lancamento';
    document.getElementById('gasto-id').value = entry.id || '';
    document.getElementById('gasto-tipo').value = entry.tipo || 'Despesa';
    document.getElementById('gasto-data').value = entry.data_lancamento || '<?= date('Y-m-d') ?>';
    document.getElementById('gasto-descricao').value = entry.descricao || '';
    document.getElementById('gasto-categoria').value = entry.categoria || '';
    document.getElementById('gasto-valor').value = moedaParaInput(entry.valor || 0);
    document.getElementById('gasto-forma').value = entry.forma_pagamento || '';
    document.getElementById('gasto-recorrente').checked = Number(entry.recorrente || 0) === 1;
    document.getElementById('gasto-observacoes').value = entry.observacoes || '';
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
