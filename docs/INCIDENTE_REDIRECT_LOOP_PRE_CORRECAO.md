# Incidente de loop de redirecionamento — diagnóstico pré-correção

Data local: 2026-07-11 (America/Sao_Paulo)

## Escopo e preservação

- Desenvolvimento das fases seguintes interrompido.
- Nenhuma migration, alteração de banco, restauração, limpeza de sessão do servidor ou reversão foi executada.
- Nenhum segredo, valor de cookie ou identificador de sessão foi registrado.
- O diff pré-existente foi preservado em `private_debug/pre_redirect_loop_changes.patch` (490871 bytes), caminho ignorado localmente por `.git/info/exclude`.
- Nenhum commit foi criado.

## Estado inicial do Git

`git status --short` revelou uma árvore de trabalho extensamente modificada antes desta correção: 52 arquivos rastreados no `git diff --stat`, com 4860 inserções e 2706 remoções, além de arquivos novos não rastreados. Entre os componentes relevantes ao incidente estão `.env.example`, `.htaccess`, `app/Controllers/AuthController.php`, `app/Support/helpers.php`, `public/index.php` e os novos `app/Core/AuthSession.php`, `app/Core/Router.php` e `app/Config/routes.php`.

O diff completo foi inspecionado e salvo no patch local acima. Alterações não relacionadas serão preservadas.

## Reprodução inicial com cookie jar limpo

Base testada: `http://localhost/sistema_conectados`

| Origem | Status | Location | Cookie observado | Destino funcional | Redirecionamentos |
|---|---:|---|---|---|---:|
| `/` | 302 | `/sistema_conectados/login` | `conectados_session` criado; valor omitido | `/login` | 1 |
| `/login` | 200 | ausente | mesmo jar | `/login` | 0 |
| `/dashboard` | 302 | `/sistema_conectados/login` | nenhum cookie de nome alternativo | `/login` | 1 |

Cadeia medida para visitante: `/dashboard` (302) → `/login` (200). Não houve loop no cenário de cookie limpo.

O cookie inicial foi emitido com `Path=/sistema_conectados`, `HttpOnly` e `SameSite=Lax`; o atributo `Secure` não foi emitido em HTTP local.

## Hipótese ainda não promovida a causa raiz

Como o cenário limpo está correto, o incidente relatado provavelmente depende do estado de uma sessão autenticada, inválida ou legada. A próxima etapa é reproduzir esses estados de forma controlada e auditar exclusivamente o ciclo Router/AuthSession/login/dashboard, sem assumir a causa.
