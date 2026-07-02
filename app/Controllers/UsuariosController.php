<?php
namespace App\Controllers;

use App\Core\Controller;

class UsuariosController extends Controller {
    private $model;

    public function __construct() {
        $this->model = new \App\Models\UsuarioModel();
    }

    public function index() {
        $usuarios = $this->model->getAll();
        $this->view('usuarios/index', [
            'title' => 'Usuários - Conectados',
            'page_title' => 'Usuários do Sistema',
            'usuarios' => $usuarios,
        ]);
    }

    public function create() {
        $this->view('usuarios/create', [
            'title' => 'Novo Usuario - Conectados',
            'page_title' => 'Cadastrar Usuario',
            'usuario' => null,
            'isEdit' => false,
            'errorCode' => $_GET['error'] ?? '',
        ]);
    }

    public function store() {
        $payload = [
            'nome' => trim($_POST['nome'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'perfil' => $_POST['perfil'] ?? 'Atendente',
            'status' => $_POST['status'] ?? 'Ativo',
            'especialidade' => trim($_POST['especialidade'] ?? ''),
            'comissao' => $_POST['comissao'] ?? '0.00',
            'meta_os_mes' => $_POST['meta_os_mes'] ?? '30',
        ];

        if ($payload['email'] !== '' && $this->model->emailExists($payload['email'])) {
            $this->redirect(route_url('usuarios/create', array_merge(['error' => 'email'], $payload)));
        }

        if (empty($_POST['senha'])) {
            $this->redirect(route_url('usuarios/create', array_merge(['error' => 'senha'], $payload)));
        }

        try {
            $this->model->create($_POST);
            $this->redirect(route_url('usuarios', ['success' => 1]));
        } catch (\Throwable $e) {
            $this->redirect(route_url('usuarios/create', array_merge(['error' => 'save'], $payload)));
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        $usuario = $this->model->find($id);
        if (!$usuario) {
            $this->redirect(route_url('usuarios'));
        }

        $this->view('usuarios/create', [
            'title' => 'Editar Usuario - Conectados',
            'page_title' => 'Editar Usuario',
            'usuario' => $usuario,
            'isEdit' => true,
            'errorCode' => $_GET['error'] ?? '',
        ]);
    }

    public function update() {
        $id = $_POST['id'] ?? 0;
        if (!empty($_POST['email']) && $this->model->emailExists((string) $_POST['email'], (int) $id)) {
            $this->redirect(route_url('usuarios/edit', ['id' => $id, 'error' => 'email']));
        }

        try {
            $this->model->update($id, $_POST);
            $this->redirect(route_url('usuarios', ['success' => 1]));
        } catch (PDOException $e) {
            $this->redirect(route_url('usuarios/edit', ['id' => $id, 'error' => 'save']));
        }
    }
}
