<?php
namespace App\Controllers;

use App\Core\Controller;

class ComprasController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \App\Models\ComprasModel();
    }

    public function index()
    {
        $filters = $this->filters();

        $pager = pagination_request(20, 100);
        $pagination = pagination_meta($this->model->count($filters), $pager['page'], $pager['per_page']);

        $this->view('compras/index', [
            'title' => 'Compras e Solicitacoes - Conectados',
            'page_title' => 'Compras e Solicitacoes',
            'items' => $this->model->getAll($filters, $pagination['per_page'], $pagination['offset']),
            'filters' => $filters,
            'pagination' => $pagination,
            'resumo' => $this->model->resumoStatus(),
            'statuses' => $this->statuses(),
            'tipos' => $this->tipos(),
            'prioridades' => $this->prioridades(),
        ]);
    }

    public function print()
    {
        $filters = $this->filters();

        $this->view('compras/print', [
            'title' => 'Relatorio de Solicitacoes de Compra',
            'items' => $this->model->getAll($filters, 1000, 0),
            'filters' => $filters,
            'resumo' => $this->model->resumoStatus(),
            'statuses' => $this->statuses(),
            'tipos' => $this->tipos(),
        ]);
    }

    public function store()
    {
        $nome = trim((string) ($_POST['item_nome'] ?? ''));
        if ($nome === '') {
            $this->redirect(route_url('compras', ['error' => 'nome']));
        }

        $this->model->create([
            ':item_nome' => $nome,
            ':tipo' => $this->validTipo($_POST['tipo'] ?? '') ?: 'peca',
            ':quantidade' => max(1, (int) ($_POST['quantidade'] ?? 1)),
            ':fornecedor' => trim((string) ($_POST['fornecedor'] ?? '')),
            ':prioridade' => $this->validPrioridade($_POST['prioridade'] ?? '') ?: 'Normal',
            ':status' => 'Pendente',
            ':observacoes' => trim((string) ($_POST['observacoes'] ?? '')),
            ':usuario_id' => current_user_id(),
            ':data_solicitacao' => $_POST['data_solicitacao'] ?: date('Y-m-d'),
        ]);

        $this->redirect(route_url('compras', ['success' => 1]));
    }

    public function updateStatus()
    {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $this->validStatus($_POST['status'] ?? '');
        if ($id <= 0 || $status === null) {
            $this->redirect(route_url('compras', ['error' => 'status']));
        }

        $this->model->updateStatus($id, $status);
        $this->redirect(route_url('compras', ['updated' => 1]));
    }

    public function delete()
    {
        if (current_user_profile() !== 'Administrador') {
            http_response_code(403);
            echo 'Apenas administradores podem remover solicitacoes.';
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
        }
        $this->redirect(route_url('compras', ['deleted' => 1]));
    }

    private function statuses(): array
    {
        return ['Pendente', 'Solicitado', 'Comprado', 'Recebido', 'Cancelado'];
    }

    private function tipos(): array
    {
        return ['peca' => 'Peca tecnica', 'produto' => 'Produto da loja'];
    }

    private function prioridades(): array
    {
        return ['Baixa', 'Normal', 'Alta', 'Urgente'];
    }

    private function filters(): array
    {
        return [
            'search' => trim((string) ($_GET['search'] ?? '')),
            'status' => $this->validStatus($_GET['status'] ?? '') ?: '',
            'tipo' => $this->validTipo($_GET['tipo'] ?? '') ?: '',
        ];
    }

    private function validStatus($status): ?string
    {
        $status = trim((string) $status);
        return in_array($status, $this->statuses(), true) ? $status : null;
    }

    private function validTipo($tipo): ?string
    {
        $tipo = trim((string) $tipo);
        return array_key_exists($tipo, $this->tipos()) ? $tipo : null;
    }

    private function validPrioridade($prioridade): ?string
    {
        $prioridade = trim((string) $prioridade);
        return in_array($prioridade, $this->prioridades(), true) ? $prioridade : null;
    }
}
