<?php
namespace App\Services;

class PaymentException extends \RuntimeException
{
    public string $domainCode;

    public function __construct(string $code, string $message, public int $httpStatus = 422)
    {
        parent::__construct($message);
        $this->code = 0;
        $this->domainCode = $code;
    }
}
