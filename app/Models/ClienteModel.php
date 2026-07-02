<?php
namespace App\Models;
use App\Config\Database;

class ClienteModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }

    private function listWhere($search = ''): array {
        $sql = " FROM clientes WHERE 1=1";
        $params = [];
        if ($search) {
            $sql .= " AND (nome LIKE :s OR cpf_cnpj LIKE :s2 OR telefone LIKE :s3)";
            $params = [':s' => "%$search%", ':s2' => "%$search%", ':s3' => "%$search%"];
        }

        return [$sql, $params];
    }

    public function getAll($search = '', ?int $limit = null, int $offset = 0) {
        [$where, $params] = $this->listWhere($search);
        $sql = "SELECT *" . $where . " ORDER BY nome ASC";
        if ($limit !== null) {
            $limit = max(1, (int) $limit);
            $offset = max(0, (int) $offset);
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countFiltered($search = ''): int {
        [$where, $params] = $this->listWhere($search);
        $stmt = $this->db->prepare("SELECT COUNT(*)" . $where);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO clientes (nome, cpf_cnpj, telefone, whatsapp, email, endereco, observacoes) 
                VALUES (:nome, :cpf_cnpj, :telefone, :whatsapp, :email, :endereco, :observacoes)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function cpfCnpjExists(string $cpfCnpj, ?int $ignoreId = null): bool {
        $sql = "SELECT COUNT(*) FROM clientes WHERE cpf_cnpj = :cpf_cnpj";
        $params = [':cpf_cnpj' => $cpfCnpj];

        if ($ignoreId !== null) {
            $sql .= " AND id <> :id";
            $params[':id'] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function update($id, $data) {
        $sql = "UPDATE clientes SET nome=:nome, cpf_cnpj=:cpf_cnpj, telefone=:telefone, whatsapp=:whatsapp, email=:email, endereco=:endereco, observacoes=:observacoes WHERE id=:id";
        $data[':id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getHistoricoOS($cliente_id) {
        $sql = "SELECT os.*, a.modelo FROM ordens_servico os JOIN aparelhos a ON os.aparelho_id = a.id WHERE os.cliente_id = :id ORDER BY os.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $cliente_id]);
        return $stmt->fetchAll();
    }

    public function count() {
        return $this->db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    }
}
