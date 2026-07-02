<?php

namespace App\Controllers;

use App\Core\Controller;

class VitrineController extends Controller
{
    private $model;
    private $configModel;
    private $contaModel;

    public function __construct()
    {
        $this->model = new \App\Models\EstoqueModel();
        $this->configModel = new \App\Models\ConfigModel();
        $this->contaModel = new \App\Models\ContaPublicaModel();
    }

    public function index()
    {
        $settings = $this->configModel->getAll();
        $search = $_GET['search'] ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $produtos = $this->model->getAll($search, $categoria, 'produto');
        $lancamentos = $this->model->getRecent('produto', 4);
        $categorias = $this->model->getCategorias('produto');

        $this->view('vitrine/index', [
            'settings' => $settings,
            'title' => 'Conectados - Assistencia Tecnica e Catalogo',
            'page_title' => 'Conectados',
            'show_public_intro' => false,
            'produtos' => $produtos,
            'lancamentos' => $lancamentos,
            'categorias' => $categorias,
            'filters' => ['search' => $search, 'categoria' => $categoria]
        ]);
    }

    public function catalogo()
    {
        $settings = $this->configModel->getAll();
        $search = $_GET['search'] ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $produtos = $this->model->getAll($search, $categoria, 'produto');
        $categorias = $this->model->getCategorias('produto');

        $this->view('vitrine/catalogo', [
            'settings' => $settings,
            'title' => 'Catalogo de Produtos - Conectados',
            'page_title' => 'Nosso Catalogo',
            'show_public_intro' => false,
            'produtos' => $produtos,
            'categorias' => $categorias,
            'filters' => ['search' => $search, 'categoria' => $categoria]
        ]);
    }

    public function papelaria()
    {
        $settings = $this->configModel->getAll();
        $search = trim((string) ($_GET['search'] ?? ''));
        $categoria = trim((string) ($_GET['categoria'] ?? ''));
        $allProducts = $this->model->getAll('', '', 'produto');
        $keywords = ['papelaria', 'caderno', 'agenda', 'caneta', 'lapis', 'borracha', 'estojo', 'mochila', 'cola', 'papel', 'cartolina', 'marca texto'];

        $stationeryProducts = array_values(array_filter($allProducts, static function ($product) use ($keywords) {
            $haystack = strtolower(trim((string) ($product['categoria'] ?? '') . ' ' . (string) ($product['nome'] ?? '')));
            foreach ($keywords as $keyword) {
                if (strpos($haystack, $keyword) !== false) {
                    return true;
                }
            }
            return false;
        }));

        $categorias = [];
        foreach ($stationeryProducts as $product) {
            $cat = trim((string) ($product['categoria'] ?? ''));
            if ($cat !== '') {
                $categorias[$cat] = $cat;
            }
        }
        ksort($categorias, SORT_NATURAL | SORT_FLAG_CASE);

        if ($search !== '') {
            $needle = strtolower($search);
            $stationeryProducts = array_values(array_filter($stationeryProducts, static function ($product) use ($needle) {
                return strpos(strtolower((string) ($product['nome'] ?? '')), $needle) !== false
                    || strpos(strtolower((string) ($product['codigo_interno'] ?? '')), $needle) !== false;
            }));
        }

        if ($categoria !== '') {
            $stationeryProducts = array_values(array_filter($stationeryProducts, static function ($product) use ($categoria) {
                return strcasecmp((string) ($product['categoria'] ?? ''), $categoria) === 0;
            }));
        }

        $this->view('vitrine/papelaria', [
            'settings' => $settings,
            'title' => 'Papelaria - Conectados',
            'page_title' => 'Papelaria',
            'show_public_intro' => false,
            'produtos' => $stationeryProducts,
            'categorias' => array_values($categorias),
            'filters' => ['search' => $search, 'categoria' => $categoria]
        ]);
    }

    public function cadastrarConta()
    {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $senha = (string) ($_POST['senha'] ?? '');

        if ($nome === '' || $email === '' || $senha === '') {
            $this->redirect(route_url('vitrine', ['account_error' => 1]));
        }

        if ($this->contaModel->findByEmail($email)) {
            $this->redirect(route_url('vitrine', ['account_error' => 'exists']));
        }

        $this->contaModel->create([
            ':nome' => $nome,
            ':email' => $email,
            ':whatsapp' => $whatsapp !== '' ? $whatsapp : null,
            ':senha' => password_hash($senha, PASSWORD_BCRYPT),
        ]);

        $_SESSION['conta_publica_nome'] = $nome;
        $_SESSION['conta_publica_email'] = $email;

        $this->redirect(route_url('vitrine', ['account_success' => 1]));
    }

    public function produto($id = null)
    {
        $id = (int) ($id ?? $_GET['id'] ?? 0);
        $produto = $this->model->find($id);
        
        if (!$produto || $produto['tipo'] !== 'produto') {
            $this->redirect(route_url('vitrine/catalogo'));
        }

        $settings = $this->configModel->getAll();
        $relacionados = $this->model->getRecent('produto', 4); // Exibir outros itens como sugestão

        $this->view('vitrine/produto', [
            'settings' => $settings,
            'produto' => $produto,
            'galleryImages' => $this->model->getImagesForProduct((int) $produto['id']),
            'relacionados' => $relacionados,
            'title' => $produto['nome'] . ' - Conectados',
        ]);
    }

    public function sairConta()
    {
        unset($_SESSION['conta_publica_nome'], $_SESSION['conta_publica_email']);
        $this->redirect(route_url('vitrine'));
    }

    public function checkoutMercadoPago()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Metodo nao permitido.']);
            return;
        }

        $settings = $this->configModel->getAll();
        $accessToken = trim((string) ($settings['mercadopago_access_token'] ?? ''));
        if ($accessToken === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Checkout Mercado Pago sem access token configurado.']);
            return;
        }

        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        $cart = is_array($payload['items'] ?? null) ? $payload['items'] : [];

        if (empty($cart)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Carrinho vazio.']);
            return;
        }

        $items = [];
        foreach ($cart as $item) {
            $id = (int) ($item['id'] ?? 0);
            $quantity = max(1, (int) ($item['qtd'] ?? 1));

            if ($id <= 0) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'message' => 'Produto invalido no carrinho.']);
                return;
            }

            $produto = $this->model->find($id);
            if (!$produto || ($produto['tipo'] ?? '') !== 'produto') {
                http_response_code(422);
                echo json_encode(['ok' => false, 'message' => 'Produto indisponivel no carrinho.']);
                return;
            }

            if ((float) ($produto['preco_venda'] ?? 0) <= 0) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'message' => 'Produto sem preco valido para checkout.']);
                return;
            }

            if ((int) ($produto['quantidade'] ?? 0) < $quantity) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'message' => 'Estoque insuficiente para ' . $produto['nome'] . '.']);
                return;
            }

            $safeTitle = trim((string) ($produto['nome'] ?? 'Produto Conectados')) ?: 'Produto Conectados';
            $safeTitle = function_exists('mb_substr') ? mb_substr($safeTitle, 0, 120) : substr($safeTitle, 0, 120);

            $items[] = [
                'id' => (string) $id,
                'title' => $safeTitle,
                'quantity' => $quantity,
                'currency_id' => 'BRL',
                'unit_price' => round((float) $produto['preco_venda'], 2),
            ];
        }

        if (empty($items)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Itens invalidos no carrinho.']);
            return;
        }

        $externalReference = 'conectados-' . date('YmdHis') . '-' . substr(sha1(json_encode($items) . microtime(true)), 0, 8);

        $preference = [
            'items' => $items,
            'external_reference' => $externalReference,
            'notification_url' => absolute_route_url('vitrine/statusMercadoPago', ['ref' => $externalReference]),
            'back_urls' => [
                'success' => absolute_route_url('vitrine/catalogo', ['checkout' => 'success']),
                'failure' => absolute_route_url('vitrine/catalogo', ['checkout' => 'failure']),
                'pending' => absolute_route_url('vitrine/catalogo', ['checkout' => 'pending']),
            ],
            'auto_return' => 'approved',
        ];

        $response = $this->postMercadoPagoPreference($accessToken, $preference);
        if (!$response['ok']) {
            http_response_code(502);
            echo json_encode([
                'ok' => false,
                'message' => $response['message'] ?? 'Falha ao criar checkout Mercado Pago.',
            ]);
            return;
        }

        $preferenceId = (string) ($response['data']['id'] ?? '');
        $this->saveMercadoPagoOrder($externalReference, $preferenceId, $items);

        echo json_encode([
            'ok' => true,
            'init_point' => $response['data']['init_point'] ?? $response['data']['sandbox_init_point'] ?? '',
            'external_reference' => $externalReference,
            'preference_id' => $preferenceId,
        ]);
    }

    public function statusMercadoPago()
    {
        header('Content-Type: application/json; charset=utf-8');

        $settings = $this->configModel->getAll();
        $accessToken = trim((string) ($settings['mercadopago_access_token'] ?? ''));
        $reference = trim((string) ($_GET['ref'] ?? ''));

        if ($accessToken === '' || $reference === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'status' => 'unknown', 'message' => 'Referencia de pagamento indisponivel.']);
            return;
        }

        $url = 'https://api.mercadopago.com/v1/payments/search?sort=date_created&criteria=desc&external_reference=' . rawurlencode($reference);
        $response = $this->getMercadoPago($accessToken, $url);

        if (!$response['ok']) {
            http_response_code(502);
            echo json_encode(['ok' => false, 'status' => 'unknown', 'message' => $response['message'] ?? 'Nao foi possivel consultar o pagamento.']);
            return;
        }

        $payments = is_array($response['data']['results'] ?? null) ? $response['data']['results'] : [];
        $status = 'pending';
        $detail = '';
        $paymentId = null;

        foreach ($payments as $payment) {
            $paymentStatus = (string) ($payment['status'] ?? '');
            $paymentId = $payment['id'] ?? $paymentId;
            $detail = (string) ($payment['status_detail'] ?? $detail);

            if ($paymentStatus === 'approved') {
                $status = 'approved';
                break;
            }

            if (in_array($paymentStatus, ['rejected', 'cancelled', 'refunded', 'charged_back'], true)) {
                $status = $paymentStatus;
                break;
            }

            if (in_array($paymentStatus, ['in_process', 'pending', 'authorized'], true)) {
                $status = $paymentStatus;
            }
        }

        $this->updateMercadoPagoOrderStatus($reference, $status, $detail, $paymentId);
        $this->logMercadoPago($reference, $paymentId, $status, 'Consulta/retorno Mercado Pago processado.', $payments);
        if ($status === 'approved') {
            try {
                $this->processApprovedSaleOnce($reference, $paymentId, $detail, $settings);
            } catch (\Throwable $e) {
                $db = \App\Config\Database::getInstance();
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                app_log('Falha ao processar baixa Mercado Pago', ['ref' => $reference, 'erro' => $e->getMessage()]);
                $this->logMercadoPago($reference, $paymentId, $status, $e->getMessage(), $payments);
            }
        }

        echo json_encode([
            'ok' => true,
            'status' => $status,
            'status_detail' => $detail,
            'payment_id' => $paymentId,
        ]);
    }

    private function saveMercadoPagoOrder(string $reference, string $preferenceId, array $items): void
    {
        $total = 0.0;
        foreach ($items as $item) {
            $total += ((float) ($item['unit_price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }

        $db = \App\Config\Database::getInstance();
        $stmt = $db->prepare("INSERT INTO mercado_pago_pedidos
            (external_reference, preference_id, items_json, total, customer_name, customer_email)
            VALUES (:ref, :preference_id, :items_json, :total, :customer_name, :customer_email)
            ON DUPLICATE KEY UPDATE
                preference_id = VALUES(preference_id),
                items_json = VALUES(items_json),
                total = VALUES(total),
                customer_name = VALUES(customer_name),
                customer_email = VALUES(customer_email)");
        $stmt->execute([
            ':ref' => $reference,
            ':preference_id' => $preferenceId !== '' ? $preferenceId : null,
            ':items_json' => json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':total' => round($total, 2),
            ':customer_name' => $_SESSION['conta_publica_nome'] ?? null,
            ':customer_email' => $_SESSION['conta_publica_email'] ?? null,
        ]);
    }

    private function updateMercadoPagoOrderStatus(string $reference, string $status, string $detail, $paymentId): void
    {
        $db = \App\Config\Database::getInstance();
        $stmt = $db->prepare("UPDATE mercado_pago_pedidos
            SET status = :status,
                status_detail = :status_detail,
                payment_id = COALESCE(:payment_id, payment_id)
            WHERE external_reference = :ref");
        $stmt->execute([
            ':status' => $status,
            ':status_detail' => $detail !== '' ? $detail : null,
            ':payment_id' => $paymentId !== null ? (string) $paymentId : null,
            ':ref' => $reference,
        ]);
    }

    private function logMercadoPago(string $reference, $paymentId, string $status, string $message, array $payload = []): void
    {
        $db = \App\Config\Database::getInstance();
        $stmt = $db->prepare("INSERT INTO mercado_pago_logs (external_reference, payment_id, status, mensagem, payload)
            VALUES (:ref, :payment_id, :status, :mensagem, :payload)");
        $stmt->execute([
            ':ref' => $reference,
            ':payment_id' => $paymentId !== null ? (string) $paymentId : null,
            ':status' => $status,
            ':mensagem' => $message,
            ':payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function processApprovedSaleOnce(string $reference, $paymentId, string $detail, array $settings = []): void
    {
        $db = \App\Config\Database::getInstance();
        $db->beginTransaction();

        $stmt = $db->prepare("SELECT * FROM mercado_pago_pedidos WHERE external_reference = :ref FOR UPDATE");
        $stmt->execute([':ref' => $reference]);
        $order = $stmt->fetch();

        if (!$order) {
            $db->commit();
            return;
        }

        $items = json_decode((string) ($order['items_json'] ?? '[]'), true);
        if (is_array($items) && empty($order['estoque_baixado_at'])) {
            $stockUpdate = $db->prepare("UPDATE estoque
                SET quantidade = quantidade - :quantidade
                WHERE id = :id AND tipo = 'produto' AND quantidade >= :quantidade");
            $movement = $db->prepare("INSERT INTO estoque_movimentacoes
                (produto_id, tipo, quantidade, motivo, os_id, usuario_id)
                VALUES (:produto_id, 'saida', :quantidade, :motivo, NULL, NULL)");

            foreach ($items as $item) {
                $productId = (int) ($item['id'] ?? 0);
                $quantity = max(1, (int) ($item['quantity'] ?? 1));

                if ($productId <= 0) {
                    continue;
                }

                $stockUpdate->execute([
                    ':quantidade' => $quantity,
                    ':id' => $productId,
                ]);

                if ($stockUpdate->rowCount() === 0) {
                    throw new \RuntimeException('Estoque insuficiente ao processar venda Mercado Pago: produto ' . $productId);
                }

                $movement->execute([
                    ':produto_id' => $productId,
                    ':quantidade' => $quantity,
                    ':motivo' => 'Venda Mercado Pago ' . $reference,
                ]);
            }

            $markStock = $db->prepare("UPDATE mercado_pago_pedidos SET estoque_baixado_at = NOW() WHERE external_reference = :ref");
            $markStock->execute([':ref' => $reference]);
        }

        if (empty($order['financeiro_lancado_at'])) {
            $existingFinance = $db->prepare("SELECT id FROM financeiro
                WHERE tipo = 'Receita'
                  AND categoria = 'Venda Site (Mercado Pago)'
                  AND descricao LIKE :lookup
                LIMIT 1");
            $existingFinance->execute([':lookup' => '%' . $reference . '%']);

            if (!$existingFinance->fetch()) {
                $descriptionParts = ['Venda site Mercado Pago ' . $reference];
                if ($paymentId !== null) {
                    $descriptionParts[] = 'Pagamento ' . (string) $paymentId;
                }

                $financeiro = new \App\Models\FinanceiroModel();
                $financeiro->create([
                    ':tipo' => 'Receita',
                    ':categoria' => 'Venda Site (Mercado Pago)',
                    ':descricao' => implode(' - ', $descriptionParts),
                    ':valor' => (float) ($order['total'] ?? 0),
                    ':os_id' => null,
                    ':usuario_id' => null,
                    ':data_pagamento' => date('Y-m-d'),
                    ':forma_pagamento' => 'Mercado Pago',
                ]);
            }

            $markFinance = $db->prepare("UPDATE mercado_pago_pedidos SET financeiro_lancado_at = NOW() WHERE external_reference = :ref");
            $markFinance->execute([':ref' => $reference]);
        }

        if (!empty($order['email_sent_at'])) {
            $db->commit();
            return;
        }

        $lines = [];
        if (is_array($items)) {
            foreach ($items as $item) {
                $title = (string) ($item['title'] ?? 'Produto');
                $quantity = (int) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $lines[] = "- {$quantity}x {$title} - R$ " . number_format($unitPrice * $quantity, 2, ',', '.');
            }
        }

        $companyName = trim(preg_replace('/[\r\n<>]+/', ' ', (string) ($settings['nome_empresa'] ?? 'Conectados'))) ?: 'Conectados';
        $businessEmail = $this->validBusinessEmail((string) ($settings['email_negocio'] ?? '')) ?: 'contato@conectadosassistencia.com.br';
        $subject = 'Venda aprovada - ' . $companyName;
        $message = implode("\n", array_filter([
            'Uma venda foi aprovada pelo Mercado Pago.',
            '',
            'Referencia: ' . $reference,
            'Pagamento Mercado Pago: ' . ($paymentId !== null ? (string) $paymentId : 'nao informado'),
            'Status: aprovado' . ($detail !== '' ? " ({$detail})" : ''),
            'Total: R$ ' . number_format((float) ($order['total'] ?? 0), 2, ',', '.'),
            '',
            'Itens:',
            implode("\n", $lines),
            '',
            !empty($order['customer_name']) ? 'Cliente: ' . $order['customer_name'] : '',
            !empty($order['customer_email']) ? 'E-mail do cliente: ' . $order['customer_email'] : '',
            '',
            'Data: ' . date('d/m/Y H:i:s'),
        ]));

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $companyName . ' <' . $businessEmail . '>',
            'Reply-To: ' . $businessEmail,
        ];

        $sent = function_exists('mail') && mail($businessEmail, $subject, $message, implode("\r\n", $headers));
        if ($sent) {
            $update = $db->prepare("UPDATE mercado_pago_pedidos SET email_sent_at = NOW() WHERE external_reference = :ref");
            $update->execute([':ref' => $reference]);
        } else {
            error_log('Falha ao enviar e-mail de venda aprovada: ' . $reference);
        }

        $db->commit();
    }

    private function validBusinessEmail(string $email): string
    {
        $email = trim($email);
        if ($email === '' || preg_match('/[\r\n]/', $email)) {
            return '';
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }

    private function postMercadoPagoPreference(string $accessToken, array $preference): array
    {
        $body = json_encode($preference, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (function_exists('curl_init')) {
            $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 18,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_POSTFIELDS => $body,
            ]);

            $raw = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($raw === false || $raw === '') {
                return ['ok' => false, 'message' => $error ?: 'Mercado Pago nao respondeu.'];
            }

            $data = json_decode($raw, true);
            if ($status >= 200 && $status < 300 && is_array($data)) {
                return ['ok' => true, 'data' => $data];
            }

            return ['ok' => false, 'message' => $data['message'] ?? 'Mercado Pago recusou a preferencia.'];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => 18,
                'header' => "Authorization: Bearer {$accessToken}\r\nContent-Type: application/json\r\nAccept: application/json\r\n",
                'content' => $body,
                'ignore_errors' => true,
            ],
        ]);
        $raw = @file_get_contents('https://api.mercadopago.com/checkout/preferences', false, $context);
        $data = is_string($raw) ? json_decode($raw, true) : null;

        return is_array($data) && !empty($data['init_point'])
            ? ['ok' => true, 'data' => $data]
            : ['ok' => false, 'message' => is_array($data) ? ($data['message'] ?? 'Falha no Mercado Pago.') : 'Mercado Pago nao respondeu.'];
    }

    private function getMercadoPago(string $accessToken, string $url): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 18,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Accept: application/json',
                ],
            ]);

            $raw = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($raw === false || $raw === '') {
                return ['ok' => false, 'message' => $error ?: 'Mercado Pago nao respondeu.'];
            }

            $data = json_decode($raw, true);
            return $status >= 200 && $status < 300 && is_array($data)
                ? ['ok' => true, 'data' => $data]
                : ['ok' => false, 'message' => is_array($data) ? ($data['message'] ?? 'Falha no Mercado Pago.') : 'Falha no Mercado Pago.'];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 18,
                'header' => "Authorization: Bearer {$accessToken}\r\nAccept: application/json\r\n",
                'ignore_errors' => true,
            ],
        ]);
        $raw = @file_get_contents($url, false, $context);
        $data = is_string($raw) ? json_decode($raw, true) : null;

        return is_array($data)
            ? ['ok' => true, 'data' => $data]
            : ['ok' => false, 'message' => 'Mercado Pago nao respondeu.'];
    }
}
