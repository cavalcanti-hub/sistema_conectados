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

<!-- Command Palette (Ctrl + K) -->
<div id="commandPaletteModal" class="cmd-palette-overlay" onclick="handleCmdBackdrop(event)" aria-hidden="true">
    <div class="cmd-palette-dialog" onclick="event.stopPropagation()">
        <div class="cmd-palette-search-bar">
            <i data-lucide="search" class="cmd-search-icon"></i>
            <input type="text" id="cmdPaletteInput" class="cmd-palette-input" placeholder="Buscar OS, cliente, aparelho, produto ou atalho... (Ctrl + K)" autocomplete="off" spellcheck="false">
            <kbd class="cmd-esc-badge" onclick="closeCommandPalette()">ESC</kbd>
        </div>
        <div id="cmdPaletteResults" class="cmd-palette-results"></div>
        <div class="cmd-palette-footer">
            <span><kbd>↑</kbd> <kbd>↓</kbd> navegar</span>
            <span><kbd>↵</kbd> selecionar</span>
            <span><kbd>ESC</kbd> fechar</span>
        </div>
    </div>
</div>

<!-- Container Global de Notificações Toast -->
<div id="toastContainer" class="toast-container" aria-live="polite"></div>

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('<?= app_url('sw.js') ?>?v=20260704-pwa-install-fix')
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

function liberarMaquininhaMP() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Liberar Maquininha',
            text: 'Isso vai forçar a maquininha a sair do Modo PDV para vendas avulsas. Lembre-se de clicar em "Atualizar" na tela dela. Confirmar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, liberar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                fetch('<?= route_url('mercadopago/liberarPoint') ?>', {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''}
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.ok) {
                            Swal.fire('Sucesso!', data.message, 'success');
                        } else {
                            Swal.fire('Erro', data.message, 'error');
                        }
                    })
                    .catch(err => Swal.fire('Erro', 'Ocorreu um erro na requisição', 'error'));
            }
        });
    } else {
        if (confirm('Isso vai forçar a maquininha a sair do Modo PDV para vendas avulsas. Lembre-se de clicar em "Atualizar" na tela dela. Confirmar?')) {
            fetch('<?= route_url('mercadopago/liberarPoint') ?>', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''}
            })
                .then(r => r.json())
                .then(data => alert(data.message))
                .catch(err => alert('Ocorreu um erro na requisição'));
        }
    }
}

/* ================================================
   COMMAND PALETTE & TOAST SYSTEM
   ================================================ */
let cmdSelectedIndex = -1;
let cmdSearchTimeout = null;

function openCommandPalette() {
    const modal = document.getElementById('commandPaletteModal');
    const input = document.getElementById('cmdPaletteInput');
    if (!modal || !input) return;
    modal.classList.add('active');
    input.value = '';
    cmdSelectedIndex = -1;
    performCmdSearch('');
    setTimeout(() => input.focus(), 50);
    document.body.style.overflow = 'hidden';
}

function closeCommandPalette() {
    const modal = document.getElementById('commandPaletteModal');
    if (modal) modal.classList.remove('active');
    document.body.style.overflow = '';
}

function handleCmdBackdrop(e) {
    if (e.target && e.target.id === 'commandPaletteModal') {
        closeCommandPalette();
    }
}

// Global shortcut Ctrl+K / Cmd+K
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        const modal = document.getElementById('commandPaletteModal');
        if (modal && modal.classList.contains('active')) {
            closeCommandPalette();
        } else {
            openCommandPalette();
        }
    }
});

const cmdInput = document.getElementById('cmdPaletteInput');
if (cmdInput) {
    cmdInput.addEventListener('input', (e) => {
        clearTimeout(cmdSearchTimeout);
        cmdSearchTimeout = setTimeout(() => {
            performCmdSearch(e.target.value.trim());
        }, 150);
    });

    cmdInput.addEventListener('keydown', (e) => {
        const items = document.querySelectorAll('.cmd-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            cmdSelectedIndex = (cmdSelectedIndex + 1) % items.length;
            updateCmdSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            cmdSelectedIndex = (cmdSelectedIndex - 1 + items.length) % items.length;
            updateCmdSelection(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (cmdSelectedIndex >= 0 && items[cmdSelectedIndex]) {
                items[cmdSelectedIndex].click();
            } else if (items[0]) {
                items[0].click();
            }
        }
    });
}

function updateCmdSelection(items) {
    items.forEach((item, idx) => {
        if (idx === cmdSelectedIndex) {
            item.classList.add('selected');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('selected');
        }
    });
}

function performCmdSearch(query) {
    const resultsContainer = document.getElementById('cmdPaletteResults');
    if (!resultsContainer) return;

    fetch('<?= route_url('search/global') ?>?q=' + encodeURIComponent(query))
        .then(r => r.json())
        .then(data => {
            renderCmdResults(data.results || {});
        })
        .catch(() => {
            resultsContainer.innerHTML = '<div class="cmd-empty">Erro ao pesquisar. Tente novamente.</div>';
        });
}

function renderCmdResults(groups) {
    const container = document.getElementById('cmdPaletteResults');
    if (!container) return;

    const sections = [
        { key: 'acoes', title: 'Ações Rápidas' },
        { key: 'os', title: 'Ordens de Serviço' },
        { key: 'clientes', title: 'Clientes' },
        { key: 'produtos', title: 'Produtos e Peças' }
    ];

    let hasAny = false;
    let html = '';

    sections.forEach(sec => {
        const list = groups[sec.key] || [];
        if (list.length > 0) {
            hasAny = true;
            html += `<div class="cmd-group-title">${sec.title}</div>`;
            list.forEach(item => {
                html += `
                    <a href="${item.url}" class="cmd-item" onclick="closeCommandPalette()">
                        <div class="cmd-item-icon"><i data-lucide="${item.icon || 'file-text'}"></i></div>
                        <div class="cmd-item-info">
                            <div class="cmd-item-title">${escapeHtml(item.title)}</div>
                            ${item.subtitle ? `<div class="cmd-item-subtitle">${escapeHtml(item.subtitle)}</div>` : ''}
                        </div>
                        ${item.badge ? `<span class="cmd-item-badge">${escapeHtml(item.badge)}</span>` : ''}
                    </a>
                `;
            });
        }
    });

    if (!hasAny) {
        html = `
            <div class="cmd-empty">
                <i data-lucide="search-x" style="width:28px;height:28px;margin-bottom:6px;opacity:0.6;"></i>
                <div>Nenhum resultado encontrado</div>
            </div>
        `;
    }

    container.innerHTML = html;
    cmdSelectedIndex = -1;
    if (window.lucide) lucide.createIcons();
}

// TOAST NOTIFICATION SYSTEM
window.showToast = function(message, type = 'success', duration = 3500) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast-item toast-${type}`;
    const iconName = type === 'success' ? 'check-circle-2' : (type === 'error' ? 'alert-circle' : (type === 'warning' ? 'alert-triangle' : 'info'));

    toast.innerHTML = `
        <div class="toast-icon"><i data-lucide="${iconName}"></i></div>
        <div class="toast-message">${escapeHtml(message)}</div>
        <button type="button" class="toast-close" onclick="this.parentElement.remove()">&times;</button>
        <div class="toast-progress" style="animation-duration:${duration}ms;"></div>
    `;

    container.appendChild(toast);
    if (window.lucide) lucide.createIcons();

    setTimeout(() => {
        toast.classList.add('toast-fade-out');
        setTimeout(() => toast.remove(), 250);
    }, duration);
};

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
<?php foreach (($extraScripts ?? []) as $scriptSrc): ?>
<script src="<?= e($scriptSrc) ?>"></script>
<?php endforeach; ?>
</body>
</html>
