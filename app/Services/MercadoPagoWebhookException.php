<?php
namespace App\Services;
class MercadoPagoWebhookException extends \RuntimeException
{
    public function __construct(public string $domainCode,string $message,public int $httpStatus=401){parent::__construct($message);}
}
