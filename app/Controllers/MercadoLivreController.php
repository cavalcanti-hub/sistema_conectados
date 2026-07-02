<?php

namespace App\Controllers;

use App\Core\Controller;

class MercadoLivreController extends Controller
{
    private \App\Models\MercadoLivreModel $mercadoLivre;
    private \App\Models\EstoqueModel $estoque;

    public function __construct()
    {
        $this->mercadoLivre = new \App\Models\MercadoLivreModel();
        $this->estoque = new \App\Models\EstoqueModel();
    }

    public function connect(): void
    {
        if (!$this->mercadoLivre->isConfigured()) {
            $this->redirect(route_url('config', ['tab' => 'marketplace', 'ml_error' => 'configure']));
        }

        $state = $this->makeState();
        $_SESSION['mercado_livre_oauth_state'] = $state;

        header('Location: ' . $this->mercadoLivre->authorizationUrl($state));
        exit;
    }

    public function callback(): void
    {
        $state = (string) ($_GET['state'] ?? '');
        $expectedState = (string) ($_SESSION['mercado_livre_oauth_state'] ?? '');
        unset($_SESSION['mercado_livre_oauth_state']);

        if ($state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
            $this->redirect(route_url('config', ['tab' => 'marketplace', 'ml_error' => 'state']));
        }

        $code = trim((string) ($_GET['code'] ?? ''));
        if ($code === '') {
            $this->redirect(route_url('config', ['tab' => 'marketplace', 'ml_error' => 'code']));
        }

        try {
            $this->mercadoLivre->exchangeAuthorizationCode($code);
            $this->redirect(route_url('config', ['tab' => 'marketplace', 'ml_connected' => 1]));
        } catch (\Throwable $e) {
            $_SESSION['ml_error'] = substr($e->getMessage(), 0, 900);
            $this->redirect(route_url('config', ['tab' => 'marketplace']));
        }
    }

    public function disconnect(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metodo nao permitido para esta acao.';
            return;
        }

        $this->mercadoLivre->disconnect();
        $this->redirect(route_url('config', ['tab' => 'marketplace', 'success' => 1]));
    }

    public function publish($id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metodo nao permitido para esta acao.';
            return;
        }

        $id = (int) ($id ?? ($_POST['id'] ?? 0));
        $product = $this->estoque->find($id);

        if (!$product || ($product['tipo'] ?? '') !== 'produto') {
            http_response_code(404);
            exit('Produto nao encontrado.');
        }

        try {
            $this->mercadoLivre->publishOrSyncProduct($product);
            $this->redirect(route_url('produtos', ['success' => 1]));
        } catch (\Throwable $e) {
            $_SESSION['ml_error'] = substr($e->getMessage(), 0, 900);
            $this->redirect(route_url('produtos'));
        }
    }

    private function makeState(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Throwable $e) {
            return sha1(uniqid('', true));
        }
    }
}
