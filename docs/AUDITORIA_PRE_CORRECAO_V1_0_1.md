# Auditoria pré-correção — Sistema Conectados v1.0.1-security

Data da análise: 11/07/2026  
Escopo desta etapa: Fase 0 e diagnóstico necessário para a Fase 1.

## Resumo executivo

O projeto é uma aplicação PHP 8 em arquitetura MVC própria, sem framework de aplicação. A instalação analisada opera como single-tenant, com um banco MariaDB por instalação. O front controller é `public/index.php`, chamado também pelo `index.php` da raiz. A aplicação já possui prepared statements, hash de senha de usuário, token CSRF e verificação de perfil em parte do roteador, mas há riscos críticos e altos que precisam ser tratados de forma incremental para preservar dados e fluxos existentes.

Antes da correção foi confirmado um mecanismo público, executado antes da autenticação, que extraía `deploy.zip` sobre a instalação. Também estavam presentes `unzip.php`, `test_server.php`, cópia de teste `test_extract/` e arquivos ZIP de deploy na raiz. Esses itens fazem parte da Fase 1.

## Arquitetura encontrada

- Linguagem/runtime: PHP puro; ambiente local validado com PHP 8.0.30.
- Banco: MariaDB 10.4.32 via PDO MySQL.
- Padrão: MVC próprio em `app/Controllers`, `app/Models` e `app/Views`.
- Entrada principal: `index.php` encaminha para `public/index.php`.
- Rewrite: `.htaccess` da raiz encaminha URLs para `index.php` e bloqueia acesso web a diretórios internos e extensões sensíveis.
- Autoload: função SPL registrada diretamente no front controller para o namespace `App`.
- Configuração: `app/Config/App.php` carrega variáveis de `.env`.
- Banco/conexão: singleton em `app/Config/Database.php`.
- Views: renderização por `app/Core/Controller.php`, com injeção auxiliar de CSRF em formulários POST.
- Assets e uploads: `public/assets` e `public/uploads`; há `.htaccess` em uploads.
- Dependência incorporada: FPDF em `app/Support/fpdf`. Não foi encontrado Composer como requisito do runtime atual.

## Rotas e autenticação

O roteador atual é uma tabela central no próprio `public/index.php`. Cada módulo declara controlador e lista de métodos permitidos. Há listas separadas de ações POST e de perfis por módulo. Rotas internas redirecionam usuários sem sessão para o login. POST interno passa por CSRF, com exceções para callbacks públicos.

Problemas já identificados para fases posteriores:

- ações existentes (`OsController::registrarPagamento` e `MercadoPagoController::liberarPoint`) não constam na lista de rotas;
- ação desconhecida de módulo conhecido cai silenciosamente em `index()` em vez de 404;
- módulo desconhecido cai na dashboard/vitrine em vez de 404;
- autorização é majoritariamente por módulo, não por ação;
- `logout` está livre de autenticação e pode ser chamado por GET;
- o mapa de método HTTP não cobre explicitamente cada rota.

## Autenticação, sessão e permissões

- Login: `app/Controllers/AuthController.php`, com `password_verify` e `session_regenerate_id(true)` no sucesso.
- Usuários: tabela `usuarios`, senhas com `password_hash`/bcrypt.
- Sessão: iniciada no front controller; parâmetros de cookie ainda precisam de endurecimento.
- Logout: atualmente apenas `session_destroy()`, sem limpeza completa de sessão/cookie, CSRF ou auditoria.
- Permissões: perfil salvo na sessão e recarregado do banco por `current_user_profile()`; roteador aplica listas de perfis por módulo.
- CSRF: helpers em `app/Support/helpers.php`, usando `random_bytes`, `hash_equals` e histórico curto de tokens. Existe fallback não criptograficamente ideal que deve ser removido em fase posterior.
- Auditoria: `app/Models/AuditModel.php` grava em `auditoria_logs`.

## Módulos relacionados às correções

- Ordens de serviço: `OsController`, `OsModel`, views em `app/Views/os`.
- Aparelhos: `AparelhoModel`; o campo `aparelhos.senha_padrao` está em texto simples.
- Pagamento de OS: `OsController::registrarPagamento`, tabelas `os_pagamentos`, `financeiro` e `ordens_servico`.
- Mercado Pago Point: `MercadoPagoController`, `MercadoPagoPointModel`, tabela `mercado_pago_point_orders`.
- Webhook: `MercadoPagoController::pointWebhook`; atualmente aceita payload sem validação criptográfica comprovada e registra payload em erro.
- TLS: `MercadoPagoController::liberarPoint` desabilita `CURLOPT_SSL_VERIFYPEER`; correção P0 pendente.
- Estoque/PDV: `EstoqueModel`, `PdvModel`, `EstoqueController`, `PdvController` e tabelas `estoque`, `estoque_movimentacoes`, `pdv_*`.
- Financeiro: `FinanceiroController`, `FinanceiroModel`, tabela `financeiro`.
- Backup: `ConfigController::backup` e `BackupModel`, com dump SQL construído pela aplicação.

## Banco e migrações

Tabelas atuais relacionadas ao escopo: `usuarios`, `clientes`, `aparelhos`, `ordens_servico`, `os_historico`, `os_pagamentos`, `financeiro`, `auditoria_logs`, `estoque`, `estoque_movimentacoes`, `pdv_caixas`, `pdv_vendas`, `pdv_itens`, `mercado_pago_pedidos`, `mercado_pago_point_orders`, `mercado_pago_logs`, `mercado_livre_logs`, `configuracoes` e tabelas de compras/fornecedores.

Existem scripts SQL em `database/migrations`, porém não foi encontrado um executor versionado com tabela de controle. A conexão executa `ensureSchema()` por padrão, contendo `CREATE TABLE`, `ALTER TABLE`, índices e chaves estrangeiras durante requisições normais. `PdvModel` e `MercadoPagoPointModel` também alteram schema durante a construção. A variável `DB_SKIP_AUTO_SCHEMA` permite evitar parte desse comportamento, mas não substitui migrações versionadas.

Nenhuma migração foi executada nesta etapa.

## Backup pré-correção

Foi criado um dump lógico consistente com `mysqldump --single-transaction` antes de qualquer futura migração. O arquivo permanece somente em `production_backups/`, diretório ignorado pelo Git. Nome, conteúdo e dados não devem ser incluídos em pacote ou commit. O checksum SHA-256 foi conferido no momento da criação e deve ser guardado operacionalmente junto do backup.

## Arquivos sensíveis e riscos pré-mudança

- `.env` local contém configuração real e deve continuar fora de versão/pacotes.
- `env.production` aparenta conter credencial real e não segue o padrão de nome protegido pelo `.gitignore`; requer limpeza/rotação na Fase 2.
- `database/database.sqlite` pode conter dados e não deve entrar em pacote sem validação.
- `production_backups/`, uploads e possíveis cookies/sessões podem conter dados de clientes.
- mecanismo público de ZIP e scripts públicos de teste: risco crítico de sobrescrita/execução remota (tratado na Fase 1).
- alterações automáticas de schema: risco alto de lock, falha parcial e divergência entre instalações.
- exclusão física de OS: risco de perda de rastreabilidade.
- senha/PIN de aparelho em texto puro e exibida na impressão: risco de confidencialidade.
- webhook sem autenticação forte e sem idempotência persistente comprovada.
- rota Point com TLS desabilitado.
- logs podem incluir payloads sensíveis de integração.

Nenhum valor de credencial, cookie, dump, senha de aparelho ou dado de cliente foi copiado para este relatório.

## Plano de implementação conservador

1. Remover deploy e testes públicos e documentar deploy externo seguro.
2. Limpar pacote, ignorar dados sensíveis e preparar rotação manual de credenciais.
3. Tornar as rotas explícitas por método, autenticação, perfil e CSRF, com 404/405 reais.
4. Endurecer logout, sessão, login e auditoria sem invalidar hashes atuais.
5. Corrigir pagamentos e Point com validação, autorização, idempotência e transações.
6. Autenticar webhook e persistir deduplicação sem confiar no payload recebido.
7. Introduzir `APP_KEY`, criptografia autenticada e migração em lotes para senha de aparelho.
8. Trocar exclusão normal de OS por cancelamento lógico transacional.
9. Implantar executor CLI de migrações e retirar DDL das requisições.
10. Revisar uploads, XSS, SQL, erros, backup/restauração e testes.
11. Validar cada empresa separadamente, sempre após backup e teste em cópia.

## Estratégia de preservação de dados

- nunca executar instalação limpa ou seeds sobre bancos existentes;
- criar migrações apenas aditivas nesta versão;
- não remover colunas nem registros;
- usar transações para pagamentos, cancelamentos, estoque e financeiro;
- testar migrações em cópia restaurada antes de cada empresa;
- atualizar uma empresa por vez e fazer regressão antes da próxima;
- manter backup de banco, uploads e versão anterior para rollback;
- registrar somente contagens e identificadores técnicos, nunca valores sensíveis;
- interromper a atualização em qualquer falha, sem continuar parcialmente.

## Limitações do diagnóstico

O executável `git` não está disponível no PATH deste ambiente. O diretório `.git` existe, mas `git status` e inspeção do histórico precisarão ser executados quando o Git estiver acessível. Essa limitação não impede as correções locais, mas impede afirmar nesta etapa se algum segredo já está rastreado ou presente no histórico.
