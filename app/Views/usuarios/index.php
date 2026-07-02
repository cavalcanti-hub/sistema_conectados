<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>

<?php
$perfilColors = [
    'Administrador' => 'badge-blue',
    'Atendente' => 'badge-purple',
    'Tecnico' => 'badge-yellow',
    'Técnico' => 'badge-yellow',
    'Operador' => 'badge-gray',
];
?>

<style>
    .usuarios-table-card {
        padding: 1.5rem;
    }

    .usuarios-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .usuarios-card-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 0;
    }

    .usuarios-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(0, 52, 154, .1);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .usuarios-card-title h3 {
        margin: 0;
        font-size: 1rem;
        line-height: 1.2;
    }

    .usuarios-card-title p {
        color: var(--text-muted);
        font-size: .82rem;
        margin: .15rem 0 0;
    }

    .usuarios-table-card .table-container {
        border-radius: 12px;
    }

    .usuario-cell {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 0;
    }

    .usuario-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(0, 52, 154, .12);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        flex: 0 0 auto;
    }

    .usuario-main {
        min-width: 0;
    }

    .usuario-main strong {
        display: block;
        color: var(--text-main);
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .usuario-main span {
        color: var(--text-muted);
        font-size: .75rem;
    }

    .usuarios-table-card td {
        vertical-align: middle;
    }

    .usuarios-table-card .usuario-email {
        max-width: 360px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .usuario-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: .35rem;
    }

    .usuario-action-btn {
        padding: 6px;
        background: none;
        border: 0;
        color: var(--warning);
        width: 32px;
        height: 32px;
        border-radius: 8px;
    }

    .usuario-action-btn:hover {
        background: rgba(245, 158, 11, .1);
    }

    .usuario-last-login {
        font-size: .82rem;
        white-space: nowrap;
    }

    @media (max-width: 640px) {
        .usuarios-card-head > a {
            width: 100%;
        }
    }
</style>

<?php if (!empty($_GET['error'])): ?>
<div class="alert-box alert-yellow">Nao foi possivel salvar. Verifique se o e-mail ja esta cadastrado.</div>
<?php endif; ?>

<div class="card fade-in usuarios-table-card">
    <div class="usuarios-card-head">
        <div class="usuarios-card-title">
            <span class="usuarios-card-icon"><i data-lucide="users"></i></span>
            <div>
                <h3 class="brand-font">Equipe do sistema</h3>
                <p>Gerencie acessos administrativos, atendentes, tecnicos e operadores.</p>
            </div>
        </div>
        <a href="<?= route_url('usuarios/create') ?>" class="btn btn-primary" style="text-decoration:none;white-space:nowrap;">
            <i data-lucide="user-plus"></i> Novo Usuario
        </a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th>Último Login</th>
                    <th style="text-align:right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:3rem;color:var(--text-muted);">
                        <i data-lucide="users" style="width:32px;margin-bottom:10px;display:block;margin:0 auto 10px;"></i>
                        Nenhum usuario cadastrado. <a href="<?= route_url('usuarios/create') ?>" style="color:var(--secondary);">Cadastrar primeiro usuario</a>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td>
                        <div class="usuario-cell">
                            <div class="usuario-avatar"><?= strtoupper(substr($u['nome'], 0, 1)) ?></div>
                            <div class="usuario-main">
                                <strong><?= htmlspecialchars($u['nome']) ?></strong>
                                <span><?= !empty($u['especialidade']) ? htmlspecialchars($u['especialidade']) : '—' ?></span>
                            </div>
                        </div>
                    </td>
                    <td class="usuario-email"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge <?= $perfilColors[$u['perfil']] ?? 'badge-gray' ?>"><?= htmlspecialchars($u['perfil']) ?></span></td>
                    <td><span class="badge <?= $u['status'] === 'Ativo' ? 'badge-green' : 'badge-red' ?>"><?= htmlspecialchars($u['status']) ?></span></td>
                    <td class="usuario-last-login"><?= !empty($u['ultimo_login']) ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : '<span style="color:var(--text-muted)">—</span>' ?></td>
                    <td style="text-align:right;">
                        <div class="usuario-actions">
                            <a href="<?= route_url('usuarios/edit', ['id' => $u['id']]) ?>" class="btn usuario-action-btn" title="Editar usuario">
                                <i data-lucide="edit-3" style="width:18px;"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p style="font-size:.8rem;color:var(--text-muted);margin-top:1rem;"><?= count($usuarios) ?> usuario(s) encontrado(s)</p>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
