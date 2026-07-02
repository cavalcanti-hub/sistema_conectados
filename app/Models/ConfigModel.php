<?php
namespace App\Models;
use App\Config\Database;

class ConfigModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getAll() {
        $stmt = $this->db->query("SELECT chave, valor FROM configuracoes");
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $r) { $settings[$r['chave']] = $r['valor']; }
        return $settings;
    }

    public function update($data) {
        $stmt = $this->db->prepare("INSERT INTO configuracoes (chave, valor)
            VALUES (:chave, :valor)
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        foreach ($data as $chave => $valor) {
            $stmt->execute([':chave' => $chave, ':valor' => $valor]);
        }
        return true;
    }
}
