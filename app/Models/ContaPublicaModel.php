<?php
namespace App\Models;
use App\Config\Database;

class ContaPublicaModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM contas_publicas WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO contas_publicas (nome, email, whatsapp, senha) VALUES (:nome, :email, :whatsapp, :senha)");
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
}
