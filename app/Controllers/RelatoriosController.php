<?php
namespace App\Controllers;
use App\Core\Controller;

class RelatoriosController extends Controller {
    public function index() {
        $this->view('relatorios/index', $this->reportData([
            'title' => 'Relatorios - Conectados',
            'page_title' => 'Central de Relatorios',
        ]));
    }

    public function print() {
        $this->view('relatorios/print', $this->reportData([
            'title' => 'Relatorio Gerencial - Conectados',
        ]));
    }

    private function reportData(array $extra = []): array {
        $osModel = new \App\Models\OsModel();
        $finModel = new \App\Models\FinanceiroModel();
        $estModel = new \App\Models\EstoqueModel();

        $baixoPecas = $this->safe(fn() => $estModel->getEstoqueBaixo('peca'), []);
        $baixoProdutos = $this->safe(fn() => $estModel->getEstoqueBaixo('produto'), []);
        $statusCount = $this->safe(fn() => $osModel->countByStatus(), []);

        return array_merge([
            'fat_dia' => $this->safe(fn() => $osModel->faturamento('dia'), 0),
            'fat_semana' => $this->safe(fn() => $osModel->faturamento('semana'), 0),
            'fat_mes' => $this->safe(fn() => $osModel->faturamento('mes'), 0),
            'receitas_dia' => $this->safe(fn() => $finModel->totalReceitas('dia'), 0),
            'despesas_dia' => $this->safe(fn() => $finModel->totalDespesas('dia'), 0),
            'receitas_semana' => $this->safe(fn() => $finModel->totalReceitas('semana'), 0),
            'despesas_semana' => $this->safe(fn() => $finModel->totalDespesas('semana'), 0),
            'receitas_mes' => $this->safe(fn() => $finModel->totalReceitas('mes'), 0),
            'despesas_mes' => $this->safe(fn() => $finModel->totalDespesas('mes'), 0),
            'baixo' => array_merge(is_array($baixoPecas) ? $baixoPecas : [], is_array($baixoProdutos) ? $baixoProdutos : []),
            'statusCount' => is_array($statusCount) ? $statusCount : [],
        ], $extra);
    }

    private function safe(callable $callback, $fallback) {
        try {
            return $callback();
        } catch (\Throwable $e) {
            app_log('Falha ao carregar relatorio', ['erro' => $e->getMessage()]);
            return $fallback;
        }
    }
}
