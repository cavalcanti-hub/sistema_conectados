# Estado atual do Sistema Conectados

- Nome: Sistema Conectados.
- Versão real identificada nos documentos e gerador de rotas: `v1.0.1-security`.
- Data da auditoria: 2026-07-11.
- Ambiente: desenvolvimento local em `http://localhost/sistema_conectados`.
- PHP: 8.0.30; MariaDB 10.4.32 via PDO MySQL.
- Git: branch `main`; base `89fd905a7dd2d98790cea89e3a0f1199e278d36b`; 74 entradas locais iniciais.
- Código: MVC próprio, sem Composer obrigatório; 143 arquivos PHP; 110 rotas e 4 aliases.
- Implantação: single-tenant, um banco por instalação/empresa.
- Configuração efetiva: `APP_BASE_PATH=/sistema_conectados`, cookie de sessão não Secure em HTTP local, HttpOnly e SameSite Lax.

A versão não foi alterada. A árvore permanece sem commit e contém alterações extensas anteriores à auditoria.
