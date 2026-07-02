<?php
namespace App\Controllers;

use App\Core\Controller;

class RecadosController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \App\Models\RecadoModel();
    }

    public function index()
    {
        $filters = $this->filters();
        $pager = pagination_request(20, 100);
        $pagination = pagination_meta($this->model->count($filters), $pager['page'], $pager['per_page']);

        $this->view('recados/index', [
            'title' => 'Recados - Conectados',
            'page_title' => 'Recados',
            'items' => $this->model->getAll($filters, $pagination['per_page'], $pagination['offset']),
            'filters' => $filters,
            'pagination' => $pagination,
            'resumo' => $this->model->resumoStatus(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store()
    {
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $mensagem = trim((string) ($_POST['mensagem'] ?? ''));

        if ($nome === '') {
            $this->redirect(route_url('recados', ['error' => 'nome']));
        }

        if ($mensagem === '') {
            $this->redirect(route_url('recados', ['error' => 'mensagem']));
        }

        $this->model->create([
            ':nome' => $nome,
            ':telefone' => trim((string) ($_POST['telefone'] ?? '')),
            ':mensagem' => $mensagem,
            ':status' => 'Pendente',
            ':usuario_id' => current_user_id(),
            ':data_recado' => $_POST['data_recado'] ?: date('Y-m-d'),
        ]);

        $this->redirect(route_url('recados', ['success' => 1]));
    }

    public function updateStatus()
    {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $this->validStatus($_POST['status'] ?? '');
        if ($id <= 0 || $status === null) {
            $this->redirect(route_url('recados', ['error' => 'status']));
        }

        $this->model->updateStatus($id, $status);
        $this->redirect(route_url('recados', ['updated' => 1]));
    }

    public function delete()
    {
        if (current_user_profile() !== 'Administrador') {
            http_response_code(403);
            echo 'Apenas administradores podem remover recados.';
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
        }
        $this->redirect(route_url('recados', ['deleted' => 1]));
    }

    private function statuses(): array
    {
        return ['Pendente', 'Lido', 'Respondido', 'Arquivado'];
    }

    private function filters(): array
    {
        return [
            'search' => trim((string) ($_GET['search'] ?? '')),
            'status' => $this->validStatus($_GET['status'] ?? '') ?: '',
        ];
    }

    private function validStatus($status): ?string
    {
        $status = trim((string) $status);
        return in_array($status, $this->statuses(), true) ? $status : null;
    }
}
