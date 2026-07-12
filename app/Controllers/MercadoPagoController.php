<?php
namespace App\Controllers;

use App\Core\Controller;

class MercadoPagoController extends Controller
{
    public function pointWebhook(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input') ?: '';
        try {
            $signature=(string)($_SERVER['HTTP_X_SIGNATURE']??'');
            $requestId=(string)($_SERVER['HTTP_X_REQUEST_ID']??'');
            $dataId=$_GET['data.id']??($_GET['data_id']??null);
            $validated=(new \App\Services\MercadoPagoWebhookValidator())->validate($raw,$signature,$requestId,is_string($dataId)?$dataId:null);
            $result = (new \App\Models\MercadoPagoPointModel())->processWebhook($validated['payload']);
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\App\Services\MercadoPagoWebhookException $e) {
            http_response_code($e->httpStatus);
            echo json_encode(['ok'=>false,'code'=>$e->domainCode,'message'=>'Webhook rejeitado.']);
        } catch (\Throwable $e) {
            app_log('Falha segura no webhook Mercado Pago Point', ['erro_tipo' => get_class($e)]);
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

            (new \App\Services\MercadoPagoHttpClient())->request('PATCH','/point/integration-api/devices/'.rawurlencode($deviceId),['operating_mode'=>'STANDALONE'],$accessToken);
            echo json_encode(['ok' => true, 'message' => 'Maquininha liberada com sucesso! Lembre-se de clicar em "Atualizar" na tela dela.']);
        } catch (\Throwable $e) {
            http_response_code(502);
            app_log('Falha segura ao liberar Point',['erro_tipo'=>get_class($e)]);
            echo json_encode(['ok' => false, 'message' => 'Nao foi possivel liberar a maquininha com seguranca.']);
        }
    }
}
