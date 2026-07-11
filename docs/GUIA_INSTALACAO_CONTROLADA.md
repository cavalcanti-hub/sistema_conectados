# Guia de instalação controlada

1. Criar o banco vazio fora do acesso público.
2. Aplicar manualmente `database/schema_current.sql` e migrations revisadas; não existe runner automático.
3. Executar `php scripts/check_required_schema.php` e exigir `OK`.
4. Criar o administrador por CLI com nome/e-mail e senha via entrada padrão: `php scripts/create_initial_admin.php --apply --name="Nome" --email="email" --password-stdin`.
5. Opcionalmente instalar o catálogo: `php scripts/seed_phone_models.php --apply`.
6. Testar login, permissões e módulos.
7. Executar reconciliação somente quando necessária, iniciando por dry-run.

Nunca executar seeds, migrations, normalização ou backfill automaticamente em requests. Trabalhar uma empresa por vez, sempre após backup e teste em cópia.
