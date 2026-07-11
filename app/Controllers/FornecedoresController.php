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
            'ordensServico' => (new \App\Models\OsModel())->getAll([], 200),
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
            $id = $this->notas->create($this->notaPayload(), $this->itemsPayload());
            $nota = $this->notas->find($id);
            (new \App\Models\AuditModel())->record('criar', 'nota_compra', $id, 'Nota de compra lancada: ' . ($nota['numero'] ?: '#' . $id), [
                'fornecedor' => $nota['fornecedor_nome'] ?? '',
                'valor_total' => $nota['valor_total'] ?? '',
            ]);
            if (!empty($_FILES['anexo_nota']['name']) && !empty($nota['anexo_nome'])) {
                (new \App\Models\AuditModel())->record('anexar', 'nota_compra', $id, 'Anexo incluido na nota de compra: ' . ($nota['anexo_original'] ?: $nota['anexo_nome']));
            }
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
            $nota = $this->notas->find($id);
            (new \App\Models\AuditModel())->record('editar', 'nota_compra', $id, 'Nota de compra editada: ' . ($nota['numero'] ?: '#' . $id), [
                'fornecedor' => $nota['fornecedor_nome'] ?? '',
                'valor_total' => $nota['valor_total'] ?? '',
            ]);
            if (!empty($_FILES['anexo_nota']['name']) && !empty($nota['anexo_nome'])) {
                (new \App\Models\AuditModel())->record('anexar', 'nota_compra', $id, 'Anexo atualizado na nota de compra: ' . ($nota['anexo_original'] ?: $nota['anexo_nome']));
            }
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
            $nota = $this->notas->find($id);
            $osId = !empty($_POST['os_id']) ? (int) $_POST['os_id'] : null;
            $this->notas->baixar($id, $osId);
            (new \App\Models\AuditModel())->record('baixar', 'nota_compra', $id, 'Nota de compra baixada: ' . (($nota['numero'] ?? '') ?: '#' . $id), [
                'fornecedor' => $nota['fornecedor_nome'] ?? '',
                'valor_total' => $nota['valor_total'] ?? '',
                'os_id' => $osId ?: ($nota['os_id'] ?? null),
            ]);
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
            $nota = $this->notas->find($id);
            $this->notas->cancelar($id);
            (new \App\Models\AuditModel())->record('cancelar', 'nota_compra', $id, 'Nota de compra cancelada: ' . (($nota['numero'] ?? '') ?: '#' . $id), [
                'fornecedor' => $nota['fornecedor_nome'] ?? '',
                'valor_total' => $nota['valor_total'] ?? '',
            ]);
        }
        $this->redirect(route_url('fornecedores', ['cancelada' => 1]));
    }

    public function imprimirNota(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $nota = $id > 0 ? $this->notas->find($id) : null;
        if (!$nota) {
            $this->redirect(route_url('fornecedores'));
        }

        $settings = (new \App\Models\ConfigModel())->getAll();
        $company = [
            'name' => trim((string) ($settings['nome_empresa'] ?? 'Conectados')),
            'phone' => trim((string) ($settings['whatsapp'] ?? '')),
            'address' => trim((string) ($settings['endereco'] ?? '')),
            'website' => trim((string) ($settings['website'] ?? 'conectadosassistencia.com.br')),
            'logo_print' => asset_url('assets/img/logo-print.png?v=20260702-banner'),
        ];

        $this->view('fornecedores/print_nota', ['nota' => $nota, 'company' => $company]);
    }

    public function anexoNota(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $nota = $id > 0 ? $this->notas->find($id) : null;
        if (!$nota || empty($nota['anexo_nome'])) {
            http_response_code(404);
            echo 'Anexo nao encontrado.';
            return;
        }

        $fileName = basename((string) $nota['anexo_nome']);
        $path = public_path('uploads/notas_compra/' . $fileName);
        if (!is_file($path)) {
            http_response_code(404);
            echo 'Arquivo do anexo nao encontrado.';
            return;
        }

        $mime = (string) ($nota['anexo_mime'] ?? '');
        if ($mime === '') {
            $mime = 'application/octet-stream';
        }
        $original = basename((string) ($nota['anexo_original'] ?: $fileName));
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . str_replace('"', '', $original) . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    private function notaPayload(): array
    {
        $payload = [
            'fornecedor_id' => (int) ($_POST['fornecedor_id'] ?? 0),
            'os_id' => (int) ($_POST['os_id'] ?? 0),
            'numero' => $_POST['numero'] ?? '',
            'data_emissao' => $_POST['data_emissao'] ?? date('Y-m-d'),
            'data_vencimento' => $_POST['data_vencimento'] ?? '',
            'forma_pagamento' => $_POST['forma_pagamento'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
        ];

        $upload = validate_and_store_document_upload($_FILES['anexo_nota'] ?? [], 'notas_compra');
        if (!$upload['ok']) {
            throw new \RuntimeException((string) $upload['error']);
        }
        if (!empty($upload['name'])) {
            $payload['anexo_nome'] = $upload['name'];
            $payload['anexo_original'] = $upload['original'];
            $payload['anexo_mime'] = $upload['mime'];
        }

        return $payload;
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
