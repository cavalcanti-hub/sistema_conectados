# Checklist de schema por instalação

- [ ] Backup externo do banco e uploads com SHA-256.
- [ ] Cópia restaurada em banco isolado.
- [ ] `php scripts/check_required_schema.php` retorna OK no banco operacional.
- [ ] Migration necessária revisada e testada na cópia.
- [ ] Nenhuma request executa CREATE, ALTER, DROP ou TRUNCATE.
- [ ] Login, dashboard, PDV, caixa, financeiro, OS e vitrine abrem.
- [ ] Banco com tabela removida em cópia não é reparado automaticamente e retorna 503 seguro.
- [ ] `tests/NoRuntimeDdlTest.php` e lint retornam zero falhas.
- [ ] Aplicação feita em uma empresa por vez, com plano de rollback validado.
- [ ] Nenhuma credencial, SQL interno ou stack trace aparece ao usuário.
