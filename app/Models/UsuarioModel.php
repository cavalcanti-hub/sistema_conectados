<?php
namespace App\Models;

use App\Config\Database;

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        $sql = "SELECT id, nome, email, perfil, especialidade, comissao, meta_os_mes, status, ultimo_login, created_at
                FROM usuarios
                ORDER BY nome";
        return $this->db->query($sql)->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool {
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        $params = [':email' => trim($email)];

        if ($ignoreId !== null) {
            $sql .= " AND id <> :id";
            $params[':id'] = $ignoreId;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data) {
        $password = (string) ($data['senha'] ?? '');
        if ($password === '') {
            throw new \InvalidArgumentException('Senha obrigatoria.');
        }

        $sql = "INSERT INTO usuarios (nome, email, senha, perfil, especialidade, comissao, meta_os_mes, status)
                VALUES (:nome, :email, :senha, :perfil, :especialidade, :comissao, :meta_os_mes, :status)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nome' => trim($data['nome'] ?? ''),
            ':email' => trim($data['email'] ?? ''),
            ':senha' => password_hash($password, PASSWORD_BCRYPT),
            ':perfil' => $data['perfil'] ?? 'Atendente',
            ':especialidade' => trim($data['especialidade'] ?? ''),
            ':comissao' => $data['comissao'] ?? 0,
            ':meta_os_mes' => $data['meta_os_mes'] ?? 30,
            ':status' => $data['status'] ?? 'Ativo',
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, array $data) {
        $params = [
            ':id' => $id,
            ':nome' => trim($data['nome'] ?? ''),
            ':email' => trim($data['email'] ?? ''),
            ':perfil' => $data['perfil'] ?? 'Atendente',
            ':especialidade' => trim($data['especialidade'] ?? ''),
            ':comissao' => $data['comissao'] ?? 0,
            ':meta_os_mes' => $data['meta_os_mes'] ?? 30,
            ':status' => $data['status'] ?? 'Ativo',
        ];

        $setSenha = '';
        if (!empty($data['senha'])) {
            $setSenha = ", senha = :senha";
            $params[':senha'] = password_hash($data['senha'], PASSWORD_BCRYPT);
        }

        $sql = "UPDATE usuarios
                SET nome = :nome,
                    email = :email,
                    perfil = :perfil,
                    especialidade = :especialidade,
                    comissao = :comissao,
                    meta_os_mes = :meta_os_mes,
                    status = :status
                    $setSenha
                WHERE id = :id";

        return $this->db->prepare($sql)->execute($params);
    }
}
