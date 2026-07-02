<?php
require __DIR__ . '/../layout/header.php';

$items = $items ?? [];
$filters = $filters ?? ['search' => '', 'status' => '', 'tipo' => ''];
$resumo = $resumo ?? [];
$statuses = $statuses ?? [];
$tipos = $tipos ?? [];
$prioridades = $prioridades ?? [];
$statusColors = [
    'Pendente' => 'badge-yellow',
    'Solicitado' => 'badge-blue',
    'Comprado' => 'badge-purple',
    'Recebido' => 'badge-green',
    'Cancelado' => 'badge-gray',
];
?>

<style>
    .purchase-page { display:grid;gap:1.25rem; }
    .purchase-hero {
        display:flex;align-items:center;justify-content:space-between;gap:1rem;
        background:linear-gradient(135deg,#0f2f6f,#0b63ce);color:#fff;border-radius:18px;
        padding:1.4rem 1.5rem;box-shadow:0 18px 45px -28px rgba(15,47,111,.7);
    }
    .purchase-hero h2 { font-family:'Outfit',sans-serif;font-size:1.35rem;margin:0 0 .25rem; }
    .purchase-hero p { margin:0;color:rgba(255,255,255,.78);font-size:.9rem; }
    .purchase-hero i { width:46px;height:46px;opacity:.85; }
    .purchase-summary { display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.85rem; }
    .summary-card { background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:1rem;box-shadow:var(--shadow-sm); }
    .summary-card span { display:block;color:var(--text-muted);font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em; }
    .summary-card strong { display:block;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--primary);margin-top:.2rem; }
    .purchase-panel { background:var(--bg-card);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow-sm);padding:1.25rem; }
    .purchase-panel h3 { font-family:'Outfit',sans-serif;font-size:1.05rem;margin:0 0 1rem;color:var(--text-main); }
    .purchase-form-grid { display:grid;grid-template-columns:minmax(240px,2fr) 150px 110px 150px 150px minmax(190px,1.2fr);gap:.85rem;align-items:end; }
    .purchase-form-notes { grid-column:1 / -2; }
    .purchase-form-submit { height:46px;justify-content:center; }
    .purchase-filters { display:grid;grid-template-columns:minmax(180px,1fr) 150px 150px auto;gap:.75rem;align-items:end;margin-bottom:1rem; }
    .table-wrap { overflow:auto;border:1px solid var(--border);border-radius:14px; }
    .purchase-table { width:100%;border-collapse:collapse;min-width:780px;background:#fff; }
    .purchase-table th { background:#f8fafc;color:#475569;font-size:.76rem;text-align:left;text-transform:uppercase;letter-spacing:.03em;padding:.85rem; }
    .purchase-table td { padding:.85rem;border-top:1px solid var(--border);vertical-align:middle;font-size:.88rem; }
    .requested-item { min-width:260px; }
    .item-name { font-weight:800;color:var(--text-main);display:block;margin-bottom:.35rem;font-size:.98rem; }
    .item-note { color:var(--text-muted);font-size:.78rem;line-height:1.35;max-width:360px;display:block;margin-top:.3rem; }
    .item-kind { display:inline-flex;align-items:center;width:max-content;padding:3px 8px;border-radius:999px;background:#eef4ff;color:#00349a;font-size:.7rem;font-weight:800; }
    .status-form { display:flex;gap:.45rem;align-items:center; }
    .status-form select { min-width:124px;padding:.55rem .7rem;font-size:.82rem; }
    .icon-action {
        width:34px;height:34px;border-radius:10px;border:1px solid var(--border);background:#fff;color:var(--primary);
        display:inline-flex;align-items:center;justify-content:center;cursor:pointer;
    }
    .icon-action.danger { color:var(--danger); }
    .empty-state { text-align:center;padding:2.5rem 1rem;color:var(--text-muted); }
    @media(max-width:1180px){
        .purchase-summary { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .purchase-form-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .purchase-form-notes { grid-column:1 / -1; }
        .purchase-form-submit { grid-column:1 / -1; }
    }
    @media(max-width:760px){
        .purchase-hero { align-items:flex-start; }
        .purchase-summary { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .purchase-filters,.purchase-form-grid { grid-template-columns:1fr; }
        .purchase-form-notes,.purchase-form-submit { grid-column:auto; }
    }
</style>

<?php if (isset($_GET['success'])): ?>
    <div class="alert-success system-flash"><i data-lucide="check-circle"></i> Solicitação registrada com sucesso.</div>
<?php elseif (isset($_GET['updated'])): ?>
    <div class="alert-success system-flash"><i data-lucide="check-circle"></i> Status atualizado com sucesso.</div>
<?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert-success system-flash"><i data-lucide="check-circle"></i> Solicitação removida.</div>
<?php elseif (($_GET['error'] ?? '') === 'nome'): ?>
    <div class="alert-success" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;"><i data-lucide="alert-triangle"></i> Informe o nome da peça ou produto.</div>
<?php endif; ?>

<section class="purchase-page">
    <div class="purchase-hero">
        <div>
            <h2>Itens faltando para compra</h2>
            <p>Registre peças técnicas e produtos que precisam ser solicitados ao fornecedor.</p>
        </div>
        <i data-lucide="shopping-basket"></i>
    </div>

    <div class="purchase-summary">
        <?php foreach ($statuses as $status): ?>
            <div class="summary-card">
                <span><?= htmlspecialchars($status) ?></span>
                <strong><?= (int) ($resumo[$status] ?? 0) ?></strong>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="purchase-panel">
        <h3>Nova solicitação</h3>
        <form method="POST" action="<?= e(route_url('compras/store')) ?>">
            <?= csrf_field() ?>
            <div class="purchase-form-grid">
                <div class="form-group">
                    <label>Produto ou peça solicitada</label>
                    <input type="text" class="form-control" name="item_nome" placeholder="Ex: Película 3D, tela iPhone 11, cabo USB-C..." required>
                </div>

                <div class="form-group">
                    <label>Tipo</label>
                    <select class="form-control" name="tipo">
                        <?php foreach ($tipos as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Qtd</label>
                    <input type="number" class="form-control" name="quantidade" value="1" min="1" step="1" required>
                </div>

                <div class="form-group">
                    <label>Prioridade</label>
                    <select class="form-control" name="prioridade">
                        <?php foreach ($prioridades as $prioridade): ?>
                            <option value="<?= htmlspecialchars($prioridade) ?>" <?= $prioridade === 'Normal' ? 'selected' : '' ?>><?= htmlspecialchars($prioridade) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data</label>
                    <input type="date" class="form-control" name="data_solicitacao" value="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label>Fornecedor</label>
                    <input type="text" class="form-control" name="fornecedor" placeholder="Opcional">
                </div>

                <div class="form-group purchase-form-notes">
                    <label>Observações</label>
                    <input type="text" class="form-control" name="observacoes" placeholder="Modelo, cor, voltagem, urgência ou qualquer detalhe importante.">
                </div>

                <button class="btn btn-primary purchase-form-submit" type="submit">
                    <i data-lucide="plus"></i> Registrar
                </button>
            </div>
        </form>
    </div>

    <div class="purchase-panel">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;">
            <h3 style="margin:0;">Lista de solicitações</h3>
            <a href="<?= route_url('compras/print', $filters) ?>" target="_blank" class="btn btn-secondary" style="text-decoration:none;">
                <i data-lucide="printer"></i> Imprimir / PDF
            </a>
        </div>
        <form class="purchase-filters" method="GET" action="<?= route_url() ?>">
            <input type="hidden" name="url" value="compras">
            <div class="form-group">
                <label>Buscar</label>
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Produto, peça, fornecedor ou observação">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status">
                    <option value="">Todos</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= htmlspecialchars($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <select class="form-control" name="tipo">
                    <option value="">Todos</option>
                    <?php foreach ($tipos as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= ($filters['tipo'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-secondary" type="submit"><i data-lucide="filter"></i> Filtrar</button>
        </form>

        <div class="table-wrap">
            <table class="purchase-table">
                <thead>
                    <tr>
                        <th>Produto / peça solicitada</th>
                        <th>Qtd</th>
                        <th>Fornecedor</th>
                        <th>Prioridade</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="7"><div class="empty-state">Nenhuma solicitação registrada ainda.</div></td></tr>
                <?php endif; ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="requested-item">
                            <span class="item-name"><?= htmlspecialchars($item['item_nome']) ?></span>
                            <span class="item-kind"><?= htmlspecialchars($tipos[$item['tipo']] ?? $item['tipo']) ?></span>
                            <?php if (!empty($item['observacoes'])): ?>
                                <span class="item-note"><?= htmlspecialchars($item['observacoes']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= (int) $item['quantidade'] ?></td>
                        <td><?= htmlspecialchars($item['fornecedor'] ?: '-') ?></td>
                        <td><span class="badge <?= $item['prioridade'] === 'Urgente' ? 'badge-red' : ($item['prioridade'] === 'Alta' ? 'badge-yellow' : 'badge-gray') ?>"><?= htmlspecialchars($item['prioridade']) ?></span></td>
                        <td><span class="badge <?= $statusColors[$item['status']] ?? 'badge-gray' ?>"><?= htmlspecialchars($item['status']) ?></span></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($item['data_solicitacao']))) ?></td>
                        <td>
                            <div style="display:flex;gap:.45rem;align-items:center;">
                                <form class="status-form" method="POST" action="<?= e(route_url('compras/updateStatus')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <select class="form-control" name="status">
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?= htmlspecialchars($status) ?>" <?= $item['status'] === $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="icon-action" type="submit" title="Atualizar status" aria-label="Atualizar status"><i data-lucide="save" style="width:16px;"></i></button>
                                </form>
                                <?php if (current_user_profile() === 'Administrador'): ?>
                                    <form method="POST" action="<?= e(route_url('compras/delete')) ?>" data-confirm="Remover esta solicitação?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button class="icon-action danger" type="submit" title="Remover" aria-label="Remover"><i data-lucide="trash-2" style="width:16px;"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= render_pagination($pagination ?? [], 'compras', $filters) ?>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
