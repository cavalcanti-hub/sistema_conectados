<?php
namespace App\Controllers;
use App\Core\Controller;

class TecnicosController extends Controller {
    private $model;
    public function __construct() { $this->model = new \App\Models\TecnicoModel(); }

    public function index() {
        $tecnicos = $this->model->getAll();
        $this->view('tecnicos/index', ['title'=>'Técnicos - Conectados','page_title'=>'Gestão de Técnicos','tecnicos'=>$tecnicos]);
    }

    public function create() {
        $this->view('tecnicos/create', ['title'=>'Novo Técnico','page_title'=>'Cadastrar Técnico']);
    }

    public function store() {
        if (empty($_POST['senha'])) {
            $this->redirect(route_url('tecnicos/create', ['error' => 'senha']));
        }

        $this->model->create([
            ':nome' => $_POST['nome'],
            ':email' => $_POST['email'],
            ':senha' => $_POST['senha'],
            ':especialidade' => $_POST['especialidade'],
            ':status' => $_POST['status'] ?? 'Ativo'
        ]);
        $this->redirect(route_url('tecnicos', ['success' => 1]));
    }

    public function profile() {
        $id = $_GET['id'] ?? 0;
        $tecnico = $this->model->find($id);
        $os = $this->model->getOSDoTecnico($id);
        $this->view('tecnicos/view', ['title'=>'Perfil do Técnico','page_title'=>'Perfil do Técnico','tecnico'=>$tecnico,'os'=>$os]);
    }
}
