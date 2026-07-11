<?php
namespace App\Controllers;

use App\Core\Controller;

class MercadoPagoController extends Controller
{
    public function pointWebhook(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $payload = $_POST ?: $_GET ?: [];
        }

        try {
            $result = (new \App\Models\MercadoPagoPointModel())->processWebhook($payload);
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            app_log('Falha no webhook Mercado Pago Point', ['erro' => $e->getMessage(), 'payload' => $payload]);
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Falha ao processar webhook Point.']);
        }
    }

    public function liberarPoint(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $configModel = new \App\Models\ConfigModel();
            $settings = $configModel->getAll();
            $accessToken = trim((string) ($settings['mercadopago_access_token'] ?? ''));
            $deviceId = trim((string) ($settings['mercadopago_point_terminal_id'] ?? ''));

            if ($accessToken === '' || $deviceId === '') {
                echo json_encode(['ok' => false, 'message' => 'Token ou Terminal ID não configurado.']);
                return;
            }

            $ch = curl_init("https://api.mercadopago.com/point/integration-api/devices/{$deviceId}");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['operating_mode' => 'STANDALONE']));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                echo json_encode(['ok' => true, 'message' => 'Maquininha liberada com sucesso! Lembre-se de clicar em "Atualizar" na tela dela.']);
            } else {
                echo json_encode(['ok' => false, 'message' => 'Erro ao liberar maquininha.', 'details' => $response]);
            }
        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'message' => 'Erro interno ao liberar maquininha: ' . $e->getMessage()]);
        }
    }
}
