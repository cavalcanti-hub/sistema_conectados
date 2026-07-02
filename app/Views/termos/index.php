<?php
require __DIR__ . '/../layout/header.php';

$items = $items ?? [];
$filters = $filters ?? ['search' => '', 'date_from' => '', 'date_to' => ''];
$defaults = $defaults ?? [];
$old = $old ?? [];
$formData = array_merge([
    'vendedor_nome' => '',
    'vendedor_contato' => '',
    'vendedor_cpf' => '',
    'vendedor_rg' => '',
    'vendedor_endereco' => '',
    'data_entrada' => date('Y-m-d'),
    'equipamento_tipo' => 'Smartphone',
    'marca_modelo' => '',
    'imei1' => '',
    'imei2' => '',
    'senha_autorizada' => 0,
    'chip_ssd_card' => 0,
    'bateria' => 0,
    'acessorios' => '',
    'estado_aparelho' => '',
    'valor_compra' => '',
    'comprador_nome' => $defaults['nome'] ?? 'Conectados',
    'comprador_contato' => $defaults['contato'] ?? '',
    'comprador_documento' => $defaults['documento'] ?? '',
    'comprador_endereco' => $defaults['endereco'] ?? '',
    'observacoes' => '',
], $old);

$errorMessages = [
    'required' => 'Preencha vendedor, aparelho e comprador para gerar o termo.',
    'valor' => 'O valor da compra nao pode ser negativo.',
];
?>

<style>
    .terms-page { display:grid;gap:1.25rem; }
    .terms-hero {
        display:flex;align-items:center;justify-content:space-between;gap:1rem;
        background:#0f172a;color:#fff;border-radius:16px;padding:1.35rem 1.5rem;
        box-shadow:0 18px 45px -30px rgba(15,23,42,.75);
    }
    .terms-hero h2 { font-family:'Outfit',sans-serif;font-size:1.35rem;margin:0 0 .25rem; }
    .terms-hero p { margin:0;color:rgba(255,255,255,.78);font-size:.9rem; }
    .terms-hero i { width:44px;height:44px;color:#fbbf24; }
    .terms-panel { background:var(--bg-card);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow-sm);padding:1.25rem; }
    .terms-panel h3 { font-family:'Outfit',sans-serif;font-size:1.05rem;margin:0 0 1rem;color:var(--text-main); }
    .terms-section-title {
        display:flex;align-items:center;gap:.5rem;margin:1rem 0 .85rem;padding-bottom:.5rem;
        border-bottom:1px solid var(--border);font-weight:800;color:var(--primary);
    }
    .terms-section-title:first-child { margin-top:0; }
    .terms-form-grid { display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.9rem; }
    .terms-span-2 { grid-column:span 2; }
    .terms-span-4 { grid-column:span 4; }
    .terms-actions { display:flex;justify-content:flex-end;gap:.75rem;margin-top:1.25rem;flex-wrap:wrap; }
    .terms-filters { display:grid;grid-template-columns:minmax(220px,1fr) 160px 160px auto;gap:.75rem;align-items:end;margin-bottom:1rem; }
    .terms-table-wrap { overflow:auto;border:1px solid var(--border);border-radius:14px; }
    .terms-table { width:100%;border-collapse:collapse;min-width:860px;background:#fff; }
    .terms-table th { background:#f8fafc;color:#475569;font-size:.76rem;text-align:left;text-transform:uppercase;letter-spacing:.03em;padding:.85rem; }
    .terms-table td { padding:.85rem;border-top:1px solid var(--border);vertical-align:middle;font-size:.88rem; }
    .term-number { font-weight:900;color:var(--primary);display:block;margin-bottom:.25rem; }
    .term-sub { color:var(--text-muted);font-size:.78rem;line-height:1.35; }
    .term-row-actions { display:flex;gap:.45rem;align-items:center; }
    .icon-action {
        width:36px;height:36px;border-radius:10px;border:1px solid var(--border);background:#fff;color:var(--primary);
        display:inline-flex;align-items:center;justify-content:center;text-decoration:none;cursor:pointer;
    }
    .icon-action.danger { color:var(--danger); }
    .empty-state { text-align:center;padding:2.4rem 1rem;color:var(--text-muted); }
    @media(max-width:1100px){
        .terms-form-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .terms-span-4 { grid-column:span 2; }
    }
    @media(max-width:760px){
        .terms-hero { align-items:flex-start; }
        .terms-form-grid,.terms-filters { grid-template-columns:1fr; }
        .terms-span-2,.terms-span-4 { grid-column:auto; }
        .terms-actions .btn { width:100%;justify-content:center; }
    }
</style>

<?php if (!empty($error) && isset($errorMessages[$error])): ?>
    <div class="alert-success" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;">
        <i data-lucide="alert-triangle"></i> <?= e($errorMessages[$error]) ?>
    </div>
<?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert-success system-flash"><i data-lucide="check-circle"></i> Termo removido.</div>
<?php endif; ?>

<section class="terms-page">
    <div class="terms-hero">
        <div>
            <h2>Termo de compra e venda de aparelho</h2>
            <p>Gere o documento para smartphones, tablets e notebooks recebidos pela loja.</p>
        </div>
        <i data-lucide="file-signature"></i>
    </div>

    <div class="terms-panel">
        <h3>Novo termo</h3>
        <form method="POST" action="<?= e(route_url('termos/store')) ?>">
            <?= csrf_field() ?>

            <div class="terms-section-title"><i data-lucide="user-round" style="width:18px;"></i> Dados do vendedor</div>
            <div class="terms-form-grid">
                <div class="form-group terms-span-2">
                    <label class="form-label">Nome *</label>
                    <input type="text" name="vendedor_nome" class="form-control" required value="<?= e($formData['vendedor_nome']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Contato</label>
                    <input type="text" name="vendedor_contato" class="form-control" value="<?= e($formData['vendedor_contato']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Data de entrada</label>
                    <input type="date" name="data_entrada" class="form-control" value="<?= e($formData['data_entrada']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">CPF</label>
                    <input type="text" name="vendedor_cpf" class="form-control" value="<?= e($formData['vendedor_cpf']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">RG</label>
                    <input type="text" name="vendedor_rg" class="form-control" value="<?= e($formData['vendedor_rg']) ?>">
                </div>
                <div class="form-group terms-span-2">
                    <label class="form-label">Endereço</label>
                    <input type="text" name="vendedor_endereco" class="form-control" value="<?= e($formData['vendedor_endereco']) ?>">
                </div>
            </div>

            <div class="terms-section-title"><i data-lucide="smartphone" style="width:18px;"></i> Informações do equipamento e venda</div>
            <div class="terms-form-grid">
                <div class="form-group">
                    <label class="form-label">Equipamento</label>
                    <select name="equipamento_tipo" class="form-control">
                        <?php foreach (['Smartphone', 'Tablet', 'Notebook', 'Outro'] as $tipo): ?>
                            <option value="<?= e($tipo) ?>" <?= $formData['equipamento_tipo'] === $tipo ? 'selected' : '' ?>><?= e($tipo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group terms-span-2">
                    <label class="form-label">Marca / modelo *</label>
                    <input type="text" name="marca_modelo" class="form-control" required value="<?= e($formData['marca_modelo']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Valor da compra (R$)</label>
                    <input type="text" name="valor_compra" class="form-control" inputmode="decimal" value="<?= e((string) $formData['valor_compra']) ?>" placeholder="0,00">
                </div>
                <div class="form-group">
                    <label class="form-label">IMEI 1</label>
                    <input type="text" name="imei1" class="form-control" value="<?= e($formData['imei1']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">IMEI 2</label>
                    <input type="text" name="imei2" class="form-control" value="<?= e($formData['imei2']) ?>">
                </div>
                <div class="form-group terms-span-2">
                    <label class="form-label">Acessórios</label>
                    <input type="text" name="acessorios" class="form-control" value="<?= e($formData['acessorios']) ?>" placeholder="Carregador, caixa, nota, capinha...">
                </div>
                <div class="form-group terms-span-2">
                    <label class="form-label">Estado do aparelho</label>
                    <textarea name="estado_aparelho" class="form-control" rows="3" placeholder="Riscos, trincas, bloqueios, funcionamento, observações de entrada"><?= e($formData['estado_aparelho']) ?></textarea>
                </div>
            </div>

            <div class="terms-section-title"><i data-lucide="store" style="width:18px;"></i> Dados do comprador</div>
            <div class="terms-form-grid">
                <div class="form-group terms-span-2">
                    <label class="form-label">Nome / empresa *</label>
                    <input type="text" name="comprador_nome" class="form-control" required value="<?= e($formData['comprador_nome']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Contato</label>
                    <input type="text" name="comprador_contato" class="form-control" value="<?= e($formData['comprador_contato']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">CPF/CNPJ</label>
                    <input type="text" name="comprador_documento" class="form-control" value="<?= e($formData['comprador_documento']) ?>">
                </div>
                <div class="form-group terms-span-2">
                    <label class="form-label">Endereço</label>
                    <input type="text" name="comprador_endereco" class="form-control" value="<?= e($formData['comprador_endereco']) ?>">
                </div>
                <div class="form-group terms-span-2">
                    <label class="form-label">Observações internas</label>
                    <input type="text" name="observacoes" class="form-control" value="<?= e($formData['observacoes']) ?>">
                </div>
            </div>

            <div class="terms-actions">
                <button type="reset" class="btn btn-secondary"><i data-lucide="rotate-ccw"></i> Limpar</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="printer"></i> Salvar e imprimir</button>
            </div>
        </form>
    </div>

    <div class="terms-panel">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;">
            <h3 style="margin:0;">Termos emitidos</h3>
        </div>

        <form class="terms-filters" method="GET" action="<?= e(route_url()) ?>">
            <input type="hidden" name="url" value="termos">
            <div class="form-group">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Nº, vendedor, CPF, aparelho ou IMEI">
            </div>
            <div class="form-group">
                <label class="form-label">De</label>
                <input type="date" class="form-control" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Até</label>
                <input type="date" class="form-control" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <button class="btn btn-secondary" type="submit"><i data-lucide="filter"></i> Filtrar</button>
        </form>

        <div class="terms-table-wrap">
            <table class="terms-table">
                <thead>
                    <tr>
                        <th>Termo</th>
                        <th>Vendedor</th>
                        <th>Equipamento</th>
                        <th>Valor</th>
                        <th>Data</th>
                        <th>Responsável</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="7"><div class="empty-state">Nenhum termo emitido ainda.</div></td></tr>
                <?php endif; ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <span class="term-number"><?= e($item['numero_termo']) ?></span>
                            <span class="term-sub"><?= e($item['comprador_nome']) ?></span>
                        </td>
                        <td>
                            <strong><?= e($item['vendedor_nome']) ?></strong>
                            <div class="term-sub"><?= e($item['vendedor_cpf'] ?: ($item['vendedor_contato'] ?: '-')) ?></div>
                        </td>
                        <td>
                            <strong><?= e($item['marca_modelo']) ?></strong>
                            <div class="term-sub"><?= e($item['equipamento_tipo']) ?><?= $item['imei1'] ? ' | IMEI ' . e($item['imei1']) : '' ?></div>
                        </td>
                        <td>R$ <?= money_br($item['valor_compra']) ?></td>
                        <td><?= e(date('d/m/Y', strtotime($item['data_entrada']))) ?></td>
                        <td><?= e($item['usuario_nome'] ?: '-') ?></td>
                        <td>
                            <div class="term-row-actions">
                                <a class="icon-action" href="<?= e(route_url('termos/print', ['id' => (int) $item['id']])) ?>" target="_blank" title="Imprimir" aria-label="Imprimir"><i data-lucide="printer" style="width:16px;"></i></a>
                                <?php if (current_user_profile() === 'Administrador'): ?>
                                    <form method="POST" action="<?= e(route_url('termos/delete')) ?>" data-confirm="Remover este termo?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button class="icon-action danger" type="submit" title="Remover" aria-label="Remover"><i data-lucide="trash-2" style="width:16px;"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= render_pagination($pagination ?? [], 'termos', $filters) ?>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
