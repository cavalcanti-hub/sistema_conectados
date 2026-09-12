<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>

<?php
$statusColors = [
    'Recebido' => 'badge-gray',
    'Em analise' => 'badge-blue',
    'Aguardando aprovacao' => 'badge-yellow',
    'Aprovado' => 'badge-purple',
    'Reprovado' => 'badge-red',
    'Em reparo' => 'badge-blue',
    'Aguardando peca' => 'badge-yellow',
    'Pronto' => 'badge-green',
    'Entregue' => 'badge-green',
    'Cancelado' => 'badge-red',
];

$statusCountAscii = [];
foreach (($statusCount ?? []) as $statusKey => $statusTotal) {
    $asciiKey = trim((string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $statusKey));
    $asciiKey = $asciiKey !== '' ? $asciiKey : (string) $statusKey;
    $statusCountAscii[$asciiKey] = ($statusCountAscii[$asciiKey] ?? 0) + (int) $statusTotal;
}

$totalAbertas = ($statusCountAscii['Recebido'] ?? 0) 
              + ($statusCountAscii['Em analise'] ?? 0) 
              + ($statusCountAscii['Em reparo'] ?? 0) 
              + ($statusCountAscii['Aguardando peca'] ?? 0);
$totalAprovacao = $statusCountAscii['Aguardando aprovacao'] ?? 0;
$totalProntas = $statusCountAscii['Pronto'] ?? 0;
$totalReparo = $statusCountAscii['Em reparo'] ?? 0;
$totalPeca = $statusCountAscii['Aguardando peca'] ?? 0;

// Preparar OS agrupadas para os modais interativos
$osList = $todasOs ?? [];
$osDataByFilter = [
    'abertas' => array_values(array_filter($osList, static function($os) {
        return in_array($os['status'] ?? '', ['Recebido', 'Em analise', 'Em reparo', 'Aguardando peca'], true);
    })),
    'aprovacao' => array_values(array_filter($osList, static function($os) {
        return ($os['status'] ?? '') === 'Aguardando aprovacao';
    })),
    'reparo' => array_values(array_filter($osList, static function($os) {
        return ($os['status'] ?? '') === 'Em reparo';
    })),
    'peca' => array_values(array_filter($osList, static function($os) {
        return ($os['status'] ?? '') === 'Aguardando peca';
    })),
    'pronto' => array_values(array_filter($osList, static function($os) {
        return ($os['status'] ?? '') === 'Pronto';
    })),
];

// Dados financeiros estruturados para modais
$finData = [
    'dia' => [
        'titulo' => 'Faturamento Hoje',
        'receitas' => (float) ($fat_dia ?? 0),
        'despesas' => (float) ($despesas_dia ?? 0),
        'formas' => $formas_dia ?? []
    ],
    'semana' => [
        'titulo' => 'Faturamento da Semana',
        'receitas' => (float) ($fat_semana ?? 0),
        'despesas' => (float) ($despesas_semana ?? 0),
        'formas' => $formas_semana ?? []
    ],
    'mes' => [
        'titulo' => 'Faturamento do Mês',
        'receitas' => (float) ($fat_mes ?? 0),
        'despesas' => (float) ($despesas_mes ?? 0),
        'formas' => $formas_mes ?? []
    ]
];
?>


<!-- ==============================================
     CARROSSEL DE CARDS (KPI SLIDER CLEAN)
     ============================================== -->
<div class="carousel-container-wrap">
    <button type="button" class="carousel-arrow-btn prev" onclick="scrollCarousel(-1)" aria-label="Voltar">
        <i data-lucide="chevron-left"></i>
    </button>
    
    <div class="kpi-carousel-track" id="kpiCarouselTrack">
        <!-- 1. OS Abertas -->
        <div class="kpi-slider-item" onclick="openKpiModal('abertas')" role="button" tabindex="0">
            <div class="kpi-icon-bubble icon-blue">
                <i data-lucide="file-clock"></i>
            </div>
            <div class="kpi-slider-num"><?= $totalAbertas ?></div>
            <div class="kpi-slider-title">OS Abertas</div>
        </div>

        <!-- 2. Aguardando Aprovação -->
        <div class="kpi-slider-item" onclick="openKpiModal('aprovacao')" role="button" tabindex="0">
            <div class="kpi-icon-bubble icon-amber">
                <i data-lucide="clock-alert"></i>
            </div>
            <div class="kpi-slider-num"><?= $totalAprovacao ?></div>
            <div class="kpi-slider-title">Aprovação</div>
        </div>

        <!-- 3. Em Reparo -->
        <div class="kpi-slider-item" onclick="openKpiModal('reparo')" role="button" tabindex="0">
            <div class="kpi-icon-bubble icon-indigo">
                <i data-lucide="wrench"></i>
            </div>
            <div class="kpi-slider-num"><?= $totalReparo ?></div>
            <div class="kpi-slider-title">Em Reparo</div>
        </div>

        <!-- 4. Aguardando Peça -->
        <div class="kpi-slider-item" onclick="openKpiModal('peca')" role="button" tabindex="0">
            <div class="kpi-icon-bubble icon-orange">
                <i data-lucide="package-search"></i>
            </div>
            <div class="kpi-slider-num"><?= $totalPeca ?></div>
            <div class="kpi-slider-title">Aguardando Peça</div>
        </div>

        <!-- 5. Prontas p/ Entrega -->
        <div class="kpi-slider-item" onclick="openKpiModal('pronto')" role="button" tabindex="0">
            <div class="kpi-icon-bubble icon-emerald">
                <i data-lucide="check-circle-2"></i>
            </div>
            <div class="kpi-slider-num"><?= $totalProntas ?></div>
            <div class="kpi-slider-title">Prontas</div>
        </div>
    </div>

    <button type="button" class="carousel-arrow-btn next" onclick="scrollCarousel(1)" aria-label="Avançar">
        <i data-lucide="chevron-right"></i>
    </button>
</div>

<!-- ==============================================
     LAYOUT PRINCIPAL (ÚLTIMAS OS & PAINEL LATERAL)
     ============================================== -->
<div class="dashboard-layout" style="margin-top:1.5rem;">
    <!-- Ultimas OS -->
    <div class="card fade-in" style="padding:1.25rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
            <h3 class="brand-font" style="font-size:1.15rem;margin:0;">Últimas Ordens de Serviço</h3>
            <a href="<?= route_url('os') ?>" class="btn btn-secondary" style="padding:5px 12px;font-size:0.82rem;gap:4px;">
                Ver Todas <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width:90px;">Nº OS</th>
                        <th>Cliente</th>
                        <th>Aparelho</th>
                        <th>Status</th>
                        <th style="text-align:right;">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($recentes)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;padding:2.5rem 1rem;color:var(--text-muted);">
                            <i data-lucide="inbox" style="width:32px;height:32px;color:var(--text-faint);display:block;margin:0 auto 8px;"></i>
                            Nenhuma ordem de serviço registrada ainda.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach($recentes as $os): ?>
                    <tr>
                        <td>
                            <a href="<?= route_url('os/viewDetail', ['id' => $os['id']]) ?>" class="os-code-pill">
                                #<?= htmlspecialchars((string) $os['numero_os']) ?>
                            </a>
                        </td>
                        <td>
                            <div style="font-weight:600;color:var(--text-main);"><?= htmlspecialchars((string) $os['cliente_nome']) ?></div>
                        </td>
                        <td style="color:var(--text-secondary);font-size:0.875rem;">
                            <?= htmlspecialchars((string) $os['modelo']) ?>
                        </td>
                        <td>
                            <span class="badge <?= $statusColors[$os['status']] ?? 'badge-gray' ?>">
                                <?= htmlspecialchars((string) $os['status']) ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <a href="<?= route_url('os/viewDetail', ['id' => $os['id']]) ?>" class="btn btn-secondary" style="padding:4px 10px;font-size:0.8rem;gap:4px;">
                                Abrir <i data-lucide="external-link" style="width:13px;height:13px;"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="display:flex;gap:.5rem;flex-wrap:wrap;justify-content:flex-end;margin-top:1rem;padding-top:0.75rem;border-top:1px solid var(--border-light);">
            <button type="button" onclick="window.open('<?= route_url('dashboard', ['print' => 'a4']) ?>', '_blank')" class="btn btn-secondary" style="padding:6px 12px;font-size:0.82rem;gap:6px;">
                <i data-lucide="printer" style="width:15px;height:15px;"></i> A4
            </button>
            <button type="button" onclick="window.open('<?= route_url('dashboard', ['print' => '80']) ?>', '_blank')" class="btn btn-secondary" style="padding:6px 12px;font-size:0.82rem;gap:6px;">
                <i data-lucide="printer" style="width:15px;height:15px;"></i> 80mm
            </button>
        </div>
    </div>

    <!-- Painéis Laterais -->
    <div class="dashboard-side-stack">
        <?php if(!empty($estoqueBaixo)): ?>
        <div class="card" style="padding:1.15rem;">
            <h4 class="brand-font" style="color:var(--danger);margin-bottom:0.65rem;display:flex;align-items:center;gap:8px;font-size:0.95rem;">
                <i data-lucide="alert-triangle" style="width:16px;height:16px;"></i> Estoque em Alerta
            </h4>
            <?php foreach(array_slice($estoqueBaixo, 0, 4) as $item): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--border-light);font-size:0.85rem;">
                <span><?= htmlspecialchars((string) $item['nome']) ?></span>
                <span class="badge badge-red" style="font-weight:700;"><?= (int) $item['quantidade'] ?> un.</span>
            </div>
            <?php endforeach; ?>
            <a href="<?= route_url('produtos') ?>" style="display:inline-flex;align-items:center;gap:4px;font-size:0.78rem;color:var(--primary);text-decoration:none;font-weight:600;margin-top:0.65rem;">
                Ver estoque <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
            </a>
        </div>
        <?php endif; ?>

        <!-- Ações Rápidas -->
        <div class="card quick-actions-card" style="padding:1.15rem;">
            <h4 class="brand-font" style="font-size:1rem;margin-bottom:0.75rem;">Acoes Rapidas</h4>
            <div class="quick-actions-grid">
                <a href="<?= route_url('os/create') ?>" class="quick-action quick-action-primary">
                    <span class="quick-action-icon"><i data-lucide="plus"></i></span>
                    <span>Nova OS</span>
                </a>
                <a href="<?= route_url('pdv') ?>" class="quick-action">
                    <span class="quick-action-icon"><i data-lucide="shopping-cart"></i></span>
                    <span>PDV Balcao</span>
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

        <!-- Resumo Financeiro Sintético -->
        <div class="card card-fin-summary" style="padding:1.15rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                <h4 class="brand-font" style="font-size:1rem;color:var(--text-main);margin:0;">Balanço Mensal</h4>
                <a href="<?= route_url('financeiro') ?>" style="font-size:0.75rem;color:var(--primary);font-weight:600;text-decoration:none;">Financeiro ↗</a>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.55rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.85rem;">
                    <span style="color:var(--text-muted);">Receitas</span>
                    <span style="color:var(--success);font-weight:700;">+ R$ <?= number_format($receitas_mes, 2, ',', '.') ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.85rem;padding-bottom:0.55rem;border-bottom:1px solid var(--border-light);">
                    <span style="color:var(--text-muted);">Despesas</span>
                    <span style="color:var(--danger);font-weight:700;">- R$ <?= number_format($despesas_mes, 2, ',', '.') ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-weight:600;font-size:0.875rem;">Saldo</span>
                    <?php $saldo = $receitas_mes - $despesas_mes; ?>
                    <span style="font-weight:800;font-size:1.1rem;color:<?= $saldo >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                        R$ <?= number_format($saldo, 2, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     MODAL LIMPO DE DETALHAMENTO DO CARD
     ============================================== -->
<div id="kpiDetailsModal" class="kpi-modal-overlay" onclick="handleKpiBackdrop(event)">
    <div class="kpi-modal-dialog">
        <div class="kpi-modal-header">
            <div style="display:flex;align-items:center;gap:12px;">
                <div id="kpiModalIcon" class="kpi-modal-icon-box">
                    <i data-lucide="file-text"></i>
                </div>
                <div>
                    <h3 id="kpiModalTitle" class="brand-font" style="font-size:1.15rem;font-weight:700;margin:0;">Detalhes</h3>
                </div>
            </div>
            <button type="button" class="kpi-modal-close-btn" onclick="closeKpiModal()" title="Fechar (ESC)">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <div id="kpiModalBody" class="kpi-modal-body">
            <!-- Conteúdo dinâmico via JS -->
        </div>

        <div class="kpi-modal-footer">
            <span id="kpiModalCount" style="font-size:0.82rem;color:var(--text-muted);font-weight:500;">0 itens</span>
            <div style="display:flex;gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="closeKpiModal()" style="padding:6px 14px;font-size:0.82rem;">
                    Fechar
                </button>
                <a id="kpiModalLink" href="<?= route_url('os') ?>" class="btn btn-primary" style="padding:6px 14px;font-size:0.82rem;gap:6px;">
                    Ver Módulo <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Scripts de Interatividade do Carrossel e Modais -->
<script>
const osDataByFilter = <?= json_encode($osDataByFilter, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const finData = <?= json_encode($finData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const osViewBaseUrl = "<?= route_url('os/viewDetail') ?>";
const osListBaseUrl = "<?= route_url('os') ?>";
const finBaseUrl = "<?= route_url('financeiro') ?>";

// Controle do Carrossel
function scrollCarousel(dir) {
    const track = document.getElementById('kpiCarouselTrack');
    if (!track) return;
    const amount = 320;
    track.scrollBy({ left: dir * amount, behavior: 'smooth' });
}

const filterConfigs = {
    'abertas': {
        title: 'Ordens em Aberto',
        icon: 'file-clock',
        iconClass: 'icon-blue',
        filterUrlParam: '',
        statusBadge: 'badge-blue'
    },
    'aprovacao': {
        title: 'Aguardando Aprovação',
        icon: 'clock-alert',
        iconClass: 'icon-amber',
        filterUrlParam: '?status=Aguardando+aprovacao',
        statusBadge: 'badge-yellow'
    },
    'reparo': {
        title: 'Em Reparo',
        icon: 'wrench',
        iconClass: 'icon-indigo',
        filterUrlParam: '?status=Em+reparo',
        statusBadge: 'badge-blue'
    },
    'peca': {
        title: 'Aguardando Peça',
        icon: 'package-search',
        iconClass: 'icon-orange',
        filterUrlParam: '?status=Aguardando+peca',
        statusBadge: 'badge-yellow'
    },
    'pronto': {
        title: 'Prontas para Entrega',
        icon: 'check-circle-2',
        iconClass: 'icon-emerald',
        filterUrlParam: '?status=Pronto',
        statusBadge: 'badge-green'
    }
};

function openKpiModal(type) {
    const cfg = filterConfigs[type];
    if (!cfg) return;

    const list = osDataByFilter[type] || [];
    const modal = document.getElementById('kpiDetailsModal');
    const title = document.getElementById('kpiModalTitle');
    const iconBox = document.getElementById('kpiModalIcon');
    const body = document.getElementById('kpiModalBody');
    const countSpan = document.getElementById('kpiModalCount');
    const linkBtn = document.getElementById('kpiModalLink');

    title.textContent = cfg.title;
    iconBox.className = 'kpi-modal-icon-box ' + cfg.iconClass;
    iconBox.innerHTML = '<i data-lucide="' + cfg.icon + '"></i>';
    countSpan.textContent = list.length + (list.length === 1 ? ' registro' : ' registros');
    linkBtn.href = osListBaseUrl + cfg.filterUrlParam;
    linkBtn.innerHTML = 'Ver Lista de OS <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>';

    if (list.length === 0) {
        body.innerHTML = `
            <div class="kpi-modal-empty">
                <div class="kpi-modal-empty-icon"><i data-lucide="check-check"></i></div>
                <h4>Nenhum registro nesta etapa</h4>
                <p>Nenhuma ordem de serviço pendente com esse status.</p>
                <a href="${osListBaseUrl}/create" class="btn btn-primary" style="margin-top:8px;padding:7px 14px;font-size:0.82rem;">
                    <i data-lucide="plus"></i> Abrir Nova OS
                </a>
            </div>
        `;
    } else {
        let html = `
            <div class="table-container" style="max-height:380px;overflow-y:auto;">
                <table class="kpi-modal-table">
                    <thead>
                        <tr>
                            <th style="width:85px;">Nº OS</th>
                            <th>Cliente</th>
                            <th>Aparelho</th>
                            <th>Status</th>
                            <th style="text-align:right;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        list.forEach(os => {
            const osId = os.id || '';
            const num = os.numero_os || osId;
            const cliente = escapeHtml(os.cliente_nome || 'Cliente não informado');
            const aparelho = escapeHtml((os.aparelho_marca ? os.aparelho_marca + ' ' : '') + (os.aparelho_modelo || 'Aparelho'));
            const status = escapeHtml(os.status || '');
            const detailUrl = osViewBaseUrl + '?id=' + encodeURIComponent(osId);

            html += `
                <tr>
                    <td><a href="${detailUrl}" class="os-code-pill">#${escapeHtml(String(num))}</a></td>
                    <td>
                        <div style="font-weight:600;color:var(--text-main);">${cliente}</div>
                        ${os.cliente_whatsapp ? '<div style="font-size:0.75rem;color:var(--text-muted);">' + escapeHtml(os.cliente_whatsapp) + '</div>' : ''}
                    </td>
                    <td style="font-size:0.85rem;color:var(--text-secondary);">${aparelho}</td>
                    <td><span class="badge ${cfg.statusBadge}">${status}</span></td>
                    <td style="text-align:right;">
                        <a href="${detailUrl}" class="btn btn-secondary" style="padding:4px 10px;font-size:0.78rem;gap:4px;">
                            Abrir <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
                        </a>
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table></div>`;
        body.innerHTML = html;
    }

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    if (window.lucide) lucide.createIcons();
}

function openFinModal(period) {
    const data = finData[period];
    if (!data) return;

    const modal = document.getElementById('kpiDetailsModal');
    const title = document.getElementById('kpiModalTitle');
    const iconBox = document.getElementById('kpiModalIcon');
    const body = document.getElementById('kpiModalBody');
    const countSpan = document.getElementById('kpiModalCount');
    const linkBtn = document.getElementById('kpiModalLink');

    title.textContent = data.titulo;
    iconBox.className = 'kpi-modal-icon-box icon-emerald';
    iconBox.innerHTML = '<i data-lucide="circle-dollar-sign"></i>';
    countSpan.textContent = 'Período: ' + (period === 'dia' ? 'Hoje' : (period === 'semana' ? 'Últimos 7 dias' : 'Mês atual'));
    linkBtn.href = finBaseUrl;
    linkBtn.innerHTML = 'Ir para Financeiro <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>';

    const formatMoney = (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    let formasHtml = '';
    if (data.formas && data.formas.length > 0) {
        formasHtml = `
            <div style="margin-top:1rem;">
                <h5 class="brand-font" style="font-size:0.9rem;margin-bottom:0.5rem;">Formas de Pagamento</h5>
                <div style="display:flex;flex-direction:column;gap:5px;">
        `;
        data.formas.forEach(f => {
            formasHtml += `
                <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 10px;background:var(--bg-main);border-radius:8px;font-size:0.85rem;">
                    <span style="font-weight:600;">${escapeHtml(f.forma_pagamento || 'Outro')} (${f.qtd}x)</span>
                    <span style="font-weight:700;color:var(--success);">${formatMoney(f.total || 0)}</span>
                </div>
            `;
        });
        formasHtml += `</div></div>`;
    }

    body.innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:0.75rem;">
            <div class="kpi-fin-stat-box box-green">
                <span style="font-size:0.75rem;color:var(--text-muted);font-weight:600;">RECEITAS</span>
                <h3 style="font-size:1.45rem;font-weight:800;color:var(--success);margin:3px 0 0;">${formatMoney(data.receitas)}</h3>
            </div>
            <div class="kpi-fin-stat-box box-red">
                <span style="font-size:0.75rem;color:var(--text-muted);font-weight:600;">DESPESAS</span>
                <h3 style="font-size:1.45rem;font-weight:800;color:var(--danger);margin:3px 0 0;">${formatMoney(data.despesas)}</h3>
            </div>
        </div>
        ${formasHtml}
    `;

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    if (window.lucide) lucide.createIcons();
}

function closeKpiModal() {
    const modal = document.getElementById('kpiDetailsModal');
    if (modal) modal.classList.remove('active');
    document.body.style.overflow = '';
}

function handleKpiBackdrop(event) {
    if (event.target && event.target.id === 'kpiDetailsModal') {
        closeKpiModal();
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeKpiModal();
});

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
