<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>
<div style="margin-bottom:1.5rem;"><a href="<?= route_url('tecnicos') ?>" style="text-decoration:none;color:var(--text-muted);font-size:.85rem;display:inline-flex;align-items:center;gap:5px;"><i data-lucide="arrow-left" style="width:16px;"></i> Voltar</a></div>
<?php if (($_GET['error'] ?? '') === 'senha'): ?>
<div class="alert-box alert-yellow">Informe uma senha de acesso para o tecnico.</div>
<?php endif; ?>
<div><div class="card">
    <h3 class="brand-font" style="margin-bottom:1.5rem;">Novo Técnico</h3>
    <form action="<?= e(route_url('tecnicos/store')) ?>" method="POST">
        <?= csrf_field() ?>
        <div class="form-group"><label class="form-label">Nome Completo *</label><input type="text" name="nome" class="form-control" required placeholder="Nome do técnico"></div>
        <div class="form-group"><label class="form-label">E-mail *</label><input type="email" name="email" class="form-control" required placeholder="email@conectadosassistencia.com.br" autocomplete="email"></div>
        <div class="form-group"><label class="form-label">Senha de Acesso *</label><input type="password" name="senha" class="form-control" required placeholder="Senha de acesso" autocomplete="new-password"></div>
        <div class="form-group"><label class="form-label">Especialidades</label><input type="text" name="especialidade" class="form-control" placeholder="Ex: iPhone, Samsung, Reparo em Placa"></div>
        <div class="form-group"><label class="form-label">Status</label>
            <select name="status" class="form-control"><option value="Ativo">Ativo</option><option value="Inativo">Inativo</option></select>
        </div>
        <div style="display:flex;gap:1rem;margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary" style="flex:1;padding:12px;"><i data-lucide="save"></i> Cadastrar</button>
            <a href="<?= route_url('tecnicos') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div></div>
<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
