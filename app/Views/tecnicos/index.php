<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div></div>
    <a href="<?= route_url('usuarios/create', ['perfil' => 'T?cnico']) ?>" class="btn btn-primary" style="text-decoration:none;"><i data-lucide="plus"></i> Novo Técnico</a>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
    <?php foreach($tecnicos as $t): ?>
    <div class="card fade-in">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:48px;height:48px;background:var(--secondary);border-radius:12px;color:white;display:flex;align-items:center;justify-content:center;font-weight:700;"><?= strtoupper(substr($t['nome'],0,2)) ?></div>
                <div>
                    <h3 class="brand-font" style="font-size:1rem;"><?= htmlspecialchars($t['nome']) ?></h3>
                    <span class="badge <?= $t['status']==='Ativo'?'badge-green':'badge-red' ?>"><?= $t['status'] ?></span>
                </div>
            </div>
        </div>
        <?php if($t['especialidade']): ?>
        <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1rem;display:flex;align-items:center;gap:6px;"><i data-lucide="wrench" style="width:14px;"></i><?= htmlspecialchars($t['especialidade']) ?></p>
        <?php endif; ?>
        <div style="display:flex;gap:1rem;border-top:1px solid var(--border);padding-top:1rem;">
            <div style="flex:1;text-align:center;">
                <p style="font-size:1.5rem;font-weight:700;"><?= $t['total_os'] ?></p>
                <p style="font-size:.72rem;color:var(--text-muted);">Total OS</p>
            </div>
            <div style="width:1px;background:var(--border);"></div>
            <div style="flex:1;text-align:center;">
                <p style="font-size:1.5rem;font-weight:700;color:var(--success);"><?= $t['os_concluidas'] ?></p>
                <p style="font-size:.72rem;color:var(--text-muted);">Concluídas</p>
            </div>
        </div>
        <a href="<?= route_url('tecnicos/profile', ['id' => $t['id']]) ?>" class="btn" style="margin-top:1rem;width:100%;justify-content:center;border:1px solid var(--border);background:white;text-decoration:none;font-size:.85rem;">Ver Histórico</a>
    </div>
    <?php endforeach; ?>
    <?php if(empty($tecnicos)): ?>
    <div class="card" style="grid-column:span 3;text-align:center;padding:3rem;">
        <i data-lucide="wrench" style="width:48px;display:block;margin:0 auto 1rem;color:var(--text-muted);"></i>
        <h3>Nenhum técnico cadastrado</h3>
        <a href="<?= route_url('usuarios/create', ['perfil' => 'T?cnico']) ?>" class="btn btn-primary" style="text-decoration:none;margin-top:1rem;">Cadastrar Técnico</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>

