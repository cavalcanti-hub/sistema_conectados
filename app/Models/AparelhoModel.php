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

    public function storeModelo(string $marca, string $modelo): bool
    {
        $marca = trim($marca);
        $modelo = trim($modelo);

        if ($marca === '' || $modelo === '') {
            return false;
        }

        $stmt = $this->db->prepare("INSERT IGNORE INTO aparelho_modelos (marca, modelo) VALUES (:marca, :modelo)");
        $stmt->execute([
            ':marca' => $marca,
            ':modelo' => $modelo,
        ]);

        return true;
    }

    public function getModelosPorMarca(): array
    {
        $catalog = [];

        $stmt = $this->db->query("
            SELECT marca, modelo FROM aparelho_modelos
            UNION
            SELECT marca, modelo FROM aparelhos
            WHERE COALESCE(marca, '') <> '' AND COALESCE(modelo, '') <> ''
            ORDER BY marca, modelo
        ");

        foreach ($stmt->fetchAll() as $row) {
            $marca = trim((string) ($row['marca'] ?? ''));
            $modelo = trim((string) ($row['modelo'] ?? ''));
            if ($marca === '' || $modelo === '') {
                continue;
            }

            if (!isset($catalog[$marca])) {
                $catalog[$marca] = [];
            }
            $catalog[$marca][] = $modelo;
        }

        foreach ($catalog as $marca => $modelos) {
            $modelos = array_values(array_unique($modelos));
            natcasesort($modelos);
            $catalog[$marca] = array_values($modelos);
        }

        uksort($catalog, 'strnatcasecmp');
        return $catalog;
    }
}
