<?php
namespace App\Services;
require_once __DIR__.'/MercadoPagoWebhookException.php';

class MercadoPagoWebhookValidator
{
    public function __construct(private ?string $secret=null,private int $toleranceSeconds=300){}

    public function validate(string $rawBody,string $signature,string $requestId,?string $queryDataId=null,?int $now=null): array
    {
        $secret=trim((string)($this->secret??app_env('MERCADO_PAGO_WEBHOOK_SECRET','')));
        if($secret==='')throw new MercadoPagoWebhookException('WEBHOOK_SECRET_MISSING','Webhook indisponivel.',503);
        if($rawBody===''||strlen($rawBody)>1048576)throw new MercadoPagoWebhookException('WEBHOOK_PAYLOAD_INVALID','Payload invalido.',400);
        try{$payload=json_decode($rawBody,true,64,JSON_THROW_ON_ERROR);}catch(\Throwable){throw new MercadoPagoWebhookException('WEBHOOK_PAYLOAD_INVALID','Payload invalido.',400);}
        if(!is_array($payload))throw new MercadoPagoWebhookException('WEBHOOK_PAYLOAD_INVALID','Payload invalido.',400);
        $dataId=trim((string)($queryDataId??($payload['data']['id']??'')));
        if($dataId===''||!preg_match('/^[A-Za-z0-9._:-]{1,160}$/',$dataId))throw new MercadoPagoWebhookException('WEBHOOK_ID_MISSING','Identificador ausente.',400);
        $requestId=trim($requestId);if($requestId==='')throw new MercadoPagoWebhookException('WEBHOOK_REQUEST_ID_MISSING','Request ID ausente.',400);
        $parts=[];foreach(explode(',',$signature) as $part){[$k,$v]=array_pad(explode('=',trim($part),2),2,'');if($k!==''&&$v!=='')$parts[$k]=$v;}
        $ts=$parts['ts']??'';$provided=strtolower($parts['v1']??'');
        if(!preg_match('/^\d{10,13}$/',$ts))throw new MercadoPagoWebhookException('WEBHOOK_TIMESTAMP_MISSING','Timestamp ausente.',400);
        if(!preg_match('/^[a-f0-9]{64}$/',$provided))throw new MercadoPagoWebhookException('WEBHOOK_SIGNATURE_INVALID','Assinatura invalida.',401);
        $seconds=(int)$ts;if(strlen($ts)===13)$seconds=(int)floor($seconds/1000);$now=$now??time();
        if(abs($now-$seconds)>$this->toleranceSeconds)throw new MercadoPagoWebhookException('WEBHOOK_TIMESTAMP_EXPIRED','Timestamp expirado.',401);
        $manifest='id:'.strtolower($dataId).';request-id:'.$requestId.';ts:'.$ts.';';
        $expected=hash_hmac('sha256',$manifest,$secret);
        if(!hash_equals($expected,$provided))throw new MercadoPagoWebhookException('WEBHOOK_SIGNATURE_INVALID','Assinatura invalida.',401);
        return ['payload'=>$payload,'data_id'=>$dataId,'request_id'=>$requestId,'timestamp'=>$seconds];
    }
}
