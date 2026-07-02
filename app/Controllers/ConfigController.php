<?php
namespace App\Controllers;
use App\Core\Controller;

class ConfigController extends Controller {
    private $catModel;
    public function __construct() { 
        $this->model = new \App\Models\ConfigModel(); 
        $this->catModel = new \App\Models\CategoriasModel();
    }

    public function index() {
        $settings = $this->model->getAll();
        $categoriasPecas = $this->catModel->getAll('peca');
        $categoriasProdutos = $this->catModel->getAll('produto');
        
        $this->view('config/index', [
            'title' => 'Configurações - Conectados',
            'page_title' => 'Configurações do Sistema',
            'settings' => $settings,
            'categoriasPecas' => $categoriasPecas,
            'categoriasProdutos' => $categoriasProdutos,
            'diagnostico' => $this->diagnosticoSistema($settings),
        ]);
    }

    private function diagnosticoSistema(array $settings): array
    {
        $uploads = public_path('uploads');
        $uploadsEstoque = public_path('uploads/estoque');
        $appUrl = app_url();

        return [
            'APP_ENV' => app_env('APP_ENV', ''),
            'APP_MODE' => app_env('APP_MODE', ''),
            'APP_URL' => app_env('APP_URL', ''),
            'APP_BASE_PATH' => app_env('APP_BASE_PATH', ''),
            'conexao_banco' => 'ok',
            'PHP' => PHP_VERSION,
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'pdo_mysql' => extension_loaded('pdo_mysql') ? 'ativo' : 'inativo',
            'curl' => extension_loaded('curl') ? 'ativo' : 'inativo',
            'gd' => extension_loaded('gd') ? 'ativo' : 'inativo',
            'fileinfo' => extension_loaded('fileinfo') ? 'ativo' : 'inativo',
            'mbstring' => extension_loaded('mbstring') ? 'ativo' : 'inativo',
            'uploads_writable' => is_dir($uploads) && is_writable($uploads) ? 'sim' : 'nao',
            'uploads_estoque_writable' => is_dir($uploadsEstoque) && is_writable($uploadsEstoque) ? 'sim' : 'nao',
            'APP_URL_publica' => is_public_url($appUrl) ? 'sim' : 'nao',
            'APP_DEBUG_seguro' => filter_var(app_env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN) ? 'nao' : 'sim',
            'mercado_livre_conectado' => !empty($settings['mercado_livre_refresh_token']) && !empty($settings['mercado_livre_user_id']) ? 'sim' : 'nao',
            'mercado_livre_token_expira' => $settings['mercado_livre_token_expires_at'] ?? '',
            'mercado_livre_categoria_padrao' => $settings['mercado_livre_default_category_id'] ?? '',
        ];
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($_POST['settings']);
            $this->redirect(route_url('config', ['success' => 1]));
        }
    }

    public function add_categoria() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->catModel->create($_POST['nome'], $_POST['tipo']);
            $this->redirect(route_url('config', ['tab' => 'categorias', 'success' => 1]));
        }
    }

    public function delete_categoria() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metodo nao permitido para esta acao.';
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->catModel->delete($id);
            $this->redirect(route_url('config', ['tab' => 'categorias', 'success' => 1]));
        }

        $this->redirect(route_url('config', ['tab' => 'categorias', 'error' => 'invalid_category']));
    }
}
