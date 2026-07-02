<?php
namespace App\Models;
use App\Config\Database;

class CategoriasModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getAll($tipo = null) {
        $sql = "SELECT * FROM categorias WHERE 1=1";
        $params = [];
        if ($tipo) {
            $sql .= " AND tipo = :tipo";
            $params[':tipo'] = $tipo;
        }
        $sql .= " ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create($nome, $tipo) {
        $stmt = $this->db->prepare("INSERT IGNORE INTO categorias (nome, tipo) VALUES (:nome, :tipo)");
        return $stmt->execute([':nome' => $nome, ':tipo' => $tipo]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categorias WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
