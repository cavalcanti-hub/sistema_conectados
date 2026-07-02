<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>

<?php
$statusCount = is_array($statusCount ?? null) ? $statusCount : [];
$baixo = is_array($baixo ?? null) ? $baixo : [];
$lucroDia = (float) $receitas_dia - (float) $despesas_dia;
$lucroSemana = (float) $receitas_semana - (float) $despesas_semana;
$lucroMes = (float) $receitas_mes - (float) $despesas_mes;
?>

<style>
    .reports-actions { display: flex; justify-content: flex-end; gap: .75rem; margin-bottom: 1rem; }
    .reports-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    .report-stat { display: flex; gap: .9rem; align-items: center; min-height: 110px; padding: 1rem; }
    .report-stat-icon { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
    .report-stat p { margin: 0 0 .3rem; color: var(--text-muted); font-size: .78rem; font-weight: 700; }
    .report-stat h3 { margin: 0; color: var(--primary); font-size: 1.18rem; }
    .reports-panels { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 1.25rem; }
    .report-panel h3 { margin: 0 0 1rem; font-size: 1rem; }
    .status-row, .stock-row { padding: .65rem 0; border-bottom: 1px solid var(--border); }
    .status-row:last-child, .stock-row:last-child { border-bottom: 0; }
    .status-line, .stock-row { display: flex; justify-content: space-between; gap: 1rem; align-items: center; }
    .status-line span, .stock-row span:first-child { font-size: .86rem; }
    .status-bar { height: 8px; margin-top: .4rem; border-radius: 999px; background: rgba(148, 163, 184, .25); overflow: hidden; }
    .status-bar div { height: 100%; border-radius: inherit; background: var(--primary); }
    @media(max-width:1180px) {
        .reports-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .reports-panels { grid-template-columns: 1fr; }
    }
    @media(max-width:700px) {
        .reports-grid { grid-template-columns: 1fr; }
        .reports-actions { justify-content: stretch; }
        .reports-actions .btn { width: 100%; }
    }
    @media print {
        .topbar, .sidebar, .main-header, .reports-actions { display: none !important; }
        .main-content { margin: 0 !important; width: 100% !important; max-width: none !important; }
        .card { box-shadow: none !important; border: 1px solid #d7dee8 !important; }
    }
</style>

<div class="reports-actions">
    <a href="<?= route_url('relatorios/print') ?>" target="_blank" class="btn btn-outline" style="text-decoration:none;"><i data-lucide="printer"></i> Imprimir</a>
</div>

<div class="reports-grid">
    <div class="card report-stat">
        <div class="report-stat-icon" style="background:rgba(16,185,129,.1);color:var(--success);"><i data-lucide="calendar-days"></i></div>
        <div><p>Resultado Hoje</p><h3>R$ <?= number_format($lucroDia, 2, ',', '.') ?></h3></div>
    </div>
    <div class="card report-stat">
        <div class="report-stat-icon" style="background:rgba(0,52,154,.1);color:var(--primary);"><i data-lucide="calendar-range"></i></div>
        <div><p>Resultado Semana</p><h3>R$ <?= number_format($lucroSemana, 2, ',', '.') ?></h3></div>
    </div>
    <div class="card report-stat">
        <div class="report-stat-icon" style="background:rgba(124,58,237,.1);color:#7c3aed;"><i data-lucide="wallet"></i></div>
        <div><p>Resultado Mes</p><h3>R$ <?= number_format($lucroMes, 2, ',', '.') ?></h3></div>
    </div>
    <div class="card report-stat">
        <div class="report-stat-icon" style="background:rgba(234,179,8,.16);color:#a16207;"><i data-lucide="wrench"></i></div>
        <div><p>Faturamento OS Mes</p><h3>R$ <?= number_format((float) $fat_mes, 2, ',', '.') ?></h3></div>
    </div>
</div>

<div class="reports-grid">
    <div class="card report-stat">
        <div class="report-stat-icon" style="background:rgba(16,185,129,.1);color:var(--success);"><i data-lucide="trending-up"></i></div>
        <div><p>Receitas Hoje</p><h3>R$ <?= number_format((float) $receitas_dia, 2, ',', '.') ?></h3></div>
    </div>
    <div class="card report-stat">
        <div class="report-stat-icon" style="background:rgba(239,68,68,.1);color:var(--danger);"><i data-lucide="trending-down"></i></div>
        <div><p>Despesas Hoje</p><h3>R$ <?= number_format((float) $despesas_dia, 2, ',', '.') ?></h3></div>
    </div>
    <div class="card report-stat">
        <div class="report-stat-icon" style="background:rgba(16,185,129,.1);color:var(--success);"><i data-lucide="circle-dollar-sign"></i></div>
        <div><p>Receitas Mes</p><h3>R$ <?= number_format((float) $receitas_mes, 2, ',', '.') ?></h3></div>
    </div>
    <div class="card report-stat">
        <div class="report-stat-icon" style="background:rgba(239,68,68,.1);color:var(--danger);"><i data-lucide="receipt"></i></div>
        <div><p>Despesas Mes</p><h3>R$ <?= number_format((float) $despesas_mes, 2, ',', '.') ?></h3></div>
    </div>
</div>

<div class="reports-panels">
    <div class="card report-panel">
        <h3 class="brand-font">OS por Status</h3>
        <?php $totalOs = array_sum(array_map('intval', $statusCount)); ?>
        <?php if ($totalOs <= 0): ?>
            <p style="color:var(--text-muted);font-size:.9rem;">Nenhuma ordem de servico encontrada.</p>
        <?php else: foreach ($statusCount as $status => $total): ?>
            <?php $total = (int) $total; $pct = $totalOs > 0 ? ($total / $totalOs) * 100 : 0; ?>
            <div class="status-row">
                <div class="status-line">
                    <span><?= htmlspecialchars($status) ?></span>
                    <strong><?= $total ?></strong>
                </div>
                <div class="status-bar"><div style="width:<?= number_format($pct, 2, '.', '') ?>%;"></div></div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="card report-panel">
        <h3 class="brand-font">Estoque Critico</h3>
        <?php if (empty($baixo)): ?>
            <p style="color:var(--success);font-size:.9rem;">Nenhum item com estoque critico.</p>
        <?php else: foreach ($baixo as $item): ?>
            <div class="stock-row">
                <span><?= htmlspecialchars($item['nome'] ?? 'Item sem nome') ?></span>
                <strong style="color:var(--danger);"><?= (int) ($item['quantidade'] ?? 0) ?> un.</strong>
            </div>
        <?php endforeach; endif; ?>
        <a href="<?= route_url('produtos') ?>" style="display:block;text-align:right;font-size:.82rem;color:var(--primary);text-decoration:none;margin-top:.8rem;font-weight:700;">Ir para Produtos</a>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
