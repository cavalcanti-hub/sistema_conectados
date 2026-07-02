<?php
namespace App\Controllers;

use App\Core\Controller;

class GastosPessoaisController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \App\Models\GastosPessoaisModel();
    }

    public function index()
    {
        [$periodo, $tipo] = $this->filters();
        $pager = pagination_request(20, 100);
        $pagination = pagination_meta($this->model->countLancamentos($periodo, $tipo), $pager['page'], $pager['per_page']);
        $entradas = $this->model->total('Receita', $periodo);
        $despesas = $this->model->total('Despesa', $periodo);

        $this->view('gastos_pessoais/index', [
            'title' => 'Gastos Pessoais - Conectados',
            'page_title' => 'Gastos Pessoais',
            'periodo' => $periodo,
            'tipo' => $tipo,
            'lancamentos' => $this->model->getLancamentos($periodo, $tipo, $pagination['per_page'], $pagination['offset']),
            'entradas' => $entradas,
            'despesas' => $despesas,
            'saldo' => $entradas - $despesas,
            'categorias' => $this->model->resumoCategorias($periodo, 'Despesa'),
            'categoriasCadastro' => $this->model->getCategorias(),
            'formas' => $this->model->resumoFormas($periodo),
            'pagination' => $pagination,
        ]);
    }

    public function print()
    {
        [$periodo, $tipo] = $this->filters();
        $entradas = $this->model->total('Receita', $periodo);
        $despesas = $this->model->total('Despesa', $periodo);

        $this->view('gastos_pessoais/print', [
            'title' => 'Relatorio de Gastos Pessoais',
            'periodo' => $periodo,
            'tipo' => $tipo,
            'lancamentos' => $this->model->getLancamentos($periodo, $tipo, 2000, 0),
            'entradas' => $entradas,
            'despesas' => $despesas,
            'saldo' => $entradas - $despesas,
            'categorias' => $this->model->resumoCategorias($periodo, 'Despesa'),
            'formas' => $this->model->resumoFormas($periodo),
        ]);
    }

    public function store()
    {
        $this->model->create($this->payload(true));
        header('Location: ' . route_url('gastos_pessoais', ['success' => 1]));
        exit;
    }

    public function update()
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || !$this->model->find($id)) {
            header('Location: ' . route_url('gastos_pessoais', ['error' => 'not_found']));
            exit;
        }

        $this->model->update($id, $this->payload(false));
        header('Location: ' . route_url('gastos_pessoais', ['updated' => 1]));
        exit;
    }

    public function delete()
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || !$this->model->find($id)) {
            header('Location: ' . route_url('gastos_pessoais', ['error' => 'not_found']));
            exit;
        }

        $this->model->delete($id);
        header('Location: ' . route_url('gastos_pessoais', ['deleted' => 1]));
        exit;
    }

    public function addCategoria()
    {
        $this->model->createCategoria((string) ($_POST['nome'] ?? ''), (string) ($_POST['tipo'] ?? 'Despesa'));
        header('Location: ' . route_url('gastos_pessoais', ['success' => 1]));
        exit;
    }

    public function deleteCategoria()
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->deleteCategoria($id);
        }
        header('Location: ' . route_url('gastos_pessoais', ['success' => 1]));
        exit;
    }

    private function payload(bool $includeUser): array
    {
        $tipo = $_POST['tipo'] ?? 'Despesa';
        $tipo = in_array($tipo, ['Receita', 'Despesa'], true) ? $tipo : 'Despesa';

        $data = [
            ':tipo' => $tipo,
            ':categoria' => trim((string) ($_POST['categoria'] ?? '')),
            ':descricao' => trim((string) ($_POST['descricao'] ?? '')),
            ':valor' => normalize_decimal_input($_POST['valor'] ?? 0),
            ':data_lancamento' => $_POST['data_lancamento'] ?: date('Y-m-d'),
            ':forma_pagamento' => trim((string) ($_POST['forma_pagamento'] ?? '')),
            ':recorrente' => !empty($_POST['recorrente']) ? 1 : 0,
            ':observacoes' => trim((string) ($_POST['observacoes'] ?? '')),
        ];

        if ($data[':descricao'] === '') {
            $data[':descricao'] = $tipo === 'Receita' ? 'Entrada pessoal' : 'Gasto pessoal';
        }

        if ($includeUser) {
            $data[':usuario_id'] = $_SESSION['usuario_id'] ?? null;
        }

        return $data;
    }

    private function filters(): array
    {
        $periodo = $_GET['periodo'] ?? 'mes';
        $periodo = in_array($periodo, ['dia', 'semana', 'mes', 'todos'], true) ? $periodo : 'mes';
        $tipo = $_GET['tipo'] ?? '';
        $tipo = in_array($tipo, ['Receita', 'Despesa'], true) ? $tipo : '';
        return [$periodo, $tipo];
    }
}
