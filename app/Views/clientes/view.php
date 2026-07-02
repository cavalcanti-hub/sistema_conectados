<?php
$cliente = is_array($cliente ?? null) ? $cliente : [];
$historico = is_array($historico ?? null) ? $historico : [];
$whatsappDigits = preg_replace('/\D/', '', (string) ($cliente['whatsapp'] ?? ''));
$whatsappHref = $whatsappDigits !== '' ? 'https://wa.me/55' . $whatsappDigits : '#';
$totalOs = count($historico);
$valorTotalHistorico = array_sum(array_map(static fn($os) => (float) ($os['valor_total'] ?? 0), $historico));
$ultimaOs = !empty($historico[0]['created_at']) ? date('d/m/Y', strtotime($historico[0]['created_at'])) : 'Sem OS';
$contactRows = [
    ['icon' => 'phone', 'label' => 'Telefone', 'value' => trim((string) ($cliente['telefone'] ?? ''))],
    ['icon' => 'mail', 'label' => 'E-mail', 'value' => trim((string) ($cliente['email'] ?? ''))],
    ['icon' => 'map-pin', 'label' => 'Endereco', 'value' => trim((string) ($cliente['endereco'] ?? ''))],
];
require_once dirname(__DIR__) . '/layout/header.php';
?>

<style>
    .client-page {
        display: grid;
        gap: 1rem;
    }

    .client-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .client-back {
        color: var(--text-muted);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-size: .88rem;
        font-weight: 700;
    }

    .client-actions {
        display: flex;
        gap: .65rem;
        flex-wrap: wrap;
    }

    .client-actions .btn {
        min-height: 42px;
        border-radius: 12px;
        text-decoration: none;
    }

    .btn-whatsapp {
        background: #25D366;
        color: #fff !important;
    }

    .btn-whatsapp.disabled {
        pointer-events: none;
        opacity: .55;
    }

    .whatsapp-brand {
        width: 18px;
        height: 18px;
        display: block;
    }

    .client-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: end;
        padding: 1.25rem;
        border-radius: 18px;
        border: 1px solid rgba(203, 213, 225, .9);
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 18px 42px -30px rgba(15, 23, 42, .55);
    }

    .client-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: var(--primary);
        font-size: .76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: .45rem;
    }

    .client-hero h2 {
        margin: 0;
        font-size: clamp(1.35rem, 2vw, 2rem);
        line-height: 1.12;
    }

    .client-doc {
        color: var(--text-muted);
        margin-top: .55rem;
        font-size: .9rem;
        font-weight: 700;
    }

    .client-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(120px, 1fr));
        gap: .6rem;
        min-width: min(100%, 460px);
    }

    .client-stat {
        padding: .85rem;
        border-radius: 14px;
        background: #fff;
        border: 1px solid rgba(203, 213, 225, .75);
    }

    .client-stat span {
        display: block;
        color: var(--text-muted);
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: .25rem;
    }

    .client-stat strong {
        font-size: 1rem;
        color: #0f172a;
    }

    .client-layout {
        display: grid;
        grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
    }

    .client-panel {
        border-radius: 18px;
        border: 1px solid rgba(203, 213, 225, .9);
        background: #fff;
        box-shadow: 0 18px 42px -32px rgba(15, 23, 42, .5);
        overflow: hidden;
    }

    .client-panel-head {
        padding: 1rem 1.1rem;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }

    .client-panel-head h3 {
        margin: 0;
        font-size: 1rem;
    }

    .client-contact-list {
        display: grid;
        gap: .15rem;
        padding: .8rem;
    }

    .client-contact-row {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: .7rem;
        align-items: center;
        padding: .75rem;
        border-radius: 12px;
    }

    .client-contact-row:hover {
        background: #f8fafc;
    }

    .client-contact-row i {
        width: 17px;
        height: 17px;
        color: var(--primary);
        margin: auto;
    }

    .client-contact-row small {
        display: block;
        color: var(--text-muted);
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: .1rem;
    }

    .client-contact-row span {
        display: block;
        color: #0f172a;
        font-size: .9rem;
        overflow-wrap: anywhere;
    }

    .client-contact-empty {
        color: var(--text-muted) !important;
        font-style: italic;
    }

    .client-notes {
        padding: 0 .8rem .8rem;
    }

    .client-notes-box {
        padding: .85rem;
        border-radius: 12px;
        background: #f8fafc;
        color: #334155;
        font-size: .88rem;
        line-height: 1.45;
    }

    .history-card {
        min-height: 420px;
    }

    .history-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .history-count {
        color: var(--text-muted);
        font-size: .82rem;
        font-weight: 800;
    }

    .history-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .history-table th {
        background: #f8fafc;
        color: #334155;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .85rem 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }

    .history-table td {
        padding: .9rem 1rem;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
        font-size: .9rem;
    }

    .history-table tr:last-child td {
        border-bottom: none;
    }

    .history-os {
        font-weight: 900;
        color: #0f172a;
    }

    .history-device {
        color: #334155;
    }

    .history-total {
        font-weight: 900;
        color: #0f172a;
        white-space: nowrap;
    }

    .history-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }

    .history-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--text-muted);
    }

    @media (max-width: 1050px) {
        .client-hero {
            grid-template-columns: 1fr;
        }

        .client-stats {
            min-width: 0;
        }

        .client-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 680px) {
        .client-actions,
        .client-actions .btn {
            width: 100%;
        }

        .client-stats {
            grid-template-columns: 1fr;
        }

        .history-table th:nth-child(4),
        .history-table td:nth-child(4) {
            display: none;
        }
    }
</style>

<div class="client-page">
    <div class="client-nav">
        <a href="<?= route_url('clientes') ?>" class="client-back"><i data-lucide="arrow-left" style="width:17px;"></i> Voltar para clientes</a>
        <div class="client-actions">
            <a href="<?= htmlspecialchars($whatsappHref) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp <?= $whatsappDigits === '' ? 'disabled' : '' ?>">
                <svg class="whatsapp-brand" viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-156.9zM223.9 438.7c-33.1 0-65.6-8.9-94-25.7l-6.7-4-69.8 18.3 18.6-68.1-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 11-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
                WhatsApp
            </a>
            <a href="<?= route_url('clientes/edit', ['id' => $cliente['id'] ?? 0]) ?>" class="btn btn-secondary">
                <i data-lucide="edit-3"></i> Editar
            </a>
            <a href="<?= route_url('os/create') ?>" class="btn btn-primary">
                <i data-lucide="plus"></i> Nova OS
            </a>
        </div>
    </div>

    <section class="client-hero">
        <div>
            <div class="client-eyebrow"><i data-lucide="user-round" style="width:15px;"></i> Perfil do cliente</div>
            <h2 class="brand-font"><?= htmlspecialchars($cliente['nome'] ?? 'Cliente') ?></h2>
            <div class="client-doc">CPF/CNPJ: <?= htmlspecialchars($cliente['cpf_cnpj'] ?? 'Nao informado') ?></div>
        </div>
        <div class="client-stats">
            <div class="client-stat">
                <span>Ordens</span>
                <strong><?= $totalOs ?></strong>
            </div>
            <div class="client-stat">
                <span>Total em OS</span>
                <strong>R$ <?= number_format($valorTotalHistorico, 2, ',', '.') ?></strong>
            </div>
            <div class="client-stat">
                <span>Ultima OS</span>
                <strong><?= htmlspecialchars($ultimaOs) ?></strong>
            </div>
        </div>
    </section>

    <div class="client-layout">
        <aside class="client-panel">
            <div class="client-panel-head">
                <h3 class="brand-font">Contato</h3>
            </div>
            <div class="client-contact-list">
                <div class="client-contact-row">
                    <svg class="whatsapp-brand" viewBox="0 0 448 512" aria-hidden="true" focusable="false" style="color:#25D366;margin:auto;"><path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-156.9zM223.9 438.7c-33.1 0-65.6-8.9-94-25.7l-6.7-4-69.8 18.3 18.6-68.1-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 11-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
                    <div>
                        <small>WhatsApp</small>
                        <span class="<?= empty($cliente['whatsapp']) ? 'client-contact-empty' : '' ?>"><?= htmlspecialchars($cliente['whatsapp'] ?: 'Nao informado') ?></span>
                    </div>
                </div>
                <?php foreach ($contactRows as $row): ?>
                    <div class="client-contact-row">
                        <i data-lucide="<?= $row['icon'] ?>"></i>
                        <div>
                            <small><?= htmlspecialchars($row['label']) ?></small>
                            <span class="<?= $row['value'] === '' ? 'client-contact-empty' : '' ?>"><?= htmlspecialchars($row['value'] !== '' ? $row['value'] : 'Nao informado') ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($cliente['observacoes'])): ?>
                <div class="client-notes">
                    <div class="client-notes-box"><?= nl2br(htmlspecialchars($cliente['observacoes'])) ?></div>
                </div>
            <?php endif; ?>
        </aside>

        <section class="client-panel history-card">
            <div class="client-panel-head history-title">
                <h3 class="brand-font">Historico de OS</h3>
                <span class="history-count"><?= $totalOs ?> registro<?= $totalOs === 1 ? '' : 's' ?></span>
            </div>
            <?php if(empty($historico)): ?>
                <div class="history-empty">
                    <i data-lucide="clipboard-list" style="width:42px;height:42px;margin-bottom:.8rem;opacity:.35;"></i>
                    <p>Nenhuma OS registrada para este cliente.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>OS</th>
                                <th>Aparelho</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($historico as $h): ?>
                            <tr>
                                <td class="history-os">#<?= htmlspecialchars($h['numero_os'] ?? '') ?></td>
                                <td class="history-device"><?= htmlspecialchars($h['modelo'] ?? '-') ?></td>
                                <td><span class="badge badge-blue" style="font-size:.72rem;"><?= htmlspecialchars($h['status'] ?? '') ?></span></td>
                                <td><?= !empty($h['created_at']) ? date('d/m/Y', strtotime($h['created_at'])) : '-' ?></td>
                                <td class="history-total">R$ <?= number_format((float) ($h['valor_total'] ?? 0), 2, ',', '.') ?></td>
                                <td><a href="<?= route_url('os/viewDetail', ['id' => $h['id']]) ?>" class="history-link">Ver <i data-lucide="arrow-up-right" style="width:14px;"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
