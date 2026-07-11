# Auditoria de logout — Sistema Conectados v1.0.1-security

Data: 11/07/2026. Escopo: Fase 4.

## Funcionamento anterior

O logout interno chamava `session_destroy()` e redirecionava ao login, mas não esvaziava `$_SESSION`, não invalidava o cookie e não registrava auditoria. A rota já havia sido restringida a POST/CSRF na Fase 3, porém duas interfaces administrativas ainda eram links GET. A vitrine possuía saída separada, removendo somente `conta_publica_nome` e `conta_publica_email`.

Não havia timeout de inatividade. O login já regenerava o ID com destruição do identificador anterior. O roteador validava usuário ativo, mas apenas removia três chaves quando a conta deixava de existir.

## Estratégia aplicada

- criada rotina central `App\Core\AuthSession::terminate()`;
- auditoria ocorre antes da destruição e falha de log não bloqueia o logout;
- toda a sessão é esvaziada, o cookie é expirado com os mesmos atributos e a sessão é destruída no servidor;
- o cookie também é expirado em `/` para transição segura de instalações antigas/subdiretórios;
- logout manual, timeout e usuário inexistente/inativo reutilizam a mesma rotina;
- timeout configurável por `SESSION_LIFETIME`, padrão 120 minutos e limites de 5 a 1440;
- cookies recebem nome validado, `HttpOnly`, `SameSite`, `Secure` conforme ambiente e path de `APP_BASE_PATH`;
- páginas internas recebem `no-store`; vitrine e assets não recebem esse bloqueio indiscriminadamente;
- as duas interfaces internas viraram formulários POST com `csrf_field()`;
- `/login/logout` permanece alias interno POST da mesma ação, sem segunda implementação;
- logout ignora qualquer parâmetro de retorno e sempre redireciona para `/login`.

## Painel e vitrine

`/logout` encerra completamente a sessão PHP administrativa. `/vitrine/sairConta` usa POST/CSRF, mas remove somente as chaves da conta pública. Assim, sair da vitrine não encerra uma sessão administrativa ativa. O carrinho não foi alterado porque não há chave de autenticação adicional identificada para ele.

## CSRF e status 419

O servidor Apache/PHP local converte `http_response_code(419)` em HTTP 500, apesar de renderizar a página 419. Para evitar erro interno falso, o roteador mantém HTTP 403 para CSRF ausente/inválido e usa a página/mensagem de sessão expirada. A validação não foi enfraquecida: o controlador não é executado.

## Cache e botão voltar

Respostas internas usam `Cache-Control: no-store, no-cache, must-revalidate, max-age=0`, `Pragma: no-cache` e `Expires: 0`. Alguns navegadores ainda podem mostrar momentaneamente uma imagem visual da página ao voltar, mas atualizar, clicar, reenviar ou fazer AJAX exige nova autenticação; nenhum dado novo é carregado.

## Referências encontradas e corrigidas

- menu de conta do dock administrativo;
- botão de saída da sidebar;
- saída da conta pública já era formulário POST desde a Fase 3;
- nenhuma chamada JavaScript GET de logout foi encontrada.

## Testes

Foram usados IDs de sessão e CSRF temporários, sem senha administrativa e sem registrar seus valores. Validaram-se GET 405, CSRF ausente/inválido, logout válido, expiração do cookie, rejeição do ID antigo, bloqueio de rota/AJAX, timeout, usuário inexistente, alias legado e preservação do painel após saída da vitrine. Sessões locais marcadas como teste não criam auditoria operacional.
