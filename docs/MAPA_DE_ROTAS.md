# Mapa de rotas — Sistema Conectados v1.0.1-security

Gerado a partir do registro executável `app/Config/routes.php`. Nenhuma credencial é incluída.

| Caminho | Métodos | Controlador | Ação | Pública | Autenticação | Permissão/perfis | CSRF | Resposta | Observação |
|---|---|---|---|---|---|---|---|---|---|
| `/dashboard` | GET | DashboardController | `index` | Não | Obrigatória | — | Não | html |  |
| `/login` | GET | AuthController | `index` | Sim | Não | — | Não | html |  |
| `/login/login` | POST | AuthController | `login` | Sim | Não | — | Sim | html |  |
| `/logout` | POST | AuthController | `logout` | Não | Obrigatória | — | Sim | html |  |
| `/clientes` | GET | ClientesController | `index` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | html |  |
| `/clientes/searchJson` | GET | ClientesController | `searchJson` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | json |  |
| `/clientes/create` | GET | ClientesController | `create` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | html |  |
| `/clientes/store` | POST | ClientesController | `store` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Sim | html |  |
| `/clientes/edit` | GET | ClientesController | `edit` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | html |  |
| `/clientes/update` | POST | ClientesController | `update` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Sim | html |  |
| `/clientes/show` | GET | ClientesController | `show` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | html |  |
| `/clientes/delete` | POST | ClientesController | `delete` | Não | Obrigatória | Administrador, Atendente | Sim | html |  |
| `/os` | GET | OsController | `index` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | html |  |
| `/os/create` | GET | OsController | `create` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | html |  |
| `/os/store` | POST | OsController | `store` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Sim | html |  |
| `/os/edit` | GET | OsController | `edit` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | html |  |
| `/os/update` | POST | OsController | `update` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Sim | html |  |
| `/os/storeModelo` | POST | OsController | `storeModelo` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Sim | json |  |
| `/os/delete` | POST | OsController | `delete` | Não | Obrigatória | Administrador | Sim | html |  |
| `/os/receberPoint` | POST | OsController | `receberPoint` | Não | Obrigatória | Administrador, Atendente | Sim | html |  |
| `/os/registrarPagamento` | POST | OsController | `registrarPagamento` | Não | Obrigatória | Administrador, Financeiro, Atendente | Sim | html |  |
| `/os/viewDetail` | GET | OsController | `viewDetail` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | html |  |
| `/os/print` | GET | OsController | `print` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | file |  |
| `/os/pointStatus` | GET | OsController | `pointStatus` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | json |  |
| `/estoque` | GET | EstoqueController | `index` | Não | Obrigatória | Administrador, Estoque | Não | html |  |
| `/estoque/create` | GET | EstoqueController | `create` | Não | Obrigatória | Administrador, Estoque | Não | html |  |
| `/estoque/store` | POST | EstoqueController | `store` | Não | Obrigatória | Administrador, Estoque | Sim | html |  |
| `/estoque/edit` | GET | EstoqueController | `edit` | Não | Obrigatória | Administrador, Estoque | Não | html |  |
| `/estoque/update` | POST | EstoqueController | `update` | Não | Obrigatória | Administrador, Estoque | Sim | html |  |
| `/estoque/delete` | POST | EstoqueController | `delete` | Não | Obrigatória | Administrador, Estoque | Sim | html |  |
| `/estoque/movimentar` | POST | EstoqueController | `movimentar` | Não | Obrigatória | Administrador, Estoque | Sim | html |  |
| `/produtos` | GET | ProdutosController | `index` | Não | Obrigatória | Administrador, Estoque | Não | html |  |
| `/produtos/print` | GET | ProdutosController | `print` | Não | Obrigatória | Administrador, Estoque | Não | file |  |
| `/produtos/create` | GET | ProdutosController | `create` | Não | Obrigatória | Administrador, Estoque | Não | html |  |
| `/produtos/store` | POST | ProdutosController | `store` | Não | Obrigatória | Administrador, Estoque | Sim | json |  |
| `/produtos/edit` | GET | ProdutosController | `edit` | Não | Obrigatória | Administrador, Estoque | Não | html |  |
| `/produtos/update` | POST | ProdutosController | `update` | Não | Obrigatória | Administrador, Estoque | Sim | json |  |
| `/produtos/delete` | POST | ProdutosController | `delete` | Não | Obrigatória | Administrador, Estoque | Sim | html |  |
| `/recados` | GET | RecadosController | `index` | Não | Obrigatória | Administrador, Atendente | Não | html |  |
| `/recados/store` | POST | RecadosController | `store` | Não | Obrigatória | Administrador, Atendente | Sim | html |  |
| `/recados/updateStatus` | POST | RecadosController | `updateStatus` | Não | Obrigatória | Administrador, Atendente | Sim | html |  |
| `/recados/delete` | POST | RecadosController | `delete` | Não | Obrigatória | Administrador | Sim | html |  |
| `/compras` | GET | ComprasController | `index` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente, Técnico, Tecnico | Não | html |  |
| `/compras/print` | GET | ComprasController | `print` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente, Técnico, Tecnico | Não | file |  |
| `/compras/store` | POST | ComprasController | `store` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente, Técnico, Tecnico | Sim | html |  |
| `/compras/updateStatus` | POST | ComprasController | `updateStatus` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente, Técnico, Tecnico | Sim | html |  |
| `/compras/delete` | POST | ComprasController | `delete` | Não | Obrigatória | Administrador | Sim | html |  |
| `/termos` | GET | TermosController | `index` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente | Não | html |  |
| `/termos/store` | POST | TermosController | `store` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente | Sim | html |  |
| `/termos/print` | GET | TermosController | `print` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente | Não | file |  |
| `/termos/delete` | POST | TermosController | `delete` | Não | Obrigatória | Administrador | Sim | html |  |
| `/financeiro` | GET | FinanceiroController | `index` | Não | Obrigatória | Administrador, Financeiro | Não | html |  |
| `/financeiro/print` | GET | FinanceiroController | `print` | Não | Obrigatória | Administrador, Financeiro | Não | file |  |
| `/financeiro/store` | POST | FinanceiroController | `store` | Não | Obrigatória | Administrador, Financeiro | Sim | html |  |
| `/financeiro/update` | POST | FinanceiroController | `update` | Não | Obrigatória | Administrador, Financeiro | Sim | html |  |
| `/financeiro/delete` | POST | FinanceiroController | `delete` | Não | Obrigatória | Administrador | Sim | html |  |
| `/gastos_pessoais` | GET | GastosPessoaisController | `index` | Não | Obrigatória | Administrador | Não | html |  |
| `/gastos_pessoais/print` | GET | GastosPessoaisController | `print` | Não | Obrigatória | Administrador | Não | file |  |
| `/gastos_pessoais/store` | POST | GastosPessoaisController | `store` | Não | Obrigatória | Administrador | Sim | html |  |
| `/gastos_pessoais/update` | POST | GastosPessoaisController | `update` | Não | Obrigatória | Administrador | Sim | html |  |
| `/gastos_pessoais/delete` | POST | GastosPessoaisController | `delete` | Não | Obrigatória | Administrador | Sim | html |  |
| `/gastos_pessoais/addCategoria` | POST | GastosPessoaisController | `addCategoria` | Não | Obrigatória | Administrador | Sim | html |  |
| `/gastos_pessoais/deleteCategoria` | POST | GastosPessoaisController | `deleteCategoria` | Não | Obrigatória | Administrador | Sim | html |  |
| `/tecnicos` | GET | TecnicosController | `index` | Não | Obrigatória | Administrador, Atendente | Não | html |  |
| `/tecnicos/create` | GET | TecnicosController | `create` | Não | Obrigatória | Administrador, Atendente | Não | html |  |
| `/tecnicos/store` | POST | TecnicosController | `store` | Não | Obrigatória | Administrador, Atendente | Sim | html |  |
| `/tecnicos/profile` | GET | TecnicosController | `profile` | Não | Obrigatória | Administrador, Atendente, Técnico, Tecnico | Não | html |  |
| `/usuarios` | GET | UsuariosController | `index` | Não | Obrigatória | Administrador | Não | html |  |
| `/usuarios/create` | GET | UsuariosController | `create` | Não | Obrigatória | Administrador | Não | html |  |
| `/usuarios/store` | POST | UsuariosController | `store` | Não | Obrigatória | Administrador | Sim | html |  |
| `/usuarios/edit` | GET | UsuariosController | `edit` | Não | Obrigatória | Administrador | Não | html |  |
| `/usuarios/update` | POST | UsuariosController | `update` | Não | Obrigatória | Administrador | Sim | html |  |
| `/fornecedores` | GET | FornecedoresController | `index` | Não | Obrigatória | Administrador, Estoque, Financeiro | Não | html |  |
| `/fornecedores/store` | POST | FornecedoresController | `store` | Não | Obrigatória | Administrador, Estoque, Financeiro | Sim | html |  |
| `/fornecedores/update` | POST | FornecedoresController | `update` | Não | Obrigatória | Administrador, Estoque, Financeiro | Sim | html |  |
| `/fornecedores/storeNota` | POST | FornecedoresController | `storeNota` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente, Técnico, Tecnico | Sim | html |  |
| `/fornecedores/updateNota` | POST | FornecedoresController | `updateNota` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente, Técnico, Tecnico | Sim | html |  |
| `/fornecedores/baixarNota` | POST | FornecedoresController | `baixarNota` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente, Técnico, Tecnico | Sim | html |  |
| `/fornecedores/cancelarNota` | POST | FornecedoresController | `cancelarNota` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente, Técnico, Tecnico | Sim | html |  |
| `/fornecedores/imprimirNota` | GET | FornecedoresController | `imprimirNota` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente, Técnico, Tecnico | Não | file |  |
| `/fornecedores/anexoNota` | GET | FornecedoresController | `anexoNota` | Não | Obrigatória | Administrador, Estoque, Financeiro, Atendente, Técnico, Tecnico | Não | file |  |
| `/config` | GET | ConfigController | `index` | Não | Obrigatória | Administrador | Não | html |  |
| `/config/update` | POST | ConfigController | `update` | Não | Obrigatória | Administrador | Sim | html |  |
| `/config/backup` | POST | ConfigController | `backup` | Não | Obrigatória | Administrador | Sim | file |  |
| `/config/add_categoria` | POST | ConfigController | `add_categoria` | Não | Obrigatória | Administrador | Sim | html |  |
| `/config/delete_categoria` | POST | ConfigController | `delete_categoria` | Não | Obrigatória | Administrador | Sim | html |  |
| `/mercadolivre/connect` | GET | MercadoLivreController | `connect` | Não | Obrigatória | Administrador | Não | html |  |
| `/mercadolivre/callback` | GET | MercadoLivreController | `callback` | Sim | Não | — | Não | html | Autenticação externa: oauth_state |
| `/mercadolivre/disconnect` | POST | MercadoLivreController | `disconnect` | Não | Obrigatória | Administrador | Sim | html |  |
| `/mercadolivre/publish` | POST | MercadoLivreController | `publish` | Não | Obrigatória | Administrador | Sim | html |  |
| `/mercadopago/pointWebhook` | POST | MercadoPagoController | `pointWebhook` | Sim | Não | — | Não | json | Autenticação externa: mercadopago_signature |
| `/mercadopago/liberarPoint` | POST | MercadoPagoController | `liberarPoint` | Não | Obrigatória | Administrador | Sim | json |  |
| `/pdv` | GET | PdvController | `index` | Não | Obrigatória | Administrador, Atendente | Não | html |  |
| `/pdv/finalizarVenda` | POST | PdvController | `finalizarVenda` | Não | Obrigatória | Administrador, Atendente | Sim | html |  |
| `/pdv/pointStatus` | GET | PdvController | `pointStatus` | Não | Obrigatória | Administrador, Atendente | Não | json |  |
| `/pdv/abrirCaixa` | POST | PdvController | `abrirCaixa` | Não | Obrigatória | Administrador | Sim | html |  |
| `/pdv/fecharCaixa` | POST | PdvController | `fecharCaixa` | Não | Obrigatória | Administrador | Sim | html |  |
| `/pdv/getprodutos` | GET | PdvController | `getprodutos` | Não | Obrigatória | Administrador, Atendente | Não | json |  |
| `/pdv/imprimir` | GET | PdvController | `imprimir` | Não | Obrigatória | Administrador, Atendente | Não | file |  |
| `/relatorios` | GET | RelatoriosController | `index` | Não | Obrigatória | Administrador, Financeiro | Não | html |  |
| `/relatorios/print` | GET | RelatoriosController | `print` | Não | Obrigatória | Administrador, Financeiro | Não | file |  |
| `/vitrine` | GET | VitrineController | `index` | Sim | Não | — | Não | html |  |
| `/vitrine/catalogo` | GET | VitrineController | `catalogo` | Sim | Não | — | Não | html |  |
| `/vitrine/papelaria` | GET | VitrineController | `papelaria` | Sim | Não | — | Não | html |  |
| `/vitrine/produto` | GET | VitrineController | `produto` | Sim | Não | — | Não | html |  |
| `/vitrine/cadastrarConta` | POST | VitrineController | `cadastrarConta` | Sim | Não | — | Sim | html |  |
| `/vitrine/sairConta` | POST | VitrineController | `sairConta` | Sim | Não | — | Sim | html |  |
| `/vitrine/checkoutMercadoPago` | POST | VitrineController | `checkoutMercadoPago` | Sim | Não | — | Sim | json |  |
| `/vitrine/statusMercadoPago` | GET, POST | VitrineController | `statusMercadoPago` | Sim | Não | — | Não | json | Autenticação externa: provider_reference |
| `/media/estoque` | GET | MediaController | `estoque` | Sim | Não | — | Não | file |  |

## Aliases de compatibilidade

| Caminho legado | Destino | Métodos |
|---|---|---|
| `/compartilhar-catalogo` | /vitrine/catalogo | GET |
| `/index` | raiz conforme modo da aplicação | GET |
| `/checklist` | /os/create | GET |
| `/login/logout` | /logout | POST |

> Logout interno usa formulários POST com CSRF. `/login/logout` permanece apenas como alias legado POST.
