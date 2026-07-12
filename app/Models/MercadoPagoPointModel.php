<?php
namespace App\Models;

use App\Config\Database;

class MercadoPagoPointModel
{
    private const API_BASE = 'https://api.mercadopago.com';

    private $db;
    private ConfigModel $configModel;

    public function __construct(?\PDO $db = null)
    {
        $this->db = $db ?: Database::getInstance();
        \App\Core\RequiredSchema::assert($this->db, 'Mercado Pago Point', [
            'mercado_pago_point_orders' => [
                'id', 'os_id', 'pdv_venda_id', 'external_reference', 'status',
                'amount', 'payload', 'financeiro_lancado_at',
            ],
        ], [
            'mercado_pago_point_orders' => ['idx_mp_point_orders_pdv', 'idx_mp_point_orders_status'],
        ]);
        $this->configModel = new ConfigModel();
    }

    public function createOrderForOs(array $os, array $options = []): array
    {
        $settings = $this->configModel->getAll();
        $accessToken = trim((string) ($settings['mercadopago_access_token'] ?? ''));
        $terminalId = trim((string) ($settings['mercadopago_point_terminal_id'] ?? ''));

        if ($accessToken === '') {
            throw new \RuntimeException('Informe o Access Token do Mercado Pago em Configuracoes > Pagamentos.');
        }
        if ($terminalId === '') {
            throw new \RuntimeException('Informe o Terminal ID da Smart Point em Configuracoes > Pagamentos.');
        }

        $amount = round((float) ($options['amount'] ?? $os['valor_total'] ?? 0), 2);
        if ($amount <= 0) {
            throw new \RuntimeException('A OS precisa ter valor maior que zero para enviar a cobranca.');
        }

        $externalReference = $this->buildExternalReference((int) $os['id'], (string) $os['numero_os']);
        $paymentType = $this->normalizePaymentType((string) ($options['payment_type'] ?? $settings['mercadopago_point_default_payment_type'] ?? 'credit_card'));
        $installments = max(1, min(12, (int) ($options['installments'] ?? $settings['mercadopago_point_default_installments'] ?? 1)));
        $installmentsCost = (string) ($settings['mercadopago_point_installments_cost'] ?? 'seller');
        $installmentsCost = in_array($installmentsCost, ['seller', 'buyer'], true) ? $installmentsCost : 'seller';

        $payload = [
            'type' => 'point',
            'external_reference' => $externalReference,
            'expiration_time' => 'PT16M',
            'transactions' => [
                'payments' => [
                    [
                        'amount' => number_format($amount, 2, '.', ''),
                    ],
                ],
            ],
            'config' => [
                'point' => [
                    'terminal_id' => $terminalId,
                ],
            ],
            'description' => $this->limitText('OS #' . (string) $os['numero_os'] . ' - ' . (string) ($os['cliente_nome'] ?? 'Cliente'), 120),
        ];

        if (in_array($paymentType, ['credit_card', 'debit_card', 'qr_code'], true)) {
            $payload['config']['payment_method'] = [
                'default_type' => $paymentType === 'qr_code' ? 'debit_card' : $paymentType,
            ];
        }

        if ($paymentType === 'credit_card') {
            $payload['config']['payment_method']['default_installments'] = $installments;
            $payload['config']['payment_method']['installments_cost'] = $installmentsCost;
        }

        $response = $this->request('POST', '/v1/orders', $payload, $accessToken, $this->idempotencyKey($externalReference));
        $orderId = (string) ($response['id'] ?? '');
        if ($orderId === '') {
            throw new \RuntimeException('Mercado Pago nao retornou o ID da order.');
        }

        $this->saveLocalOrder([
            ':os_id' => (int) $os['id'],
            ':pdv_venda_id' => null,
            ':numero_os' => (string) $os['numero_os'],
            ':external_reference' => $externalReference,
            ':mp_order_id' => $orderId,
            ':payment_id' => $this->paymentIdFromOrder($response),
            ':status' => (string) ($response['status'] ?? 'created'),
            ':status_detail' => (string) ($response['status_detail'] ?? ''),
            ':amount' => $amount,
            ':payment_method' => $this->paymentMethodLabel($paymentType, $installments),
            ':terminal_id' => $terminalId,
            ':payload' => $this->safePayloadJson($response),
        ]);

        $this->log($externalReference, $orderId, $this->paymentIdFromOrder($response), (string) ($response['status'] ?? 'created'), 'Order enviada para Smart Point.', $response);

        return [
            'external_reference' => $externalReference,
            'order_id' => $orderId,
            'status' => (string) ($response['status'] ?? 'created'),
            'terminal_id' => $terminalId,
        ];
    }

    public function createOrderForPdv(array $venda, array $options = []): array
    {
        $settings = $this->configModel->getAll();
        $accessToken = trim((string) ($settings['mercadopago_access_token'] ?? ''));
        $terminalId = trim((string) ($settings['mercadopago_point_terminal_id'] ?? ''));

        if ($accessToken === '') {
            throw new \RuntimeException('Informe o Access Token do Mercado Pago em Configuracoes > Pagamentos.');
        }
        if ($terminalId === '') {
            throw new \RuntimeException('Informe o Terminal ID da Smart Point em Configuracoes > Pagamentos.');
        }

        $amount = round((float) ($options['amount'] ?? $venda['total'] ?? 0), 2);
        if ($amount <= 0) {
            throw new \RuntimeException('A venda precisa ter valor maior que zero para enviar a cobranca.');
        }

        $externalReference = $this->buildPdvExternalReference((int) $venda['id'], (string) $venda['numero_venda']);
        $paymentType = $this->normalizePaymentType((string) ($options['payment_type'] ?? $settings['mercadopago_point_default_payment_type'] ?? 'credit_card'));
        $installments = max(1, min(12, (int) ($options['installments'] ?? $settings['mercadopago_point_default_installments'] ?? 1)));
        $installmentsCost = (string) ($settings['mercadopago_point_installments_cost'] ?? 'seller');
        $installmentsCost = in_array($installmentsCost, ['seller', 'buyer'], true) ? $installmentsCost : 'seller';

        $payload = [
            'type' => 'point',
            'external_reference' => $externalReference,
            'expiration_time' => 'PT16M',
            'transactions' => [
                'payments' => [
                    [
                        'amount' => number_format($amount, 2, '.', ''),
                    ],
                ],
            ],
            'config' => [
                'point' => [
                    'terminal_id' => $terminalId,
                ],
            ],
            'description' => $this->limitText('Venda PDV ' . (string) $venda['numero_venda'], 120),
        ];

        if (in_array($paymentType, ['credit_card', 'debit_card', 'qr_code'], true)) {
            $payload['config']['payment_method'] = [
                'default_type' => $paymentType === 'qr_code' ? 'debit_card' : $paymentType,
            ];
        }

        if ($paymentType === 'credit_card') {
            $payload['config']['payment_method']['default_installments'] = $installments;
            $payload['config']['payment_method']['installments_cost'] = $installmentsCost;
        }

        $response = $this->request('POST', '/v1/orders', $payload, $accessToken, $this->idempotencyKey($externalReference));
        $orderId = (string) ($response['id'] ?? '');
        if ($orderId === '') {
            throw new \RuntimeException('Mercado Pago nao retornou o ID da order.');
        }

        $this->saveLocalOrder([
            ':os_id' => null,
            ':pdv_venda_id' => (int) $venda['id'],
            ':numero_os' => null,
            ':external_reference' => $externalReference,
            ':mp_order_id' => $orderId,
            ':payment_id' => $this->paymentIdFromOrder($response),
            ':status' => (string) ($response['status'] ?? 'created'),
            ':status_detail' => (string) ($response['status_detail'] ?? ''),
            ':amount' => $amount,
            ':payment_method' => $this->paymentMethodLabel($paymentType, $installments),
            ':terminal_id' => $terminalId,
            ':payload' => $this->safePayloadJson($response),
        ]);

        $this->log($externalReference, $orderId, $this->paymentIdFromOrder($response), (string) ($response['status'] ?? 'created'), 'Venda PDV enviada para Smart Point.', $response);

        return [
            'external_reference' => $externalReference,
            'order_id' => $orderId,
            'status' => (string) ($response['status'] ?? 'created'),
            'terminal_id' => $terminalId,
        ];
    }

    public function processWebhook(array $payload): array
    {
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $orderId = (string) ($data['id'] ?? $payload['id'] ?? '');
        $externalReference = (string) ($data['external_reference'] ?? $payload['external_reference'] ?? '');
        $status = (string) ($data['status'] ?? '');
        $detail = (string) ($data['status_detail'] ?? '');
        $paymentId = $this->paymentIdFromOrder($payload);

        if ($orderId === '' && $externalReference === '') {
            $this->log('', null, null, 'ignored', 'Webhook Point sem identificacao de order.', $payload);
            return ['ok' => true, 'ignored' => true];
        }

        if ($externalReference === '' && $orderId !== '') {
            $local = $this->findByOrderId($orderId);
            $externalReference = (string) ($local['external_reference'] ?? '');
        }

        $order = $payload;
        if ($orderId !== '') {
            $order = $this->getOrder($orderId);
            $status = (string) ($order['status'] ?? '');
            $detail = (string) ($order['status_detail'] ?? '');
            $externalReference = (string) ($order['external_reference'] ?? $externalReference);
            $paymentId = $this->paymentIdFromOrder($order) ?: $paymentId;
        }

        $local = $externalReference !== '' ? $this->findByReference($externalReference) : null;
        if (!$local && $orderId !== '') {
            $local = $this->findByOrderId($orderId);
        }

        $this->updateLocalStatus($externalReference, $orderId, $paymentId, $status, $detail, $order);
        $this->log($externalReference, $orderId, $paymentId, $status, 'Webhook Point processado.', $payload);

        if ($orderId !== '' && $this->isApprovedOrder($order, $status)) {
            $this->approveLocalOrderOnce($externalReference, $orderId, $paymentId, $order);
        }

        return ['ok' => true, 'status' => $status, 'external_reference' => $externalReference, 'local' => (bool) $local];
    }

    public function latestForOs(int $osId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM mercado_pago_point_orders WHERE os_id = :os_id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':os_id' => $osId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function latestForPdv(int $vendaId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM mercado_pago_point_orders WHERE pdv_venda_id = :venda_id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':venda_id' => $vendaId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function syncLatestForOs(int $osId): ?array
    {
        return $this->syncLatestOrder($this->latestForOs($osId), function () use ($osId) {
            return $this->latestForOs($osId);
        });
    }

    public function syncLatestForPdv(int $vendaId): ?array
    {
        return $this->syncLatestOrder($this->latestForPdv($vendaId), function () use ($vendaId) {
            return $this->latestForPdv($vendaId);
        });
    }

    public function webhookUrl(): string
    {
        return absolute_route_url('mercadopago/pointWebhook');
    }

    private function syncLatestOrder(?array $latest, callable $reload): ?array
    {
        if (!$latest) {
            return null;
        }

        $currentStatus = strtolower((string) ($latest['status'] ?? ''));
        if ($this->isApprovedStatus($currentStatus)) {
            return $latest;
        }

        $orderId = trim((string) ($latest['mp_order_id'] ?? ''));
        if ($orderId === '') {
            return $latest;
        }

        try {
            $order = $this->getOrder($orderId);
            $status = (string) ($order['status'] ?? ($latest['status'] ?? ''));
            $detail = (string) ($order['status_detail'] ?? ($latest['status_detail'] ?? ''));
            $externalReference = (string) ($order['external_reference'] ?? ($latest['external_reference'] ?? ''));
            $paymentId = $this->paymentIdFromOrder($order);
            if ($paymentId === null && !empty($latest['payment_id'])) {
                $paymentId = (string) $latest['payment_id'];
            }

            $isApproved = $this->isApprovedOrder($order, $status);
            $statusChanged = $status !== (string) ($latest['status'] ?? '')
                || $detail !== (string) ($latest['status_detail'] ?? '')
                || ($paymentId !== null && $paymentId !== (string) ($latest['payment_id'] ?? ''));

            $this->updateLocalStatus($externalReference, $orderId, $paymentId, $status, $detail, $order);
            if ($statusChanged || $isApproved) {
                $this->log($externalReference, $orderId, $paymentId, $status, 'Consulta Point pela tela do sistema.', $order);
            }

            if ($isApproved) {
                $this->approveLocalOrderOnce($externalReference, $orderId, $paymentId, $order);
            }

            return $reload();
        } catch (\Throwable $e) {
            app_log('Falha ao sincronizar order Point pela tela', [
                'order_id' => $orderId,
                'erro' => $e->getMessage(),
            ]);
            return $latest;
        }
    }

    private function approveLocalOrderOnce(string $externalReference, string $orderId, ?string $paymentId, array $order): void
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $local = null;
            if ($externalReference !== '') {
                $stmt = $db->prepare("SELECT * FROM mercado_pago_point_orders WHERE external_reference = :ref FOR UPDATE");
                $stmt->execute([':ref' => $externalReference]);
                $local = $stmt->fetch() ?: null;
            }
            if (!$local && $orderId !== '') {
                $stmt = $db->prepare("SELECT * FROM mercado_pago_point_orders WHERE mp_order_id = :order_id FOR UPDATE");
                $stmt->execute([':order_id' => $orderId]);
                $local = $stmt->fetch() ?: null;
            }

            if (!$local) {
                $db->commit();
                return;
            }

            $osId = (int) ($local['os_id'] ?? 0);
            $pdvVendaId = (int) ($local['pdv_venda_id'] ?? 0);
            $amount = round((float) ($local['amount'] ?? $this->paidAmountFromOrder($order)), 2);
            $paymentMethod = (string) ($local['payment_method'] ?? 'Mercado Pago Point');
            if (($osId <= 0 && $pdvVendaId <= 0) || $amount <= 0 || !empty($local['financeiro_lancado_at'])) {
                $db->commit();
                return;
            }

            if ($pdvVendaId > 0) {
                $this->approvePdvOrder($pdvVendaId, $paymentMethod, $paymentId ?: $orderId);
                $db->prepare("UPDATE mercado_pago_point_orders
                    SET financeiro_lancado_at = NOW(), payment_id = COALESCE(:payment_id, payment_id), status = 'processed'
                    WHERE id = :id")
                    ->execute([':payment_id' => $paymentId, ':id' => (int) $local['id']]);
                $db->commit();
                return;
            }

            $osModel = new OsModel();
            $numeroOs = (string) ($local['numero_os'] ?? '');

            $osModel->addPagamento([
                ':os_id' => $osId,
                ':valor' => $amount,
                ':forma_pagamento' => $paymentMethod,
                ':data_pagamento' => date('Y-m-d'),
                ':observacao' => 'Mercado Pago Point ' . ($paymentId ?: $orderId),
                ':usuario_id' => null,
            ]);

            $financeiro = new FinanceiroModel();
            $revenueId = (int) $financeiro->create([
                ':tipo' => 'Receita',
                ':categoria' => 'Pagamento OS',
                ':descricao' => 'Pagamento OS #' . $numeroOs . ' - Mercado Pago Point ' . ($paymentId ?: $orderId),
                ':valor' => $amount,
                ':os_id' => $osId,
                ':usuario_id' => null,
                ':data_pagamento' => date('Y-m-d'),
                ':forma_pagamento' => $paymentMethod,
            ]);
            $financeiro->syncCardFeeForRevenue($revenueId);

            $totalPago = $osModel->totalPagamentos($osId);
            $stmt = $db->prepare("SELECT valor_total FROM ordens_servico WHERE id = :id");
            $stmt->execute([':id' => $osId]);
            $totalOs = (float) ($stmt->fetchColumn() ?: 0);
            $situacao = ($totalOs > 0 && $totalPago + 0.01 >= $totalOs) ? 'Pago' : 'Parcial';
            $db->prepare("UPDATE ordens_servico SET situacao_pagamento = :situacao, forma_pagamento = :forma WHERE id = :id")
                ->execute([':situacao' => $situacao, ':forma' => $paymentMethod, ':id' => $osId]);

            $db->prepare("UPDATE mercado_pago_point_orders
                SET financeiro_lancado_at = NOW(), payment_id = COALESCE(:payment_id, payment_id), status = 'processed'
                WHERE id = :id")
                ->execute([':payment_id' => $paymentId, ':id' => (int) $local['id']]);

            $osModel->addHistorico($osId, null, '', 'Pagamento', 'Pagamento aprovado na Smart Point.');
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    private function saveLocalOrder(array $data): void
    {
        $stmt = $this->db->prepare("INSERT INTO mercado_pago_point_orders
            (os_id, pdv_venda_id, numero_os, external_reference, mp_order_id, payment_id, status, status_detail, amount, payment_method, terminal_id, payload)
            VALUES (:os_id, :pdv_venda_id, :numero_os, :external_reference, :mp_order_id, :payment_id, :status, :status_detail, :amount, :payment_method, :terminal_id, :payload)
            ON DUPLICATE KEY UPDATE
                os_id = VALUES(os_id),
                pdv_venda_id = VALUES(pdv_venda_id),
                mp_order_id = VALUES(mp_order_id),
                payment_id = COALESCE(VALUES(payment_id), payment_id),
                status = VALUES(status),
                status_detail = VALUES(status_detail),
                amount = VALUES(amount),
                payment_method = VALUES(payment_method),
                terminal_id = VALUES(terminal_id),
                payload = VALUES(payload)");
        $stmt->execute($data);
    }

    private function approvePdvOrder(int $vendaId, string $paymentMethod, string $paymentReference): void
    {
        $pdv = new PdvModel();
        $venda = $pdv->findVenda($vendaId);
        if (!$venda || ($venda['status'] ?? '') === 'finalizada') {
            return;
        }

        $itens = $pdv->getItensVenda($vendaId);
        $estoque = new EstoqueModel();
        foreach ($itens as $item) {
            $produtoId = (int) ($item['produto_id'] ?? 0);
            if ($produtoId > 0) {
                $estoque->baixarEstoque($produtoId, (int) ($item['quantidade'] ?? 1), $venda['os_id'] ?: null, $venda['usuario_id'] ?: null, 'Venda PDV Point');
            }
        }

        $financeiro = new FinanceiroModel();
        $pdv->marcarFinalizada($vendaId, (float) ($venda['taxa_cartao_percentual'] ?? 0));
        $vendaFinal = $pdv->findVenda($vendaId);
        $financeiro->create([
            ':tipo' => 'Receita',
            ':categoria' => 'Venda Balcao (PDV)',
            ':descricao' => 'Venda ' . $vendaFinal['numero_venda'] . ' - Mercado Pago Point ' . $paymentReference,
            ':valor' => (float) ($vendaFinal['total'] ?? 0),
            ':os_id' => $vendaFinal['os_id'] ?: null,
            ':usuario_id' => $vendaFinal['usuario_id'] ?: null,
            ':data_pagamento' => date('Y-m-d'),
            ':forma_pagamento' => $paymentMethod,
        ]);

        if ((float) ($vendaFinal['taxa_cartao_valor'] ?? 0) > 0) {
            $financeiro->create([
                ':tipo' => 'Despesa',
                ':categoria' => 'Taxa Maquininha',
                ':descricao' => 'Taxa da maquininha - venda ' . $vendaFinal['numero_venda'] . ' (' . number_format((float) $vendaFinal['taxa_cartao_percentual'], 2, ',', '.') . '%)',
                ':valor' => (float) $vendaFinal['taxa_cartao_valor'],
                ':os_id' => $vendaFinal['os_id'] ?: null,
                ':usuario_id' => $vendaFinal['usuario_id'] ?: null,
                ':data_pagamento' => date('Y-m-d'),
                ':forma_pagamento' => $paymentMethod,
            ]);
        }
    }

    private function updateLocalStatus(string $externalReference, string $orderId, ?string $paymentId, string $status, string $detail, array $payload): void
    {
        if ($externalReference === '' && $orderId === '') {
            return;
        }

        $json = $this->safePayloadJson($payload);
        if ($externalReference !== '') {
            $stmt = $this->db->prepare("UPDATE mercado_pago_point_orders
                SET mp_order_id = COALESCE(NULLIF(:order_id, ''), mp_order_id),
                    payment_id = COALESCE(:payment_id, payment_id),
                    status = CASE WHEN financeiro_lancado_at IS NOT NULL THEN status ELSE COALESCE(NULLIF(:status, ''), status) END,
                    status_detail = :detail,
                    payload = :payload
                WHERE external_reference = :ref");
            $stmt->execute([
                ':order_id' => $orderId,
                ':payment_id' => $paymentId,
                ':status' => $status,
                ':detail' => $detail !== '' ? $detail : null,
                ':payload' => $json,
                ':ref' => $externalReference,
            ]);
            return;
        }

        $stmt = $this->db->prepare("UPDATE mercado_pago_point_orders
            SET payment_id = COALESCE(:payment_id, payment_id),
                status = CASE WHEN financeiro_lancado_at IS NOT NULL THEN status ELSE COALESCE(NULLIF(:status, ''), status) END,
                status_detail = :detail,
                payload = :payload
            WHERE mp_order_id = :order_id");
        $stmt->execute([
            ':payment_id' => $paymentId,
            ':status' => $status,
            ':detail' => $detail !== '' ? $detail : null,
            ':payload' => $json,
            ':order_id' => $orderId,
        ]);
    }

    private function findByReference(string $reference): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM mercado_pago_point_orders WHERE external_reference = :ref LIMIT 1");
        $stmt->execute([':ref' => $reference]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function findByOrderId(string $orderId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM mercado_pago_point_orders WHERE mp_order_id = :order_id LIMIT 1");
        $stmt->execute([':order_id' => $orderId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function getOrder(string $orderId): array
    {
        $settings = $this->configModel->getAll();
        $accessToken = trim((string) ($settings['mercadopago_access_token'] ?? ''));
        if ($accessToken === '') {
            throw new \RuntimeException('Access Token do Mercado Pago nao configurado.');
        }

        return $this->request('GET', '/v1/orders/' . rawurlencode($orderId), null, $accessToken);
    }

    private function request(string $method, string $path, ?array $payload, string $accessToken, ?string $idempotencyKey = null): array
    {
        $headers=$idempotencyKey!==null?['X-Idempotency-Key: '.$idempotencyKey]:[];
        return (new \App\Services\MercadoPagoHttpClient())->request($method,$path,$payload,$accessToken,$headers);
    }

    private function log(string $reference, ?string $orderId, ?string $paymentId, string $status, string $message, array $payload = []): void
    {
        $logPayload = array_filter([
            'external_reference'=>$reference!==''?$reference:null,
            'order_id'=>$orderId,
            'payment_id'=>$paymentId,
            'status'=>$status,
            'status_detail'=>isset($payload['status_detail'])?substr((string)$payload['status_detail'],0,100):null,
        ],static fn($value)=>$value!==null&&$value!=='');

        $stmt = $this->db->prepare("INSERT INTO mercado_pago_logs (external_reference, payment_id, status, mensagem, payload)
            VALUES (:ref, :payment_id, :status, :mensagem, :payload)");
        $stmt->execute([
            ':ref' => $reference !== '' ? $reference : null,
            ':payment_id' => $paymentId,
            ':status' => $status,
            ':mensagem' => $message,
            ':payload' => json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function safePayloadJson(array $payload): string
    {
        $safe = array_filter([
            'id' => isset($payload['id']) ? (string) $payload['id'] : null,
            'external_reference' => isset($payload['external_reference']) ? (string) $payload['external_reference'] : null,
            'status' => isset($payload['status']) ? (string) $payload['status'] : null,
            'status_detail' => isset($payload['status_detail']) ? substr((string) $payload['status_detail'], 0, 100) : null,
            'payment_id' => $this->paymentIdFromOrder($payload),
        ], static fn($value) => $value !== null && $value !== '');
        return json_encode($safe, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function buildExternalReference(int $osId, string $numeroOs): string
    {
        $safeNumber = preg_replace('/[^A-Za-z0-9_-]+/', '-', $numeroOs) ?: (string) $osId;
        return substr('OS-' . $safeNumber . '-' . $osId . '-' . date('YmdHis'), 0, 64);
    }

    private function buildPdvExternalReference(int $vendaId, string $numeroVenda): string
    {
        $safeNumber = preg_replace('/[^A-Za-z0-9_-]+/', '-', $numeroVenda) ?: (string) $vendaId;
        return substr('PDV-' . $safeNumber . '-' . $vendaId . '-' . date('YmdHis'), 0, 64);
    }

    private function normalizePaymentType(string $type): string
    {
        $type = trim($type);
        return in_array($type, ['credit_card', 'debit_card', 'qr_code'], true) ? $type : 'credit_card';
    }

    private function paymentMethodLabel(string $type, int $installments): string
    {
        if ($type === 'debit_card') {
            return 'Mercado Pago Point Debito';
        }
        if ($type === 'qr_code') {
            return 'QR / Saldo Mercado Pago Point';
        }

        return 'Mercado Pago Point Credito' . ($installments > 1 ? ' ' . $installments . 'x' : '');
    }

    private function paymentIdFromOrder(array $order): ?string
    {
        $payments = $order['transactions']['payments'] ?? null;
        if (is_array($payments) && isset($payments[0]) && is_array($payments[0]) && !empty($payments[0]['id'])) {
            return (string) $payments[0]['id'];
        }
        return null;
    }

    private function paidAmountFromOrder(array $order): float
    {
        $value = $order['total_paid_amount'] ?? $order['transactions']['payments'][0]['amount'] ?? 0;
        return (float) $value;
    }

    private function isApprovedOrder(array $order, string $status): bool
    {
        if ($this->isApprovedStatus($status)) {
            return true;
        }

        $payments = $order['transactions']['payments'] ?? [];
        if (is_array($payments)) {
            foreach ($payments as $payment) {
                if (($payment['status'] ?? '') === 'approved') {
                    return true;
                }
            }
        }

        return false;
    }

    private function isApprovedStatus(string $status): bool
    {
        return in_array(strtolower($status), ['processed', 'paid', 'approved', 'finished'], true);
    }

    private function idempotencyKey(string $reference): string
    {
        return substr(sha1($reference), 0, 8) . '-' . substr(sha1($reference), 8, 4) . '-' . substr(sha1($reference), 12, 4) . '-' . substr(sha1($reference), 16, 4) . '-' . substr(sha1($reference), 20, 12);
    }

    private function errorMessage($data): string
    {
        if (is_array($data)) {
            return (string) ($data['message'] ?? $data['error'] ?? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return 'Falha na comunicacao com Mercado Pago.';
    }

    private function limitText(string $text, int $limit): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $limit, 'UTF-8');
        }
        return substr($text, 0, $limit);
    }
}
