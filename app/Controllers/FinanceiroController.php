<?php
namespace App\Controllers;
use App\Core\Controller;

class FinanceiroController extends Controller {
    private \App\Models\FinanceiroModel $model;
    public function __construct() { $this->model = new \App\Models\FinanceiroModel(); }

    public function index() {
        [$periodo, $tipo] = $this->filters();

        $pager = pagination_request(25, 100);
        $pagination = pagination_meta($this->model->countMovimentacoes($tipo, $periodo), $pager['page'], $pager['per_page']);
        $movimentacoes = $this->model->getMovimentacoes($tipo, $periodo, $pagination['per_page'], $pagination['offset']);
        foreach ($movimentacoes as &$movimentacao) {
            $movimentacao['taxa_referencia'] = $this->model->cardFeeReference($movimentacao);
        }
        unset($movimentacao);
        $formas = $this->model->getFormasPagamento($periodo);
        $receitas = $this->model->totalReceitas($periodo);
        $despesas = $this->model->totalDespesas($periodo);
        $qtdReceitas = $tipo === 'Despesa' ? 0 : $this->model->countMovimentacoes('Receita', $periodo);
        $qtdDespesas = $tipo === 'Receita' ? 0 : $this->model->countMovimentacoes('Despesa', $periodo);
        $receitas_dia = $this->model->totalReceitas('dia');
        $despesas_dia = $this->model->totalDespesas('dia');
        $this->view('financeiro/index', [
            'title'=>'Financeiro - Conectados',
            'page_title'=>'Fluxo de Caixa',
            'movimentacoes'=>$movimentacoes,
            'formas'=>$formas,
            'categorias_despesas'=>$this->model->getResumoCategorias($periodo, 'Despesa'),
            'receitas'=>$receitas,
            'despesas'=>$despesas,
            'lucro'=>$receitas - $despesas,
            'receitas_dia'=>$receitas_dia,
            'despesas_dia'=>$despesas_dia,
            'saldo_dia'=>$receitas_dia - $despesas_dia,
            'periodo'=>$periodo,
            'tipo'=>$tipo,
            'pagination'=>$pagination,
            'qtdReceitas'=>$qtdReceitas,
            'qtdDespesas'=>$qtdDespesas,
        ]);
    }

    public function print() {
        [$periodo, $tipo] = $this->filters();
        $movimentacoes = $this->model->getMovimentacoes($tipo, $periodo, 1000, 0);
        $receitas = $this->model->totalReceitas($periodo);
        $despesas = $this->model->totalDespesas($periodo);

        $this->view('financeiro/print', [
            'title' => 'Relatorio Financeiro',
            'periodo' => $periodo,
            'tipo' => $tipo,
            'movimentacoes' => $movimentacoes,
            'receitas' => $receitas,
            'despesas' => $despesas,
            'lucro' => $receitas - $despesas,
            'formas' => $this->model->getFormasPagamento($periodo),
            'categorias_despesas' => $this->model->getResumoCategorias($periodo, 'Despesa'),
        ]);
    }

    public function store() {
        $id = (int) $this->model->create($this->payload());
        $this->model->syncCardFeeForRevenue($id);
        $entry = $this->model->find($id);
        (new \App\Models\AuditModel())->record('criar', 'financeiro', $id, 'Lancamento financeiro criado: ' . ($entry['descricao'] ?? '#' . $id), [
            'tipo' => $entry['tipo'] ?? '',
            'valor' => $entry['valor'] ?? '',
        ]);
        header('Location: ' . route_url('financeiro', ['success' => 1])); exit;
    }

    public function update() {
        if (current_user_profile() !== 'Administrador') {
            http_response_code(403);
            echo 'Apenas administradores podem editar lancamentos financeiros.';
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || !$this->model->find($id)) {
            header('Location: ' . route_url('financeiro', ['error' => 'not_found']));
            exit;
        }

        $before = $this->model->find($id);
        $this->model->update($id, $this->payload(false));
        $this->model->syncCardFeeForRevenue($id);
        $after = $this->model->find($id);
        (new \App\Models\AuditModel())->record('editar', 'financeiro', $id, 'Lancamento financeiro editado: ' . ($after['descricao'] ?? '#' . $id), [
            'antes' => $before,
            'depois' => $after,
        ]);
        header('Location: ' . route_url('financeiro', ['updated' => 1])); exit;
    }

    public function delete() {
        if (current_user_profile() !== 'Administrador') {
            http_response_code(403);
            echo 'Apenas administradores podem remover lancamentos financeiros.';
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || !$this->model->find($id)) {
            header('Location: ' . route_url('financeiro', ['error' => 'not_found']));
            exit;
        }

        $entry = $this->model->find($id);
        $this->model->deleteCardFeeForRevenue($id);
        $this->model->delete($id);
        (new \App\Models\AuditModel())->record('excluir', 'financeiro', $id, 'Lancamento financeiro excluido: ' . ($entry['descricao'] ?? '#' . $id), [
            'tipo' => $entry['tipo'] ?? '',
            'valor' => $entry['valor'] ?? '',
        ]);
        header('Location: ' . route_url('financeiro', ['deleted' => 1])); exit;
    }

    private function payload(bool $includeUser = true): array {
        $tipo = $_POST['tipo'] ?? 'Despesa';
        $tipo = in_array($tipo, ['Receita', 'Despesa'], true) ? $tipo : 'Despesa';

        $data = [
            ':tipo' => $tipo,
            ':categoria' => trim((string) ($_POST['categoria'] ?? '')),
            ':descricao' => trim((string) ($_POST['descricao'] ?? '')),
            ':valor' => normalize_decimal_input($_POST['valor'] ?? 0),
            ':os_id' => !empty($_POST['os_id']) ? (int) $_POST['os_id'] : null,
            ':data_pagamento' => $_POST['data_pagamento'] ?: date('Y-m-d'),
            ':forma_pagamento' => trim((string) ($_POST['forma_pagamento'] ?? '')),
        ];

        if ($includeUser) {
            $data += [
            ':usuario_id' => $_SESSION['usuario_id'] ?? 1,
            ];
        }

        return $data;
    }

    private function filters(): array {
        $periodo = $_GET['periodo'] ?? 'dia';
        if ($periodo === 'custom' && !empty($_GET['data_custom'])) {
            $periodo = $_GET['data_custom'];
        }

        $periodosPermitidos = ['dia', 'semana', 'mes', 'todos'];
        if (in_array($periodo, $periodosPermitidos, true)) {
            // Valido
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodo)) {
            // Data personalizada valida
        } else {
            $periodo = 'dia';
        }
        $tipo = $_GET['tipo'] ?? '';
        $tipo = in_array($tipo, ['Receita', 'Despesa'], true) ? $tipo : '';
        return [$periodo, $tipo];
    }
}
