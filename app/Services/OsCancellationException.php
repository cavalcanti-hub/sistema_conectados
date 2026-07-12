<?php
namespace App\Services;

class OsCancellationException extends \RuntimeException
{
    public function __construct(public string $domainCode, string $message, public int $httpStatus = 422)
    {
        parent::__construct($message);
    }
}
