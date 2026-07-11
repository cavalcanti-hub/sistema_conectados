# Fluxo de migrations

DDL é proibido em requisições HTTP, construtores, bootstrap, controllers e models.

1. Criar backup lógico e de uploads fora do `htdocs`, registrar tamanho e SHA-256.
2. Restaurar o backup em banco isolado.
3. Executar `php scripts/check_required_schema.php` no banco-alvo; a opção `--database=NOME_TESTE` serve apenas para cópias isoladas.
4. Revisar manualmente a migration SQL versionada em `database/migrations/`.
5. Aplicar migrations com cliente administrativo somente após autorização. O projeto ainda não possui executor automático; não documentar um comando inexistente.
6. Executar uma empresa por vez, rodar diagnóstico, regressão e conferir logs antes da próxima.
7. Em falha, interromper. Restaurar primeiro em ambiente isolado e só então seguir o procedimento operacional aprovado; nunca restaurar cegamente sobre produção.

`DB_SKIP_AUTO_SCHEMA` é compatibilidade obsoleta e não controla mais DDL. Alterar seu valor não habilita reparo automático.
