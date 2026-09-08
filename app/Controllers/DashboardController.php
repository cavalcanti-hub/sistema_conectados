<?php
namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller {
    private $configModel;

    public function __construct()
    {
        $this->configModel = new \App\Models\ConfigModel();
    }

    private function buildCompanyData(): array
    {
        $settings = $this->configModel->getAll();

        return [
            'name' => trim((string) ($settings['nome_empresa'] ?? 'Conectados')),
            'phone' => trim((string) ($settings['whatsapp'] ?? '')),
            'address' => trim((string) ($settings['endereco'] ?? '')),
            'email' => trim((string) ($settings['email_negocio'] ?? '')),
            'website' => trim((string) ($settings['website'] ?? '')),
            'logo' => asset_url('assets/img/logo.png'),
            'logo_print' => asset_url('assets/img/logo-print.png?v=20260702-banner'),
        ];
    }

    public function index() {
        $osModel = new \App\Models\OsModel();
        $estModel = new \App\Models\EstoqueModel();
        $finModel = new \App\Models\FinanceiroModel();

        $statusCount = $osModel->countByStatus();
        $estoqueBaixo = $estModel->getEstoqueBaixo();
        $recentes = $osModel->recentes(8);

        // OS ativas para detalhamento instantaneo nos modais
        $todasOs = $osModel->getAll([], 100);

        $fatDia = (float) $finModel->totalReceitas('dia');
        $fatSemana = (float) $finModel->totalReceitas('semana');
        $fatMes = (float) $finModel->totalReceitas('mes');
        $despesasMes = (float) $finModel->totalDespesas('mes');
        $despesasDia = (float) $finModel->totalDespesas('dia');
        $despesasSemana = (float) $finModel->totalDespesas('semana');

        $formasDia = $finModel->getFormasPagamento('dia');
        $formasSemana = $finModel->getFormasPagamento('semana');
        $formasMes = $finModel->getFormasPagamento('mes');

        $data = [
            'title' => 'Conectados - Dashboard',
            'page_title' => 'Painel de Controle',
            'statusCount' => $statusCount,
            'estoqueBaixo' => $estoqueBaixo,
            'recentes' => $recentes,
            'todasOs' => $todasOs,
            'fat_dia' => $fatDia,
            'fat_semana' => $fatSemana,
            'fat_mes' => $fatMes,
            'despesas_dia' => $despesasDia,
            'despesas_semana' => $despesasSemana,
            'despesas_mes' => $despesasMes,
            'receitas_mes' => $fatMes,
            'formas_dia' => $formasDia,
            'formas_semana' => $formasSemana,
            'formas_mes' => $formasMes,
        ];

        $printMode = normalize_print_mode($_GET['print'] ?? '');
        if (isset($_GET['print']) && $printMode !== '') {
            $agenda = array_values(array_filter($osModel->getAll(), static function ($os) {
                return !in_array($os['status'] ?? '', ['Entregue', 'Cancelado', 'Reprovado'], true);
            }));
            $agenda = array_slice($agenda, 0, 30);

            render_resource_view(
                $printMode === 'a4' ? 'print/a4' : 'print/thermal_80',
                [
                    'printMode' => $printMode,
                    'printContext' => 'agenda',
                    'company' => $this->buildCompanyData(),
                    'documentTitle' => 'Agenda operacional',
                    'agendaItems' => $agenda,
                    'printData' => [
                        'title' => 'Agenda operacional',
                        'generated_at' => date('Y-m-d H:i:s'),
                        'items' => $agenda,
                        'final_message' => 'Fim da agenda do dia',
                    ],
                ]
            );
            return;
        }

        $this->view('dashboard/index', $data);
    }
}
