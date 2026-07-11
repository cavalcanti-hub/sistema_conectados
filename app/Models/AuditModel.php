<?php
namespace App\Models;

use App\Config\Database;

class AuditModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function record(string $acao, string $entidade, ?int $entidadeId = null, string $descricao = '', array $dados = []): void
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO auditoria_logs
                (usuario_id, usuario_nome, acao, entidade, entidade_id, descricao, dados, ip)
                VALUES (:usuario_id, :usuario_nome, :acao, :entidade, :entidade_id, :descricao, :dados, :ip)");
            $stmt->execute([
                ':usuario_id' => current_user_id() ?: null,
                ':usuario_nome' => $_SESSION['usuario_nome'] ?? null,
                ':acao' => substr($acao, 0, 80),
                ':entidade' => substr($entidade, 0, 80),
                ':entidade_id' => $entidadeId,
                ':descricao' => $descricao,
                ':dados' => !empty($dados) ? json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                ':ip' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64),
            ]);
        } catch (\Throwable $e) {
            app_log('Falha ao gravar auditoria', ['erro' => $e->getMessage(), 'acao' => $acao, 'entidade' => $entidade]);
        }
    }

    public function recent(int $limit = 80): array
    {
        $limit = max(10, min(200, $limit));
        $stmt = $this->db->query("SELECT * FROM auditoria_logs ORDER BY created_at DESC, id DESC LIMIT {$limit}");
        return $stmt->fetchAll();
    }
}
