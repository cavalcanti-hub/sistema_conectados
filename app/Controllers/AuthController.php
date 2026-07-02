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
        $email = trim((string) ($_POST['email'] ?? ''));
        $senha = $_POST['senha'] ?? '';

        $db = \App\Config\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email AND status = 'Ativo' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        $ok = $user && password_verify($senha, $user['senha']);

        if ($ok) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['perfil'] = $user['perfil'];
            $db->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id")
                ->execute([':id' => $user['id']]);
            $this->redirect(route_url('dashboard'));
        }

        $this->redirect(route_url('login', ['erro' => 1]));
    }

    public function logout()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_destroy();
        $this->redirect(route_url('login'));
    }
}
