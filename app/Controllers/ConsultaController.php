<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Config\Database;
use App\Models\ConfigModel;

class ConsultaController extends Controller
{
    private \PDO $db;
    private ConfigModel $configModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->configModel = new ConfigModel();
    }

    public function index(): void
    {
        $numeroOs = trim((string) ($_GET['os'] ?? ''));
        $osData = null;
        $error = null;

        if ($numeroOs !== '') {
            $stmt = $this->db->prepare("
                SELECT os.id, os.numero_os, os.status, os.valor_total, os.prazo_estimado, os.created_at,
                       os.problema_relatado, os.diagnostico_tecnico, os.servico_realizar,
                       c.nome as cliente_nome,
                       a.marca, a.modelo, a.cor
                FROM ordens_servico os
                JOIN clientes c ON os.cliente_id = c.id
                JOIN aparelhos a ON os.aparelho_id = a.id
                WHERE os.numero_os = :num OR os.id = :id
                LIMIT 1
            ");
            $stmt->execute([':num' => $numeroOs, ':id' => is_numeric($numeroOs) ? (int)$numeroOs : 0]);
            $osData = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

            if (!$osData) {
                $error = 'Ordem de serviço não encontrada. Verifique o número informado.';
            }
        }

        $settings = $this->configModel->getAll();

        $this->view('consulta/index', [
            'title' => 'Consulta de Ordem de Serviço • ' . ($settings['nome_empresa'] ?? 'Conectados'),
            'os' => $osData,
            'numeroBuscado' => $numeroOs,
            'error' => $error,
            'empresa' => [
                'nome' => $settings['nome_empresa'] ?? 'Conectados',
                'whatsapp' => $settings['whatsapp'] ?? '',
                'telefone' => $settings['telefone'] ?? '',
                'endereco' => $settings['endereco'] ?? '',
                'horario' => $settings['horario_funcionamento'] ?? 'Seg à Sex: 08h às 18h'
            ]
        ]);
    }
}
