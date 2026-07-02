<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>

<style>
    .clientes-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    .clientes-search {
        display: flex;
        gap: .5rem;
        min-width: min(100%, 360px);
    }

    .clientes-search .form-control {
        width: min(420px, 100%);
    }

    .clientes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: .9rem;
        align-items: start;
    }

    .cliente-card {
        --cliente-cor: var(--primary);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        padding: 1rem;
        border-radius: 14px;
        border: 1px solid rgba(203, 213, 225, .85);
        box-shadow: 0 14px 30px -24px rgba(15, 23, 42, .5);
        min-height: 142px;
        height: 100%;
    }

    .cliente-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 3px;
        background: var(--cliente-cor);
    }

    .cliente-top {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: start;
        gap: .8rem;
    }

    .cliente-main {
        min-width: 0;
    }

    .cliente-main h3 {
        margin: .08rem 0 0;
        font-size: 1rem;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .cliente-actions {
        display: flex;
        gap: .35rem;
    }

    .cliente-icon-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        border-radius: 10px;
        background: #fff;
        color: #475569;
        border: 1px solid rgba(203, 213, 225, .9);
    }

    .cliente-icon-btn:hover {
        color: var(--primary);
        border-color: rgba(0, 52, 154, .35);
        background: rgba(0, 52, 154, .06);
    }

    .cliente-icon-btn.danger {
        color: var(--danger);
        border-color: rgba(239, 68, 68, .18);
    }

    .cliente-icon-btn.danger:hover {
        background: var(--danger);
        color: #fff;
        border-color: var(--danger);
    }

    .cliente-contact-summary {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin: .95rem 0 1rem;
        min-width: 0;
        color: #334155;
        font-size: .86rem;
        min-height: 22px;
    }

    .cliente-contact-summary i,
    .whatsapp-brand {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
    }

    .cliente-contact-summary i {
        color: var(--cliente-cor);
    }

    .whatsapp-brand {
        display: block;
        color: #25D366;
    }

    .cliente-contact-summary span {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cliente-contact-summary.empty {
        color: var(--text-muted);
        font-style: italic;
    }

    .cliente-footer {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 44px;
        gap: .55rem;
        padding-top: .85rem;
        border-top: 1px solid var(--border);
        margin-top: auto;
    }

    .cliente-footer .btn {
        min-height: 38px;
        border-radius: 11px;
        padding: 8px 12px;
        font-size: .82rem;
        text-decoration: none;
    }

    .cliente-profile {
        background: rgba(0, 52, 154, .08);
        color: var(--primary) !important;
        font-weight: 800;
    }

    .cliente-whatsapp {
        width: 44px;
        padding: 0 !important;
        background: #22c55e;
        color: #fff !important;
        box-shadow: 0 12px 22px rgba(34, 197, 94, .16);
    }

    .cliente-whatsapp .whatsapp-brand {
        width: 19px;
        height: 19px;
        color: #fff;
    }

    .cliente-whatsapp.disabled {
        pointer-events: none;
        background: rgba(148, 163, 184, .18);
        color: var(--text-muted) !important;
        box-shadow: none;
    }

    @media (max-width: 640px) {
        .clientes-search,
        .clientes-toolbar > a {
            width: 100%;
        }

        .clientes-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="clientes-toolbar">
    <form action="" method="GET" class="clientes-search">
        <input type="hidden" name="url" value="clientes">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Buscar por nome, CPF ou telefone...">
        <button type="submit" class="btn btn-primary" aria-label="Buscar clientes"><i data-lucide="search"></i></button>
    </form>
    <a href="<?= route_url('clientes/create') ?>" class="btn btn-primary" style="text-decoration:none;"><i data-lucide="user-plus"></i> Novo Cliente</a>
</div>

<?php if(empty($clientes)): ?>
<div class="card" style="text-align:center;padding:3rem;">
    <i data-lucide="users" style="width:48px;height:48px;margin:0 auto 1rem;display:block;color:var(--text-muted);"></i>
    <h3>Nenhum cliente encontrado</h3>
    <p style="color:var(--text-muted);margin:.5rem 0 1.5rem;">Comece cadastrando seu primeiro cliente.</p>
    <a href="<?= route_url('clientes/create') ?>" class="btn btn-primary" style="text-decoration:none;">Cadastrar Cliente</a>
</div>
<?php else: ?>
<div class="clientes-grid">
    <?php
    $paleta = ['#2563eb','#7c3aed','#db2777','#059669','#d97706','#dc2626'];
    foreach($clientes as $i => $c):
        $cor = $paleta[$i % count($paleta)];
        $whatsapp = trim((string) ($c['whatsapp'] ?? ''));
        $telefone = trim((string) ($c['telefone'] ?? ''));
        $email = trim((string) ($c['email'] ?? ''));
        $contatoPrincipal = $whatsapp !== '' ? $whatsapp : ($telefone !== '' ? $telefone : ($email !== '' ? $email : 'Contato nao informado'));
        $contatoIcone = $telefone !== '' ? 'phone' : ($email !== '' ? 'mail' : 'circle-alert');
        $whatsappDigits = preg_replace('/\D/', '', $whatsapp);
        $whatsappHref = $whatsappDigits !== '' ? 'https://wa.me/55' . $whatsappDigits : '#';
    ?>
    <div class="card fade-in cliente-card" style="--cliente-cor: <?= htmlspecialchars($cor) ?>;">
        <div class="cliente-top">
            <div class="cliente-main">
                <h3 class="brand-font"><?= htmlspecialchars($c['nome']) ?></h3>
            </div>
            <div class="cliente-actions">
                <a href="<?= route_url('clientes/edit', ['id' => $c['id']]) ?>" class="btn cliente-icon-btn" title="Editar cliente" aria-label="Editar cliente"><i data-lucide="edit-3" style="width:16px;"></i></a>
                <form action="<?= e(route_url('clientes/delete')) ?>" method="POST" data-confirm="Excluir este cliente?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                    <button type="submit" class="btn cliente-icon-btn danger" title="Excluir cliente" aria-label="Excluir cliente"><i data-lucide="trash-2" style="width:16px;"></i></button>
                </form>
            </div>
        </div>

        <div class="cliente-contact-summary <?= ($whatsapp === '' && $telefone === '' && $email === '') ? 'empty' : '' ?>">
            <?php if ($whatsapp !== ''): ?>
                <svg class="whatsapp-brand" viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-156.9zM223.9 438.7c-33.1 0-65.6-8.9-94-25.7l-6.7-4-69.8 18.3 18.6-68.1-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 11-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
            <?php else: ?>
                <i data-lucide="<?= $contatoIcone ?>"></i>
            <?php endif; ?>
            <span><?= htmlspecialchars($contatoPrincipal) ?></span>
        </div>

        <div class="cliente-footer">
            <a href="<?= route_url('clientes/show', ['id' => $c['id']]) ?>" class="btn cliente-profile">
                <i data-lucide="user-round"></i> Ver Perfil
            </a>
            <a href="<?= htmlspecialchars($whatsappHref) ?>" target="_blank" rel="noopener" class="btn cliente-whatsapp <?= $whatsappDigits === '' ? 'disabled' : '' ?>" title="Abrir WhatsApp" aria-label="Abrir WhatsApp">
                <svg class="whatsapp-brand" viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-156.9zM223.9 438.7c-33.1 0-65.6-8.9-94-25.7l-6.7-4-69.8 18.3 18.6-68.1-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 11-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?= render_pagination($pagination ?? [], 'clientes', ['search' => $search]) ?>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
