</div><!-- .public-container -->

<?php
$footerCompany = trim((string) ($settings['nome_empresa'] ?? '')) ?: 'Conectados';
$footerWhats = preg_replace('/\D+/', '', (string) ($settings['whatsapp'] ?? ''));
$footerWhats = $footerWhats !== '' ? $footerWhats : '5511999999999';
?>
<footer id="footer-contact" style="margin-top: 5rem; padding: 4rem 2rem; background: var(--primary); color: white; font-size: 0.9rem; width: 100%;">
    <div class="footer-store-grid">
        <div style="text-align: left;">
            <img src="<?= app_url('assets/img/logo.png') ?>" alt="<?= htmlspecialchars($footerCompany) ?>" style="height: 40px; filter: brightness(1.42) saturate(1.12) drop-shadow(0 0 5px rgba(255,255,255,.82)) drop-shadow(0 0 14px rgba(96,190,255,.72)); margin-bottom: 1.5rem;">
            <p style="opacity: 0.92; max-width: 620px; line-height: 1.7; margin: 0;">Bem-vindo a loja virtual da <?= htmlspecialchars($footerCompany) ?>. Encontre produtos, acessorios e atendimento especializado em um so lugar.</p>
            <p style="margin-top: 1rem; opacity: 0.78; line-height: 1.7;">
                <?= htmlspecialchars($settings['endereco'] ?? '') ?>
                <?php if (!empty($settings['website'])): ?> | <?= htmlspecialchars($settings['website']) ?><?php endif; ?>
            </p>
            <p style="opacity: 0.9; margin-top: 1.25rem;">&copy; <?= date('Y') ?> <?= htmlspecialchars($footerCompany) ?> - Todos os direitos reservados.</p>
        </div>
        <div class="footer-link-panel">
            <nav class="footer-link-group" aria-label="Atalhos da loja">
                <h3>Loja</h3>
                <a class="footer-store-link" href="<?= route_url('vitrine/catalogo') ?>"><i data-lucide="shopping-bag"></i> Fazer compras</a>
                <a class="footer-store-link" href="mailto:<?= htmlspecialchars($settings['email_negocio'] ?? '') ?>"><i data-lucide="mail"></i> Contato</a>
            </nav>
            <nav class="footer-link-group" aria-label="Redes sociais">
                <h3>Redes sociais</h3>
                <a class="footer-store-link is-brand" href="https://wa.me/<?= htmlspecialchars($footerWhats) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
                    <svg viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-156.9zM223.9 438.7c-33.1 0-65.6-8.9-94-25.7l-6.7-4-69.8 18.3 18.6-68.1-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 11-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
                    WhatsApp
                </a>
                <?php if (!empty($settings['facebook'])): ?>
                <a class="footer-store-link is-brand" href="<?= htmlspecialchars($settings['facebook']) ?>" target="_blank" rel="noopener" aria-label="Facebook">
                    <svg viewBox="0 0 320 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06H297V6.26S260.43 0 225.36 0C152.14 0 104.11 44.38 104.11 124.72v70.62H22.89V288h81.22v224h100.34V288z"/></svg>
                    Facebook
                </a>
                <?php endif; ?>
                <?php if (!empty($settings['instagram'])): ?>
                <a class="footer-store-link is-brand" href="<?= htmlspecialchars($settings['instagram']) ?>" target="_blank" rel="noopener" aria-label="Instagram">
                    <svg viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9S160.5 370.8 224.1 370.8 339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9S352.4 35.1 316.5 33.4c-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1S3.2 127.5 1.5 163.4c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.5 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2s34.5-58 36.2-93.9c2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>
                    Instagram
                </a>
                <?php endif; ?>
                <?php if (!empty($settings['tiktok'])): ?>
                <a class="footer-store-link is-brand" href="<?= htmlspecialchars($settings['tiktok']) ?>" target="_blank" rel="noopener" aria-label="TikTok">
                    <svg viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M448 209.9c-44.9.1-87-13.9-122-37.9v178.7c0 89.9-72.9 162.8-162.8 162.8S.4 440.6.4 350.7s72.9-162.8 162.8-162.8c13.8 0 27.2 1.7 40 4.9v89.9c-12.1-6.7-26-10.5-40.8-10.5-46.5 0-84.2 37.7-84.2 84.2s37.7 84.2 84.2 84.2 84.2-37.7 84.2-84.2V0h79.5c7.5 71.2 64.2 127.8 121.9 134.4v75.5z"/></svg>
                    TikTok
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</footer>
<style>
    .footer-store-grid {
        max-width: 1180px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(300px, .85fr);
        gap: 2.5rem;
        align-items: start;
    }
    .footer-link-panel {
        display: grid;
        gap: 1rem;
    }
    .footer-link-group {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
        justify-content: flex-start;
        padding: 1rem;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 18px;
        background: rgba(255,255,255,.06);
    }
    .footer-link-group h3 {
        width: 100%;
        margin: 0 0 .15rem;
        font-family: 'Outfit', sans-serif;
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: rgba(255,255,255,.72);
    }
    .footer-store-link {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .65rem .85rem;
        border-radius: 999px;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.16);
        color: #fff;
        text-decoration: none;
        font-weight: 800;
        white-space: nowrap;
    }
    .footer-store-link i,
    .footer-store-link svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
    }
    .footer-store-link.is-brand svg {
        width: 18px;
        height: 18px;
    }
    .footer-store-link:hover {
        background: rgba(255,255,255,.16);
        transform: translateY(-1px);
    }
    @media (max-width: 760px) {
        .footer-store-grid {
            grid-template-columns: 1fr !important;
        }
        .footer-link-group {
            padding: .85rem;
        }
        #footer-contact {
            padding: 3rem 1rem !important;
        }
    }
</style>

<button id="pwa-install-button" type="button" style="position:fixed;left:50%;bottom:18px;transform:translateX(-50%);z-index:2300;display:none;align-items:center;gap:8px;border:0;border-radius:999px;background:var(--primary);color:#fff;padding:13px 18px;font-weight:800;box-shadow:0 18px 36px -20px rgba(0,0,0,.65);font-family:inherit;">
    <i data-lucide="download" style="width:18px;height:18px;"></i>
    Instalar app
</button>

<script>
    let pwaInstallPrompt = null;
    const pwaInstallButton = document.getElementById('pwa-install-button');
    const isStandalonePwa = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const isMobilePwaCandidate = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || '');
    const isIosPwa = /iPhone|iPad|iPod/i.test(navigator.userAgent || '') && !window.MSStream;

    function showPwaInstallButton() {
        if (pwaInstallButton && !isStandalonePwa && isMobilePwaCandidate) {
            pwaInstallButton.style.display = 'inline-flex';
            lucide.createIcons();
        }
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        pwaInstallPrompt = event;
        showPwaInstallButton();
    });

    window.addEventListener('appinstalled', () => {
        pwaInstallPrompt = null;
        if (pwaInstallButton) {
            pwaInstallButton.style.display = 'none';
        }
    });

    pwaInstallButton?.addEventListener('click', async () => {
        if (pwaInstallPrompt) {
            pwaInstallPrompt.prompt();
            await pwaInstallPrompt.userChoice.catch(() => null);
            pwaInstallPrompt = null;
            pwaInstallButton.style.display = 'none';
            return;
        }

        if (isIosPwa) {
            abrirAvisoVitrine('Instalar app', 'No Safari, toque em Compartilhar e escolha Adicionar a Tela de Inicio.', 'download');
        }
    });

    if (isIosPwa && !isStandalonePwa) {
        window.addEventListener('load', () => setTimeout(showPwaInstallButton, 1200));
    }

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            const swUrl = '<?= app_url('sw.js') ?>?v=20260702-banner';
            navigator.serviceWorker.getRegistrations()
                .then((registrations) => Promise.all(registrations.map((registration) => {
                    const scriptUrl = registration.active?.scriptURL || registration.waiting?.scriptURL || registration.installing?.scriptURL || '';
                    return scriptUrl.includes('/public/sw.js') ? registration.unregister() : Promise.resolve();
                })))
                .then(() => navigator.serviceWorker.register(swUrl))
                .then((registration) => registration.update())
                .catch((error) => console.warn('Service worker nao registrado:', error));
        });
    }

    const offerStrip = document.getElementById('store-offer-strip');
    offerStrip?.addEventListener('click', () => {
        offerStrip.classList.toggle('is-paused');
    });
    offerStrip?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            offerStrip.classList.toggle('is-paused');
        }
    });

    lucide.createIcons();
</script>
</body>
</html>
