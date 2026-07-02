<?php
namespace App\Controllers;

use App\Core\Controller;

class FornecedoresController extends Controller
{
    private \App\Models\FornecedoresModel $fornecedores;
    private \App\Models\ComprasNotasModel $notas;
    private \App\Models\EstoqueModel $estoque;

    public function __construct()
    {
        $this->fornecedores = new \App\Models\FornecedoresModel();
        $this->notas = new \App\Models\ComprasNotasModel();
        $this->estoque = new \App\Models\EstoqueModel();
    }

    public function index(): void
    {
        $filters = [
            'search' => trim((string) ($_GET['search'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
            'fornecedor_id' => (int) ($_GET['fornecedor_id'] ?? 0),
        ];
        if (!in_array($filters['status'], ['Aberta', 'Baixada', 'Cancelada'], true)) {
            $filters['status'] = '';
        }

        $notas = $this->notas->getAll($filters);
        foreach ($notas as &$nota) {
            $nota['itens'] = $this->notas->items((int) $nota['id']);
        }
        unset($nota);

        $this->view('fornecedores/index', [
            'title' => 'Fornecedores e Notas - Conectados',
            'page_title' => 'Fornecedores e Notas',
            'fornecedores' => $this->fornecedores->getAll($filters['search']),
            'fornecedoresAtivos' => $this->fornecedores->getAll('', true),
            'notas' => $notas,
            'produtos' => $this->estoque->getAll('', '', null),
            'filters' => $filters,
            'formasPagamento' => $this->formasPagamento(),
            'tipos' => ['peca' => 'Peca tecnica', 'produto' => 'Produto da loja'],
        ]);
    }

    public function store(): void
    {
        $name = trim((string) ($_POST['nome'] ?? ''));
        if ($name === '') {
            $this->redirect(route_url('fornecedores', ['error' => 'nome']));
        }

        $this->fornecedores->create([
            'nome' => $name,
            'documento' => $_POST['documento'] ?? '',
            'telefone' => $_POST['telefone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'endereco' => $_POST['endereco'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
            'ativo' => 1,
        ]);

        $this->redirect(route_url('fornecedores', ['success' => 1]));
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['nome'] ?? ''));
        if ($id <= 0 || $name === '') {
            $this->redirect(route_url('fornecedores', ['error' => 'fornecedor']));
        }

        $this->fornecedores->update($id, [
            'nome' => $name,
            'documento' => $_POST['documento'] ?? '',
            'telefone' => $_POST['telefone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'endereco' => $_POST['endereco'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ]);

        $this->redirect(route_url('fornecedores', ['updated' => 1]));
    }

    public function storeNota(): void
    {
        try {
            $this->notas->create($this->notaPayload(), $this->itemsPayload());
            $this->redirect(route_url('fornecedores', ['nota_success' => 1]));
        } catch (\Throwable $e) {
            $_SESSION['fornecedores_error'] = $e->getMessage();
            $this->redirect(route_url('fornecedores', ['error' => 'nota']));
        }
    }

    public function updateNota(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            if ($id <= 0) {
                throw new \RuntimeException('Nota invalida.');
            }
            $this->notas->update($id, $this->notaPayload(), $this->itemsPayload());
            $this->redirect(route_url('fornecedores', ['nota_updated' => 1]));
        } catch (\Throwable $e) {
            $_SESSION['fornecedores_error'] = $e->getMessage();
            $this->redirect(route_url('fornecedores', ['error' => 'nota']));
        }
    }

    public function baixarNota(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            if ($id <= 0) {
                throw new \RuntimeException('Nota invalida.');
            }
            $this->notas->baixar($id);
            $this->redirect(route_url('fornecedores', ['baixada' => 1]));
        } catch (\Throwable $e) {
            $_SESSION['fornecedores_error'] = $e->getMessage();
            $this->redirect(route_url('fornecedores', ['error' => 'baixa']));
        }
    }

    public function cancelarNota(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->notas->cancelar($id);
        }
        $this->redirect(route_url('fornecedores', ['cancelada' => 1]));
    }

    private function notaPayload(): array
    {
        return [
            'fornecedor_id' => (int) ($_POST['fornecedor_id'] ?? 0),
            'numero' => $_POST['numero'] ?? '',
            'data_emissao' => $_POST['data_emissao'] ?? date('Y-m-d'),
            'data_vencimento' => $_POST['data_vencimento'] ?? '',
            'forma_pagamento' => $_POST['forma_pagamento'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
        ];
    }

    private function itemsPayload(): array
    {
        $items = [];
        $descriptions = $_POST['item_descricao'] ?? [];
        $productIds = $_POST['item_produto_id'] ?? [];
        $types = $_POST['item_tipo'] ?? [];
        $quantities = $_POST['item_quantidade'] ?? [];
        $unitValues = $_POST['item_valor_unitario'] ?? [];

        foreach ((array) $descriptions as $index => $description) {
            $items[] = [
                'descricao' => $description,
                'produto_id' => (int) ($productIds[$index] ?? 0),
                'tipo' => (string) ($types[$index] ?? 'peca'),
                'quantidade' => (int) ($quantities[$index] ?? 1),
                'valor_unitario' => $unitValues[$index] ?? 0,
            ];
        }

        return $items;
    }

    private function formasPagamento(): array
    {
        return ['Pix', 'Dinheiro', 'Cartao de Debito', 'Cartao de Credito', 'Transferencia', 'Boleto', 'Saldo Mercado Livre'];
    }
}
