<?php
$isEdit = isset($cliente);
$action = $isEdit ? route_url('clientes/update') : route_url('clientes/store');
$formData = $isEdit ? array_merge($cliente ?? [], $old ?? []) : ($old ?? []);
$errorMessages = [
    'required' => 'Preencha pelo menos nome e CPF/CNPJ para continuar.',
    'email' => 'O e-mail informado nao parece valido.',
    'duplicate' => 'Ja existe um cliente cadastrado com este CPF/CNPJ.',
    'save' => 'Nao foi possivel salvar este cliente agora. Tente novamente.',
];
require_once dirname(__DIR__) . '/layout/header.php';
?>

<div style="margin-bottom:1.5rem;"><a href="<?= route_url('clientes') ?>" style="text-decoration:none;color:var(--text-muted);font-size:.85rem;display:inline-flex;align-items:center;gap:5px;"><i data-lucide="arrow-left" style="width:16px;"></i> Voltar para Clientes</a></div>

<div>
    <div class="card">
        <h3 class="brand-font" style="margin-bottom:1.5rem;"><?= $isEdit ? 'Editar: ' . htmlspecialchars($cliente['nome']) : 'Novo Cliente' ?></h3>
        <?php if (!empty($error) && isset($errorMessages[$error])): ?>
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:10px;margin-bottom:1.5rem;">
            <?= $errorMessages[$error] ?>
        </div>
        <?php endif; ?>
        <form action="<?= e($action) ?>" method="POST">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int) $cliente['id'] ?>"><?php endif; ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($formData['nome'] ?? '') ?>" placeholder="Nome completo do cliente">
                </div>
                <div class="form-group">
                    <label class="form-label">CPF / CNPJ *</label>
                    <input type="text" name="cpf_cnpj" class="form-control" required value="<?= htmlspecialchars($formData['cpf_cnpj'] ?? '') ?>" placeholder="000.000.000-00">
                </div>
                <div class="form-group">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($formData['telefone'] ?? '') ?>" placeholder="(00) 0000-0000">
                </div>
                <div class="form-group">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($formData['whatsapp'] ?? '') ?>" placeholder="(00) 90000-0000">
                </div>
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" placeholder="email@exemplo.com">
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Endereco</label>
                    <input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($formData['endereco'] ?? '') ?>" placeholder="Rua, numero, bairro, cidade - CEP">
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Observacoes</label>
                    <textarea name="observacoes" class="form-control" rows="3" placeholder="Informacoes adicionais relevantes..."><?= htmlspecialchars($formData['observacoes'] ?? '') ?></textarea>
                </div>
            </div>
            <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;padding:12px;"><i data-lucide="save"></i> <?= $isEdit ? 'Salvar Alteracoes' : 'Cadastrar Cliente' ?></button>
                <a href="<?= route_url('clientes') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
