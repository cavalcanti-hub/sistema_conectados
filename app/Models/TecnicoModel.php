<?php
namespace App\Models;
use App\Config\Database;

class TecnicoModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getAll() {
        $sql = "SELECT u.*, 
                (SELECT COUNT(*) FROM ordens_servico WHERE tecnico_id = u.id) as total_os,
                (SELECT COUNT(*) FROM ordens_servico WHERE tecnico_id = u.id AND status = 'Entregue') as os_concluidas
                FROM usuarios u WHERE u.perfil = 'Técnico' ORDER BY u.nome";
        return $this->db->query($sql)->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id AND perfil = 'Técnico'");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $data[':senha'] = password_hash($data[':senha'], PASSWORD_BCRYPT);
        $sql = "INSERT INTO usuarios (nome, email, senha, perfil, especialidade, status) VALUES (:nome, :email, :senha, 'Técnico', :especialidade, :status)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE usuarios SET nome=:nome, email=:email, especialidade=:especialidade, status=:status WHERE id=:id";
        $data[':id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function getOSDoTecnico($tecnico_id, $limit = 20) {
        $sql = "SELECT os.*, c.nome as cliente_nome, a.modelo FROM ordens_servico os JOIN clientes c ON os.cliente_id=c.id JOIN aparelhos a ON os.aparelho_id=a.id WHERE os.tecnico_id=:id ORDER BY os.created_at DESC LIMIT $limit";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $tecnico_id]);
        return $stmt->fetchAll();
    }

    public function getAtivos() {
        return $this->db->query("SELECT id, nome, especialidade FROM usuarios WHERE perfil = 'Técnico' AND status = 'Ativo' ORDER BY nome")->fetchAll();
    }
}
