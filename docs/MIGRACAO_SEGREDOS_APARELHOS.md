# Migracao de segredos de aparelhos

1. Gere backup fora do `htdocs` e valide sua restauracao.
2. Instale `DEVICE_SECRET_KEY` exclusiva no ambiente.
3. Execute a migration controlada `database/migrations/2026_07_11_expand_device_secret.sql` para ampliar somente `senha_padrao`.
4. Execute `php scripts/encrypt_existing_device_secrets.php --dry-run` e revise apenas as contagens.
5. Aplique com `php scripts/encrypt_existing_device_secrets.php --apply --confirm=CRIPTOGRAFAR`.
6. Repita o dry-run; `legados` deve ser zero. Um segundo apply deve alterar zero registros.

Use `--limit=N` para lotes. O script omite valores, é transacional, valida ciphertext existente e interrompe integralmente diante de chave incorreta, adulteracao ou concorrencia. Ele nunca roda em login, bootstrap ou requisição HTTP.

Rollback operacional: pare a aplicacao, restaure o backup do banco e a configuracao anterior de forma coordenada. Nao perca nem troque a chave enquanto houver ciphertext dependente dela. Nunca compartilhe a chave entre empresas.
