<?php

namespace App\Controllers;

use App\Core\Controller;

class TermosController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \App\Models\TermosCompraVendaModel();
    }

    public function index()
    {
        [$old, $error] = $this->consumeFormState();
        $filters = $this->filters();
        $pager = pagination_request(15, 80);
        $pagination = pagination_meta($this->model->count($filters), $pager['page'], $pager['per_page']);

        $this->view('termos/index', [
            'title' => 'Termos de Compra e Venda - Conectados',
            'page_title' => 'Termos de Compra e Venda',
            'items' => $this->model->getAll($filters, $pagination['per_page'], $pagination['offset']),
            'pagination' => $pagination,
            'filters' => $filters,
            'old' => $old,
            'error' => $error,
            'defaults' => $this->companyDefaults(),
        ]);
    }

    public function store()
    {
        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input);

        if ($error !== null) {
            $this->storeFormState($input, $error);
            $this->redirect(route_url('termos'));
        }

        $id = $this->model->create([
            ':numero_termo' => $this->generateNumber(),
            ':vendedor_nome' => $input['vendedor_nome'],
            ':vendedor_contato' => $input['vendedor_contato'],
            ':vendedor_cpf' => $input['vendedor_cpf'],
            ':vendedor_rg' => $input['vendedor_rg'],
            ':vendedor_endereco' => $input['vendedor_endereco'],
            ':data_entrada' => $input['data_entrada'],
            ':equipamento_tipo' => $input['equipamento_tipo'],
            ':marca_modelo' => $input['marca_modelo'],
            ':imei1' => $input['imei1'],
            ':imei2' => $input['imei2'],
            ':senha_autorizada' => $input['senha_autorizada'],
            ':chip_ssd_card' => $input['chip_ssd_card'],
            ':bateria' => $input['bateria'],
            ':acessorios' => $input['acessorios'],
            ':estado_aparelho' => $input['estado_aparelho'],
            ':valor_compra' => $input['valor_compra'],
            ':comprador_nome' => $input['comprador_nome'],
            ':comprador_contato' => $input['comprador_contato'],
            ':comprador_documento' => $input['comprador_documento'],
            ':comprador_endereco' => $input['comprador_endereco'],
            ':observacoes' => $input['observacoes'],
            ':usuario_id' => current_user_id(),
        ]);

        $this->redirect(route_url('termos/print', ['id' => $id]));
    }

    public function print()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $termo = $this->model->find($id);
        if (!$termo) {
            $this->redirect(route_url('termos'));
        }

        $this->view('termos/print', [
            'title' => 'Termo de Compra e Venda',
            'termo' => $termo,
            'company' => $this->companyDefaults(),
        ]);
    }

    public function delete()
    {
        if (current_user_profile() !== 'Administrador') {
            http_response_code(403);
            echo 'Apenas administradores podem remover termos.';
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
        }

        $this->redirect(route_url('termos', ['deleted' => 1]));
    }

    private function sanitizeInput(array $source): array
    {
        $dataEntrada = trim((string) ($source['data_entrada'] ?? ''));
        if ($dataEntrada === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataEntrada)) {
            $dataEntrada = date('Y-m-d');
        }

        $equipamentoTipo = trim((string) ($source['equipamento_tipo'] ?? 'Smartphone'));
        $tipos = ['Smartphone', 'Tablet', 'Notebook', 'Outro'];
        if (!in_array($equipamentoTipo, $tipos, true)) {
            $equipamentoTipo = 'Smartphone';
        }

        return [
            'vendedor_nome' => trim((string) ($source['vendedor_nome'] ?? '')),
            'vendedor_contato' => trim((string) ($source['vendedor_contato'] ?? '')),
            'vendedor_cpf' => trim((string) ($source['vendedor_cpf'] ?? '')),
            'vendedor_rg' => trim((string) ($source['vendedor_rg'] ?? '')),
            'vendedor_endereco' => trim((string) ($source['vendedor_endereco'] ?? '')),
            'data_entrada' => $dataEntrada,
            'equipamento_tipo' => $equipamentoTipo,
            'marca_modelo' => trim((string) ($source['marca_modelo'] ?? '')),
            'imei1' => trim((string) ($source['imei1'] ?? '')),
            'imei2' => trim((string) ($source['imei2'] ?? '')),
            'senha_autorizada' => 0,
            'chip_ssd_card' => 0,
            'bateria' => 0,
            'acessorios' => trim((string) ($source['acessorios'] ?? '')),
            'estado_aparelho' => trim((string) ($source['estado_aparelho'] ?? '')),
            'valor_compra' => normalize_decimal_input($source['valor_compra'] ?? '0'),
            'comprador_nome' => trim((string) ($source['comprador_nome'] ?? '')),
            'comprador_contato' => trim((string) ($source['comprador_contato'] ?? '')),
            'comprador_documento' => trim((string) ($source['comprador_documento'] ?? '')),
            'comprador_endereco' => trim((string) ($source['comprador_endereco'] ?? '')),
            'observacoes' => trim((string) ($source['observacoes'] ?? '')),
        ];
    }

    private function validateInput(array $input): ?string
    {
        if ($input['vendedor_nome'] === '' || $input['marca_modelo'] === '' || $input['comprador_nome'] === '') {
            return 'required';
        }

        if ($input['valor_compra'] < 0) {
            return 'valor';
        }

        return null;
    }

    private function filters(): array
    {
        return [
            'search' => trim((string) ($_GET['search'] ?? '')),
            'date_from' => $this->validDate($_GET['date_from'] ?? '') ?: '',
            'date_to' => $this->validDate($_GET['date_to'] ?? '') ?: '',
        ];
    }

    private function validDate($date): ?string
    {
        $date = trim((string) $date);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
    }

    private function companyDefaults(): array
    {
        $settings = (new \App\Models\ConfigModel())->getAll();

        return [
            'nome' => trim((string) ($settings['nome_empresa'] ?? 'Conectados')) ?: 'Conectados',
            'contato' => trim((string) ($settings['whatsapp'] ?? '')),
            'endereco' => trim((string) ($settings['endereco'] ?? '')),
            'documento' => '',
            'email' => trim((string) ($settings['email_negocio'] ?? '')),
        ];
    }

    private function generateNumber(): string
    {
        try {
            $suffix = strtoupper(bin2hex(random_bytes(2)));
        } catch (\Throwable $e) {
            $suffix = (string) random_int(1000, 9999);
        }

        return 'TCV-' . date('Ymd-His') . '-' . $suffix;
    }

    private function storeFormState(array $input, string $error): void
    {
        $_SESSION['termos_form_old'] = $input;
        $_SESSION['termos_form_error'] = $error;
    }

    private function consumeFormState(): array
    {
        $old = $_SESSION['termos_form_old'] ?? [];
        $error = $_SESSION['termos_form_error'] ?? null;

        unset($_SESSION['termos_form_old'], $_SESSION['termos_form_error']);

        return [$old, $error];
    }
}
