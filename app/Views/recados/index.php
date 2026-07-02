<?php
require __DIR__ . '/../layout/header.php';

$items = $items ?? [];
$filters = $filters ?? ['search' => '', 'status' => ''];
$resumo = $resumo ?? [];
$statuses = $statuses ?? [];
$statusColors = [
    'Pendente' => 'badge-yellow',
    'Lido' => 'badge-blue',
    'Respondido' => 'badge-green',
    'Arquivado' => 'badge-gray',
];
?>

<style>
    .messages-page { display:grid;gap:1.25rem; }
    .messages-hero {
        display:flex;align-items:center;justify-content:space-between;gap:1rem;
        background:linear-gradient(135deg,#002b7a,#0b63ce);color:#fff;border-radius:18px;
        padding:1.4rem 1.5rem;box-shadow:0 18px 45px -28px rgba(15,47,111,.7);
    }
    .messages-hero h2 { font-family:'Outfit',sans-serif;font-size:1.35rem;margin:0 0 .25rem; }
    .messages-hero p { margin:0;color:rgba(255,255,255,.78);font-size:.9rem; }
    .messages-hero i { width:46px;height:46px;opacity:.88; }
    .messages-summary { display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.85rem; }
    .message-summary-card { background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:1rem;box-shadow:var(--shadow-sm); }
    .message-summary-card span { display:block;color:var(--text-muted);font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em; }
    .message-summary-card strong { display:block;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--primary);margin-top:.2rem; }
    .messages-panel { background:var(--bg-card);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow-sm);padding:1.25rem; }
    .messages-panel h3 { font-family:'Outfit',sans-serif;font-size:1.05rem;margin:0 0 1rem;color:var(--text-main); }
    .message-form-grid { display:grid;grid-template-columns:minmax(210px,1fr) minmax(170px,.8fr) 150px auto;gap:.85rem;align-items:end; }
    .message-form-text { grid-column:1 / -2; }
    .message-form-submit { height:46px;justify-content:center; }
    .message-filters { display:grid;grid-template-columns:minmax(220px,1fr) 170px auto;gap:.75rem;align-items:end;margin-bottom:1rem; }
    .message-list { display:grid;gap:.85rem; }
    .message-card {
        display:grid;grid-template-columns:minmax(0,1fr) auto;gap:1rem;
        border:1px solid var(--border);border-radius:14px;padding:1rem;background:#fff;
    }
    .message-card h4 { margin:0;font-family:'Outfit',sans-serif;font-size:1rem;color:var(--text-main); }
    .message-meta { display:flex;flex-wrap:wrap;gap:.45rem .9rem;color:var(--text-muted);font-size:.8rem;margin:.35rem 0 .7rem; }
    .message-meta span { display:inline-flex;align-items:center;gap:.3rem; }
    .message-text { margin:0;color:#334155;line-height:1.5;font-size:.92rem;white-space:pre-wrap; }
    .message-actions { display:flex;gap:.45rem;align-items:flex-start;justify-content:flex-end; }
    .status-form { display:flex;gap:.45rem;align-items:center; }
    .status-form select { min-width:128px;padding:.55rem .7rem;font-size:.82rem; }
    .icon-action {
        width:34px;height:34px;border-radius:10px;border:1px solid var(--border);background:#fff;color:var(--primary);
        display:inline-flex;align-items:center;justify-content:center;cursor:pointer;
    }
    .icon-action.danger { color:var(--danger); }
    .empty-state { text-align:center;padding:2.5rem 1rem;color:var(--text-muted);border:1px dashed var(--border);border-radius:14px;background:#f8fafc; }
    @media(max-width:980px){
        .messages-summary { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .message-form-grid { grid-template-columns:1fr 1fr; }
        .message-form-text,.message-form-submit { grid-column:1 / -1; }
        .message-card { grid-template-columns:1fr; }
        .message-actions { justify-content:flex-start; }
    }
    @media(max-width:680px){
        .message-form-grid,.message-filters { grid-template-columns:1fr; }
        .messages-summary { grid-template-columns:1fr; }
        .messages-hero { align-items:flex-start; }
        .status-form { flex-wrap:wrap; }
    }
</style>

<?php if (isset($_GET['success'])): ?>
    <div class="alert-success system-flash"><i data-lucide="check-circle"></i> Recado registrado com sucesso.</div>
<?php elseif (isset($_GET['updated'])): ?>
    <div class="alert-success system-flash"><i data-lucide="check-circle"></i> Status do recado atualizado.</div>
<?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert-success system-flash"><i data-lucide="check-circle"></i> Recado removido.</div>
<?php elseif (($_GET['error'] ?? '') === 'nome'): ?>
    <div class="alert-success" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;"><i data-lucide="alert-triangle"></i> Informe o nome da pessoa.</div>
<?php elseif (($_GET['error'] ?? '') === 'mensagem'): ?>
    <div class="alert-success" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;"><i data-lucide="alert-triangle"></i> Informe o recado.</div>
<?php endif; ?>

<section class="messages-page">
    <div class="messages-hero">
        <div>
            <h2>Agenda de recados do proprietario</h2>
            <p>Registre quem ligou, o telefone para retorno e o assunto deixado para a loja.</p>
        </div>
        <i data-lucide="message-square-text"></i>
    </div>

    <div class="messages-summary">
        <?php foreach ($statuses as $status): ?>
            <div class="message-summary-card">
                <span><?= htmlspecialchars($status) ?></span>
                <strong><?= (int) ($resumo[$status] ?? 0) ?></strong>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="messages-panel">
        <h3>Novo recado</h3>
        <form method="POST" action="<?= e(route_url('recados/store')) ?>">
            <?= csrf_field() ?>
            <div class="message-form-grid">
                <div class="form-group">
                    <label>Nome da pessoa</label>
                    <input type="text" class="form-control" name="nome" placeholder="Ex: Joao Silva" required>
                </div>

                <div class="form-group">
                    <label>Telefone / WhatsApp</label>
                    <input type="text" class="form-control" name="telefone" placeholder="(00) 00000-0000">
                </div>

                <div class="form-group">
                    <label>Data</label>
                    <input type="date" class="form-control" name="data_recado" value="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group message-form-text">
                    <label>Recado</label>
                    <textarea class="form-control" name="mensagem" rows="3" placeholder="Digite o recado deixado para o proprietario..." required></textarea>
                </div>

                <button class="btn btn-primary message-form-submit" type="submit">
                    <i data-lucide="plus"></i> Adicionar recado
                </button>
            </div>
        </form>
    </div>

    <div class="messages-panel">
        <h3>Recados registrados</h3>
        <form class="message-filters" method="GET" action="<?= route_url() ?>">
            <input type="hidden" name="url" value="recados">
            <div class="form-group">
                <label>Buscar</label>
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Nome, telefone ou texto do recado">
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
            <button class="btn btn-secondary" type="submit"><i data-lucide="filter"></i> Filtrar</button>
        </form>

        <div class="message-list">
            <?php if (empty($items)): ?>
                <div class="empty-state">Nenhum recado registrado ainda.</div>
            <?php endif; ?>

            <?php foreach ($items as $item): ?>
                <article class="message-card">
                    <div>
                        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                            <h4><?= htmlspecialchars($item['nome']) ?></h4>
                            <span class="badge <?= $statusColors[$item['status']] ?? 'badge-gray' ?>"><?= htmlspecialchars($item['status']) ?></span>
                        </div>
                        <div class="message-meta">
                            <span><i data-lucide="phone" style="width:14px;"></i><?= htmlspecialchars($item['telefone'] ?: 'Telefone nao informado') ?></span>
                            <span><i data-lucide="calendar-days" style="width:14px;"></i><?= htmlspecialchars(date('d/m/Y', strtotime($item['data_recado']))) ?></span>
                            <span><i data-lucide="user" style="width:14px;"></i><?= htmlspecialchars($item['usuario_nome'] ?: 'Sistema') ?></span>
                        </div>
                        <p class="message-text"><?= htmlspecialchars($item['mensagem']) ?></p>
                    </div>

                    <div class="message-actions">
                        <form class="status-form" method="POST" action="<?= e(route_url('recados/updateStatus')) ?>">
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
                            <form method="POST" action="<?= e(route_url('recados/delete')) ?>" data-confirm="Remover este recado?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <button class="icon-action danger" type="submit" title="Remover" aria-label="Remover"><i data-lucide="trash-2" style="width:16px;"></i></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?= render_pagination($pagination ?? [], 'recados', $filters) ?>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
