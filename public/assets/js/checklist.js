(function () {
    'use strict';

    const PHOTO_CATEGORIES = ['Frente', 'Traseira', 'IMEI', 'Tela', 'Defeito', 'Acessorios'];
    const PHYSICAL_BAD_STATES = ['trincado', 'quebrado', 'amassado'];
    const PHYSICAL_WARN_STATES = ['arranhado', 'outro'];

    let root = null;
    let saveTimer = null;
    let activeZone = null;
    let signatureDirty = false;
    let signatureDrawing = false;
    let signatureContext = null;

    const state = {
        startedAt: Date.now(),
        physical: {},
        tests: {},
        components: {},
        accessories: [],
        photos: [],
        notes: '',
        signature: '',
        lastSavedAt: null
    };

    function bySelector(selector, scope) {
        return (scope || root || document).querySelector(selector);
    }

    function all(selector, scope) {
        return Array.from((scope || root || document).querySelectorAll(selector));
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);
    }

    function scheduleSave() {
        window.clearTimeout(saveTimer);
        saveTimer = window.setTimeout(saveDraft, 380);
    }

    function stateClassForPhysical(value) {
        if (value === 'perfeito') {
            return 'state-good';
        }

        if (PHYSICAL_WARN_STATES.includes(value)) {
            return 'state-warn';
        }

        if (PHYSICAL_BAD_STATES.includes(value)) {
            return 'state-bad';
        }

        return '';
    }

    function setZoneVisual(zone, value) {
        zone.classList.remove('state-good', 'state-warn', 'state-bad');
        const stateClass = stateClassForPhysical(value);
        if (stateClass) {
            zone.classList.add(stateClass);
        }
    }

    function activeSection(sectionId) {
        all('[data-section]').forEach((section) => {
            section.classList.toggle('active', section.dataset.section === sectionId);
        });

        all('[data-section-target]').forEach((button) => {
            button.classList.toggle('active', button.dataset.sectionTarget === sectionId);
        });
    }

    function serializeForPayload() {
        return {
            started_at: new Date(state.startedAt).toISOString(),
            physical: state.physical,
            tests: state.tests,
            components: state.components,
            accessories: state.accessories,
            photos: state.photos.map((photo) => ({
                id: photo.id,
                name: photo.name,
                category: photo.category
            })),
            notes: state.notes,
            signature_saved: Boolean(state.signature),
            progress: getProgress(),
            last_saved_at: state.lastSavedAt ? new Date(state.lastSavedAt).toISOString() : null
        };
    }

    function updatePayloadField() {
        const field = bySelector('[data-payload-field]');
        if (field) {
            field.value = JSON.stringify(serializeForPayload(), null, 2);
        }
    }

    function getProgress() {
        const zonesTotal = all('.inspection-zone').length;
        const zonesDone = Object.values(state.physical).filter((item) => item && item.state).length;

        const testsTotal = all('[data-test-row]').length;
        const testsDone = Object.values(state.tests).filter((value) => value && value !== 'untested').length;

        const componentsTotal = all('[data-component-card]').length;
        const componentsDone = Object.values(state.components).filter((item) => item && item.state).length;

        const extraTotal = 4;
        let extraDone = 0;
        if (state.accessories.length > 0) extraDone += 1;
        if (state.photos.length > 0) extraDone += 1;
        if (state.notes.trim() !== '') extraDone += 1;
        if (state.signature) extraDone += 1;

        const total = zonesTotal + testsTotal + componentsTotal + extraTotal;
        const done = zonesDone + testsDone + componentsDone + extraDone;

        return total > 0 ? Math.round((done / total) * 100) : 0;
    }

    function updateProgress() {
        const progress = getProgress();
        const label = bySelector('[data-progress-label]');
        const ring = bySelector('[data-progress-ring]');

        if (label) {
            label.textContent = `${progress}%`;
        }

        if (ring) {
            ring.style.strokeDashoffset = String(113 - ((113 * progress) / 100));
        }

        updatePayloadField();
    }

    function updateElapsedTime() {
        const target = bySelector('[data-elapsed-time]');
        if (!target) {
            return;
        }

        const seconds = Math.max(0, Math.floor((Date.now() - state.startedAt) / 1000));
        const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
        const rest = String(seconds % 60).padStart(2, '0');
        target.textContent = `${minutes}:${rest}`;
    }

    function updatePhotoCount() {
        const target = bySelector('[data-photo-count]');
        if (target) {
            target.textContent = String(state.photos.length);
        }
    }

    function openInspectionPanel(zone) {
        activeZone = zone;
        all('.inspection-zone').forEach((item) => item.classList.toggle('selected', item === zone));

        const zoneId = zone.dataset.zone;
        const current = state.physical[zoneId] || {};
        const panel = bySelector('[data-inspection-panel]');
        const title = bySelector('[data-inspection-title]');
        const note = bySelector('[data-inspection-note]');

        if (title) {
            title.textContent = zone.dataset.label || 'Area';
        }

        all('input[name="physical_state"]', panel).forEach((input) => {
            input.checked = input.value === current.state;
        });

        if (note) {
            note.value = current.note || '';
        }

        panel.classList.add('open');
        panel.setAttribute('aria-hidden', 'false');
    }

    function closeInspectionPanel() {
        const panel = bySelector('[data-inspection-panel]');
        if (panel) {
            panel.classList.remove('open');
            panel.setAttribute('aria-hidden', 'true');
        }

        all('.inspection-zone').forEach((item) => item.classList.remove('selected'));
        activeZone = null;
    }

    function persistActiveZone() {
        if (!activeZone) {
            return;
        }

        const zoneId = activeZone.dataset.zone;
        const checked = bySelector('input[name="physical_state"]:checked', bySelector('[data-inspection-panel]'));
        const note = bySelector('[data-inspection-note]');

        state.physical[zoneId] = {
            label: activeZone.dataset.label || zoneId,
            state: checked ? checked.value : '',
            note: note ? note.value.trim() : ''
        };

        setZoneVisual(activeZone, state.physical[zoneId].state);
        updateSummary();
        updateProgress();
        scheduleSave();
    }

    function setTestState(row, value) {
        const name = row.dataset.testRow;
        state.tests[name] = value;

        all('[data-test-state]', row).forEach((button) => {
            button.classList.toggle('active', button.dataset.testState === value);
        });

        updateSummary();
        updateProgress();
        scheduleSave();
    }

    function syncComponent(card) {
        const name = card.dataset.componentCard;
        const select = bySelector('[data-component-state]', card);
        const note = bySelector('[data-component-note]', card);

        state.components[name] = {
            state: select ? select.value : '',
            note: note ? note.value.trim() : ''
        };

        updateSummary();
        updateProgress();
        scheduleSave();
    }

    function toggleAccessory(button) {
        const value = button.dataset.accessory;
        const exists = state.accessories.includes(value);
        state.accessories = exists
            ? state.accessories.filter((item) => item !== value)
            : state.accessories.concat(value);

        button.classList.toggle('active', !exists);
        updateSummary();
        updateProgress();
        scheduleSave();
    }

    function renderPhotos() {
        const gallery = bySelector('[data-photo-gallery]');
        if (!gallery) {
            return;
        }

        gallery.innerHTML = '';

        state.photos.forEach((photo) => {
            const card = document.createElement('article');
            card.className = 'photo-card';
            const safeName = escapeHtml(photo.name);
            card.innerHTML = `
                <img src="${photo.dataUrl}" alt="${safeName}">
                <div class="photo-card-body">
                    <strong title="${safeName}">${safeName}</strong>
                    <select class="form-control" data-photo-category="${photo.id}">
                        ${PHOTO_CATEGORIES.map((category) => `<option value="${category}" ${category === photo.category ? 'selected' : ''}>${category}</option>`).join('')}
                    </select>
                    <button type="button" class="btn btn-secondary" data-photo-remove="${photo.id}">Remover</button>
                </div>
            `;
            gallery.appendChild(card);
        });

        updatePhotoCount();
    }

    function addPhotoFiles(files) {
        Array.from(files || []).filter((file) => file.type.startsWith('image/')).forEach((file) => {
            const reader = new FileReader();
            reader.onload = () => {
                state.photos.push({
                    id: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
                    name: file.name,
                    category: PHOTO_CATEGORIES[0],
                    dataUrl: String(reader.result || '')
                });
                renderPhotos();
                updateSummary();
                updateProgress();
                scheduleSave();
            };
            reader.readAsDataURL(file);
        });
    }

    function appendQuickNote(note) {
        const field = bySelector('[data-notes-field]');
        if (!field) {
            return;
        }

        const value = field.value.trim();
        field.value = value === '' ? note : `${value}\n${note}`;
        state.notes = field.value;
        field.focus();
        updateSummary();
        updateProgress();
        scheduleSave();
    }

    function setupSignature() {
        const canvas = bySelector('[data-signature-canvas]');
        if (!canvas) {
            return;
        }

        signatureContext = canvas.getContext('2d');
        signatureContext.lineWidth = 3;
        signatureContext.lineCap = 'round';
        signatureContext.strokeStyle = '#111827';

        const position = (event) => {
            const rect = canvas.getBoundingClientRect();
            const point = event.touches ? event.touches[0] : event;
            return {
                x: ((point.clientX - rect.left) / rect.width) * canvas.width,
                y: ((point.clientY - rect.top) / rect.height) * canvas.height
            };
        };

        const start = (event) => {
            event.preventDefault();
            signatureDrawing = true;
            signatureDirty = true;
            const pos = position(event);
            signatureContext.beginPath();
            signatureContext.moveTo(pos.x, pos.y);
        };

        const move = (event) => {
            if (!signatureDrawing) {
                return;
            }
            event.preventDefault();
            const pos = position(event);
            signatureContext.lineTo(pos.x, pos.y);
            signatureContext.stroke();
        };

        const stop = () => {
            if (!signatureDrawing) {
                return;
            }
            signatureDrawing = false;
            state.signature = canvas.toDataURL('image/png');
            updateSummary();
            updateProgress();
            scheduleSave();
        };

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        window.addEventListener('mouseup', stop);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        canvas.addEventListener('touchend', stop);
    }

    function clearSignature() {
        const canvas = bySelector('[data-signature-canvas]');
        if (!canvas || !signatureContext) {
            return;
        }

        signatureContext.clearRect(0, 0, canvas.width, canvas.height);
        signatureDirty = false;
        state.signature = '';
        updateSummary();
        updateProgress();
        scheduleSave();
    }

    function saveSignature() {
        const canvas = bySelector('[data-signature-canvas]');
        if (!canvas || !signatureDirty) {
            return;
        }

        state.signature = canvas.toDataURL('image/png');
        updateSummary();
        updateProgress();
        saveDraft();
    }

    function drawSignatureFromDataUrl(dataUrl) {
        const canvas = bySelector('[data-signature-canvas]');
        if (!canvas || !signatureContext || !dataUrl) {
            return;
        }

        const image = new Image();
        image.onload = () => {
            signatureContext.clearRect(0, 0, canvas.width, canvas.height);
            signatureContext.drawImage(image, 0, 0, canvas.width, canvas.height);
            signatureDirty = true;
        };
        image.src = dataUrl;
    }

    function updateSummary() {
        const summary = bySelector('[data-summary]');
        if (!summary) {
            updatePayloadField();
            return;
        }

        const defects = [
            ...Object.values(state.physical).filter((item) => item && PHYSICAL_BAD_STATES.includes(item.state)),
            ...Object.values(state.tests).filter((value) => value === 'fail'),
            ...Object.values(state.components).filter((item) => item && PHYSICAL_BAD_STATES.includes(item.state))
        ].length;

        const tested = Object.values(state.tests).filter((value) => value && value !== 'untested').length;
        const physicalDone = Object.values(state.physical).filter((item) => item && item.state).length;
        const accessories = state.accessories.length ? state.accessories.join(', ') : 'Nenhum acessorio selecionado';
        const firstNote = state.notes.trim() ? state.notes.trim().split('\n')[0] : 'Sem observacoes';

        summary.innerHTML = `
            <article class="summary-card"><span>Estado fisico</span><strong>${physicalDone}</strong><p>areas avaliadas</p></article>
            <article class="summary-card"><span>Testes</span><strong>${tested}</strong><p>testes realizados</p></article>
            <article class="summary-card"><span>Defeitos</span><strong>${defects}</strong><p>pontos criticos</p></article>
            <article class="summary-card"><span>Fotos</span><strong>${state.photos.length}</strong><p>imagens anexadas</p></article>
            <article class="summary-card"><span>Acessorios</span><strong>${state.accessories.length}</strong><p>${escapeHtml(accessories)}</p></article>
            <article class="summary-card"><span>Assinatura</span><strong>${state.signature ? 'Sim' : 'Nao'}</strong><p>${escapeHtml(firstNote)}</p></article>
        `;

        updatePayloadField();
    }

    function validateChecklist() {
        return {
            ok: getProgress() >= 25,
            progress: getProgress(),
            message: getProgress() >= 25 ? 'Checklist iniciado.' : 'Preencha pelo menos parte da triagem.'
        };
    }

    function saveDraft() {
        if (!root) {
            return;
        }

        state.lastSavedAt = Date.now();
        updatePayloadField();

        try {
            localStorage.setItem(root.dataset.draftKey, JSON.stringify(state));
            const saveButton = bySelector('[data-save-checklist]');
            if (saveButton) {
                const original = saveButton.dataset.originalText || saveButton.innerHTML;
                saveButton.dataset.originalText = original;
                saveButton.innerHTML = '<i data-lucide="check"></i> Salvo';
                if (window.lucide) window.lucide.createIcons();
                window.setTimeout(() => {
                    saveButton.innerHTML = original;
                    if (window.lucide) window.lucide.createIcons();
                }, 900);
            }
        } catch (error) {
            console.warn('Nao foi possivel salvar o rascunho do checklist.', error);
        }
    }

    function loadDraft() {
        if (!root) {
            return;
        }

        try {
            const raw = localStorage.getItem(root.dataset.draftKey);
            if (!raw) {
                return;
            }

            const saved = JSON.parse(raw);
            Object.assign(state, saved);
        } catch (error) {
            console.warn('Nao foi possivel carregar o rascunho do checklist.', error);
        }
    }

    function applyLoadedState() {
        all('.inspection-zone').forEach((zone) => {
            const item = state.physical[zone.dataset.zone];
            setZoneVisual(zone, item ? item.state : '');
        });

        all('[data-test-row]').forEach((row) => {
            setTestState(row, state.tests[row.dataset.testRow] || 'untested');
        });

        all('[data-component-card]').forEach((card) => {
            const item = state.components[card.dataset.componentCard] || {};
            const select = bySelector('[data-component-state]', card);
            const note = bySelector('[data-component-note]', card);
            if (select) select.value = item.state || '';
            if (note) note.value = item.note || '';
        });

        all('[data-accessory]').forEach((button) => {
            button.classList.toggle('active', state.accessories.includes(button.dataset.accessory));
        });

        const notes = bySelector('[data-notes-field]');
        if (notes) {
            notes.value = state.notes || '';
        }

        renderPhotos();
        drawSignatureFromDataUrl(state.signature);
        updateSummary();
        updateProgress();
        updateElapsedTime();
    }

    function bindEvents() {
        all('[data-section-target]').forEach((button) => {
            button.addEventListener('click', () => activeSection(button.dataset.sectionTarget));
        });

        all('.inspection-zone').forEach((zone) => {
            zone.setAttribute('tabindex', '0');
            zone.setAttribute('role', 'button');
            zone.setAttribute('aria-label', zone.dataset.label || 'Area do aparelho');
            zone.addEventListener('click', () => openInspectionPanel(zone));
            zone.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openInspectionPanel(zone);
                }
            });
        });

        const panel = bySelector('[data-inspection-panel]');
        if (panel) {
            panel.addEventListener('click', (event) => {
                if (event.target === panel) {
                    closeInspectionPanel();
                }
            });
        }

        const closePanel = bySelector('[data-close-inspection]');
        if (closePanel) {
            closePanel.addEventListener('click', closeInspectionPanel);
        }

        all('input[name="physical_state"]').forEach((input) => {
            input.addEventListener('change', persistActiveZone);
        });

        const inspectionNote = bySelector('[data-inspection-note]');
        if (inspectionNote) {
            inspectionNote.addEventListener('input', persistActiveZone);
        }

        all('[data-test-state]').forEach((button) => {
            button.addEventListener('click', () => setTestState(button.closest('[data-test-row]'), button.dataset.testState));
        });

        all('[data-component-card]').forEach((card) => {
            card.addEventListener('change', () => syncComponent(card));
            card.addEventListener('input', () => syncComponent(card));
        });

        all('[data-accessory]').forEach((button) => {
            button.addEventListener('click', () => toggleAccessory(button));
        });

        const dropzone = bySelector('[data-photo-dropzone]');
        const photoInput = bySelector('[data-photo-input]');
        if (photoInput) {
            photoInput.addEventListener('change', () => addPhotoFiles(photoInput.files));
        }
        if (dropzone) {
            ['dragenter', 'dragover'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    dropzone.classList.add('dragover');
                });
            });
            ['dragleave', 'drop'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    dropzone.classList.remove('dragover');
                });
            });
            dropzone.addEventListener('drop', (event) => addPhotoFiles(event.dataTransfer.files));
        }

        const gallery = bySelector('[data-photo-gallery]');
        if (gallery) {
            gallery.addEventListener('change', (event) => {
                const select = event.target.closest('[data-photo-category]');
                if (!select) return;
                const photo = state.photos.find((item) => item.id === select.dataset.photoCategory);
                if (photo) {
                    photo.category = select.value;
                    updateSummary();
                    updateProgress();
                    scheduleSave();
                }
            });
            gallery.addEventListener('click', (event) => {
                const remove = event.target.closest('[data-photo-remove]');
                if (!remove) return;
                state.photos = state.photos.filter((item) => item.id !== remove.dataset.photoRemove);
                renderPhotos();
                updateSummary();
                updateProgress();
                scheduleSave();
            });
        }

        all('[data-quick-note]').forEach((button) => {
            button.addEventListener('click', () => appendQuickNote(button.dataset.quickNote));
        });

        const notes = bySelector('[data-notes-field]');
        if (notes) {
            notes.addEventListener('input', () => {
                state.notes = notes.value;
                updateSummary();
                updateProgress();
                scheduleSave();
            });
        }

        const clear = bySelector('[data-clear-signature]');
        if (clear) {
            clear.addEventListener('click', clearSignature);
        }

        const saveSignatureButton = bySelector('[data-save-signature]');
        if (saveSignatureButton) {
            saveSignatureButton.addEventListener('click', saveSignature);
        }

        const saveButton = bySelector('[data-save-checklist]');
        if (saveButton) {
            saveButton.addEventListener('click', () => {
                validateChecklist();
                saveDraft();
            });
        }
    }

    function initChecklist() {
        root = document.querySelector('[data-checklist-root]');
        if (!root) {
            return;
        }

        loadDraft();
        bindEvents();
        setupSignature();
        applyLoadedState();
        window.setInterval(updateElapsedTime, 1000);
    }

    window.initChecklist = initChecklist;
    window.updateProgress = updateProgress;
    window.saveDraft = saveDraft;
    window.loadDraft = loadDraft;
    window.openInspectionPanel = openInspectionPanel;
    window.updateSummary = updateSummary;
    window.validateChecklist = validateChecklist;

    document.addEventListener('DOMContentLoaded', initChecklist);
})();
