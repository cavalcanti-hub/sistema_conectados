<?php
namespace App\Controllers;

use App\Core\Controller;

class PdvController extends Controller
{
    use Traits\CompanyDataTrait;

    private \App\Models\PdvModel $model;

    public function __construct()
    {
        $this->model = new \App\Models\PdvModel();
    }

    private function taxaCartaoPercentual(string $formaPagamento, array $settings): float
    {
        if (\App\Support\PaymentFormNormalizer::isCredit($formaPagamento)) {
            return normalize_decimal_input($settings['taxa_cartao_credito'] ?? 0);
        }

        if (\App\Support\PaymentFormNormalizer::isDebit($formaPagamento)) {
            return normalize_decimal_input($settings['taxa_cartao_debito'] ?? 0);
        }

        return 0.0;
    }

    private function taxaPointPercentual(string $formaPagamento, array $settings): float
    {
        $forma = \App\Support\PaymentFormNormalizer::normalize($formaPagamento);

        if (str_contains($forma, 'debito') || str_contains($forma, 'qr') || str_contains($forma, 'saldo mercado')) {
            $point = normalize_decimal_input($settings['taxa_point_debito_qr_saldo'] ?? '');
            return $point > 0 ? $point : normalize_decimal_input($settings['taxa_cartao_debito'] ?? 0);
        }

        if (\App\Support\PaymentFormNormalizer::isCredit($formaPagamento)) {
            if (str_contains($forma, '30')) {
                $base = normalize_decimal_input($settings['taxa_point_credito_30d'] ?? 0);
            } elseif (str_contains($forma, '14')) {
                $base = normalize_decimal_input($settings['taxa_point_credito_14d'] ?? 0);
            } else {
                $base = normalize_decimal_input($settings['taxa_point_credito_hora'] ?? 0);
            }

            if ($base <= 0) {
                $base = normalize_decimal_input($settings['taxa_cartao_credito'] ?? 0);
            }

            $installmentFee = 0.0;
            $installments = \App\Support\PaymentFormNormalizer::extractInstallments($formaPagamento);
            if ($installments > 1) {
                $installmentFee = normalize_decimal_input($settings['taxa_point_parcelamento_' . $installments . 'x'] ?? 0);
            }

            return $base + $installmentFee;
        }

        return 0.0;
    }

    private function pointTaxSettings(array $settings): array
    {
        return [
            'debitoQrSaldo' => normalize_decimal_input($settings['taxa_point_debito_qr_saldo'] ?? 1.99),
            'creditoHora' => normalize_decimal_input($settings['taxa_point_credito_hora'] ?? 4.74),
            'credito14d' => normalize_decimal_input($settings['taxa_point_credito_14d'] ?? 3.79),
            'credito30d' => normalize_decimal_input($settings['taxa_point_credito_30d'] ?? 3.03),
            'parcelamento' => array_reduce(range(2, 12), function (array $carry, int $parcelas) use ($settings): array {
                $carry[$parcelas] = normalize_decimal_input($settings['taxa_point_parcelamento_' . $parcelas . 'x'] ?? 0);
                return $carry;
            }, []),
        ];
    }

    private function pointOptionsFromForma(string $formaPagamento): array
    {
        $forma = \App\Support\PaymentFormNormalizer::normalize($formaPagamento);

        $paymentType = 'credit_card';
        if (str_contains($forma, 'debito')) {
            $paymentType = 'debit_card';
        } elseif (str_contains($forma, 'qr ') || str_contains($forma, 'saldo ')) {
            $paymentType = 'qr_code';
        }

        $installments = \App\Support\PaymentFormNormalizer::extractInstallments($formaPagamento);

        return ['payment_type' => $paymentType, 'installments' => $installments];
    }

    private function isPointPayment(string $formaPagamento): bool
    {
        return \App\Support\PaymentFormNormalizer::isPointPayment($formaPagamento);
    }

    public function index()
    {
        $settings = (new \App\Models\ConfigModel())->getAll();
        // Reconcile pending Point orders before calculating the cash summary.
        // The model keeps this read-only for pending payments and only posts
        // to the cash/financial records after Mercado Pago confirms approval.
        (new \App\Models\MercadoPagoPointModel())->syncPendingPdvOrders(10);
        $caixa = $this->model->sincronizarCaixaAutomatico(current_user_id());
        $caixaResumo = $this->model->resumoCaixa(!empty($caixa['id']) ? (int) $caixa['id'] : null);
        $horarioCaixa = $this->model->horarioCaixa();
        $clientes = (new \App\Models\ClienteModel())->getAll();
        $produtos = (new \App\Models\EstoqueModel())->getAll();
        $vendasHoje = $this->model->getVendas(50);
        $this->view('pdv/index', [
            'title' => 'PDV - Conectados',
            'page_title' => 'Ponto de Venda',
            'clientes' => $clientes,
            'produtos' => $produtos,
            'vendasHoje' => $vendasHoje,
            'totalHoje' => $this->model->totalHoje(),
            'taxaDebito' => normalize_decimal_input($settings['taxa_cartao_debito'] ?? 0),
            'taxaCredito' => normalize_decimal_input($settings['taxa_cartao_credito'] ?? 0),
            'pointTaxas' => $this->pointTaxSettings($settings),
            'caixa' => $caixa,
            'caixaResumo' => $caixaResumo,
            'horarioCaixa' => $horarioCaixa,
            'caixaNoHorario' => $this->model->estaNoHorario(),
            'isAdmin' => current_user_profile() === 'Administrador',
        ]);
    }

    public function finalizarVenda()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(route_url('pdv'));
        }

        $itens = $this->parseItensJson((string) ($_POST['itens_json'] ?? '[]'));
        if (!is_array($itens) || empty($itens)) {
            $this->redirect(route_url('pdv', ['erro_msg' => 'Carrinho vazio ou invalido.']));
        }

        $usuarioId = current_user_id();
        if (!$usuarioId) {
            $this->redirect(route_url('login'));
        }

        $settings = (new \App\Models\ConfigModel())->getAll();
        $caixa = $this->model->sincronizarCaixaAutomatico($usuarioId);
        if (!$caixa || ($caixa['status'] ?? '') !== 'aberto') {
            $this->redirect(route_url('pdv', ['erro_msg' => 'Caixa fechado. Apenas administradores podem abrir manualmente fora do horario.']));
        }
        $formaPagamento = (string) ($_POST['forma_pagamento'] ?? '');
        $taxaCartaoPercentual = $this->taxaPointPercentual($formaPagamento, $settings);
        $enviarPoint = (string) ($_POST['enviar_point'] ?? '') === '1' && $this->isPointPayment($formaPagamento);
        $db = \App\Config\Database::getInstance();
        $estoqueModel = new \App\Models\EstoqueModel();
        $db->beginTransaction();

        try {
            $vendaId = $this->model->criarVenda([
                'cliente_id' => $_POST['cliente_id'] ?: null,
                'os_id' => $_POST['os_id'] ?: null,
                'caixa_id' => (int) $caixa['id'],
                'usuario_id' => $usuarioId,
                'forma_pagamento' => $enviarPoint ? 'Mercado Pago Point - ' . $formaPagamento : $formaPagamento,
                'desconto' => normalize_decimal_input($_POST['desconto'] ?? 0),
                'observacoes' => $_POST['observacoes'] ?? ''
            ]);

            foreach ($itens as $item) {
                $produtoId = (int) ($item['produto_id'] ?? 0);
                $qtd = max(1, (int) ($item['qtd'] ?? 1));
                $descricao = trim((string) ($item['descricao'] ?? 'Item avulso'));
                $preco = normalize_decimal_input($item['preco'] ?? 0);

                if ($produtoId > 0) {
                    $produto = $estoqueModel->find($produtoId);
                    if (!$produto) {
                        throw new \RuntimeException('Produto do PDV nao encontrado.');
                    }

                    $descricao = (string) $produto['nome'];
                    $preco = (float) $produto['preco_venda'];
                    if (!$enviarPoint) {
                        $estoqueModel->baixarEstoque($produtoId, $qtd, null, $usuarioId, 'Venda PDV');
                    }
                }

                if ($preco <= 0) {
                    throw new \RuntimeException('Preco invalido em item do PDV.');
                }

                $this->model->addItem($vendaId, $produtoId ?: null, $descricao, $qtd, $preco);
            }

            if ($enviarPoint) {
                $this->model->prepararVendaPoint($vendaId, $taxaCartaoPercentual);
                $db->commit();

                $vendaPoint = $this->model->findVenda($vendaId);
                $pointOptions = $this->pointOptionsFromForma($formaPagamento);
                $result = (new \App\Models\MercadoPagoPointModel())->createOrderForPdv($vendaPoint, [
                    'amount' => (float) ($vendaPoint['total'] ?? 0),
                    'payment_type' => $pointOptions['payment_type'],
                    'installments' => $pointOptions['installments'],
                ]);

                (new \App\Models\AuditModel())->record('criar', 'mercado_pago_point', (int) $vendaId, 'Venda PDV enviada para Smart Point', [
                    'order_id' => $result['order_id'] ?? '',
                    'external_reference' => $result['external_reference'] ?? '',
                    'total' => $vendaPoint['total'] ?? 0,
                    'forma_pagamento' => $formaPagamento,
                ]);

                $this->redirect(route_url('pdv', ['point_sent' => 1, 'venda_id' => $vendaId]));
            }

            $this->model->finalizarVenda($vendaId, $taxaCartaoPercentual);
            $vendaFinal = $this->model->findVenda($vendaId);

            $financeiroModel = new \App\Models\FinanceiroModel();
            $financeiroModel->create([
                ':tipo' => 'Receita',
                ':categoria' => 'Venda Balcao (PDV)',
                ':descricao' => 'Venda ' . $vendaFinal['numero_venda'],
                ':valor' => $vendaFinal['total'],
                ':os_id' => $_POST['os_id'] ?: null,
                ':usuario_id' => $usuarioId,
                ':data_pagamento' => date('Y-m-d'),
                ':forma_pagamento' => $formaPagamento
            ]);

            if ((float) ($vendaFinal['taxa_cartao_valor'] ?? 0) > 0) {
                $financeiroModel->create([
                    ':tipo' => 'Despesa',
                    ':categoria' => 'Taxa Maquininha',
                    ':descricao' => 'Taxa da maquininha - venda ' . $vendaFinal['numero_venda'] . ' (' . number_format((float) $vendaFinal['taxa_cartao_percentual'], 2, ',', '.') . '%)',
                    ':valor' => $vendaFinal['taxa_cartao_valor'],
                    ':os_id' => $_POST['os_id'] ?: null,
                    ':usuario_id' => $usuarioId,
                    ':data_pagamento' => date('Y-m-d'),
                    ':forma_pagamento' => $formaPagamento
                ]);
            }

            $db->commit();
            (new \App\Models\AuditModel())->record('venda', 'pdv_caixa', (int) $caixa['id'], 'Venda PDV finalizada no caixa', [
                'venda_id' => $vendaId,
                'total' => $vendaFinal['total'] ?? 0,
                'forma_pagamento' => $formaPagamento,
            ]);
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            app_log('Falha ao finalizar PDV', ['erro' => $e->getMessage()]);
            $this->redirect(route_url('pdv', ['erro_msg' => substr($e->getMessage(), 0, 160)]));
        }

        $this->redirect(route_url('pdv', ['venda_id' => $vendaId, 'success' => 1]));
    }

    public function pointStatus(): void
    {
        session_write_close();
        header('Content-Type: application/json; charset=utf-8');

        $vendaId = (int) ($_GET['venda_id'] ?? ($_GET['id'] ?? 0));
        if ($vendaId <= 0) {
            echo json_encode(['ok' => false, 'status' => 'error']);
            return;
        }

        $latest = (new \App\Models\MercadoPagoPointModel())->syncLatestForPdv($vendaId);
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

    public function abrirCaixa(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(route_url('pdv'));
        }
        if (current_user_profile() !== 'Administrador') {
            http_response_code(403);
            echo 'Apenas administradores podem abrir o caixa manualmente.';
            exit;
        }

        $caixa = $this->model->abrirCaixa(
            current_user_id(),
            'manual',
            normalize_decimal_input($_POST['valor_inicial'] ?? 0),
            trim((string) ($_POST['observacoes'] ?? 'Abertura manual pelo administrador'))
        );
        (new \App\Models\AuditModel())->record('abrir', 'pdv_caixa', (int) ($caixa['id'] ?? 0), 'Caixa aberto manualmente pelo administrador');
        $this->redirect(route_url('pdv', ['caixa' => 'aberto']));
    }

    public function fecharCaixa(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(route_url('pdv'));
        }
        if (current_user_profile() !== 'Administrador') {
            http_response_code(403);
            echo 'Apenas administradores podem fechar o caixa manualmente.';
            exit;
        }

        $caixa = $this->model->caixaDoDia();
        if ($caixa && ($caixa['status'] ?? '') === 'aberto') {
            $caixa = $this->model->fecharCaixa(
                (int) $caixa['id'],
                current_user_id(),
                'manual',
                ($_POST['valor_informado'] ?? '') !== '' ? normalize_decimal_input($_POST['valor_informado']) : null,
                trim((string) ($_POST['observacoes'] ?? 'Fechamento manual pelo administrador'))
            );
            (new \App\Models\AuditModel())->record('fechar', 'pdv_caixa', (int) ($caixa['id'] ?? 0), 'Caixa fechado manualmente pelo administrador');
        }

        $this->redirect(route_url('pdv', ['caixa' => 'fechado']));
    }

    public function getprodutos()
    {
        header('Content-Type: application/json');
        $produtos = (new \App\Models\EstoqueModel())->getAll($_GET['q'] ?? '');
        echo json_encode($produtos);
        exit;
    }

    private function parseItensJson(string $json): array
    {
        $json = trim($json);
        if ($json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $decoded = json_decode(stripslashes($json), true);
        return is_array($decoded) ? $decoded : [];
    }

    public function imprimir()
    {
        $id = $_GET['id'] ?? 0;
        $venda = $this->model->findVenda($id);
        $itens = $this->model->getItensVenda($id);
        if (!$venda) {
            $this->redirect(route_url('pdv'));
        }

        $this->view('pdv/print', ['venda' => $venda, 'itens' => $itens, 'company' => $this->buildCompanyData()]);
    }
}
