# Protecao de segredos de aparelhos

O campo `aparelhos.senha_padrao` usa criptografia autenticada libsodium `sodium_crypto_secretbox` no formato versionado `v1:<base64(nonce+ciphertext)>`. Cada gravacao usa nonce aleatorio. A extensao `sodium` deve estar habilitada no PHP web e CLI antes da implantacao; se estiver ausente, a operacao falha fechada.

## Chave por empresa

Execute `php scripts/generate_device_secret_key.php` em terminal seguro e instale o valor exibido como `DEVICE_SECRET_KEY` no `.env` da empresa. O script nao altera o arquivo. Cada uma das tres empresas deve gerar sua propria chave; a chave nunca deve ser compartilhada, versionada ou reutilizada como senha/token.

A chave deve integrar o backup seguro de segredos da empresa. Sua perda impede definitivamente recuperar PINs e senhas existentes. Chave ausente, invalida ou incorreta faz o sistema falhar fechado sem expor nem sobrescrever o dado.

Listas, dashboard, exportacoes e logs nao recebem o segredo. A edicao nunca preenche o campo; vazio preserva o ciphertext e a remocao exige marcacao explicita.
