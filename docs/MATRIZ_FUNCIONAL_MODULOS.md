# Matriz funcional dos módulos

| Módulo | Classificação | Evidência/limite |
|---|---|---|
| Autenticação e sessão | APROVADO | Login público 200; dashboard autenticada 200; RouterTest e LogoutTest OK; expiração, cookie antigo e duplicado validados na correção anterior. |
| Dashboard | APROVADO COM RESSALVAS | HTTP 200 em 89 ms; consultas e cards carregam. Sem validação visual responsiva nesta execução. |
| Rotas | APROVADO COM RESSALVAS | 110 alvos validados pelo RouterTest; mapa regenerado. Comparação literal é limitada por rotas dinâmicas/case. |
| Usuários/permissões | APROVADO COM RESSALVAS | Tela 200 e bloqueio central por perfis presente; CRUD completo com fixtures não executado. |
| Clientes | APROVADO COM RESSALVAS | Listagem 200; CRUD mutável não executado. |
| Aparelhos | REPROVADO | PIN/senha permanece em texto puro; fluxo CRUD não foi alterado. |
| Ordens de serviço | REPROVADO | Tela 200, impressão/código presentes; exclusão física de OS, pagamentos, histórico e possível aparelho é risco de perda/rastreabilidade. |
| Pagamento manual novo | APROVADO COM RESSALVAS | Testes unitário e integração com rollback OK; centavos, transação e `FOR UPDATE`. Concorrência multiprocesso não executada. |
| Pagamento legado | REPROVADO | Usa `float`, grava diretamente e não reutiliza o serviço/nonce; não limita explicitamente ao saldo. |
| Estoque | APROVADO COM RESSALVAS | Tela 200; operações mutáveis e concorrência não executadas. |
| PDV/vendas/caixa | REPROVADO | `PdvModel` executa CREATE/ALTER no construtor durante requisição normal. |
| Financeiro | APROVADO COM RESSALVAS | Tela 200; cinco diagnósticos retornaram zero divergências; cobertura histórica não é exaustiva. |
| Vitrine | APROVADO COM RESSALVAS | Inicial, catálogo e papelaria 200; alias antigo 302 correto. Checkout real não executado. |
| Uploads | APROVADO COM RESSALVAS | Validação MIME/extensão, nome aleatório e `move_uploaded_file` presentes; matriz dinâmica maliciosa não executada. |
| Impressões/relatórios | APROVADO COM RESSALVAS | Rotas/telas e FPDF sem erro de sintaxe; QA visual/PDF completo não executado. |
| Backup | APROVADO | Dump externo restaurado em banco isolado (31 tabelas), hashes conferidos e fixture removida. |
| Mercado Pago Point | REPROVADO | TLS desabilitado em `liberarPoint`; DDL no model; nenhuma operação real executada. |
| Webhook Point | REPROVADO | Rota pública declara autenticação externa, mas controller não valida assinatura; erro registra payload. |
| Mercado Livre | NÃO TESTADO | Código/configuração auditados estaticamente; nenhuma chamada real foi enviada. |
