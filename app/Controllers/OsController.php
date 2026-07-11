<?php

namespace App\Controllers;

use App\Core\Controller;

class OsController extends Controller
{
    private $model;
    private $clienteModel;
    private $tecnicoModel;
    private $configModel;

    public function __construct()
    {
        $this->model = new \App\Models\OsModel();
        $this->clienteModel = new \App\Models\ClienteModel();
        $this->tecnicoModel = new \App\Models\TecnicoModel();
        $this->configModel = new \App\Models\ConfigModel();
    }

    private function buildCompanyData(): array
    {
        $settings = $this->configModel->getAll();

        return [
            'name' => trim((string) ($settings['nome_empresa'] ?? 'Conectados')),
            'phone' => trim((string) ($settings['whatsapp'] ?? '')),
            'address' => trim((string) ($settings['endereco'] ?? '')),
            'email' => trim((string) ($settings['email_negocio'] ?? '')),
            'website' => trim((string) ($settings['website'] ?? '')),
            'logo' => asset_url('assets/img/logo.png'),
            'logo_print' => asset_url('assets/img/logo-print.png?v=20260702-banner'),
        ];
    }

    private function renderPrintDocument(string $mode, array $payload): void
    {
        $templates = [
            'a4' => 'print/a4',
            '80' => 'print/thermal_80',
        ];

        render_resource_view($templates[$mode] ?? $templates['80'], $payload);
    }

    private function deviceBrandModels(): array
    {
        return (new \App\Models\AparelhoModel())->getModelosPorMarca();
    }

    private function getOsUploadDir(): string
    {
        $dir = public_path('uploads/os');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir;
    }

    private function storeOsPhotos(array $files): array
    {
        $stored = [];
        if (empty($files['name']) || !is_array($files['name'])) {
            return $stored;
        }

        foreach ($files['name'] as $key => $name) {
            if ($name === '') {
                continue;
            }

            $file = [
                'name' => $name,
                'type' => $files['type'][$key] ?? '',
                'tmp_name' => $files['tmp_name'][$key] ?? '',
                'error' => $files['error'][$key] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$key] ?? 0,
            ];
            $result = validate_and_store_image_upload($file, 'os');
            if ($result['ok'] && !empty($result['name'])) {
                $stored[] = $result['name'];
                continue;
            }

            if (!empty($result['error'])) {
                app_log('Falha no upload de foto da OS', ['erro' => $result['error']]);
            }
        }

        return $stored;
    }

    public function index()
    {
        $filters = ['status' => $_GET['status'] ?? '', 'search' => $_GET['search'] ?? ''];
        $pager = pagination_request(20, 100);
        $pagination = pagination_meta($this->model->countFiltered($filters), $pager['page'], $pager['per_page']);
        $ordens = $this->model->getAll($filters, $pagination['per_page'], $pagination['offset']);
        $this->view('os/index', [
            'title' => 'Ordens de Servico - Conectados',
            'page_title' => 'Gerenciamento de OS',
            'status_list' => os_status_list(),
            'ordens' => $ordens,
            'filters' => $filters,
            'pagination' => $pagination
        ]);
    }

    public function create()
    {
        $clientes = []; // loaded via AJAX
        $tecnicos = $this->tecnicoModel->getAtivos();
        $this->view('os/create', [
            'title' => 'Nova OS - Conectados',
            'page_title' => 'Abrir Nova OS',
            'clientes' => $clientes,
            'tecnicos' => $tecnicos,
            'deviceBrandModels' => $this->deviceBrandModels(),
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(route_url('os'));
        }

        $db = \App\Config\Database::getInstance();
        $db->beginTransaction();

        try {
            $aparelhoModel = new \App\Models\AparelhoModel();
            $aparelhoModel->storeModelo($_POST['marca'] ?? 'Geral', $_POST['modelo'] ?? '');
            $estadoFisico = trim($_POST['estado_fisico'] ?? '');
            if (isset($_POST['checklist']) && is_array($_POST['checklist'])) {
                $chkArr = [];
                foreach ($_POST['checklist'] as $item => $state) {
                    if ($state !== 'N/A' && $state !== '') {
                        $chkArr[] = "- $item: $state";
                    }
                }
                if (!empty($chkArr)) {
                    $prefix = "Checklist de Entrada:\n" . implode("\n", $chkArr);
                    $estadoFisico = $prefix . "\n\n" . $estadoFisico;
                }
            }

            $aparelhoId = $aparelhoModel->create([
                ':cliente_id' => $_POST['cliente_id'],
                ':marca' => $_POST['marca'] ?? 'Geral',
                ':modelo' => $_POST['modelo'],
                ':imei' => $_POST['imei'] ?? '',
                ':cor' => $_POST['cor'] ?? '',
                ':senha_padrao' => $_POST['senha_padrao'] ?? '',
                ':estado_fisico' => trim($estadoFisico)
            ]);

            $fotosNomes = $this->storeOsPhotos($_FILES['fotos'] ?? []);

            $osId = $this->model->create([
                ':numero_os' => $this->model->generateOSNumber(),
                ':cliente_id' => $_POST['cliente_id'],
                ':aparelho_id' => $aparelhoId,
                ':tecnico_id' => $_POST['tecnico_id'] ?: null,
                ':problema_relatado' => $_POST['problema_relatado'],
                ':prioridade' => $_POST['prioridade'],
                ':status' => 'Recebido',
                ':prazo_estimado' => $_POST['prazo_estimado'] ?: null,
                ':valor_mao_obra' => (float) ($_POST['valor_mao_obra'] ?? 0),
                ':valor_pecas' => (float) ($_POST['valor_pecas'] ?? 0),
                ':fotos' => !empty($fotosNomes) ? json_encode($fotosNomes) : null
            ]);

            $this->model->addHistorico($osId, current_user_id(), '', 'Recebido', 'OS aberta no sistema');
            $db->commit();
            (new \App\Models\AuditModel())->record('criar', 'os', (int) $osId, 'OS criada no sistema', [
                'cliente_id' => $_POST['cliente_id'] ?? null,
                'status' => 'Recebido',
            ]);
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            app_log('Falha ao criar OS', [
                'cliente_id' => $_POST['cliente_id'] ?? null,
                'erro' => $e->getMessage(),
            ]);
            $this->redirect(route_url('os/create', ['error' => 'store_failed']));
        }

        $this->redirect(route_url('os', ['success' => 1]));
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $os = $this->model->find($id);
        $tecnicos = $this->tecnicoModel->getAtivos();
        $this->view('os/edit', [
            'title' => 'Editar OS - Conectados',
            'page_title' => 'Editar Ordem de Servico',
            'os' => $os,
            'pagamentos' => $this->model->getPagamentos($id),
            'totalPago' => $this->model->totalPagamentos($id),
            'tecnicos' => $tecnicos,
            'deviceBrandModels' => $this->deviceBrandModels(),
            'status_list' => os_status_list()
        ]);
    }

    public function update()
    {
        $id = $_POST['id'];
        $osAtual = $this->model->find($id);
        if (!$osAtual) {
            $this->redirect(route_url('os'));
        }

        $db = \App\Config\Database::getInstance();
        $db->beginTransaction();

        try {
            (new \App\Models\AparelhoModel())->storeModelo($_POST['marca'] ?? $osAtual['marca'], $_POST['modelo'] ?? $osAtual['modelo']);
            $stmt = $db->prepare("UPDATE aparelhos SET marca=:marca, modelo=:modelo, imei=:imei, cor=:cor, senha_padrao=:senha_padrao, estado_fisico=:estado_fisico WHERE id=:id");
            $stmt->execute([
                ':marca' => $_POST['marca'] ?? $osAtual['marca'],
                ':modelo' => $_POST['modelo'] ?? $osAtual['modelo'],
                ':imei' => $_POST['imei'] ?? $osAtual['imei'],
                ':cor' => $_POST['cor'] ?? $osAtual['cor'],
                ':senha_padrao' => $_POST['senha_padrao'] ?? $osAtual['senha_padrao'],
                ':estado_fisico' => $_POST['estado_fisico'] ?? $osAtual['estado_fisico'],
                ':id' => $osAtual['aparelho_id']
            ]);

            $fotosNomes = !empty($osAtual['fotos']) ? json_decode($osAtual['fotos'], true) : [];
            $fotosNomes = array_merge(is_array($fotosNomes) ? $fotosNomes : [], $this->storeOsPhotos($_FILES['fotos'] ?? []));
            $fotosSaidaNomes = !empty($osAtual['fotos_saida']) ? json_decode($osAtual['fotos_saida'], true) : [];
            $fotosSaidaNomes = array_merge(is_array($fotosSaidaNomes) ? $fotosSaidaNomes : [], $this->storeOsPhotos($_FILES['fotos_saida'] ?? []));

            $novoStatus = normalize_os_status($_POST['status'] ?? $osAtual['status']);
            if (!in_array($novoStatus, os_status_list(), true)) {
                $novoStatus = normalize_os_status($osAtual['status']);
            }

            $payload = [
                ':tecnico_id' => $_POST['tecnico_id'] ?: null,
                ':diagnostico_tecnico' => $_POST['diagnostico_tecnico'],
                ':servico_realizar' => $_POST['servico_realizar'],
                ':status' => $novoStatus,
                ':prioridade' => $_POST['prioridade'],
                ':valor_mao_obra' => (float) $_POST['valor_mao_obra'],
                ':valor_pecas' => (float) $_POST['valor_pecas'],
                ':desconto' => (float) ($_POST['desconto'] ?? 0),
                ':prazo_estimado' => $_POST['prazo_estimado'] ?: null,
                ':forma_pagamento' => $_POST['forma_pagamento'],
                ':situacao_pagamento' => $_POST['situacao_pagamento'],
                ':fotos' => !empty($fotosNomes) ? json_encode($fotosNomes) : null,
                ':fotos_saida' => !empty($fotosSaidaNomes) ? json_encode($fotosSaidaNomes) : null
            ];

            $totalOs = max(0, $payload[':valor_mao_obra'] + $payload[':valor_pecas'] - $payload[':desconto']);
            $totalPago = $this->model->totalPagamentos($id);
            $pagamentoNovo = $this->registerOsPaymentFromPost((int) $id, (string) $osAtual['numero_os'], $totalOs);
            $totalPago += $pagamentoNovo;
            if ($totalOs > 0 && $totalPago > 0) {
                $payload[':situacao_pagamento'] = $totalPago + 0.01 >= $totalOs ? 'Pago' : 'Parcial';
            }

            $this->model->update($id, $payload);
            if (normalize_os_status($osAtual['status']) !== $novoStatus) {
                $this->model->addHistorico($id, current_user_id(), normalize_os_status($osAtual['status']), $novoStatus, $_POST['obs_interna'] ?? '');
            }

            $this->syncFinanceiroFromOs((int) $id, $payload, (string) $osAtual['numero_os'], $totalPago);
            $db->commit();
            (new \App\Models\AuditModel())->record('editar', 'os', (int) $id, 'OS editada: #' . (string) $osAtual['numero_os'], [
                'status_anterior' => normalize_os_status($osAtual['status']),
                'status_novo' => $novoStatus,
                'valor_total' => $totalOs,
            ]);
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            app_log('Falha ao atualizar OS', ['os_id' => $id, 'erro' => $e->getMessage()]);
            $this->redirect(route_url('os/viewDetail', ['id' => $id, 'error' => 'update_failed']));
        }

        $this->redirect(route_url('os/viewDetail', ['id' => $id, 'success' => 1]));
    }

    public function storeModelo()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $marca = trim((string) ($_POST['marca'] ?? ''));
        $modelo = trim((string) ($_POST['modelo'] ?? ''));
        if ($marca === '' || $modelo === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'invalid_model']);
            exit;
        }

        $ok = (new \App\Models\AparelhoModel())->storeModelo($marca, $modelo);
        echo json_encode(['ok' => $ok, 'marca' => $marca, 'modelo' => $modelo], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function delete()
    {
        if (current_user_profile() !== 'Administrador') {
            http_response_code(403);
            echo 'Apenas administradores podem excluir ordens de servico.';
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect(route_url('os'));
        }

        try {
            $deleted = $this->model->delete($id);
            if ($deleted) {
                $this->deleteOsPhotos($deleted['fotos'] ?? null);
                $this->deleteOsPhotos($deleted['fotos_saida'] ?? null);
            }
            (new \App\Models\AuditModel())->record('excluir', 'os', $id, 'OS excluida: #' . ($deleted['numero_os'] ?? $id), [
                'cliente' => $deleted['cliente_nome'] ?? '',
                'status' => $deleted['status'] ?? '',
            ]);
            $this->redirect(route_url('os', ['deleted' => 1]));
        } catch (\Throwable $e) {
            app_log('Falha ao excluir OS', ['os_id' => $id, 'erro' => $e->getMessage()]);
            $this->redirect(route_url('os/viewDetail', ['id' => $id, 'error' => 'delete_failed']));
        }
    }

    public function receberPoint()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(route_url('os'));
        }

        $id = (int) ($_POST['id'] ?? 0);
        $os = $this->model->find($id);
        if (!$os) {
            $this->redirect(route_url('os'));
        }

        $totalPago = $this->model->totalPagamentos($id);
        $saldo = round(max(0, (float) ($os['valor_total'] ?? 0) - $totalPago), 2);
        $valor = normalize_decimal_input($_POST['valor'] ?? $saldo);
        if ($valor <= 0) {
            $this->redirect(route_url('os/viewDetail', ['id' => $id, 'point_error' => 'valor']));
        }

        try {
            $result = (new \App\Models\MercadoPagoPointModel())->createOrderForOs($os, [
                'amount' => $valor,
                'payment_type' => $_POST['payment_type'] ?? null,
                'installments' => $_POST['installments'] ?? null,
            ]);
            (new \App\Models\AuditModel())->record('criar', 'mercado_pago_point', $id, 'Cobranca enviada para Smart Point: OS #' . (string) $os['numero_os'], [
                'order_id' => $result['order_id'] ?? '',
                'external_reference' => $result['external_reference'] ?? '',
                'valor' => $valor,
            ]);
            $this->redirect(route_url('os/viewDetail', ['id' => $id, 'point_sent' => 1]));
        } catch (\Throwable $e) {
            app_log('Falha ao enviar cobranca Point', ['os_id' => $id, 'erro' => $e->getMessage()]);
            $this->redirect(route_url('os/viewDetail', ['id' => $id, 'point_error' => rawurlencode($e->getMessage())]));
        }
    }

    public function registrarPagamento()
    {
        $id = (int) ($_POST['id'] ?? 0);
        $nonce = trim((string) ($_POST['payment_nonce'] ?? ''));
        try {
            $profile = current_user_profile();
            if (!in_array($profile, ['Administrador', 'Financeiro', 'Atendente'], true)) throw new \App\Services\PaymentException('FORBIDDEN', 'Usuario sem permissao para registrar pagamento.', 403);
            if (!validate_os_payment_nonce($nonce, $id)) throw new \App\Services\PaymentException('PAYMENT_DUPLICATE', 'Solicitacao duplicada ou expirada.', 409);
            $amountCents = \App\Services\ManualOsPaymentService::parseMoneyToCents($_POST['valor'] ?? '');
            $result = (new \App\Services\ManualOsPaymentService())->register($id, $amountCents, (string) ($_POST['forma_pagamento'] ?? ''), (string) ($_POST['observacao'] ?? ''), (int) current_user_id());
            consume_os_payment_nonce($nonce);
            (new \App\Models\AuditModel())->record('registrar_pagamento_os', 'ordem_servico', $id, 'Pagamento manual registrado.', ['pagamento_id' => $result['payment_id'], 'valor' => number_format($result['amount_cents'] / 100, 2, '.', ''), 'forma' => $result['method'], 'origem' => 'manual', 'resultado' => 'sucesso']);
            if (is_ajax_request()) { header('Content-Type: application/json; charset=utf-8'); echo json_encode(['success' => true, 'message' => 'Pagamento registrado com sucesso.', 'payment_status' => $result['status'], 'remaining_balance' => number_format($result['remaining_cents'] / 100, 2, ',', '.')]); return; }
            $this->redirect(route_url('os/viewDetail', ['id' => $id, 'pagamento_ok' => 1]));
        } catch (\App\Services\PaymentException $e) {
            if (is_ajax_request()) { http_response_code($e->httpStatus); header('Content-Type: application/json; charset=utf-8'); echo json_encode(['success' => false, 'message' => $e->getMessage(), 'code' => $e->domainCode]); return; }
            $this->redirect(route_url('os/viewDetail', ['id' => max(0, $id), 'payment_error' => $e->domainCode]));
        } catch (\Throwable $e) {
            app_log('Falha transacional ao registrar pagamento manual', ['os_id' => $id, 'erro_tipo' => get_class($e)]);
            if (is_ajax_request()) { http_response_code(500); header('Content-Type: application/json; charset=utf-8'); echo json_encode(['success' => false, 'message' => 'Nao foi possivel registrar o pagamento.', 'code' => 'PAYMENT_TRANSACTION_FAILED']); return; }
            $this->redirect(route_url('os/viewDetail', ['id' => max(0, $id), 'payment_error' => 'PAYMENT_TRANSACTION_FAILED']));
        }
    }

    private function deleteOsPhotos($photos): void
    {
        if (empty($photos)) {
            return;
        }

        $decoded = is_array($photos) ? $photos : json_decode((string) $photos, true);
        if (!is_array($decoded)) {
            return;
        }

        $dir = $this->getOsUploadDir();
        foreach ($decoded as $photo) {
            $name = basename(str_replace('\\', '/', (string) $photo));
            if ($name === '') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $name;
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function registerOsPaymentFromPost(int $osId, string $numeroOs, float $totalOs): float
    {
        $valor = normalize_decimal_input($_POST['pagamento_valor'] ?? 0);
        if ($osId <= 0 || $valor <= 0) {
            return 0.0;
        }

        $forma = trim((string) ($_POST['pagamento_forma'] ?? ($_POST['forma_pagamento'] ?? '')));
        $data = trim((string) ($_POST['pagamento_data'] ?? '')) ?: date('Y-m-d');
        $observacao = trim((string) ($_POST['pagamento_observacao'] ?? ''));
        $usuarioId = current_user_id();

        $this->model->addPagamento([
            ':os_id' => $osId,
            ':valor' => $valor,
            ':forma_pagamento' => $forma,
            ':data_pagamento' => $data,
            ':observacao' => $observacao,
            ':usuario_id' => $usuarioId,
        ]);

        $financeiro = new \App\Models\FinanceiroModel();
        $receitaId = (int) $financeiro->create([
            ':tipo' => 'Receita',
            ':categoria' => 'Pagamento OS',
            ':descricao' => 'Pagamento OS #' . $numeroOs . ($totalOs > 0 && $valor < $totalOs ? ' (parcial)' : ''),
            ':valor' => $valor,
            ':os_id' => $osId,
            ':usuario_id' => $usuarioId,
            ':data_pagamento' => $data,
            ':forma_pagamento' => $forma,
        ]);
        $financeiro->syncCardFeeForRevenue($receitaId);

        return $valor;
    }

    private function syncFinanceiroFromOs(int $osId, array $payload, string $numeroOs, float $totalPago = 0.0): void
    {
        $situacao = (string) ($payload[':situacao_pagamento'] ?? '');
        if ($osId <= 0 || !in_array($situacao, ['Pago', 'Parcial'], true)) {
            return;
        }

        $valorMaoObra = (float) ($payload[':valor_mao_obra'] ?? 0);
        $valorPecas = (float) ($payload[':valor_pecas'] ?? 0);
        $desconto = (float) ($payload[':desconto'] ?? 0);
        $valorTotal = max(0, $valorMaoObra + $valorPecas - $desconto);
        $financeiro = new \App\Models\FinanceiroModel();
        $usuarioId = current_user_id();
        $formaPagamento = (string) ($payload[':forma_pagamento'] ?? '');

        if ($valorTotal > 0 && $situacao === 'Pago' && $totalPago <= 0) {
            $this->model->addPagamento([
                ':os_id' => $osId,
                ':valor' => $valorTotal,
                ':forma_pagamento' => $formaPagamento,
                ':data_pagamento' => date('Y-m-d'),
                ':observacao' => 'Baixa automatica do valor total',
                ':usuario_id' => $usuarioId,
            ]);
            $receitaId = $financeiro->syncAutoEntryForOs($osId, [
                ':tipo' => 'Receita',
                ':categoria' => 'Pagamento OS',
                ':descricao' => 'Pagamento OS #' . $numeroOs,
                ':valor' => $valorTotal,
                ':os_id' => $osId,
                ':usuario_id' => $usuarioId,
                ':data_pagamento' => date('Y-m-d'),
                ':forma_pagamento' => $formaPagamento,
            ]);
            $financeiro->syncCardFeeForRevenue($receitaId);
        }

        if ($valorPecas > 0 && ($totalPago > 0 || $situacao === 'Pago')) {
            $financeiro->syncAutoEntryForOs($osId, [
                ':tipo' => 'Despesa',
                ':categoria' => 'Custo de Peças OS',
                ':descricao' => 'Peças utilizadas na OS #' . $numeroOs,
                ':valor' => $valorPecas,
                ':os_id' => $osId,
                ':usuario_id' => $usuarioId,
                ':data_pagamento' => date('Y-m-d'),
                ':forma_pagamento' => $formaPagamento,
            ]);
        }
    }

    private function uniquePrintLines(string $text): array
    {
        $lines = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim(preg_replace('/\s+/u', ' ', (string) $line) ?? (string) $line);
            if ($line === '') {
                continue;
            }

            $normalized = $this->normalizePrintLine($line);
            $isDuplicate = false;
            foreach ($lines as $existing) {
                $existingNormalized = $this->normalizePrintLine($existing);
                similar_text($normalized, $existingNormalized, $similarity);
                if ($normalized === $existingNormalized || $similarity >= 88) {
                    $isDuplicate = true;
                    break;
                }
            }

            if (!$isDuplicate) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    private function normalizePrintLine(string $line): string
    {
        $line = trim($line);
        if (function_exists('mb_strtolower')) {
            $line = mb_strtolower($line, 'UTF-8');
        } else {
            $line = strtolower($line);
        }

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $line);
        if (is_string($converted) && $converted !== '') {
            $line = $converted;
        }

        return preg_replace('/[^a-z0-9]+/', '', $line) ?? $line;
    }

    public function viewDetail()
    {
        $id = $_GET['id'] ?? 0;
        $os = $this->model->find($id);
        if (!$os) {
            $this->redirect(route_url('os'));
        }
        $historico = $this->model->getHistorico($id);
        $pagamentos = $this->model->getPagamentos($id);
        $totalPago = $this->model->totalPagamentos($id);
        $tecnicos = $this->tecnicoModel->getAtivos();
        $this->view('os/view', [
            'title' => 'OS #' . $os['numero_os'] . ' - Conectados',
            'page_title' => 'Ordem de Servico #' . $os['numero_os'],
            'os' => $os,
            'historico' => $historico,
            'pagamentos' => $pagamentos,
            'totalPago' => $totalPago,
            'paymentNonce' => issue_os_payment_nonce((int) $id),
            'pointOrder' => (new \App\Models\MercadoPagoPointModel())->latestForOs((int) $id),
            'pointSettings' => $this->configModel->getAll(),
            'tecnicos' => $tecnicos,
            'status_list' => os_status_list()
        ]);
    }

    public function print()
    {
        $id = $_GET['id'] ?? 0;
        $os = $this->model->find($id);
        if (!$os) {
            $this->redirect(route_url('os'));
        }

        $mode = normalize_print_mode($_GET['print'] ?? '80', '80');
        $showDetailedValues = (string) ($_GET['detalhada'] ?? '') === '1';
        $company = $this->buildCompanyData();
        $pagamentos = $this->model->getPagamentos($id);
        $totalPago = $this->model->totalPagamentos($id);
        $saldoRestante = max(0, (float) ($os['valor_total'] ?? 0) - $totalPago);

        $services = $this->uniquePrintLines((string) ($os['servico_realizar'] ?? ''));
        if (empty($services)) {
            $services = $this->uniquePrintLines((string) ($os['problema_relatado'] ?? 'Servico nao informado'));
        }

        $values = [
            ['label' => 'Mao de obra', 'amount' => (float) ($os['valor_mao_obra'] ?? 0)],
            ['label' => 'Pecas', 'amount' => (float) ($os['valor_pecas'] ?? 0)],
        ];
        if ((float) ($os['desconto'] ?? 0) > 0) {
            $values[] = ['label' => 'Desconto', 'amount' => -1 * (float) $os['desconto']];
        }

        $summary = [
            'number' => (string) $os['numero_os'],
            'created_at' => $os['created_at'] ?? null,
            'status' => (string) ($os['status'] ?? ''),
            'customer_name' => (string) ($os['cliente_nome'] ?? ''),
            'customer_phone' => (string) ($os['cliente_whatsapp'] ?: ($os['cliente_telefone'] ?? '')),
            'customer_address' => (string) ($os['cliente_endereco'] ?? ''),
            'equipment' => trim((string) (($os['marca'] ?? '') . ' ' . ($os['modelo'] ?? ''))),
            'address_short' => substr(trim((string) ($os['cliente_endereco'] ?? '')), 0, 60),
            'services' => $services,
            'values' => $showDetailedValues ? $values : [],
            'total' => (float) ($os['valor_total'] ?? 0),
            'paid_total' => $totalPago,
            'remaining_total' => $saldoRestante,
            'payments' => $pagamentos,
            'payment_method' => (string) ($os['forma_pagamento'] ?? ''),
            'notes' => (function() use ($os) {
                $estadoFisico = trim((string) ($os['estado_fisico'] ?? ''));
                // Remove the checklist block from free-text notes
                $clean = preg_replace('/Checklist de Entrada:[\s\S]*?(?=\n\n|$)/u', '', $estadoFisico);
                return trim(implode("\n", array_filter([
                    $os['diagnostico_tecnico'] ?? '',
                    trim($clean),
                ])));
            })(),
            'checklist' => (function() use ($os) {
                $estadoFisico = trim((string) ($os['estado_fisico'] ?? ''));
                if (!str_contains($estadoFisico, 'Checklist de Entrada:')) return [];
                $items = [];
                if (preg_match('/Checklist de Entrada:\n(.+?)(?=\n\n|$)/su', $estadoFisico, $m)) {
                    foreach (explode("\n", trim($m[1])) as $line) {
                        $line = ltrim($line, '- ');
                        if (str_contains($line, ':')) {
                            [$label, $state] = array_map('trim', explode(':', $line, 2));
                            $items[] = ['label' => $label, 'state' => $state];
                        }
                    }
                }
                return $items;
            })(),
            'final_message' => 'Obrigado pela preferencia',
        ];

        $this->renderPrintDocument($mode, [
            'printMode' => $mode,
            'printContext' => 'os',
            'company' => $company,
            'documentTitle' => 'OS #' . $os['numero_os'],
            'os' => $os,
            'printData' => $summary,
        ]);
    }

    public function pointStatus()
    {
        session_write_close();
        header('Content-Type: application/json; charset=utf-8');
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'status' => 'error']);
            return;
        }

        $latest = (new \App\Models\MercadoPagoPointModel())->syncLatestForOs($id);
        $status = strtolower((string) ($latest['status'] ?? 'unknown'));
        $paid = in_array($status, ['paid', 'approved', 'finished', 'processed'], true);

        echo json_encode([
            'ok' => true,
            'status' => $status,
            'paid' => $paid,
            'order_id' => $latest['mp_order_id'] ?? null,
            'payment_id' => $latest['payment_id'] ?? null,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
