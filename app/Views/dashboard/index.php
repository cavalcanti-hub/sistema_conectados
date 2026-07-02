<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>

<?php
$statusColors = [
    'Recebido' => 'badge-gray',
    'Em análise' => 'badge-blue',
    'Aguardando aprovação' => 'badge-yellow',
    'Aprovado' => 'badge-purple',
    'Reprovado' => 'badge-red',
    'Em reparo' => 'badge-blue',
    'Aguardando peça' => 'badge-yellow',
    'Pronto' => 'badge-green',
    'Entregue' => 'badge-green',
    'Cancelado' => 'badge-red',
];
?>

<!-- KPIs -->
<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(59,130,246,.1);color:var(--secondary);">
            <i data-lucide="file-clock"></i>
        </div>
        <div class="stat-info">
            <p>OS Abertas</p>
            <h3><?= ($statusCount['Recebido']??0) + ($statusCount['Em análise']??0) + ($statusCount['Em reparo']??0) + ($statusCount['Aguardando peça']??0) ?></h3>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,.1);color:var(--warning);">
            <i data-lucide="clock-alert"></i>
        </div>
        <div class="stat-info">
            <p>Aguardando Aprovação</p>
            <h3><?= $statusCount['Aguardando aprovação']??0 ?></h3>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,.1);color:var(--success);">
            <i data-lucide="check-circle-2"></i>
        </div>
        <div class="stat-info">
            <p>Prontas p/ Entrega</p>
            <h3><?= $statusCount['Pronto']??0 ?></h3>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(0,52,154,.1);color:var(--primary);">
            <i data-lucide="wrench"></i>
        </div>
        <div class="stat-info">
            <p>Em Reparo</p>
            <h3><?= $statusCount['Em reparo']??0 ?></h3>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,.1);color:var(--warning);">
            <i data-lucide="package-search"></i>
        </div>
        <div class="stat-info">
            <p>Aguardando Peça</p>
            <h3><?= $statusCount['Aguardando peça']??0 ?></h3>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(234,179,8,.1);color:var(--accent);">
            <i data-lucide="trending-up"></i>
        </div>
        <div class="stat-info">
            <p>Faturamento Hoje</p>
            <h3>R$ <?= number_format($fat_dia, 2, ',', '.') ?></h3>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(59,130,246,.1);color:var(--secondary);">
            <i data-lucide="calendar-check"></i>
        </div>
        <div class="stat-info">
            <p>Faturamento Semana</p>
            <h3>R$ <?= number_format($fat_semana, 2, ',', '.') ?></h3>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(139,92,246,.1);color:#7c3aed;">
            <i data-lucide="bar-chart-2"></i>
        </div>
        <div class="stat-info">
            <p>Faturamento Mês</p>
            <h3>R$ <?= number_format($fat_mes, 2, ',', '.') ?></h3>
        </div>
    </div>
</div>

<div class="dashboard-layout" style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;margin-top:1.5rem;">
    <!-- Últimas OS -->
    <div class="card fade-in">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
            <h3 class="brand-font">Últimas Ordens de Serviço</h3>
            <a href="<?= route_url('os') ?>" style="font-size:.8rem;color:var(--secondary);text-decoration:none;font-weight:600;">Ver todas →</a>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>OS</th><th>Cliente</th><th>Aparelho</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    <?php if(empty($recentes)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">Nenhuma OS encontrada. <a href="<?= route_url('os/create') ?>">Abrir a primeira!</a></td></tr>
                    <?php else: ?>
                    <?php foreach($recentes as $os): ?>
                    <tr>
                        <td><strong style="font-size:.8rem;">#<?= $os['numero_os'] ?></strong></td>
                        <td><?= htmlspecialchars($os['cliente_nome']) ?></td>
                        <td style="font-size:.85rem;"><?= htmlspecialchars($os['modelo']) ?></td>
                        <td><span class="badge <?= $statusColors[$os['status']] ?? 'badge-gray' ?>"><?= $os['status'] ?></span></td>
                        <td><a href="<?= route_url('os/viewDetail', ['id' => $os['id']]) ?>" style="color:var(--secondary);font-size:.8rem;text-decoration:none;font-weight:600;">Ver</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;justify-content:flex-end;margin-top:1rem;">
            <button type="button" onclick="window.open('<?= route_url('dashboard', ['print' => 'a4']) ?>', '_blank')" class="btn btn-secondary" style="padding:8px 12px;">
                <i data-lucide="printer"></i> Imprimir A4
            </button>
            <button type="button" onclick="window.open('<?= route_url('dashboard', ['print' => '80']) ?>', '_blank')" class="btn btn-secondary" style="padding:8px 12px;">
                <i data-lucide="printer"></i> Imprimir 80mm
            </button>
        </div>
    </div>

    <!-- Alertas e Atalhos -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        <?php if(!empty($estoqueBaixo)): ?>
        <div class="card" style="border-left:4px solid var(--danger);">
            <h4 class="brand-font" style="color:var(--danger);margin-bottom:1rem;display:flex;align-items:center;gap:8px;">
                <i data-lucide="alert-triangle" style="width:18px;"></i> Estoque Crítico
            </h4>
            <?php foreach(array_slice($estoqueBaixo, 0, 4) as $item): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);font-size:.85rem;">
                <span><?= htmlspecialchars($item['nome']) ?></span>
                <span style="font-weight:700;color:var(--danger);"><?= $item['quantidade'] ?> un.</span>
            </div>
            <?php endforeach; ?>
            <a href="<?= route_url('produtos') ?>" style="display:block;text-align:right;font-size:.75rem;color:var(--secondary);text-decoration:none;margin-top:.5rem;">Ver produtos →</a>
        </div>
        <?php endif; ?>

        <div class="card quick-actions-card">
            <h4 class="brand-font">Ações Rápidas</h4>
            <div class="quick-actions-grid">
                <a href="<?= route_url('os/create') ?>" class="quick-action quick-action-primary">
                    <span class="quick-action-icon"><i data-lucide="plus"></i></span>
                    <span>Nova OS</span>
                </a>
                <a href="<?= route_url('pdv') ?>" class="quick-action">
                    <span class="quick-action-icon"><i data-lucide="shopping-cart"></i></span>
                    <span>PDV Balcão</span>
                </a>
                <a href="<?= route_url('clientes/create') ?>" class="quick-action">
                    <span class="quick-action-icon"><i data-lucide="user-plus"></i></span>
                    <span>Novo Cliente</span>
                </a>
                <a href="<?= route_url('produtos/create') ?>" class="quick-action">
                    <span class="quick-action-icon"><i data-lucide="package-plus"></i></span>
                    <span>Novo Produto</span>
                </a>
            </div>
        </div>

        <div class="card" style="background:var(--primary);color:white;">
            <h4 class="brand-font" style="margin-bottom:1rem;color:var(--accent);">Resumo Financeiro</h4>
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#94a3b8;font-size:.85rem;">Receitas (mês)</span>
                    <span style="color:#4ade80;font-weight:600;">R$ <?= number_format($receitas_mes, 2, ',', '.') ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;padding-bottom:.75rem;border-bottom:1px solid rgba(255,255,255,.1);">
                    <span style="color:#94a3b8;font-size:.85rem;">Despesas (mês)</span>
                    <span style="color:#f87171;font-weight:600;">R$ <?= number_format($despesas_mes, 2, ',', '.') ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:white;font-weight:600;">Lucro Estimado</span>
                    <span style="color:var(--accent);font-weight:700;font-size:1.1rem;">R$ <?= number_format($receitas_mes - $despesas_mes, 2, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
