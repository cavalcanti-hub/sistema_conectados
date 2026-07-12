<?php
namespace App\Services;

class MercadoPagoHttpClient
{
    private const API_BASE = 'https://api.mercadopago.com';

    public function __construct(private $transport = null) {}

    public function request(string $method, string $path, ?array $payload, string $accessToken, array $extraHeaders = []): array
    {
        $accessToken = trim($accessToken);
        if ($accessToken === '') throw new \RuntimeException('Access Token do Mercado Pago nao configurado.');
        $url = str_starts_with($path, 'https://') ? $path : self::API_BASE . '/' . ltrim($path, '/');
        if (!str_starts_with($url, self::API_BASE . '/')) throw new \RuntimeException('Endpoint Mercado Pago invalido.');
        $body = $payload === null ? '' : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $headers = array_merge(['Authorization: Bearer ' . $accessToken, 'Accept: application/json', 'Content-Type: application/json'], $extraHeaders);
        $options = ['verify_peer'=>true,'verify_peer_name'=>true,'verify_host'=>2,'connect_timeout'=>8,'timeout'=>22];

        if (is_callable($this->transport)) {
            $result = ($this->transport)(strtoupper($method), $url, $body, $headers, $options);
        } elseif (function_exists('curl_init')) {
            $ch=curl_init($url);
            curl_setopt_array($ch,[CURLOPT_CUSTOMREQUEST=>strtoupper($method),CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>22,CURLOPT_HTTPHEADER=>$headers,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
            if($payload!==null)curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
            $raw=curl_exec($ch);$result=['status'=>(int)curl_getinfo($ch,CURLINFO_HTTP_CODE),'body'=>$raw,'error'=>$raw===false?curl_error($ch):''];curl_close($ch);
        } else {
            $context=stream_context_create(['http'=>['method'=>strtoupper($method),'timeout'=>22,'header'=>implode("\r\n",$headers)."\r\n",'content'=>$body,'ignore_errors'=>true],'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'allow_self_signed'=>false]]);
            $raw=@file_get_contents($url,false,$context);$status=0;foreach($http_response_header??[] as $line){if(preg_match('/^HTTP\/\S+\s+(\d{3})/',$line,$m)){$status=(int)$m[1];break;}}$result=['status'=>$status,'body'=>$raw,'error'=>$raw===false?'TLS ou comunicacao recusada.':''];
        }

        $status=(int)($result['status']??0);$raw=$result['body']??false;$error=(string)($result['error']??'');
        if($raw===false||$raw==='')throw new \RuntimeException($error!==''?'Falha segura na comunicacao TLS com Mercado Pago.':'Mercado Pago nao respondeu.');
        $data=json_decode((string)$raw,true);
        if(!is_array($data))throw new \RuntimeException('Mercado Pago retornou JSON invalido.');
        if($status<200||$status>=300)throw new \RuntimeException('Mercado Pago retornou erro HTTP '.$status.'.');
        return $data;
    }
}
