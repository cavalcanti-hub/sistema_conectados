<?php

namespace App\Controllers;

use App\Core\Controller;

class AuthController extends Controller
{
    public function index()
    {
        $this->view('auth/login', ['title' => 'Login - Conectados']);
    }

    public function login()
    {
        $login = trim((string) ($_POST['email'] ?? ''));
        $senha = $_POST['senha'] ?? '';

        $db = \App\Config\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE (email = :login_email OR nome = :login_nome) AND status = 'Ativo' LIMIT 1");
        $stmt->execute([':login_email' => $login, ':login_nome' => $login]);
        $user = $stmt->fetch();

        $ok = $user && password_verify($senha, $user['senha']);

        if ($ok) {
            if (password_needs_rehash((string) $user['senha'], PASSWORD_BCRYPT)) {
                $db->prepare('UPDATE usuarios SET senha = :senha WHERE id = :id')
                    ->execute([
                        ':senha' => password_hash((string) $senha, PASSWORD_BCRYPT),
                        ':id' => $user['id'],
                    ]);
            }
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['perfil'] = $user['perfil'];
            $_SESSION['_last_activity'] = time();
            $db->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id")
                ->execute([':id' => $user['id']]);
            $this->redirect(route_url('dashboard'));
        }

        $this->redirect(route_url('login', ['erro' => 1]));
    }

    public function logout(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }
        \App\Core\AuthSession::terminate('manual');
        if (is_ajax_request()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'message' => 'Sessao encerrada.', 'redirect' => route_url('login')], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }
        $this->redirect(route_url('login'));
    }
}
