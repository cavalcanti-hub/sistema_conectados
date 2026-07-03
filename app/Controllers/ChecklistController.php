<?php
namespace App\Controllers;

use App\Core\Controller;

class ChecklistController extends Controller
{
    public function index(): void
    {
        $os = null;
        $osId = (int) ($_GET['os_id'] ?? 0);

        if ($osId > 0) {
            $osModel = new \App\Models\OsModel();
            $os = $osModel->find($osId) ?: null;
        }

        $createdAt = !empty($os['created_at'] ?? null)
            ? date('d/m/Y H:i', strtotime((string) $os['created_at']))
            : date('d/m/Y H:i');

        $osInfo = [
            'id' => $osId,
            'number' => (string) ($os['numero_os'] ?? 'CHECK-IN'),
            'status' => (string) ($os['status'] ?? 'Rascunho'),
            'client' => (string) ($os['cliente_nome'] ?? 'Cliente nao vinculado'),
            'brand' => (string) ($os['marca'] ?? 'Marca nao informada'),
            'model' => (string) ($os['modelo'] ?? 'Modelo nao informado'),
            'imei' => (string) ($os['imei'] ?? 'IMEI nao informado'),
            'created_at' => $createdAt,
            'technician' => (string) ($os['tecnico_nome'] ?? ($_SESSION['usuario_nome'] ?? 'Tecnico responsavel')),
        ];

        $this->view('checklist/index', [
            'title' => 'Checklist de Entrada - Conectados',
            'page_title' => 'Checklist de Entrada',
            'osInfo' => $osInfo,
        ]);
    }
}
