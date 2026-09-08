<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Acompanhar Ordem de Serviço') ?></title>
    <meta name="theme-color" content="#2d2dff">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        :root {
            --primary: #2d2dff;
            --primary-light: #eff0ff;
            --success: #059669;
            --success-light: #ecfdf5;
            --warning: #d97706;
            --warning-light: #fffbeb;
            --danger: #dc2626;
            --bg-main: #f4f6fa;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 16px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            line-height: 1.5;
            padding: 1rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .consulta-container {
            width: 100%;
            max-width: 580px;
            margin: 1rem auto 2rem;
        }
        .consulta-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .consulta-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
        }
        .consulta-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-top: 4px;
        }
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.06);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .search-box {
            display: flex;
            gap: 8px;
            margin-bottom: 1rem;
        }
        .search-box input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(45, 45, 255, 0.12);
        }
        .btn-search {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0 20px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
        }
        .status-badge-lg {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 1.25rem;
        }
        .badge-blue { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .badge-green { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-yellow { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .badge-purple { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
        .badge-red { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .badge-gray { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        /* TIMELINE */
        .timeline {
            position: relative;
            margin: 1.5rem 0 1rem;
            padding-left: 28px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 9px;
            top: 6px;
            bottom: 6px;
            width: 2px;
            background: #e2e8f0;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 1.25rem;
        }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-dot {
            position: absolute;
            left: -28px;
            top: 3px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .timeline-item.active .timeline-dot {
            border-color: var(--primary);
            background: var(--primary);
            box-shadow: 0 0 0 4px rgba(45, 45, 255, 0.2);
        }
        .timeline-item.done .timeline-dot {
            border-color: var(--success);
            background: var(--success);
        }
        .timeline-dot i {
            width: 11px;
            height: 11px;
            color: white;
        }
        .timeline-title {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-main);
        }
        .timeline-desc {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin: 1rem 0;
            padding: 12px;
            background: var(--bg-main);
            border-radius: 12px;
        }
        .info-item span {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }
        .info-item strong {
            font-size: 0.9rem;
            color: var(--text-main);
        }

        .btn-whatsapp {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: #25d366;
            color: white;
            text-decoration: none;
            padding: 13px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 4px 14px rgba(37, 211, 102, 0.3);
            transition: transform 0.15s, background-color 0.15s;
            margin-top: 1rem;
        }
        .btn-whatsapp:hover {
            background: #20ba5a;
            transform: translateY(-2px);
        }

        .footer-note {
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>

<div class="consulta-container">
    <div class="consulta-header">
        <h1><?= htmlspecialchars($empresa['nome'] ?? 'Conectados') ?></h1>
        <p>Acompanhamento de Ordem de Serviço em Tempo Real</p>
    </div>

    <!-- Barra de busca rápida -->
    <form action="<?= route_url('consulta') ?>" method="GET" class="search-box">
        <input type="text" name="os" placeholder="Digite o número da sua OS (ex: 102)..." value="<?= htmlspecialchars($numeroBuscado ?? '') ?>" required autofocus>
        <button type="submit" class="btn-search">
            <i data-lucide="search" style="width:16px;height:16px;"></i> Consultar
        </button>
    </form>

    <?php if (!empty($error)): ?>
        <div class="card" style="border-left: 4px solid var(--danger); text-align: center; padding: 2rem;">
            <i data-lucide="alert-circle" style="width:40px;height:40px;color:var(--danger);display:block;margin:0 auto 10px;"></i>
            <h3 style="font-size:1.1rem;margin-bottom:6px;">Ordem de Serviço Não Localizada</h3>
            <p style="color:var(--text-muted);font-size:0.875rem;"><?= htmlspecialchars($error) ?></p>
        </div>
    <?php elseif (!empty($os)): ?>
        <?php
        $status = (string)($os['status'] ?? 'Recebido');
        $badgeClass = 'badge-blue';
        if (in_array($status, ['Pronto', 'Entregue'], true)) $badgeClass = 'badge-green';
        if (in_array($status, ['Aguardando peca', 'Aguardando aprovacao'], true)) $badgeClass = 'badge-yellow';
        if (in_array($status, ['Cancelado', 'Reprovado'], true)) $badgeClass = 'badge-red';

        // Etapas da Timeline
        $steps = [
            'Recebido' => 'Aparelho recebido na loja e cadastrado',
            'Em analise' => 'Em diagnóstico técnico na bancada',
            'Aguardando aprovacao' => 'Aguardando confirmação do orçamento pelo cliente',
            'Em reparo' => 'Serviço em execução pelo técnico especializado',
            'Pronto' => 'Aparelho testado e pronto para entrega/retirada',
            'Entregue' => 'Aparelho finalizado e entregue ao cliente'
        ];

        $stepKeys = array_keys($steps);
        $currentIdx = array_search($status, $stepKeys, true);
        if ($currentIdx === false) $currentIdx = 0;
        ?>

        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
                <div>
                    <span style="font-size:0.8rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Ordem de Serviço</span>
                    <h2 style="font-family:'Outfit',sans-serif;font-size:1.6rem;font-weight:800;color:var(--primary);margin:2px 0 10px;">
                        #<?= htmlspecialchars((string) $os['numero_os']) ?>
                    </h2>
                </div>
                <div class="status-badge-lg <?= $badgeClass ?>">
                    <i data-lucide="activity" style="width:18px;height:18px;"></i>
                    <?= htmlspecialchars($status) ?>
                </div>
            </div>

            <!-- Dados do Aparelho -->
            <div class="info-grid">
                <div class="info-item">
                    <span>Aparelho</span>
                    <strong><?= htmlspecialchars(trim(($os['marca'] ?? '') . ' ' . ($os['modelo'] ?? 'Aparelho'))) ?></strong>
                </div>
                <div class="info-item">
                    <span>Cliente</span>
                    <strong><?= htmlspecialchars((string) ($os['cliente_nome'] ?? 'Cliente')) ?></strong>
                </div>
                <?php if (!empty($os['prazo_estimado'])): ?>
                <div class="info-item">
                    <span>Previsão de Entrega</span>
                    <strong><?= date('d/m/Y', strtotime((string)$os['prazo_estimado'])) ?></strong>
                </div>
                <?php endif; ?>
                <?php if (!empty($os['valor_total']) && (float)$os['valor_total'] > 0): ?>
                <div class="info-item">
                    <span>Valor Orçado</span>
                    <strong style="color:var(--success);">R$ <?= number_format((float)$os['valor_total'], 2, ',', '.') ?></strong>
                </div>
                <?php endif; ?>
            </div>

            <!-- Timeline do Serviço -->
            <h4 style="font-family:'Outfit',sans-serif;font-size:1.05rem;font-weight:700;margin:1.25rem 0 0.5rem;">
                Progresso do Serviço
            </h4>

            <div class="timeline">
                <?php foreach ($steps as $stepKey => $stepDesc): ?>
                    <?php
                    $stepIdx = array_search($stepKey, $stepKeys, true);
                    $itemClass = '';
                    if ($stepIdx < $currentIdx) $itemClass = 'done';
                    elseif ($stepIdx === $currentIdx) $itemClass = 'active';
                    ?>
                    <div class="timeline-item <?= $itemClass ?>">
                        <div class="timeline-dot">
                            <?php if ($stepIdx < $currentIdx): ?>
                                <i data-lucide="check"></i>
                            <?php endif; ?>
                        </div>
                        <div class="timeline-title"><?= htmlspecialchars($stepKey) ?></div>
                        <div class="timeline-desc"><?= htmlspecialchars($stepDesc) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Ação WhatsApp -->
            <?php if (!empty($empresa['whatsapp'])): ?>
                <?php
                $zapNum = preg_replace('/\D/', '', (string) $empresa['whatsapp']);
                $zapMsg = rawurlencode("Olá! Gostaria de informações sobre a minha Ordem de Serviço #" . ($os['numero_os'] ?? '') . " (" . ($os['modelo'] ?? 'Aparelho') . ").");
                $zapUrl = "https://wa.me/{$zapNum}?text={$zapMsg}";
                ?>
                <a href="<?= $zapUrl ?>" target="_blank" rel="noopener" class="btn-whatsapp">
                    <i data-lucide="message-circle" style="width:20px;height:20px;"></i>
                    Falar com a Assistência no WhatsApp
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($empresa['endereco'])): ?>
        <div class="card" style="padding:1rem 1.25rem;font-size:0.85rem;color:var(--text-muted);display:flex;align-items:center;gap:10px;">
            <i data-lucide="map-pin" style="width:20px;height:20px;color:var(--primary);flex:0 0 auto;"></i>
            <div>
                <strong style="color:var(--text-main);"><?= htmlspecialchars($empresa['nome']) ?></strong><br>
                <?= htmlspecialchars($empresa['endereco']) ?>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="card" style="text-align: center; padding: 2.5rem 1rem;">
            <i data-lucide="smartphone" style="width:48px;height:48px;color:var(--primary);display:block;margin:0 auto 12px;opacity:0.8;"></i>
            <h3 style="font-size:1.15rem;font-weight:700;margin-bottom:6px;">Consulte o Status da sua OS</h3>
            <p style="color:var(--text-muted);font-size:0.875rem;max-width:360px;margin:0 auto;">
                Digite o número da ordem de serviço que você recebeu no comprovante para acompanhar cada etapa do reparo.
            </p>
        </div>
    <?php endif; ?>

    <div class="footer-note">
        <?= htmlspecialchars($empresa['nome'] ?? 'Conectados') ?> • Sistema de Gestão e Assistência
    </div>
</div>

<script>
    if (window.lucide) lucide.createIcons();
</script>
</body>
</html>
