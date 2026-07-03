<?php
$extraStyles = [asset_url('assets/css/checklist.css?v=20260703-checklist')];
$extraScripts = [asset_url('assets/js/checklist.js?v=20260703-checklist')];
require_once dirname(__DIR__) . '/layout/header.php';

$osInfo = $osInfo ?? [];
$functionalTests = [
    'Tela', 'Touch', 'Display', 'Face ID', 'Touch ID', 'Sensor Digital',
    'Microfone', 'Alto-falante Superior', 'Alto-falante Inferior', 'Vibracao',
    'Bluetooth', 'Wi-Fi', 'GPS', 'NFC', 'Flash', 'USB', 'Carregamento',
    'Carregamento Rapido', 'Leitura do SIM', 'Camera Frontal', 'Camera Traseira',
    'Zoom', 'Autofoco', 'Sensor de Proximidade', 'Sensor de Luz', 'Giroscopio', 'Bussola',
];
$components = ['Tela', 'Bateria', 'Carcaca', 'Conector de Carga', 'Flex', 'Cameras', 'Botoes', 'Microfone', 'Alto-falantes'];
$accessories = ['Carregador', 'Cabo USB', 'Caixa', 'Chip', 'Cartao SD', 'Pelicula', 'Capa', 'Fone', 'Chave SIM', 'Manual'];
$quickNotes = [
    'Tela quebrada.',
    'Cliente ciente dos riscos.',
    'Equipamento oxidado.',
    'Sem garantia contra novos danos.',
    'Cliente recusou backup.',
];
?>

<div
    class="checklist-page"
    data-checklist-root
    data-draft-key="conectados:checklist:<?= e($osInfo['id'] ?: preg_replace('/\W+/', '-', strtolower((string) $osInfo['number']))) ?>"
>
    <header class="checklist-hero">
        <div class="checklist-identity">
            <span class="checklist-kicker">Entrada tecnica</span>
            <h2>Checklist de Entrada</h2>
            <div class="checklist-meta-grid">
                <span><strong>OS</strong><?= e($osInfo['number'] ?? 'CHECK-IN') ?></span>
                <span><strong>Status</strong><?= e($osInfo['status'] ?? 'Rascunho') ?></span>
                <span><strong>Cliente</strong><?= e($osInfo['client'] ?? '') ?></span>
                <span><strong>Marca</strong><?= e($osInfo['brand'] ?? '') ?></span>
                <span><strong>Modelo</strong><?= e($osInfo['model'] ?? '') ?></span>
                <span><strong>IMEI</strong><?= e($osInfo['imei'] ?? '') ?></span>
                <span><strong>Data/Hora</strong><?= e($osInfo['created_at'] ?? '') ?></span>
                <span><strong>Tecnico</strong><?= e($osInfo['technician'] ?? '') ?></span>
            </div>
        </div>
        <div class="checklist-command">
            <div class="checklist-progress-ring" aria-label="Progresso do checklist">
                <svg viewBox="0 0 44 44" aria-hidden="true">
                    <circle class="progress-bg" cx="22" cy="22" r="18"></circle>
                    <circle class="progress-value" cx="22" cy="22" r="18" data-progress-ring></circle>
                </svg>
                <strong data-progress-label>0%</strong>
            </div>
            <div class="checklist-live-stat">
                <span>Tempo</span>
                <strong data-elapsed-time>00:00</strong>
            </div>
            <div class="checklist-live-stat">
                <span>Fotos</span>
                <strong data-photo-count>0</strong>
            </div>
            <button type="button" class="btn btn-primary checklist-save" data-save-checklist>
                <i data-lucide="save"></i> Salvar
            </button>
        </div>
    </header>

    <div class="checklist-shell">
        <aside class="checklist-sidebar" aria-label="Secoes do checklist">
            <?php
            $sections = [
                ['id' => 'physical', 'icon' => 'smartphone', 'label' => 'Estado Fisico'],
                ['id' => 'tests', 'icon' => 'activity', 'label' => 'Testes Funcionais'],
                ['id' => 'components', 'icon' => 'cpu', 'label' => 'Componentes'],
                ['id' => 'accessories', 'icon' => 'package-check', 'label' => 'Acessorios'],
                ['id' => 'photos', 'icon' => 'camera', 'label' => 'Fotos'],
                ['id' => 'notes', 'icon' => 'notebook-pen', 'label' => 'Observacoes'],
                ['id' => 'signature', 'icon' => 'signature', 'label' => 'Assinatura'],
                ['id' => 'summary', 'icon' => 'clipboard-check', 'label' => 'Resumo'],
            ];
            ?>
            <?php foreach ($sections as $index => $section): ?>
            <button
                type="button"
                class="checklist-nav-link <?= $index === 0 ? 'active' : '' ?>"
                data-section-target="<?= e($section['id']) ?>"
            >
                <i data-lucide="<?= e($section['icon']) ?>"></i>
                <span><?= e($section['label']) ?></span>
            </button>
            <?php endforeach; ?>
        </aside>

        <main class="checklist-workspace">
            <section class="checklist-section active" data-section="physical">
                <div class="section-heading">
                    <div>
                        <span>Inspecao visual</span>
                        <h3>Estado Fisico</h3>
                    </div>
                    <p>Clique em uma area do aparelho para classificar o estado e registrar uma observacao.</p>
                </div>

                <div class="phone-board">
                    <div class="phone-card">
                        <h4>Frente</h4>
                        <svg class="phone-svg" viewBox="0 0 260 520" role="img" aria-label="Frente do smartphone">
                            <rect class="phone-body" x="44" y="20" width="172" height="480" rx="32"></rect>
                            <rect class="phone-screen inspection-zone" data-zone="front-screen" data-label="Tela" x="62" y="62" width="136" height="356" rx="18"></rect>
                            <rect class="inspection-zone" data-zone="top-speaker" data-label="Alto-falante Superior" x="103" y="42" width="54" height="9" rx="5"></rect>
                            <rect class="inspection-zone" data-zone="left-side" data-label="Lateral esquerda" x="34" y="150" width="14" height="164" rx="7"></rect>
                            <rect class="inspection-zone" data-zone="right-side" data-label="Lateral direita" x="212" y="150" width="14" height="164" rx="7"></rect>
                            <rect class="inspection-zone" data-zone="home-area" data-label="Botoes" x="105" y="438" width="50" height="28" rx="14"></rect>
                            <rect class="inspection-zone" data-zone="usb-port" data-label="Conector USB" x="109" y="488" width="42" height="8" rx="4"></rect>
                            <text x="130" y="254" text-anchor="middle">Tela</text>
                        </svg>
                    </div>

                    <div class="phone-card">
                        <h4>Traseira</h4>
                        <svg class="phone-svg" viewBox="0 0 260 520" role="img" aria-label="Traseira do smartphone">
                            <rect class="phone-body" x="44" y="20" width="172" height="480" rx="32"></rect>
                            <rect class="inspection-zone" data-zone="back-cover" data-label="Tampa traseira" x="62" y="98" width="136" height="330" rx="20"></rect>
                            <circle class="inspection-zone" data-zone="rear-camera" data-label="Camera" cx="92" cy="70" r="22"></circle>
                            <circle class="inspection-zone" data-zone="flash" data-label="Flash" cx="136" cy="70" r="10"></circle>
                            <rect class="inspection-zone" data-zone="microphone" data-label="Microfone" x="78" y="466" width="32" height="8" rx="4"></rect>
                            <rect class="inspection-zone" data-zone="bottom-speaker" data-label="Alto-falante" x="142" y="466" width="42" height="8" rx="4"></rect>
                            <text x="130" y="270" text-anchor="middle">Traseira</text>
                        </svg>
                    </div>
                </div>
            </section>

            <section class="checklist-section" data-section="tests">
                <div class="section-heading">
                    <div>
                        <span>Diagnostico rapido</span>
                        <h3>Testes Funcionais</h3>
                    </div>
                    <p>Use os estados operacionais sem checkbox tradicional.</p>
                </div>
                <div class="test-list">
                    <?php foreach ($functionalTests as $test): ?>
                    <div class="test-row" data-test-row="<?= e($test) ?>">
                        <strong><?= e($test) ?></strong>
                        <div class="state-group" role="group" aria-label="<?= e($test) ?>">
                            <button type="button" data-test-state="ok">Funcionando</button>
                            <button type="button" data-test-state="partial">Parcial</button>
                            <button type="button" data-test-state="fail">Nao funciona</button>
                            <button type="button" data-test-state="untested" class="active">Nao testado</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="checklist-section" data-section="components">
                <div class="section-heading">
                    <div>
                        <span>Pecas principais</span>
                        <h3>Componentes</h3>
                    </div>
                    <p>Registre estado e observacoes por componente.</p>
                </div>
                <div class="component-grid">
                    <?php foreach ($components as $component): ?>
                    <article class="component-card" data-component-card="<?= e($component) ?>">
                        <h4><?= e($component) ?></h4>
                        <select class="form-control" data-component-state>
                            <option value="">Nao avaliado</option>
                            <option value="perfeito">Perfeito</option>
                            <option value="arranhado">Arranhado</option>
                            <option value="trincado">Trincado</option>
                            <option value="quebrado">Quebrado</option>
                            <option value="amassado">Amassado</option>
                            <option value="outro">Outro</option>
                        </select>
                        <textarea class="form-control" rows="2" data-component-note placeholder="Observacao"></textarea>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="checklist-section" data-section="accessories">
                <div class="section-heading">
                    <div>
                        <span>Itens entregues</span>
                        <h3>Acessorios</h3>
                    </div>
                    <p>Selecione todos os itens recebidos junto com o aparelho.</p>
                </div>
                <div class="chip-grid">
                    <?php foreach ($accessories as $accessory): ?>
                    <button type="button" class="check-chip" data-accessory="<?= e($accessory) ?>">
                        <i data-lucide="plus"></i><?= e($accessory) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="checklist-section" data-section="photos">
                <div class="section-heading">
                    <div>
                        <span>Evidencias</span>
                        <h3>Fotos</h3>
                    </div>
                    <p>Arraste arquivos ou use o botao para anexar imagens da entrada.</p>
                </div>
                <div class="photo-dropzone" data-photo-dropzone>
                    <input type="file" accept="image/*" multiple capture="environment" data-photo-input>
                    <i data-lucide="camera"></i>
                    <strong>Tirar Foto ou Enviar Arquivos</strong>
                    <span>Frente, traseira, IMEI, tela, defeito e acessorios.</span>
                </div>
                <div class="photo-gallery" data-photo-gallery></div>
            </section>

            <section class="checklist-section" data-section="notes">
                <div class="section-heading">
                    <div>
                        <span>Triagem</span>
                        <h3>Observacoes</h3>
                    </div>
                    <p>Use notas rapidas ou escreva uma descricao completa.</p>
                </div>
                <div class="quick-note-row">
                    <?php foreach ($quickNotes as $note): ?>
                    <button type="button" class="quick-note" data-quick-note="<?= e($note) ?>"><?= e($note) ?></button>
                    <?php endforeach; ?>
                </div>
                <textarea class="form-control checklist-notes" rows="8" data-notes-field placeholder="Observacoes gerais do checklist"></textarea>
            </section>

            <section class="checklist-section" data-section="signature">
                <div class="section-heading">
                    <div>
                        <span>Confirmacao</span>
                        <h3>Assinatura</h3>
                    </div>
                    <p>Assinatura do cliente ou responsavel pela entrada.</p>
                </div>
                <div class="signature-box">
                    <canvas data-signature-canvas width="900" height="280" aria-label="Area de assinatura"></canvas>
                    <div class="signature-actions">
                        <button type="button" class="btn btn-secondary" data-clear-signature><i data-lucide="eraser"></i> Limpar</button>
                        <button type="button" class="btn btn-primary" data-save-signature><i data-lucide="check"></i> Salvar assinatura</button>
                    </div>
                </div>
            </section>

            <section class="checklist-section" data-section="summary">
                <div class="section-heading">
                    <div>
                        <span>Conferencia final</span>
                        <h3>Resumo</h3>
                    </div>
                    <p>Gerado automaticamente com base no preenchimento.</p>
                </div>
                <div class="summary-grid" data-summary></div>
                <textarea class="checklist-payload" name="checklist_payload" data-payload-field readonly></textarea>
            </section>
        </main>
    </div>

    <aside class="inspection-panel" data-inspection-panel aria-hidden="true">
        <div class="inspection-panel-card">
            <button type="button" class="panel-close" data-close-inspection aria-label="Fechar"><i data-lucide="x"></i></button>
            <span>Area selecionada</span>
            <h3 data-inspection-title>Area</h3>
            <div class="inspection-options">
                <?php foreach (['perfeito' => 'Perfeito', 'arranhado' => 'Arranhado', 'trincado' => 'Trincado', 'quebrado' => 'Quebrado', 'amassado' => 'Amassado', 'outro' => 'Outro'] as $value => $label): ?>
                <label>
                    <input type="radio" name="physical_state" value="<?= e($value) ?>">
                    <span><?= e($label) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <label class="form-label" for="inspection-note">Observacao</label>
            <textarea id="inspection-note" class="form-control" rows="5" data-inspection-note placeholder="Detalhe riscos, trincas, marcas ou avarias"></textarea>
        </div>
    </aside>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
