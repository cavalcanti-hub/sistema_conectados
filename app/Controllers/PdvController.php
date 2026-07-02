<?php
namespace App\Controllers;

use App\Core\Controller;

class PdvController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \App\Models\PdvModel();
    }

    private function buildCompanyData(): array
    {
        $settings = (new \App\Models\ConfigModel())->getAll();

        return [
            'name' => trim((string) ($settings['nome_empresa'] ?? 'Conectados')),
            'phone' => trim((string) ($settings['whatsapp'] ?? '')),
            'address' => trim((string) ($settings['endereco'] ?? '')),
            'email' => trim((string) ($settings['email_negocio'] ?? '')),
            'website' => trim((string) ($settings['website'] ?? 'conectadosassistencia.com.br')),
            'logo' => asset_url('assets/img/logo.png'),
            'logo_print' => asset_url('assets/img/logo-print.png?v=20260702-banner'),
        ];
    }

    private function taxaCartaoPercentual(string $formaPagamento, array $settings): float
    {
        $forma = strtolower($formaPagamento);
        if (str_contains($forma, 'crédito') || str_contains($forma, 'credito') || str_contains($forma, 'crÃ©dito') || str_contains($forma, 'cr')) {
            return normalize_decimal_input($settings['taxa_cartao_credito'] ?? 0);
        }

        if (str_contains($forma, 'débito') || str_contains($forma, 'debito') || str_contains($forma, 'dÃ©bito')) {
            return normalize_decimal_input($settings['taxa_cartao_debito'] ?? 0);
        }

        return 0.0;
    }

    public function index()
    {
        $settings = (new \App\Models\ConfigModel())->getAll();
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
        $formaPagamento = (string) ($_POST['forma_pagamento'] ?? '');
        $taxaCartaoPercentual = $this->taxaCartaoPercentual($formaPagamento, $settings);
        $db = \App\Config\Database::getInstance();
        $estoqueModel = new \App\Models\EstoqueModel();
        $db->beginTransaction();

        try {
            $vendaId = $this->model->criarVenda([
                'cliente_id' => $_POST['cliente_id'] ?: null,
                'os_id' => $_POST['os_id'] ?: null,
                'usuario_id' => $usuarioId,
                'forma_pagamento' => $formaPagamento,
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
                    $estoqueModel->baixarEstoque($produtoId, $qtd, null, $usuarioId, 'Venda PDV');
                }

                if ($preco <= 0) {
                    throw new \RuntimeException('Preco invalido em item do PDV.');
                }

                $this->model->addItem($vendaId, $produtoId ?: null, $descricao, $qtd, $preco);
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
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            app_log('Falha ao finalizar PDV', ['erro' => $e->getMessage()]);
            $this->redirect(route_url('pdv', ['erro_msg' => substr($e->getMessage(), 0, 160)]));
        }

        $this->redirect(route_url('pdv', ['venda_id' => $vendaId, 'success' => 1]));
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
