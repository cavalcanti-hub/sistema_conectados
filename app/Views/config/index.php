<?php require_once dirname(__DIR__) . '/layout/header.php'; ?>
<?php
if (empty($diagnostico) || !is_array($diagnostico)) {
    $uploads = public_path('uploads');
    $uploadsEstoque = public_path('uploads/estoque');
    $appUrl = app_url();
    $diagnostico = [
        'APP_ENV' => app_env('APP_ENV', ''),
        'APP_MODE' => app_env('APP_MODE', ''),
        'APP_URL' => app_env('APP_URL', ''),
        'APP_BASE_PATH' => app_env('APP_BASE_PATH', ''),
        'conexao_banco' => 'ok',
        'PHP' => PHP_VERSION,
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'pdo_mysql' => extension_loaded('pdo_mysql') ? 'ativo' : 'inativo',
        'curl' => extension_loaded('curl') ? 'ativo' : 'inativo',
        'gd' => extension_loaded('gd') ? 'ativo' : 'inativo',
        'fileinfo' => extension_loaded('fileinfo') ? 'ativo' : 'inativo',
        'mbstring' => extension_loaded('mbstring') ? 'ativo' : 'inativo',
        'uploads_writable' => is_dir($uploads) && is_writable($uploads) ? 'sim' : 'nao',
        'uploads_estoque_writable' => is_dir($uploadsEstoque) && is_writable($uploadsEstoque) ? 'sim' : 'nao',
        'APP_URL_publica' => function_exists('is_public_url') && is_public_url($appUrl) ? 'sim' : 'nao',
    ];
}
$backupInfo = $backupInfo ?? ['last_at' => '', 'last_file' => '', 'last_size' => 0];
$auditLogs = is_array($auditLogs ?? null) ? $auditLogs : [];
$formatBackupSize = static function ($bytes): string {
    $bytes = (int) $bytes;
    if ($bytes <= 0) {
        return 'nao informado';
    }

    $units = ['B', 'KB', 'MB', 'GB'];
    $size = (float) $bytes;
    $unitIndex = 0;
    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }

    return number_format($size, $unitIndex === 0 ? 0 : 2, ',', '.') . ' ' . $units[$unitIndex];
};
$offerIconOptions = [
    'truck' => 'Frete',
    'package' => 'Pacote',
    'tag' => 'Oferta',
    'credit-card' => 'Cartao',
    'shield-check' => 'Compra segura',
    'message-circle' => 'Atendimento',
    'smartphone' => 'Celular',
    'headphones' => 'Audio',
    'gift' => 'Presente',
    'zap' => 'Rapidez',
    'clock' => 'Horario',
    'map-pin' => 'Localizacao',
    'wrench' => 'Assistencia',
    'sparkles' => 'Destaque',
    'star' => 'Favorito',
    'flame' => 'Oferta quente',
    'party-popper' => 'Celebracao',
    'check-circle' => 'Confirmado',
    'shopping-cart' => 'Carrinho',
    'badge-percent' => 'Desconto',
    'megaphone' => 'Aviso',
    'rocket' => 'Lancamento',
    'phone' => 'Telefone',
    'settings' => 'Configuracao',
    'battery' => 'Bateria',
    'plug' => 'Carregador',
    'laptop' => 'Notebook',
    'monitor' => 'Computador',
    'camera' => 'Camera',
    'wifi' => 'Conectividade',
    'store' => 'Loja',
    'home' => 'Casa',
    'lock' => 'Seguro',
    'gem' => 'Premium',
    'trophy' => 'Campeao',
    'thumbs-up' => 'Aprovado',
    'trending-up' => 'Alta procura',
    'lightbulb' => 'Dica',
    'info' => 'Informacao',
    'pin' => 'Fixado',
];
$offerDefaults = [
    ['text' => 'Frete gratis em ofertas selecionadas', 'icon' => '🚚'],
    ['text' => 'Parcelamento facilitado', 'icon' => '💳'],
    ['text' => 'Compra segura na loja virtual', 'icon' => '🛡️'],
    ['text' => 'Retirada rapida na loja', 'icon' => '📦'],
    ['text' => 'Acessorios com preco especial', 'icon' => '🏷️'],
    ['text' => 'Atendimento direto pelo WhatsApp', 'icon' => '💬'],
];
?>

<style>
    .config-container {
        display: block;
        min-height: 70vh;
    }
    .config-sidebar {
        width: 100%;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: .75rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
    }
    .config-main {
        width: 100%;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: var(--shadow);
    }
    .config-nav {
        display: flex;
        flex-direction: row;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 2px;
        scrollbar-width: thin;
    }
    .config-nav-link {
        padding: 12px 16px;
        border-radius: 12px;
        color: var(--text-muted);
        text-decoration: none;
        font-weight: 600;
        transition: 0.2s;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.9rem;
        white-space: nowrap;
        min-height: 46px;
    }
    .config-nav-link:hover {
        background: rgba(0, 52, 154, 0.05);
        color: var(--primary);
    }
    .config-nav-link.active {
        background: var(--primary);
        color: white;
    }
    .config-section { display: none; }
    .config-section.active { display: block; animation: fadeIn 0.3s ease; }

    .cat-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    .cat-list {
        background: var(--bg-main);
        padding: 1rem;
        border-radius: 12px;
        margin-top: 1rem;
    }
    .cat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: var(--bg-card);
        margin-bottom: 5px;
        border-radius: 8px;
        border: 1px solid var(--border);
    }
    .btn-delete-cat {
        color: var(--danger);
        cursor: pointer;
        opacity: 0.6;
        transition: 0.2s;
        background: transparent;
        border: 0;
        padding: 4px;
        border-radius: 8px;
    }
    .btn-delete-cat:hover { opacity: 1; }
    .config-card {
        background: #f8fbff;
        border: 1px solid rgba(0, 52, 154, 0.08);
        border-radius: 18px;
        padding: 1.75rem;
        margin-bottom: 2rem;
        transition: 0.3s;
    }
    .config-card:hover {
        border-color: rgba(0, 52, 154, 0.2);
        box-shadow: 0 10px 25px -15px rgba(0, 52, 154, 0.15);
    }
    .config-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        color: #071b37;
        margin-bottom: 1.5rem;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .config-card-title i {
        color: var(--primary);
    }
    .config-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }
    .config-grid-full {
        grid-column: span 2;
    }
    .offer-config-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 280px;
        gap: .85rem;
        align-items: end;
        padding: .95rem;
        background: #fff;
        border: 1px solid rgba(0, 52, 154, 0.08);
        border-radius: 14px;
    }
    .diagnostic-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
    }
    .diagnostic-item {
        background: #fff;
        border: 1px solid rgba(0, 52, 154, 0.1);
        border-radius: 12px;
        padding: .85rem;
        min-width: 0;
    }
    .diagnostic-item span {
        display: block;
        color: var(--text-muted);
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: .35rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .diagnostic-item strong {
        display: block;
        color: #071b37;
        font-size: .9rem;
        overflow-wrap: anywhere;
    }
    @media (max-width: 760px) {
        .config-sidebar {
            border-radius: 14px;
            padding: .5rem;
            margin-bottom: 1rem;
        }
        .config-main {
            border-radius: 14px;
            padding: 1.25rem;
        }
        .config-nav-link {
            padding: 10px 12px;
            font-size: .84rem;
        }
        .config-nav-link i {
            width: 18px;
            height: 18px;
        }
        .config-grid-2,
        .offer-config-row,
        .cat-grid,
        .diagnostic-grid {
            grid-template-columns: 1fr;
        }
        .config-grid-full {
            grid-column: span 1;
        }
    }
</style>

<div class="config-container">
    <aside class="config-sidebar">
        <nav class="config-nav">
            <a href="javascript:void(0)" onclick="showTab('geral')" id="tab-link-geral" class="config-nav-link active">
                <i data-lucide="building-2"></i> Empresa
            </a>
            <a href="javascript:void(0)" onclick="showTab('social')" id="tab-link-social" class="config-nav-link">
                <i data-lucide="share-2"></i> Redes Sociais
            </a>
            <a href="javascript:void(0)" onclick="showTab('vitrine')" id="tab-link-vitrine" class="config-nav-link">
                <i data-lucide="monitor-smartphone"></i> Vitrine
            </a>
            <a href="javascript:void(0)" onclick="showTab('categorias')" id="tab-link-categorias" class="config-nav-link">
                <i data-lucide="tags"></i> Categorias
            </a>
            <a href="javascript:void(0)" onclick="showTab('marketplace')" id="tab-link-marketplace" class="config-nav-link">
                <i data-lucide="store"></i> Marketplace
            </a>
            <a href="javascript:void(0)" onclick="showTab('pagamentos')" id="tab-link-pagamentos" class="config-nav-link">
                <i data-lucide="credit-card"></i> Pagamentos
            </a>
            <a href="javascript:void(0)" onclick="showTab('backup')" id="tab-link-backup" class="config-nav-link">
                <i data-lucide="database-backup"></i> Backup
            </a>
            <a href="javascript:void(0)" onclick="showTab('auditoria')" id="tab-link-auditoria" class="config-nav-link">
                <i data-lucide="history"></i> Auditoria
            </a>
            <a href="javascript:void(0)" onclick="showTab('diagnostico')" id="tab-link-diagnostico" class="config-nav-link">
                <i data-lucide="activity"></i> Diagnostico
            </a>
        </nav>
    </aside>

    <main class="config-main">
        <form action="<?= e(route_url('config/update')) ?>" method="POST" id="config-form">
            <?= csrf_field() ?>
            
            <!-- Empresa -->
            <section id="sect-geral" class="config-section active">
                <h3 class="brand-font" style="margin-bottom: 2rem;">Dados da Empresa</h3>
                
                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="building-2"></i> Identificação e Contato
                    </div>
                    <div class="config-grid-2">
                        <div class="form-group">
                            <label class="form-label">Nome da Empresa</label>
                            <input type="text" name="settings[nome_empresa]" class="form-control" value="<?= htmlspecialchars($settings['nome_empresa'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">WhatsApp (Vendas)</label>
                            <input type="text" name="settings[whatsapp]" class="form-control" value="<?= htmlspecialchars($settings['whatsapp'] ?? '') ?>">
                            <small style="color: var(--text-muted);">Ex: 5511999999999</small>
                        </div>
                        <div class="form-group config-grid-full">
                            <label class="form-label">Endereço Completo</label>
                            <input type="text" name="settings[endereco]" class="form-control" value="<?= htmlspecialchars($settings['endereco'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Website</label>
                            <input type="text" name="settings[website]" class="form-control" value="<?= htmlspecialchars($settings['website'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">E-mail de Contato</label>
                            <input type="email" name="settings[email_negocio]" class="form-control" value="<?= htmlspecialchars($settings['email_negocio'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="folder-output"></i> Fechamento de Caixa
                    </div>
                    <div class="config-grid-2">
                        <div class="form-group config-grid-full">
                            <label class="form-label">Pasta para Salvar Relatórios de Fechamento de Caixa (PDF)</label>
                            <input type="text" name="settings[fechamento_caixa_pasta]" class="form-control" value="<?= htmlspecialchars($settings['fechamento_caixa_pasta'] ?? dirname(__DIR__, 4) . '/public/uploads/relatorios_financeiro') ?>">
                            <small style="color: var(--text-muted);">Caminho completo da pasta onde os arquivos PDF do fechamento serão salvos automaticamente. Deixe em branco para usar o padrão do sistema.</small>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Social -->
            <section id="sect-social" class="config-section">
                <h3 class="brand-font" style="margin-bottom: 2rem;">Redes Sociais</h3>
                
                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="share-2"></i> Presença Digital
                    </div>
                    <div class="config-grid-2">
                        <div class="form-group">
                            <label class="form-label"><i data-lucide="instagram" style="width: 14px;"></i> Instagram</label>
                            <input type="text" name="settings[instagram]" class="form-control" value="<?= htmlspecialchars($settings['instagram'] ?? '') ?>" placeholder="https://instagram.com/...">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i data-lucide="facebook" style="width: 14px;"></i> Facebook</label>
                            <input type="text" name="settings[facebook]" class="form-control" value="<?= htmlspecialchars($settings['facebook'] ?? '') ?>" placeholder="https://facebook.com/...">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i data-lucide="video" style="width: 14px;"></i> TikTok</label>
                            <input type="text" name="settings[tiktok]" class="form-control" value="<?= htmlspecialchars($settings['tiktok'] ?? '') ?>" placeholder="https://tiktok.com/@...">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i data-lucide="linkedin" style="width: 14px;"></i> LinkedIn</label>
                            <input type="text" name="settings[linkedin]" class="form-control" value="<?= htmlspecialchars($settings['linkedin'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </section>

            <section id="sect-vitrine" class="config-section">
                <h3 class="brand-font" style="margin-bottom: 2rem;">Personalização da Vitrine</h3>

                <!-- Banner Principal -->
                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="layout-template"></i> Banner de Destaque
                    </div>
                    <div class="config-grid-2">
                        <div class="form-group">
                            <label class="form-label">Kicker (Texto pequeno acima do título)</label>
                            <input type="text" name="settings[vitrine_hero_kicker]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_hero_kicker'] ?? '') ?>" placeholder="Ex: Tecnologia conectada">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Título Principal</label>
                            <input type="text" name="settings[vitrine_hero_titulo]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_hero_titulo'] ?? '') ?>">
                        </div>
                        <div class="form-group config-grid-full">
                            <label class="form-label">Descrição (Texto abaixo do título)</label>
                            <input type="text" name="settings[vitrine_hero_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_hero_texto'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="badge-percent"></i> Faixa Rotativa de Ofertas
                    </div>
                    <p style="color: var(--text-muted); font-size: .86rem; margin: -0.5rem 0 1.25rem;">Essas frases aparecem na barra azul do topo do site. A faixa fica em movimento e pausa quando o visitante clica nela.</p>
                    <div style="display: grid; gap: .85rem;">
                        <?php foreach ($offerDefaults as $i => $defaultOffer): ?>
                            <?php
                            $number = $i + 1;
                            $textKey = 'vitrine_offer_' . $number . '_texto';
                            $iconKey = 'vitrine_offer_' . $number . '_icone';
                            $selectedIcon = $settings[$iconKey] ?? $defaultOffer['icon'];
                            ?>
                            <div class="offer-config-row">
                                <div class="form-group" style="margin: 0;">
                                    <label class="form-label">Oferta <?= $number ?> - Texto</label>
                                    <input type="text" name="settings[<?= $textKey ?>]" class="form-control" value="<?= htmlspecialchars($settings[$textKey] ?? $defaultOffer['text']) ?>" placeholder="<?= htmlspecialchars($defaultOffer['text']) ?>">
                                </div>
                                <div class="form-group" style="margin: 0;">
                                    <label class="form-label">Emoji</label>
                                    <select name="settings[<?= $iconKey ?>]" class="form-control">
                                        <?php foreach ($offerIconOptions as $iconValue => $iconLabel): ?>
                                            <option value="<?= htmlspecialchars($iconValue) ?>" <?= $selectedIcon === $iconValue ? 'selected' : '' ?>><?= htmlspecialchars($iconLabel . ' (' . $iconValue . ')') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Cards de Benefícios -->
                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="shield-check"></i> Faixa de Benefícios (4 Cards)
                    </div>
                    <div class="config-grid-2">
                        <!-- Card 1 -->
                        <div class="form-group"><label class="form-label">Card 1 - Título</label><input type="text" name="settings[vitrine_benefit_1_titulo]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_benefit_1_titulo'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Card 1 - Texto</label><input type="text" name="settings[vitrine_benefit_1_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_benefit_1_texto'] ?? '') ?>"></div>
                        <!-- Card 2 -->
                        <div class="form-group"><label class="form-label">Card 2 - Título</label><input type="text" name="settings[vitrine_benefit_2_titulo]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_benefit_2_titulo'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Card 2 - Texto</label><input type="text" name="settings[vitrine_benefit_2_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_benefit_2_texto'] ?? '') ?>"></div>
                        <!-- Card 3 -->
                        <div class="form-group"><label class="form-label">Card 3 - Título</label><input type="text" name="settings[vitrine_benefit_3_titulo]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_benefit_3_titulo'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Card 3 - Texto</label><input type="text" name="settings[vitrine_benefit_3_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_benefit_3_texto'] ?? '') ?>"></div>
                        <!-- Card 4 -->
                        <div class="form-group"><label class="form-label">Card 4 - Título</label><input type="text" name="settings[vitrine_benefit_4_titulo]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_benefit_4_titulo'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Card 4 - Texto</label><input type="text" name="settings[vitrine_benefit_4_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_benefit_4_texto'] ?? '') ?>"></div>
                    </div>
                </div>

                <!-- Títulos das Seções -->
                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="type"></i> Chamadas das Seções
                    </div>
                    <div class="config-grid-2">
                        <div class="form-group"><label class="form-label">Categorias - Texto</label><input type="text" name="settings[vitrine_categorias_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_categorias_texto'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Lançamentos - Título</label><input type="text" name="settings[vitrine_lancamentos_titulo]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_lancamentos_titulo'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Lançamentos - Descrição</label><input type="text" name="settings[vitrine_lancamentos_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_lancamentos_texto'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Mais procurados - Título</label><input type="text" name="settings[vitrine_mais_procurados_titulo]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_mais_procurados_titulo'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Mais procurados - Descrição</label><input type="text" name="settings[vitrine_mais_procurados_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_mais_procurados_texto'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Serviços - Título</label><input type="text" name="settings[vitrine_servicos_titulo]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_servicos_titulo'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Serviços - Descrição</label><input type="text" name="settings[vitrine_servicos_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_servicos_texto'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Depoimentos - Título</label><input type="text" name="settings[vitrine_depoimentos_titulo]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_depoimentos_titulo'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Depoimentos - Descrição</label><input type="text" name="settings[vitrine_depoimentos_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_depoimentos_texto'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Localização - Título</label><input type="text" name="settings[vitrine_localizacao_titulo]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_localizacao_titulo'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Localização - Descrição</label><input type="text" name="settings[vitrine_localizacao_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_localizacao_texto'] ?? '') ?>"></div>
                    </div>
                </div>

                <!-- Localização e Mapa -->
                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="map-pin"></i> Localização e Mapa
                    </div>
                    <div class="config-grid-2">
                        <div class="form-group"><label class="form-label">Descrição Interna (Onde estamos)</label><input type="text" name="settings[vitrine_localizacao_descricao]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_localizacao_descricao'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Mapa - Título</label><input type="text" name="settings[vitrine_localizacao_mapa_titulo]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_localizacao_mapa_titulo'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Mapa - Texto</label><input type="text" name="settings[vitrine_localizacao_mapa_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_localizacao_mapa_texto'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Google Maps Embed URL (opcional)</label><input type="text" name="settings[vitrine_mapa_embed_url]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_mapa_embed_url'] ?? '') ?>" placeholder="https://www.google.com/maps/embed?..."><small style="color: var(--text-muted);">Se ficar vazio, o mapa usa o endereco da empresa automaticamente.</small></div>
                        <div class="form-group config-grid-full"><label class="form-label">Faixa Final (CTA)</label><input type="text" name="settings[vitrine_cta_texto]" class="form-control" value="<?= htmlspecialchars($settings['vitrine_cta_texto'] ?? '') ?>"></div>
                    </div>
                </div>
            </section>

            <!-- Categorias (Este será tratado via outro form ou AJAX, mas para consistência deixamos aqui os cards) -->
            <section id="sect-categorias" class="config-section">
                <h3 class="brand-font" style="margin-bottom: 1.5rem;">Gestão de Categorias</h3>
                <div class="cat-grid">
                    <div>
                        <h4 style="font-size: 0.9rem; color: var(--primary);">Produtos (Loja)</h4>
                        <div class="cat-list">
                            <?php foreach($categoriasProdutos as $cat): ?>
                            <div class="cat-item">
                                <span><?= htmlspecialchars($cat['nome']) ?></span>
                                <button type="button" class="btn-delete-cat" data-confirm="Excluir esta categoria?" onclick="deleteCategoriaConfig(<?= (int) $cat['id'] ?>, this.dataset.confirm)" aria-label="Excluir categoria"><i data-lucide="trash-2" style="width: 14px;"></i></button>
                            </div>
                            <?php endforeach; ?>
                            <div style="margin-top: 1rem;">
                                <button type="button" onclick="openAddCat('produto')" class="btn" style="width: 100%; border: 1px dashed var(--primary); color: var(--primary); background: none; font-size: 0.8rem;">+ Nova Categoria de Produto</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="sect-marketplace" class="config-section">
                <h3 class="brand-font" style="margin-bottom: 0.5rem;">Mercado Livre</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 2rem;">Conecte sua conta para publicar produtos e sincronizar preco e estoque.</p>

                <?php $mlError = (string) ($_SESSION['ml_error'] ?? ($_GET['ml_error'] ?? '')); unset($_SESSION['ml_error']); ?>
                <?php if (!empty($_GET['ml_connected'])): ?>
                <div class="alert-success"><i data-lucide="check-circle" style="width:18px;"></i> Conta Mercado Livre conectada com sucesso.</div>
                <?php elseif ($mlError !== ''): ?>
                <div class="alert-success" style="background:#fef2f2;border-color:#fecaca;color:#991b1b;">
                    <i data-lucide="alert-triangle" style="width:18px;"></i>
                    Mercado Livre: <?= htmlspecialchars($mlError) ?>
                </div>
                <?php endif; ?>

                <div class="config-card" style="background: rgba(255, 241, 89, 0.12); border-color: rgba(45, 50, 119, 0.12);">
                    <div class="config-card-title" style="color: #2d3277;">
                        <i data-lucide="store"></i> Aplicativo Mercado Livre
                    </div>
                    <div class="config-grid-2">
                        <div class="form-group">
                            <label class="form-label">Client ID / App ID</label>
                            <input type="text" name="settings[mercado_livre_client_id]" class="form-control" value="<?= htmlspecialchars($settings['mercado_livre_client_id'] ?? '') ?>" placeholder="Informe o App ID">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Client Secret</label>
                            <input type="password" name="settings[mercado_livre_client_secret]" class="form-control" value="<?= htmlspecialchars($settings['mercado_livre_client_secret'] ?? '') ?>">
                        </div>
                        <div class="form-group config-grid-full">
                            <label class="form-label">Redirect URI para cadastrar no Mercado Livre</label>
                            <input type="text" class="form-control" readonly value="<?= htmlspecialchars(absolute_route_url('mercadolivre/callback')) ?>">
                        </div>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="package-check"></i> Padroes para Publicacao
                    </div>
                    <div class="config-grid-2">
                        <div class="form-group">
                            <label class="form-label">Tipo de Anuncio Padrao</label>
                            <?php $mlDefaultListing = $settings['mercado_livre_default_listing_type_id'] ?? 'gold_special'; ?>
                            <select name="settings[mercado_livre_default_listing_type_id]" class="form-control">
                                <option value="gold_special" <?= $mlDefaultListing === 'gold_special' ? 'selected' : '' ?>>Classico</option>
                                <option value="gold_pro" <?= $mlDefaultListing === 'gold_pro' ? 'selected' : '' ?>>Premium</option>
                                <option value="free" <?= $mlDefaultListing === 'free' ? 'selected' : '' ?>>Gratis</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Condicao Padrao</label>
                            <?php $mlDefaultCondition = $settings['mercado_livre_default_condition'] ?? 'new'; ?>
                            <select name="settings[mercado_livre_default_condition]" class="form-control">
                                <option value="new" <?= $mlDefaultCondition === 'new' ? 'selected' : '' ?>>Novo</option>
                                <option value="used" <?= $mlDefaultCondition === 'used' ? 'selected' : '' ?>>Usado</option>
                                <option value="not_specified" <?= $mlDefaultCondition === 'not_specified' ? 'selected' : '' ?>>Nao especificado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Envio Padrao</label>
                            <?php $mlDefaultShippingMode = $settings['mercado_livre_default_shipping_mode'] ?? 'me2'; ?>
                            <select name="settings[mercado_livre_default_shipping_mode]" class="form-control">
                                <option value="me2" <?= $mlDefaultShippingMode === 'me2' ? 'selected' : '' ?>>Mercado Envios 2</option>
                                <option value="not_specified" <?= $mlDefaultShippingMode === 'not_specified' ? 'selected' : '' ?>>Nao especificado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="plug-zap"></i> Conexao da Conta
                    </div>
                    <?php $mlConnected = !empty($settings['mercado_livre_refresh_token']) && !empty($settings['mercado_livre_user_id']); ?>
                    <p style="color: var(--text-muted); margin-bottom: 1rem;">
                        Status:
                        <strong style="color: <?= $mlConnected ? '#047857' : '#991b1b' ?>;">
                            <?= $mlConnected ? 'conectado como ' . htmlspecialchars($settings['mercado_livre_nickname'] ?: $settings['mercado_livre_user_id']) : 'desconectado' ?>
                        </strong>
                    </p>
                    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                        <a href="<?= route_url('mercadolivre/connect') ?>" class="btn btn-primary" style="text-decoration:none;">
                            <i data-lucide="link"></i> Conectar Mercado Livre
                        </a>
                        <?php if ($mlConnected): ?>
                        <button type="button" class="btn btn-secondary" style="text-decoration:none;" onclick="disconnectMercadoLivre()">
                            <i data-lucide="unlink"></i> Desconectar
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Pagamentos -->
            <section id="sect-pagamentos" class="config-section">
                <h3 class="brand-font" style="margin-bottom: 0.5rem;">Configurações de Pagamento</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 2rem;">Configure suas chaves para recebimento via Vitrine Virtual.</p>
                
                <div class="config-card" style="background: rgba(0, 158, 227, 0.03); border-color: rgba(0, 158, 227, 0.15);">
                    <div class="config-card-title" style="color: #009EE3;">
                        <img src="https://logodownload.org/wp-content/uploads/2019/06/mercado-pago-logo-1.png" style="height: 18px;"> Mercado Pago
                    </div>
                    <div class="config-grid-2">
                        <div class="form-group">
                            <label class="form-label">Public Key</label>
                            <input type="text" name="settings[mercadopago_public_key]" class="form-control" value="<?= htmlspecialchars($settings['mercadopago_public_key'] ?? '') ?>" placeholder="APP_USR-...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Access Token</label>
                            <input type="password" name="settings[mercadopago_access_token]" class="form-control" value="<?= htmlspecialchars($settings['mercadopago_access_token'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="config-card" style="background: rgba(0, 158, 227, 0.03); border-color: rgba(0, 158, 227, 0.15);">
                    <div class="config-card-title" style="color: #009EE3;">
                        <i data-lucide="smartphone"></i> Smart Point presencial
                    </div>
                    <div class="config-grid-2">
                        <div class="form-group config-grid-full">
                            <label class="form-label">Terminal ID da Point</label>
                            <input type="text" name="settings[mercadopago_point_terminal_id]" class="form-control" value="<?= htmlspecialchars($settings['mercadopago_point_terminal_id'] ?? '') ?>" placeholder="Ex: NEWLAND_N950__N950...">
                            <small style="color: var(--text-muted);">Use o identificador retornado em Terminals no Mercado Pago Developers.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Forma padrao</label>
                            <?php $pointDefaultType = $settings['mercadopago_point_default_payment_type'] ?? 'credit_card'; ?>
                            <select name="settings[mercadopago_point_default_payment_type]" class="form-control">
                                <option value="credit_card" <?= $pointDefaultType === 'credit_card' ? 'selected' : '' ?>>Credito</option>
                                <option value="debit_card" <?= $pointDefaultType === 'debit_card' ? 'selected' : '' ?>>Debito</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Parcelas padrao</label>
                            <?php $pointDefaultInstallments = max(1, min(12, (int) ($settings['mercadopago_point_default_installments'] ?? 1))); ?>
                            <select name="settings[mercadopago_point_default_installments]" class="form-control">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= $i === $pointDefaultInstallments ? 'selected' : '' ?>><?= $i ?>x</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Juros do parcelamento</label>
                            <?php $pointInstallmentsCost = $settings['mercadopago_point_installments_cost'] ?? 'seller'; ?>
                            <select name="settings[mercadopago_point_installments_cost]" class="form-control">
                                <option value="seller" <?= $pointInstallmentsCost === 'seller' ? 'selected' : '' ?>>Loja assume</option>
                                <option value="buyer" <?= $pointInstallmentsCost === 'buyer' ? 'selected' : '' ?>>Cliente assume</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Impressao na maquininha</label>
                            <input type="text" name="settings[mercadopago_point_print_on_terminal]" class="form-control" value="no_ticket" readonly>
                            <small style="color: var(--text-muted);">Valor usado pela API Orders para nao imprimir ticket no terminal.</small>
                        </div>
                        <div class="form-group config-grid-full">
                            <label class="form-label">URL de webhook para Orders</label>
                            <input type="text" class="form-control" readonly value="<?= htmlspecialchars(absolute_route_url('mercadopago/pointWebhook')) ?>">
                            <small style="color: var(--text-muted);">No Mercado Pago, ative Webhooks de producao para o topico Order e cole esta URL HTTPS.</small>
                        </div>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="qr-code"></i> Pagamento Direto
                    </div>
                    <div class="form-group">
                        <label class="form-label">Chave PIX (Para exibição no PDV e Checkout)</label>
                        <input type="text" name="settings[chave_pix]" class="form-control" value="<?= htmlspecialchars($settings['chave_pix'] ?? '') ?>" placeholder="CPF, CNPJ, E-mail ou Aleatória">
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="badge-percent"></i> Taxas da Maquininha
                    </div>
                    <div class="config-grid-2">
                        <div class="form-group">
                            <label class="form-label">Point Debito / QR / Saldo Mercado Pago (%)</label>
                            <input type="text" name="settings[taxa_point_debito_qr_saldo]" class="form-control" value="<?= htmlspecialchars($settings['taxa_point_debito_qr_saldo'] ?? '1,99') ?>" placeholder="1,99">
                            <small style="color: var(--text-muted);">Dinheiro na hora.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Point Credito na hora (%)</label>
                            <input type="text" name="settings[taxa_point_credito_hora]" class="form-control" value="<?= htmlspecialchars($settings['taxa_point_credito_hora'] ?? '4,74') ?>" placeholder="4,74">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Point Credito 14 dias (%)</label>
                            <input type="text" name="settings[taxa_point_credito_14d]" class="form-control" value="<?= htmlspecialchars($settings['taxa_point_credito_14d'] ?? '3,79') ?>" placeholder="3,79">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Point Credito 30 dias (%)</label>
                            <input type="text" name="settings[taxa_point_credito_30d]" class="form-control" value="<?= htmlspecialchars($settings['taxa_point_credito_30d'] ?? '3,03') ?>" placeholder="3,03">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Taxa Cartao de Debito (%)</label>
                            <input type="text" name="settings[taxa_cartao_debito]" class="form-control" value="<?= htmlspecialchars($settings['taxa_cartao_debito'] ?? '') ?>" placeholder="Ex: 1,99">
                            <small style="color: var(--text-muted);">Fallback antigo, usado se a taxa Point estiver vazia.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Taxa Cartao de Credito (%)</label>
                            <input type="text" name="settings[taxa_cartao_credito]" class="form-control" value="<?= htmlspecialchars($settings['taxa_cartao_credito'] ?? '') ?>" placeholder="Ex: 3,49">
                            <small style="color: var(--text-muted);">Fallback antigo, usado se a taxa Point estiver vazia.</small>
                        </div>
                    </div>
                    <div class="config-grid-2" style="margin-top:1rem;">
                        <?php $parcelamentoDefaults = [2 => '4,59', 3 => '0,57', 12 => '17,28']; ?>
                        <?php for ($parcelas = 2; $parcelas <= 12; $parcelas++): ?>
                        <?php $key = 'taxa_point_parcelamento_' . $parcelas . 'x'; ?>
                        <?php $defaultTaxa = $parcelamentoDefaults[$parcelas] ?? ''; ?>
                        <div class="form-group">
                            <label class="form-label">Acrescimo parcelamento <?= $parcelas ?>x (%)</label>
                            <input type="text" name="settings[<?= e($key) ?>]" class="form-control" value="<?= htmlspecialchars($settings[$key] ?? $defaultTaxa) ?>" placeholder="<?= $defaultTaxa !== '' ? e($defaultTaxa) : 'Informe a taxa' ?>">
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </section>

            <section id="sect-backup" class="config-section">
                <h3 class="brand-font" style="margin-bottom: 0.5rem;">Backup do Sistema</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 2rem;">Gere uma copia do banco de dados para guardar fora da hospedagem.</p>

                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="database-backup"></i> Banco de Dados
                    </div>
                    <div class="diagnostic-grid" style="margin-bottom:1.4rem;">
                        <div class="diagnostic-item">
                            <span>Ultimo backup</span>
                            <strong><?= !empty($backupInfo['last_at']) ? date('d/m/Y H:i', strtotime((string) $backupInfo['last_at'])) : 'nenhum registrado' ?></strong>
                        </div>
                        <div class="diagnostic-item">
                            <span>Arquivo</span>
                            <strong><?= htmlspecialchars((string) ($backupInfo['last_file'] ?: 'nao gerado')) ?></strong>
                        </div>
                        <div class="diagnostic-item">
                            <span>Tamanho</span>
                            <strong><?= htmlspecialchars($formatBackupSize($backupInfo['last_size'] ?? 0)) ?></strong>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); margin:0 0 1rem; line-height:1.55;">
                        O arquivo gerado contem tabelas e dados do banco atual. Guarde em local seguro, pois ele pode conter informacoes de clientes, financeiro e configuracoes.
                    </p>
                    <button type="button" class="btn btn-primary" onclick="downloadDatabaseBackup()">
                        <i data-lucide="download"></i> Baixar backup do banco
                    </button>
                </div>
            </section>

            <section id="sect-auditoria" class="config-section">
                <h3 class="brand-font" style="margin-bottom: 0.5rem;">Auditoria de Acoes</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 2rem;">Ultimas acoes importantes registradas no sistema.</p>

                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="history"></i> Eventos recentes
                    </div>
                    <?php if (empty($auditLogs)): ?>
                        <p style="color: var(--text-muted); margin: 0;">Nenhum evento de auditoria registrado ainda.</p>
                    <?php else: ?>
                        <div style="overflow:auto;border:1px solid var(--border);border-radius:12px;background:#fff;">
                            <table style="width:100%;border-collapse:collapse;min-width:820px;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left;padding:.75rem;border-bottom:1px solid var(--border);font-size:.72rem;text-transform:uppercase;color:var(--text-muted);">Data</th>
                                        <th style="text-align:left;padding:.75rem;border-bottom:1px solid var(--border);font-size:.72rem;text-transform:uppercase;color:var(--text-muted);">Usuario</th>
                                        <th style="text-align:left;padding:.75rem;border-bottom:1px solid var(--border);font-size:.72rem;text-transform:uppercase;color:var(--text-muted);">Acao</th>
                                        <th style="text-align:left;padding:.75rem;border-bottom:1px solid var(--border);font-size:.72rem;text-transform:uppercase;color:var(--text-muted);">Entidade</th>
                                        <th style="text-align:left;padding:.75rem;border-bottom:1px solid var(--border);font-size:.72rem;text-transform:uppercase;color:var(--text-muted);">Descricao</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($auditLogs as $log): ?>
                                        <tr>
                                            <td style="padding:.75rem;border-bottom:1px solid #eef2f7;white-space:nowrap;"><?= !empty($log['created_at']) ? date('d/m/Y H:i', strtotime((string) $log['created_at'])) : '-' ?></td>
                                            <td style="padding:.75rem;border-bottom:1px solid #eef2f7;"><?= htmlspecialchars((string) ($log['usuario_nome'] ?: 'Sistema')) ?></td>
                                            <td style="padding:.75rem;border-bottom:1px solid #eef2f7;"><span class="badge badge-gray"><?= htmlspecialchars((string) $log['acao']) ?></span></td>
                                            <td style="padding:.75rem;border-bottom:1px solid #eef2f7;"><?= htmlspecialchars((string) $log['entidade']) ?><?= !empty($log['entidade_id']) ? ' #' . (int) $log['entidade_id'] : '' ?></td>
                                            <td style="padding:.75rem;border-bottom:1px solid #eef2f7;"><?= htmlspecialchars((string) ($log['descricao'] ?? '')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section id="sect-diagnostico" class="config-section">
                <h3 class="brand-font" style="margin-bottom: 0.5rem;">Diagnostico do Sistema</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 2rem;">Visao rapida do ambiente de producao, uploads e integracoes.</p>

                <div class="config-card">
                    <div class="config-card-title">
                        <i data-lucide="activity"></i> Ambiente
                    </div>
                    <?php if (empty($diagnostico)): ?>
                    <p style="color: var(--text-muted); margin: 0;">Nao foi possivel montar o diagnostico agora.</p>
                    <?php else: ?>
                    <div class="diagnostic-grid">
                        <?php foreach (($diagnostico ?? []) as $diagKey => $diagValue): ?>
                        <div class="diagnostic-item">
                            <span><?= htmlspecialchars($diagKey) ?></span>
                            <strong><?= htmlspecialchars((string) ($diagValue === '' ? 'nao definido' : $diagValue)) ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <div id="save-bar" style="margin-top: 3rem; border-top: 1px solid var(--border); padding-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                    <i data-lucide="save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </main>
</div>

<!-- Modal para Nova Categoria -->
<div id="modal-cat" class="modal-overlay">
    <div class="modal-content">
        <h3 class="brand-font" id="modal-title">Nova Categoria</h3>
        <form action="<?= e(route_url('config/add_categoria')) ?>" method="POST" style="margin-top: 1.5rem;">
            <?= csrf_field() ?>
            <input type="hidden" name="tipo" id="cat-tipo">
            <div class="form-group">
                <label class="form-label">Nome da Categoria</label>
                <input type="text" name="nome" class="form-control" required autofocus>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end;">
                <button type="button" onclick="closeModalCat()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Adicionar</button>
            </div>
        </form>
    </div>
</div>

<script>
function showTab(tab) {
    document.querySelectorAll('.config-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.config-nav-link').forEach(l => l.classList.remove('active'));
    
    document.getElementById('sect-' + tab).classList.add('active');
    document.getElementById('tab-link-' + tab).classList.add('active');
    
    // Esconde o botão de salvar se estiver na aba de categorias (que tem forms próprios)
    document.getElementById('save-bar').style.display = (tab === 'categorias' || tab === 'backup' || tab === 'auditoria' || tab === 'diagnostico') ? 'none' : 'flex';
}

function openAddCat(tipo) {
    document.getElementById('cat-tipo').value = tipo;
    document.getElementById('modal-title').innerText = 'Nova Categoria de ' + (tipo === 'peca' ? 'Estoque' : 'Produto');
    document.getElementById('modal-cat').style.display = 'flex';
}

function closeModalCat() {
    document.getElementById('modal-cat').style.display = 'none';
}

function postConfigAction(action, fields = {}) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = action;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    if (csrf) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_csrf_token';
        csrfInput.value = csrf;
        form.appendChild(csrfInput);
    }

    Object.entries(fields).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

function deleteCategoriaConfig(id, message) {
    const action = '<?= route_url('config/delete_categoria') ?>';
    const submit = () => postConfigAction(action, { id });
    if (typeof abrirConfirmSistema === 'function') {
        abrirConfirmSistema(message || 'Excluir esta categoria?', submit);
    } else if (window.confirm(message || 'Excluir esta categoria?')) {
        submit();
    }
}

function disconnectMercadoLivre() {
    const message = 'Desconectar a conta do Mercado Livre?';
    const submit = () => postConfigAction('<?= route_url('mercadolivre/disconnect') ?>');
    if (typeof abrirConfirmSistema === 'function') {
        abrirConfirmSistema(message, submit);
    } else if (window.confirm(message)) {
        submit();
    }
}

function downloadDatabaseBackup() {
    const message = 'Gerar e baixar um backup do banco de dados agora?';
    const submit = () => postConfigAction('<?= route_url('config/backup') ?>');
    if (typeof abrirConfirmSistema === 'function') {
        abrirConfirmSistema(message, submit);
    } else if (window.confirm(message)) {
        submit();
    }
}

// Checar aba pela URL
const urlParams = new URLSearchParams(window.location.search);
const tab = urlParams.get('tab');
if (tab) showTab(tab);
</script>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
