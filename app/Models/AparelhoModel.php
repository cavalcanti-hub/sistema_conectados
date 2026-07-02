<?php
namespace App\Models;
use App\Config\Database;

class AparelhoModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function create($data) {
        $sql = "INSERT INTO aparelhos (cliente_id, marca, modelo, imei, cor, senha_padrao, estado_fisico) 
                VALUES (:cliente_id, :marca, :modelo, :imei, :cor, :senha_padrao, :estado_fisico)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM aparelhos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
