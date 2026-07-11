<?php
namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?self $instance = null;
    private PDO $conn;

    private function __construct()
    {
        $isProduction = strtolower((string) \app_env('APP_ENV', 'production')) === 'production';
        if ($isProduction) {
            foreach (['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $requiredKey) {
                if (\config_status($requiredKey) !== 'Configurado') {
                    throw new \RuntimeException('Configuracao critica de banco ausente ou invalida.');
                }
            }
        }

        try {
            $this->conn = new PDO(
                sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    \app_env('DB_HOST', '127.0.0.1'),
                    \app_env('DB_PORT', '3306'),
                    \app_env('DB_DATABASE', 'sistema-conectados')
                ),
                \app_env('DB_USERNAME', 'root'),
                \app_env('DB_PASSWORD', ''),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            $debug = filter_var(\app_env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
            die($debug ? 'Erro na conexao: ' . $e->getMessage() : 'Erro ao conectar ao banco de dados.');
        }
    }

    public static function getInstance(): PDO
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance->conn;
    }

    public static function reconnect(): PDO
    {
        self::$instance = new self();
        return self::$instance->conn;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}
