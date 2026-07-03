    </main>
</div>

<div id="system-confirm-overlay" class="system-confirm-overlay" aria-hidden="true" onclick="fecharConfirmSistema(event)">
    <div class="system-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="system-confirm-title" aria-describedby="system-confirm-message" onclick="event.stopPropagation()">
        <div class="system-confirm-head">
            <div class="system-confirm-icon">
                <i data-lucide="trash-2"></i>
            </div>
            <div>
                <h3 id="system-confirm-title">Confirmar ação</h3>
                <p id="system-confirm-message">Deseja continuar?</p>
            </div>
        </div>
        <div class="system-confirm-actions">
            <button type="button" class="btn system-confirm-cancel" onclick="fecharConfirmSistema()">Cancelar</button>
            <button type="button" class="btn system-confirm-danger" id="system-confirm-action">Confirmar</button>
        </div>
    </div>
</div>

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const swUrl = '<?= app_url('sw.js') ?>?v=20260703-blue-dock';
        navigator.serviceWorker.getRegistrations()
            .then((registrations) => Promise.all(registrations.map((registration) => {
                const scriptUrl = registration.active?.scriptURL || registration.waiting?.scriptURL || registration.installing?.scriptURL || '';
                return scriptUrl.includes('/public/sw.js') ? registration.unregister() : Promise.resolve();
            })))
            .then(() => navigator.serviceWorker.register(swUrl))
            .catch((error) => console.warn('Service worker nao registrado:', error));
    });
}

lucide.createIcons();

let systemConfirmCallback = null;

document.querySelectorAll('.system-nav-menu, .system-dock-user').forEach((menu) => {
    menu.addEventListener('toggle', () => {
        if (!menu.open) {
            return;
        }

        document.querySelectorAll('.system-nav-menu[open], .system-dock-user[open]').forEach((openMenu) => {
            if (openMenu !== menu) {
                openMenu.open = false;
            }
        });
    });
});

document.addEventListener('click', (event) => {
    if (event.target.closest('.system-nav-menu, .system-dock-user')) {
        return;
    }

    document.querySelectorAll('.system-nav-menu[open], .system-dock-user[open]').forEach((menu) => {
        menu.open = false;
    });
});

document.querySelectorAll('.system-flash').forEach((flash) => {
    window.setTimeout(() => {
        flash.style.transition = 'opacity .25s ease, transform .25s ease, margin .25s ease, padding .25s ease';
        flash.style.opacity = '0';
        flash.style.transform = 'translateY(-6px)';
        window.setTimeout(() => flash.remove(), 260);
    }, 3500);
});

function ensureSystemCsrf(form) {
    if (!form || !form.matches || !form.matches('form')) {
        return;
    }

    const method = (form.getAttribute('method') || 'GET').toUpperCase();
    if (method !== 'POST' || form.dataset.csrf === 'off') {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    if (!token) {
        return;
    }

    let input = form.querySelector('input[name="_csrf_token"]');
    if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_csrf_token';
        form.prepend(input);
    }
    input.value = token;
}

function abrirConfirmSistema(message, onConfirm) {
    const overlay = document.getElementById('system-confirm-overlay');
    const messageEl = document.getElementById('system-confirm-message');
    const actionBtn = document.getElementById('system-confirm-action');

    systemConfirmCallback = onConfirm;
    messageEl.textContent = message || 'Deseja continuar?';
    overlay.classList.add('active');
    overlay.setAttribute('aria-hidden', 'false');
    actionBtn.focus();
}

function fecharConfirmSistema(event) {
    if (event && event.target && event.target.id !== 'system-confirm-overlay') {
        return;
    }

    const overlay = document.getElementById('system-confirm-overlay');
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden', 'true');
    systemConfirmCallback = null;
}

function executarConfirmSistema() {
    const callback = systemConfirmCallback;
    fecharConfirmSistema();

    if (typeof callback === 'function') {
        callback();
    }
}

document.getElementById('system-confirm-action').addEventListener('click', executarConfirmSistema);
document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-confirm]');

    if (!link) {
        return;
    }

    event.preventDefault();
    abrirConfirmSistema(link.dataset.confirm, () => {
        window.location.href = link.href;
    });
});
document.addEventListener('submit', (event) => {
    ensureSystemCsrf(event.target);
}, true);
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!form.matches('form[data-confirm]')) {
        return;
    }

    event.preventDefault();
    abrirConfirmSistema(form.dataset.confirm, () => {
        form.submit();
    });
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        fecharConfirmSistema();
        document.querySelectorAll('.system-nav-menu[open], .system-dock-user[open]').forEach((menu) => {
            menu.open = false;
        });
    }
});

try { localStorage.removeItem('theme'); } catch (error) {}
</script>
</body>
</html>
