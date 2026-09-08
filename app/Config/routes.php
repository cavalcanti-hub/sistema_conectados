<?php

use App\Controllers\AuthController;
use App\Controllers\ClientesController;
use App\Controllers\ComprasController;
use App\Controllers\ConfigController;
use App\Controllers\ConsultaController;
use App\Controllers\DashboardController;
use App\Controllers\EstoqueController;
use App\Controllers\FinanceiroController;
use App\Controllers\FornecedoresController;
use App\Controllers\GastosPessoaisController;
use App\Controllers\MediaController;
use App\Controllers\MercadoLivreController;
use App\Controllers\MercadoPagoController;
use App\Controllers\OsController;
use App\Controllers\PdvController;
use App\Controllers\ProdutosController;
use App\Controllers\RecadosController;
use App\Controllers\RelatoriosController;
use App\Controllers\SearchController;
use App\Controllers\TecnicosController;
use App\Controllers\TermosController;
use App\Controllers\UsuariosController;
use App\Controllers\VitrineController;

$routes = [];
$add = static function (string $path, array $methods, string $controller, string $action, array $options = []) use (&$routes): void {
    $routes[strtolower(trim($path, '/'))] = array_merge([
        'path' => trim($path, '/'),
        'methods' => $methods,
        'controller' => $controller,
        'action' => $action,
        'auth' => true,
        'profiles' => [],
        'csrf' => in_array('POST', $methods, true),
        'public' => false,
        'external_auth' => null,
        'response' => 'html',
        'params' => [],
    ], $options);
};

$admin = ['Administrador'];
$adminFinance = ['Administrador', 'Financeiro'];
$adminStock = ['Administrador', 'Estoque'];
$frontDesk = ['Administrador', 'Atendente'];
$service = ['Administrador', 'Atendente', 'Técnico', 'Tecnico'];
$purchasing = ['Administrador', 'Estoque', 'Financeiro', 'Atendente', 'Técnico', 'Tecnico'];

$add('dashboard', ['GET'], DashboardController::class, 'index');
$add('login', ['GET'], AuthController::class, 'index', ['auth' => false, 'public' => true, 'csrf' => false]);
$add('login/login', ['POST'], AuthController::class, 'login', ['auth' => false, 'public' => true, 'csrf' => true]);
$add('logout', ['POST'], AuthController::class, 'logout', ['csrf' => true]);
$add('search/global', ['GET'], SearchController::class, 'globalSearch', ['response' => 'json']);
$add('consulta', ['GET'], ConsultaController::class, 'index', ['auth' => false, 'public' => true, 'csrf' => false]);

$add('clientes', ['GET'], ClientesController::class, 'index', ['profiles' => $service]);
$add('clientes/searchJson', ['GET'], ClientesController::class, 'searchJson', ['profiles' => $service, 'response' => 'json']);
$add('clientes/create', ['GET'], ClientesController::class, 'create', ['profiles' => $service]);
$add('clientes/store', ['POST'], ClientesController::class, 'store', ['profiles' => $service]);
$add('clientes/edit', ['GET'], ClientesController::class, 'edit', ['profiles' => $service]);
$add('clientes/update', ['POST'], ClientesController::class, 'update', ['profiles' => $service]);
$add('clientes/show', ['GET'], ClientesController::class, 'show', ['profiles' => $service]);
$add('clientes/delete', ['POST'], ClientesController::class, 'delete', ['profiles' => $frontDesk]);

$add('os', ['GET'], OsController::class, 'index', ['profiles' => $service]);
$add('os/create', ['GET'], OsController::class, 'create', ['profiles' => $service]);
$add('os/store', ['POST'], OsController::class, 'store', ['profiles' => $service]);
$add('os/edit', ['GET'], OsController::class, 'edit', ['profiles' => $service]);
$add('os/update', ['POST'], OsController::class, 'update', ['profiles' => $service]);
$add('os/storeModelo', ['POST'], OsController::class, 'storeModelo', ['profiles' => $service, 'response' => 'json']);
$add('os/cancel', ['POST'], OsController::class, 'cancel', ['profiles' => $admin]);
$add('os/receberPoint', ['POST'], OsController::class, 'receberPoint', ['profiles' => $frontDesk]);
$add('os/registrarPagamento', ['POST'], OsController::class, 'registrarPagamento', ['profiles' => ['Administrador', 'Financeiro', 'Atendente']]);
$add('os/viewDetail', ['GET'], OsController::class, 'viewDetail', ['profiles' => $service]);
$add('os/print', ['GET'], OsController::class, 'print', ['profiles' => $service, 'response' => 'file']);
$add('os/pointStatus', ['GET'], OsController::class, 'pointStatus', ['profiles' => $service, 'response' => 'json']);

$add('estoque', ['GET'], EstoqueController::class, 'index', ['profiles' => $adminStock]);
$add('estoque/create', ['GET'], EstoqueController::class, 'create', ['profiles' => $adminStock]);
$add('estoque/store', ['POST'], EstoqueController::class, 'store', ['profiles' => $adminStock]);
$add('estoque/edit', ['GET'], EstoqueController::class, 'edit', ['profiles' => $adminStock]);
$add('estoque/update', ['POST'], EstoqueController::class, 'update', ['profiles' => $adminStock]);
$add('estoque/delete', ['POST'], EstoqueController::class, 'delete', ['profiles' => $adminStock]);
$add('estoque/movimentar', ['POST'], EstoqueController::class, 'movimentar', ['profiles' => $adminStock]);

$add('produtos', ['GET'], ProdutosController::class, 'index', ['profiles' => $adminStock]);
$add('produtos/print', ['GET'], ProdutosController::class, 'print', ['profiles' => $adminStock, 'response' => 'file']);
$add('produtos/create', ['GET'], ProdutosController::class, 'create', ['profiles' => $adminStock]);
$add('produtos/store', ['POST'], ProdutosController::class, 'store', ['profiles' => $adminStock, 'response' => 'json']);
$add('produtos/edit', ['GET'], ProdutosController::class, 'edit', ['profiles' => $adminStock, 'params' => ['positive_int?']]);
$add('produtos/update', ['POST'], ProdutosController::class, 'update', ['profiles' => $adminStock, 'response' => 'json']);
$add('produtos/delete', ['POST'], ProdutosController::class, 'delete', ['profiles' => $adminStock]);

$add('recados', ['GET'], RecadosController::class, 'index', ['profiles' => $frontDesk]);
$add('recados/store', ['POST'], RecadosController::class, 'store', ['profiles' => $frontDesk]);
$add('recados/updateStatus', ['POST'], RecadosController::class, 'updateStatus', ['profiles' => $frontDesk]);
$add('recados/delete', ['POST'], RecadosController::class, 'delete', ['profiles' => $admin]);

$add('compras', ['GET'], ComprasController::class, 'index', ['profiles' => $purchasing]);
$add('compras/print', ['GET'], ComprasController::class, 'print', ['profiles' => $purchasing, 'response' => 'file']);
$add('compras/store', ['POST'], ComprasController::class, 'store', ['profiles' => $purchasing]);
$add('compras/updateStatus', ['POST'], ComprasController::class, 'updateStatus', ['profiles' => $purchasing]);
$add('compras/delete', ['POST'], ComprasController::class, 'delete', ['profiles' => $admin]);

$add('termos', ['GET'], TermosController::class, 'index', ['profiles' => ['Administrador', 'Estoque', 'Financeiro', 'Atendente']]);
$add('termos/store', ['POST'], TermosController::class, 'store', ['profiles' => ['Administrador', 'Estoque', 'Financeiro', 'Atendente']]);
$add('termos/print', ['GET'], TermosController::class, 'print', ['profiles' => ['Administrador', 'Estoque', 'Financeiro', 'Atendente'], 'response' => 'file']);
$add('termos/delete', ['POST'], TermosController::class, 'delete', ['profiles' => $admin]);

$add('financeiro', ['GET'], FinanceiroController::class, 'index', ['profiles' => $adminFinance]);
$add('financeiro/print', ['GET'], FinanceiroController::class, 'print', ['profiles' => $adminFinance, 'response' => 'file']);
$add('financeiro/store', ['POST'], FinanceiroController::class, 'store', ['profiles' => $adminFinance]);
$add('financeiro/update', ['POST'], FinanceiroController::class, 'update', ['profiles' => $adminFinance]);
$add('financeiro/delete', ['POST'], FinanceiroController::class, 'delete', ['profiles' => $admin]);

$add('gastos_pessoais', ['GET'], GastosPessoaisController::class, 'index', ['profiles' => $admin]);
$add('gastos_pessoais/print', ['GET'], GastosPessoaisController::class, 'print', ['profiles' => $admin, 'response' => 'file']);
$add('gastos_pessoais/store', ['POST'], GastosPessoaisController::class, 'store', ['profiles' => $admin]);
$add('gastos_pessoais/update', ['POST'], GastosPessoaisController::class, 'update', ['profiles' => $admin]);
$add('gastos_pessoais/delete', ['POST'], GastosPessoaisController::class, 'delete', ['profiles' => $admin]);
$add('gastos_pessoais/addCategoria', ['POST'], GastosPessoaisController::class, 'addCategoria', ['profiles' => $admin]);
$add('gastos_pessoais/deleteCategoria', ['POST'], GastosPessoaisController::class, 'deleteCategoria', ['profiles' => $admin]);

$add('tecnicos', ['GET'], TecnicosController::class, 'index', ['profiles' => $frontDesk]);
$add('tecnicos/create', ['GET'], TecnicosController::class, 'create', ['profiles' => $frontDesk]);
$add('tecnicos/store', ['POST'], TecnicosController::class, 'store', ['profiles' => $frontDesk]);
$add('tecnicos/profile', ['GET'], TecnicosController::class, 'profile', ['profiles' => $service]);

$add('usuarios', ['GET'], UsuariosController::class, 'index', ['profiles' => $admin]);
$add('usuarios/create', ['GET'], UsuariosController::class, 'create', ['profiles' => $admin]);
$add('usuarios/store', ['POST'], UsuariosController::class, 'store', ['profiles' => $admin]);
$add('usuarios/edit', ['GET'], UsuariosController::class, 'edit', ['profiles' => $admin]);
$add('usuarios/update', ['POST'], UsuariosController::class, 'update', ['profiles' => $admin]);

$add('fornecedores', ['GET'], FornecedoresController::class, 'index', ['profiles' => ['Administrador', 'Estoque', 'Financeiro']]);
$add('fornecedores/store', ['POST'], FornecedoresController::class, 'store', ['profiles' => ['Administrador', 'Estoque', 'Financeiro']]);
$add('fornecedores/update', ['POST'], FornecedoresController::class, 'update', ['profiles' => ['Administrador', 'Estoque', 'Financeiro']]);
$add('fornecedores/storeNota', ['POST'], FornecedoresController::class, 'storeNota', ['profiles' => $purchasing]);
$add('fornecedores/updateNota', ['POST'], FornecedoresController::class, 'updateNota', ['profiles' => $purchasing]);
$add('fornecedores/baixarNota', ['POST'], FornecedoresController::class, 'baixarNota', ['profiles' => $purchasing]);
$add('fornecedores/cancelarNota', ['POST'], FornecedoresController::class, 'cancelarNota', ['profiles' => $purchasing]);
$add('fornecedores/imprimirNota', ['GET'], FornecedoresController::class, 'imprimirNota', ['profiles' => $purchasing, 'response' => 'file']);
$add('fornecedores/anexoNota', ['GET'], FornecedoresController::class, 'anexoNota', ['profiles' => $purchasing, 'response' => 'file']);

$add('config', ['GET'], ConfigController::class, 'index', ['profiles' => $admin]);
$add('config/update', ['POST'], ConfigController::class, 'update', ['profiles' => $admin]);
$add('config/backup', ['POST'], ConfigController::class, 'backup', ['profiles' => $admin, 'response' => 'file']);
$add('config/add_categoria', ['POST'], ConfigController::class, 'add_categoria', ['profiles' => $admin]);
$add('config/delete_categoria', ['POST'], ConfigController::class, 'delete_categoria', ['profiles' => $admin]);

$add('mercadolivre/connect', ['GET'], MercadoLivreController::class, 'connect', ['profiles' => $admin]);
$add('mercadolivre/callback', ['GET'], MercadoLivreController::class, 'callback', ['auth' => false, 'public' => true, 'csrf' => false, 'external_auth' => 'oauth_state']);
$add('mercadolivre/disconnect', ['POST'], MercadoLivreController::class, 'disconnect', ['profiles' => $admin]);
$add('mercadolivre/publish', ['POST'], MercadoLivreController::class, 'publish', ['profiles' => $admin, 'params' => ['positive_int?']]);

$add('mercadopago/pointWebhook', ['POST'], MercadoPagoController::class, 'pointWebhook', ['auth' => false, 'public' => true, 'csrf' => false, 'external_auth' => 'mercadopago_signature', 'response' => 'json']);
$add('mercadopago/liberarPoint', ['POST'], MercadoPagoController::class, 'liberarPoint', ['profiles' => $admin, 'response' => 'json']);

$add('pdv', ['GET'], PdvController::class, 'index', ['profiles' => $frontDesk]);
$add('pdv/finalizarVenda', ['POST'], PdvController::class, 'finalizarVenda', ['profiles' => $frontDesk]);
$add('pdv/pointStatus', ['GET'], PdvController::class, 'pointStatus', ['profiles' => $frontDesk, 'response' => 'json']);
$add('pdv/abrirCaixa', ['POST'], PdvController::class, 'abrirCaixa', ['profiles' => $admin]);
$add('pdv/fecharCaixa', ['POST'], PdvController::class, 'fecharCaixa', ['profiles' => $admin]);
$add('pdv/getprodutos', ['GET'], PdvController::class, 'getprodutos', ['profiles' => $frontDesk, 'response' => 'json']);
$add('pdv/imprimir', ['GET'], PdvController::class, 'imprimir', ['profiles' => $frontDesk, 'response' => 'file']);

$add('relatorios', ['GET'], RelatoriosController::class, 'index', ['profiles' => $adminFinance]);
$add('relatorios/print', ['GET'], RelatoriosController::class, 'print', ['profiles' => $adminFinance, 'response' => 'file']);

$add('vitrine', ['GET'], VitrineController::class, 'index', ['auth' => false, 'public' => true, 'csrf' => false]);
$add('vitrine/catalogo', ['GET'], VitrineController::class, 'catalogo', ['auth' => false, 'public' => true, 'csrf' => false]);
$add('vitrine/papelaria', ['GET'], VitrineController::class, 'papelaria', ['auth' => false, 'public' => true, 'csrf' => false]);
$add('vitrine/produto', ['GET'], VitrineController::class, 'produto', ['auth' => false, 'public' => true, 'csrf' => false, 'params' => ['positive_int?']]);
$add('vitrine/cadastrarConta', ['POST'], VitrineController::class, 'cadastrarConta', ['auth' => false, 'public' => true, 'csrf' => true]);
$add('vitrine/sairConta', ['POST'], VitrineController::class, 'sairConta', ['auth' => false, 'public' => true, 'csrf' => true]);
$add('vitrine/checkoutMercadoPago', ['POST'], VitrineController::class, 'checkoutMercadoPago', ['auth' => false, 'public' => true, 'csrf' => true, 'response' => 'json']);
$add('vitrine/statusMercadoPago', ['GET', 'POST'], VitrineController::class, 'statusMercadoPago', ['auth' => false, 'public' => true, 'csrf' => false, 'external_auth' => 'provider_reference', 'response' => 'json']);
$add('media/estoque', ['GET'], MediaController::class, 'estoque', ['auth' => false, 'public' => true, 'csrf' => false, 'response' => 'file']);

return [
    'routes' => $routes,
    'aliases' => [
        'compartilhar-catalogo' => ['target' => 'vitrine/catalogo', 'methods' => ['GET']],
        'index' => ['target' => '', 'methods' => ['GET']],
        'checklist' => ['target' => 'os/create', 'methods' => ['GET']],
        'login/logout' => ['target' => 'logout', 'methods' => ['POST'], 'internal' => true],
    ],
];
