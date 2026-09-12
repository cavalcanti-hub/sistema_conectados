<?php
namespace App\Controllers;
use App\Core\Controller;

class ClientesController extends Controller {
    private \App\Models\ClienteModel $model;
    public function __construct() { $this->model = new \App\Models\ClienteModel(); }

    public function index() {
        $search = $_GET['search'] ?? '';
        $pager = pagination_request(24, 96);
        $pagination = pagination_meta($this->model->countFiltered($search), $pager['page'], $pager['per_page']);
        $clientes = $this->model->getAll($search, $pagination['per_page'], $pagination['offset']);
        $this->view('clientes/index', ['title'=>'Clientes - Conectados','page_title'=>'Cadastro de Clientes','clientes'=>$clientes,'search'=>$search,'pagination'=>$pagination]);
    }

    public function searchJson() {
        $q = trim((string) ($_GET['q'] ?? ''));
        if ($q === '') {
            $clientes = $this->model->getAll('', 30);
        } else {
            $clientes = $this->model->getAll($q, 30);
        }
        
        $data = [];
        foreach ($clientes as $c) {
            $whats = !empty($c['whatsapp']) ? $c['whatsapp'] : (!empty($c['telefone']) ? $c['telefone'] : '');
            $text = $c['nome'] . ($whats ? ' - ' . $whats : '');
            $data[] = [
                'value' => $c['id'],
                'text' => $text
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function create() {
        [$old, $error] = $this->consumeFormState();
        $this->view('clientes/create', ['title'=>'Novo Cliente - Conectados','page_title'=>'Novo Cliente','old'=>$old,'error'=>$error]);
    }

    public function store() {
        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input);

        if ($error !== null) {
            $this->storeFormState($input, $error);
            $this->redirect(\route_url('clientes/create'));
        }

        try {
            $this->model->create([
                ':nome' => $input['nome'],
                ':cpf_cnpj' => $input['cpf_cnpj'],
                ':telefone' => $input['telefone'],
                ':whatsapp' => $input['whatsapp'],
                ':email' => $input['email'],
                ':endereco' => $input['endereco'],
                ':observacoes' => $input['observacoes']
            ]);
            $this->redirect(\route_url('clientes', ['success' => 1]));
        } catch (\Throwable $e) {
            $this->storeFormState($input, 'save');
            $this->redirect(\route_url('clientes/create'));
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        $cliente = $this->model->find($id);
        if (!$cliente) {
            $this->redirect(route_url('clientes'));
        }
        [$old, $error] = $this->consumeFormState();
        $this->view('clientes/edit', ['title'=>'Editar Cliente','page_title'=>'Editar Cliente','cliente'=>$cliente,'old'=>$old,'error'=>$error]);
    }

    public function update() {
        $id = (int) ($_POST['id'] ?? 0);
        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input, $id);

        if ($error !== null) {
            $this->storeFormState($input, $error);
            $this->redirect(\route_url('clientes/edit', ['id' => $id]));
        }

        try {
            $this->model->update($id, [
                ':nome' => $input['nome'],
                ':cpf_cnpj' => $input['cpf_cnpj'],
                ':telefone' => $input['telefone'],
                ':whatsapp' => $input['whatsapp'],
                ':email' => $input['email'],
                ':endereco' => $input['endereco'],
                ':observacoes' => $input['observacoes']
            ]);
            $this->redirect(\route_url('clientes', ['success' => 1]));
        } catch (\Throwable $e) {
            $this->storeFormState($input, 'save');
            $this->redirect(\route_url('clientes/edit', ['id' => $id]));
        }
    }

    public function show() {
        $id = $_GET['id'] ?? 0;
        $cliente = $this->model->find($id);
        $historico = $this->model->getHistoricoOS($id);
        $this->view('clientes/view', ['title'=>'Perfil do Cliente','page_title'=>'Perfil do Cliente','cliente'=>$cliente,'historico'=>$historico]);
    }

    public function delete() {
        $id = $_POST['id'] ?? 0;
        $this->model->delete($id);
        $this->redirect(route_url('clientes'));
    }

    private function sanitizeInput(array $source): array {
        return [
            'nome' => trim((string) ($source['nome'] ?? '')),
            'cpf_cnpj' => trim((string) ($source['cpf_cnpj'] ?? '')),
            'telefone' => trim((string) ($source['telefone'] ?? '')),
            'whatsapp' => trim((string) ($source['whatsapp'] ?? '')),
            'email' => trim((string) ($source['email'] ?? '')),
            'endereco' => trim((string) ($source['endereco'] ?? '')),
            'observacoes' => trim((string) ($source['observacoes'] ?? '')),
        ];
    }

    private function validateInput(array $input, ?int $ignoreId = null): ?string {
        if ($input['nome'] === '' || $input['cpf_cnpj'] === '') {
            return 'required';
        }

        if ($input['email'] !== '' && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if ($this->model->cpfCnpjExists($input['cpf_cnpj'], $ignoreId)) {
            return 'duplicate';
        }

        return null;
    }

    private function storeFormState(array $input, string $error): void {
        $_SESSION['clientes_form_old'] = $input;
        $_SESSION['clientes_form_error'] = $error;
    }

    private function consumeFormState(): array {
        $old = $_SESSION['clientes_form_old'] ?? [];
        $error = $_SESSION['clientes_form_error'] ?? null;

        unset($_SESSION['clientes_form_old'], $_SESSION['clientes_form_error']);

        return [$old, $error];
    }
}
