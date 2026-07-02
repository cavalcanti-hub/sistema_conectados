<?php require_once dirname(__DIR__) . '/layout/header.php';
$statusColors = ['Recebido'=>'badge-gray','Em análise'=>'badge-blue','Aguardando aprovação'=>'badge-yellow','Aprovado'=>'badge-purple','Reprovado'=>'badge-red','Em reparo'=>'badge-blue','Aguardando peça'=>'badge-yellow','Pronto'=>'badge-green','Entregue'=>'badge-green','Cancelado'=>'badge-red'];
?>
<div style="margin-bottom:1.5rem;"><a href="<?= route_url('tecnicos') ?>" style="text-decoration:none;color:var(--text-muted);font-size:.85rem;">← Técnicos</a></div>
<div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem;">
    <div class="card" style="text-align:center;padding:2rem;align-self:start;">
        <div style="width:70px;height:70px;background:var(--secondary);border-radius:16px;color:white;font-size:1.5rem;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;"><?= strtoupper(substr($tecnico['nome'],0,2)) ?></div>
        <h2 class="brand-font"><?= htmlspecialchars($tecnico['nome']) ?></h2>
        <span class="badge <?= $tecnico['status']==='Ativo'?'badge-green':'badge-red' ?>" style="margin:.5rem 0"><?= $tecnico['status'] ?></span>
        <?php if($tecnico['especialidade']): ?><p style="font-size:.82rem;color:var(--text-muted);margin-top:.5rem;"><?= htmlspecialchars($tecnico['especialidade']) ?></p><?php endif; ?>
        <p style="font-size:.8rem;color:var(--text-muted);margin-top:.5rem;"><?= $tecnico['email'] ?></p>
    </div>
    <div class="card">
        <h3 class="brand-font" style="margin-bottom:1.25rem;">OS Atribuídas (<?= count($os) ?>)</h3>
        <?php if(empty($os)): ?>
        <p style="color:var(--text-muted);">Nenhuma OS atribuída.</p>
        <?php else: ?>
        <div class="table-container"><table>
            <thead><tr><th>OS</th><th>Aparelho</th><th>Status</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach($os as $o): ?>
            <tr>
                <td><a href="<?= route_url('os/viewDetail', ['id' => $o['id']]) ?>" style="color:var(--secondary);font-weight:600;text-decoration:none;">#<?= $o['numero_os'] ?></a></td>
                <td><?= htmlspecialchars($o['modelo']) ?></td>
                <td><span class="badge <?= $statusColors[$o['status']]??'badge-gray' ?>"><?= $o['status'] ?></span></td>
                <td style="font-size:.8rem;"><?= date('d/m/Y',strtotime($o['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>
<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
