<?php
namespace App\Services;
require_once __DIR__.'/DeviceSecretException.php';

class DeviceSecretService
{
    private const PREFIX='v1:';
    private string $key;

    public function __construct(?string $encodedKey=null)
    {
        if(!function_exists('sodium_crypto_secretbox'))throw new DeviceSecretException('Protecao de segredo indisponivel.');
        $encodedKey=trim((string)($encodedKey??app_env('DEVICE_SECRET_KEY','')));
        $key=base64_decode($encodedKey,true);
        if($encodedKey===''||$key===false||strlen($key)!==SODIUM_CRYPTO_SECRETBOX_KEYBYTES)throw new DeviceSecretException('Chave de protecao do aparelho ausente ou invalida.');
        $this->key=$key;
    }

    public function encrypt(string $plain): string
    {
        if($plain==='')return '';
        if($this->isEncrypted($plain))return $plain;
        $nonce=random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return self::PREFIX.base64_encode($nonce.sodium_crypto_secretbox($plain,$nonce,$this->key));
    }

    public function decrypt(string $encoded): string
    {
        if($encoded==='')return '';
        if(!$this->isEncrypted($encoded))throw new DeviceSecretException('Segredo legado requer migracao controlada.');
        $binary=base64_decode(substr($encoded,strlen(self::PREFIX)),true);
        if($binary===false||strlen($binary)<=SODIUM_CRYPTO_SECRETBOX_NONCEBYTES)throw new DeviceSecretException('Segredo protegido invalido.');
        $nonce=substr($binary,0,SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);$cipher=substr($binary,SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain=sodium_crypto_secretbox_open($cipher,$nonce,$this->key);
        if($plain===false)throw new DeviceSecretException('Nao foi possivel abrir o segredo protegido.');
        return $plain;
    }

    public function isEncrypted(string $value): bool{return str_starts_with($value,self::PREFIX);}
}
