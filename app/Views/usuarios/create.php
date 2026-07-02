<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>
<?php
$perfis = ['Administrador', 'Atendente', 'Técnico', 'Financeiro', 'Estoque'];
$statusOptions = ['Ativo', 'Inativo'];
$perfilTecnico = $perfis[2];
$usuario = $usuario ?? [];
$errorCode = $errorCode ?? ($_GET['error'] ?? '');
$isTechnicalPreset = !$isEdit && (($_GET['perfil'] ?? '') === 'T?cnico' || ($_GET['perfil'] ?? '') === 'Técnico' || ($usuario['perfil'] ?? '') === 'T?cnico' || ($usuario['perfil'] ?? '') === 'Técnico');

if (!$isEdit && !empty($_GET)) {
    $usuario = array_merge($usuario, [
        'nome' => $_GET['nome'] ?? ($usuario['nome'] ?? ''),
        'email' => $_GET['email'] ?? ($usuario['email'] ?? ''),
        'perfil' => $isTechnicalPreset ? $perfilTecnico : ($_GET['perfil'] ?? ($usuario['perfil'] ?? 'Atendente')),
        'status' => $_GET['status'] ?? ($usuario['status'] ?? 'Ativo'),
        'especialidade' => $_GET['especialidade'] ?? ($usuario['especialidade'] ?? ''),
        'comissao' => $_GET['comissao'] ?? ($usuario['comissao'] ?? '0.00'),
        'meta_os_mes' => $_GET['meta_os_mes'] ?? ($usuario['meta_os_mes'] ?? '30'),
    ]);
}
?>

<div style="margin-bottom:1.5rem;">
    <a href="<?= route_url($isTechnicalPreset ? 'tecnicos' : 'usuarios') ?>" style="text-decoration:none;color:var(--text-muted);font-size:.85rem;display:inline-flex;align-items:center;gap:5px;">
        <i data-lucide="arrow-left" style="width:16px;"></i> Voltar
    </a>
</div>

<?php if ($errorCode !== ''): ?>
<div class="alert-box alert-yellow">
    <?php
    $messages = [
        'email' => 'Este e-mail ja esta cadastrado. Use outro endereco para criar um novo usuario.',
        'senha' => 'Informe uma senha de acesso para criar o usuario.',
        'save' => 'Nao foi possivel salvar o usuario. Revise os dados e tente novamente.',
    ];
    echo htmlspecialchars($messages[$errorCode] ?? 'Nao foi possivel concluir a operacao.');
    ?>
</div>
<?php endif; ?>

<div class="card">
    <h3 class="brand-font" style="margin-bottom:1.5rem;"><?= $isEdit ? 'Editar Usuario' : ($isTechnicalPreset ? 'Novo Tecnico' : 'Novo Usuario') ?></h3>
    <form action="<?= e(route_url('usuarios/' . ($isEdit ? 'update' : 'store'))) ?>" method="POST">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) ($usuario['id'] ?? 0) ?>">
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label class="form-label">Nome completo *</label>
                <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" placeholder="Nome do usuario">
            </div>
            <div class="form-group">
                <label class="form-label">E-mail *</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" placeholder="email@conectadosassistencia.com.br" autocomplete="email">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label class="form-label">Perfil *</label>
                <select name="perfil" class="form-control" required>
                    <?php foreach ($perfis as $perfil): ?>
                    <option value="<?= e($perfil) ?>" <?= ($usuario['perfil'] ?? 'Atendente') === $perfil ? 'selected' : '' ?>><?= e($perfil) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <?php foreach ($statusOptions as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($usuario['status'] ?? 'Ativo') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label class="form-label">Senha <?= $isEdit ? '' : '*' ?></label>
                <input type="password" name="senha" class="form-control" <?= $isEdit ? '' : 'required' ?> placeholder="<?= $isEdit ? 'Deixe em branco para manter a senha atual' : 'Senha de acesso' ?>" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label"><?= $isTechnicalPreset ? 'Especialidade t?cnica' : 'Especialidade' ?></label>
                <input type="text" name="especialidade" class="form-control" value="<?= htmlspecialchars($usuario['especialidade'] ?? '') ?>" placeholder="Ex: Vendas, iPhone, Financeiro">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label class="form-label">Comissao (%)</label>
                <input type="number" name="comissao" class="form-control" min="0" max="100" step="0.01" value="<?= htmlspecialchars($usuario['comissao'] ?? '0.00') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Meta OS por mes</label>
                <input type="number" name="meta_os_mes" class="form-control" min="0" step="1" value="<?= htmlspecialchars($usuario['meta_os_mes'] ?? '30') ?>">
            </div>
        </div>

        <div style="display:flex;gap:1rem;margin-top:1.5rem;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="flex:1;min-width:220px;padding:12px;">
                <i data-lucide="save"></i> <?= $isEdit ? 'Salvar Alteracoes' : ($isTechnicalPreset ? 'Cadastrar Tecnico' : 'Cadastrar Usuario') ?>
            </button>
            <a href="<?= route_url($isTechnicalPreset ? 'tecnicos' : 'usuarios') ?>" class="btn btn-secondary" style="text-decoration:none;">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>


