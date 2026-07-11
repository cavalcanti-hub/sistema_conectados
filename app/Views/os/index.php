<?php
require_once dirname(__DIR__) . '/layout/header.php';

$statusColors = [
    'Recebido' => 'badge-gray',
    'Em analise' => 'badge-blue',
    'Aguardando aprovacao' => 'badge-yellow',
    'Aprovado' => 'badge-purple',
    'Em reparo' => 'badge-blue',
    'Aguardando peca' => 'badge-yellow',
    'Reprovado' => 'badge-red',
    'Pronto' => 'badge-green',
    'Entregue' => 'badge-green',
    'Cancelado' => 'badge-red',
];
?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert-success system-flash"><i data-lucide="check-circle"></i> Ordem de servico excluida com sucesso.</div>
<?php elseif (($_GET['error'] ?? '') === 'delete_failed'): ?>
    <div class="alert-success" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;"><i data-lucide="alert-triangle"></i> Nao foi possivel excluir esta OS agora.</div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap;">
    <form action="" method="GET" style="display:flex;gap:.75rem;flex:1;flex-wrap:wrap;">
        <input type="hidden" name="url" value="os">
        <div style="position:relative;flex:1;min-width:220px;">
            <input type="text" name="search" class="form-control" placeholder="Buscar por Cliente, OS, IMEI..." style="padding-left:40px;" value="<?= e($filters['search'] ?? '') ?>">
            <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;color:var(--text-muted);"></i>
        </div>
        <select name="status" class="form-control" style="width:200px;" onchange="this.form.submit()">
            <option value="">Todos os Status</option>
            <?php foreach ($status_list as $s): ?>
                <?php $statusOptionLabel = trim((string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $s)) ?: (string) $s; ?>
                <option value="<?= e($s) ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= e($statusOptionLabel) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn" style="background:var(--primary);color:white;"><i data-lucide="filter"></i> Filtrar</button>
    </form>
    <a href="<?= e(route_url('os/create')) ?>" class="btn btn-primary" style="text-decoration:none;white-space:nowrap;"><i data-lucide="plus"></i> Nova OS</a>
</div>

<div class="card fade-in">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Numero OS</th>
                    <th>Entrada</th>
                    <th>Cliente</th>
                    <th>Aparelho</th>
                    <th>Tecnico</th>
                    <th>Status</th>
                    <th>Prior.</th>
                    <th>Total</th>
                    <th style="text-align:right;">Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ordens)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center;padding:3rem;color:var(--text-muted);">
                            <i data-lucide="inbox" style="width:32px;margin-bottom:10px;display:block;margin:0 auto 10px;"></i>
                            Nenhuma OS encontrada. <a href="<?= e(route_url('os/create')) ?>" style="color:var(--secondary);">Criar primeira OS</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ordens as $os): ?>
                        <?php
                        $osId = (int) ($os['id'] ?? 0);
                        $numeroOs = (string) ($os['numero_os'] ?? '');
                        $status = (string) ($os['status'] ?? '');
                        $statusLabel = trim((string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $status)) ?: $status;
                        $prioridade = (string) ($os['prioridade'] ?? '');
                        $aparelho = trim((string) ($os['aparelho_marca'] ?? '') . ' ' . (string) ($os['aparelho_modelo'] ?? ''));
                        $prioColor = $prioridade === 'Urgente' ? '#dc2626' : ($prioridade === 'Alta' ? '#ea580c' : ($prioridade === 'Normal' ? '#2563eb' : '#64748b'));
                        $statusBadge = $statusColors[$statusLabel] ?? 'badge-gray';
                        $viewUrl = route_url('os/viewDetail', ['id' => $osId]);
                        $editUrl = route_url('os/edit', ['id' => $osId]);
                        $whatsappDigits = preg_replace('/\D/', '', (string) ($os['cliente_whatsapp'] ?? ''));
                        $wppMsg = "*OS #{$numeroOs}* - *CONECTADOS*\n" .
                            "- *Aparelho:* {$aparelho}\n" .
                            "- *Status:* {$statusLabel}";
                        $whatsappUrl = $whatsappDigits !== '' ? 'https://wa.me/55' . $whatsappDigits . '?text=' . urlencode($wppMsg) : '';
                        ?>
                        <tr style="cursor:pointer;" onclick="window.location='<?= e($viewUrl) ?>'">
                            <td><strong>#<?= e($numeroOs) ?></strong></td>
                            <td style="font-size:.8rem;white-space:nowrap;"><?= e(date('d/m/Y H:i', strtotime((string) ($os['created_at'] ?? 'now')))) ?></td>
                            <td><?= e($os['cliente_nome'] ?? '') ?></td>
                            <td style="font-size:.85rem;"><?= e($aparelho) ?></td>
                            <td style="font-size:.85rem;">
                                <?php if (!empty($os['tecnico_nome'])): ?>
                                    <?= e($os['tecnico_nome']) ?>
                                <?php else: ?>
                                    <span style="color:var(--text-muted)">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?= e($statusBadge) ?>"><?= e($statusLabel) ?></span></td>
                            <td><span style="font-size:.75rem;font-weight:600;color:<?= e($prioColor) ?>;"><?= e($prioridade) ?></span></td>
                            <td><strong>R$ <?= e(number_format((float) ($os['valor_total'] ?? 0), 2, ',', '.')) ?></strong></td>
                            <td style="text-align:right;" onclick="event.stopPropagation()">
                                <a href="<?= e($viewUrl) ?>" class="btn" style="padding:6px;color:var(--secondary);background:none;"><i data-lucide="eye" style="width:16px;"></i></a>
                                <a href="<?= e($editUrl) ?>" class="btn" style="padding:6px;color:var(--warning);background:none;"><i data-lucide="edit-3" style="width:16px;"></i></a>
                                <?php if ($whatsappUrl !== ''): ?>
                                    <a href="<?= e($whatsappUrl) ?>" target="_blank" class="btn" style="padding:6px;color:#25D366;background:none;"><svg style="width:18px;height:18px;" viewBox="0 0 448 512" fill="currentColor"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.1 0-65.6-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-5.5-2.8-23.2-8.5-44.2-27.1-16.4-14.6-27.4-32.7-30.6-38.1-3.2-5.4-.3-8.3 2.5-11.1 2.5-2.5 5.5-6.5 8.3-9.7 2.8-3.2 3.7-5.5 5.5-9.2 1.9-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 13.2 5.8 23.5 9.2 31.6 11.8 13.6 4.3 25.9 3.7 35.8 2.2 11-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg></a>
                                <?php endif; ?>
                                <?php if (current_user_profile() === 'Administrador'): ?>
                                    <form method="POST" action="<?= e(route_url('os/delete')) ?>" data-confirm="Excluir a OS #<?= e($numeroOs) ?>?Esta acao remove a ordem, historico, pagamentos e fotos anexadas." style="display:inline-flex;" onclick="event.stopPropagation()">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $osId ?>">
                                        <button type="submit" class="btn" style="padding:6px;color:var(--danger);background:none;" title="Excluir OS" aria-label="Excluir OS" onclick="event.stopPropagation()"><i data-lucide="trash-2" style="width:16px;"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination($pagination ?? [], 'os', ['search' => $filters['search'] ?? '', 'status' => $filters['status'] ?? '']) ?>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
