# Auditoria de rotas — Sistema Conectados v1.0.1-security

Data: 11/07/2026. Escopo: Fase 3.

## Funcionamento anterior

O Apache encaminha caminhos amigáveis para `index.php?url=...`; o `index.php` da raiz carrega `public/index.php`. O front controller separava o valor de `url` em módulo, ação e parâmetros. Uma lista permitia módulos e outra permitia nomes de métodos, enquanto método HTTP, perfil e exceções CSRF ficavam em estruturas separadas.

Também são usados `route_url()`, `absolute_route_url()` e `app_url()`. Foram encontradas URLs em formulários, links, redirects, `fetch()` e callbacks. Não foram encontrados roteadores alternativos por `?route=`, `?r=` ou escolha livre de classe; `?url=` é o formato interno produzido pelo rewrite e continua compatível.

## Riscos encontrados

- módulo inexistente abria dashboard ou vitrine, ocultando 404;
- ação inexistente em módulo conhecido chamava `index()`, ocultando erro e criando ambiguidade;
- método HTTP era controlado por lista incompleta separada do registro de ações;
- `_method` vindo do POST podia substituir o nome da ação;
- autorização era majoritariamente aplicada por módulo, não por rota;
- rotas públicas eram agrupadas por módulo, tornando ações internas difíceis de distinguir;
- logout era aceito por GET;
- `OsController::registrarPagamento` e `MercadoPagoController::liberarPoint` existiam, eram chamados pela interface, mas não estavam registrados;
- webhook Point estava público e sem CSRF, porém não estava marcado formalmente como dependente de autenticação externa;
- a liberação Point era chamada por `fetch()` GET apesar de alterar estado;
- sessão de usuário removido/inativo não era invalidada no nível da rota;
- erros AJAX podiam receber HTML/redirecionamento inadequado.

## Cruzamento de controladores e interface

Todos os métodos públicos destinados a ações web foram comparados com o roteador e referências da interface. Construtores e métodos internos não foram registrados. Métodos perigosos só podem ser executados por entrada exata no registro.

Rotas implementadas antes inacessíveis: `/os/registrarPagamento` e `/mercadopago/liberarPoint`.

Rotas registradas com destaque:

- `/logout`: POST, sessão, CSRF;
- `/os/registrarPagamento`: POST, sessão, perfis Administrador/Financeiro/Atendente, CSRF;
- `/mercadopago/liberarPoint`: POST, sessão, Administrador, CSRF;
- `/mercadopago/pointWebhook`: POST, pública, sem CSRF, marcada `mercadopago_signature` para a futura validação externa.

Links GET de logout permanecem identificados em `app/Views/layout/header.php` e na antiga tela de acesso restrito removida com o roteador anterior. Eles agora recebem 405 e deverão virar formulários POST na Fase 4, quando a lógica completa de logout for implementada.

## Estratégia aplicada

- registro único em `app/Config/routes.php`, com caminho, métodos, controlador, ação, autenticação, perfis, CSRF, publicidade, autenticação externa, resposta e parâmetros;
- despacho por correspondência exata em `app/Core/Router.php`;
- normalização conservadora de barras e caixa apenas para busca;
- rejeição de traversal, null byte, barra invertida, segmentos inválidos e parâmetros fora do padrão;
- 404 sem fallback para dashboard;
- 405 com cabeçalho `Allow`;
- 403 antes do controlador para perfil não permitido;
- 419 antes do controlador para CSRF inválido;
- JSON com status e código estável para rotas AJAX;
- validação de usuário ativo no banco antes de cada rota autenticada;
- aliases explícitos, sem transformação de POST em GET;
- logs técnicos limitados a status, rota, método, métodos permitidos e ID do usuário, sem corpo, cookies ou tokens.

## Compatibilidade

Foram preservados `?url=`, URLs amigáveis, query strings, subdiretório XAMPP, `APP_BASE_PATH`, rotas de views, impressão, AJAX, vitrine, Mercado Livre e Mercado Pago. URLs com ID no caminho continuam aceitas apenas quando o ID é inteiro positivo. O alias `/checklist` continua redirecionando para `/os/create`; `/login/logout` é alias POST de `/logout`.

O antigo redirecionamento global de qualquer rota `estoque/*` para produtos foi removido porque tornava as ações registradas de estoque inalcançáveis. As URLs explícitas de estoque agora chegam ao controlador com as mesmas permissões já adotadas.

## Limites desta fase

Não foram alteradas regras internas de logout, pagamento, Point, TLS ou webhook. A assinatura Mercado Pago continua pendente; o registro apenas declara a obrigação. A conversão visual dos links de logout fica para a Fase 4 conforme escopo solicitado.

## Contagem de PHP

A contagem anterior de 367 incluía cópias de deploy, artefatos em `build/` e árvore FPDF completa/duplicações então presentes no workspace. Após a Fase 2, `build/` e artefatos privados foram movidos para fora de `htdocs`. A validação desta fase considera todo PHP atualmente existente no workspace ativo, incluindo `app`, `public`, `resources`, `database`, `scripts`, `tests` e a biblioteca FPDF incorporada. O número final consta no relatório da fase.
