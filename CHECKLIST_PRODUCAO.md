# Checklist de Producao

## Antes de subir

- [ ] Remover `.env` local e arquivos `.env.*.production` do pacote.
- [ ] Remover `production_backups/`, pasta duplicada `sistema_conectados/`, logs e zips.
- [ ] Confirmar `APP_DEBUG=false`.
- [ ] Confirmar `APP_ENV=production`.
- [ ] Confirmar `APP_URL` público com HTTPS.
- [ ] Confirmar `APP_MODE=system` no painel e `APP_MODE=vitrine` na vitrine.

## Servidor

- [ ] PHP com `pdo_mysql`.
- [ ] PHP com `curl`.
- [ ] PHP com `fileinfo`.
- [ ] PHP com `gd` recomendado.
- [ ] PHP com `mbstring` recomendado.
- [ ] `public/uploads` gravável.
- [ ] `public/uploads/estoque` gravável.
- [ ] `public/uploads/os` gravável.
- [ ] `public/uploads/.htaccess` presente para bloquear PHP.

## Testes funcionais

- [ ] Login no painel.
- [ ] Dashboard abre.
- [ ] Criar produto sem imagem.
- [ ] Criar produto com JPG.
- [ ] Criar produto com PNG.
- [ ] Criar produto com WEBP.
- [ ] Enviar arquivo HEIC e confirmar mensagem amigável.
- [ ] Editar produto.
- [ ] Excluir produto.
- [ ] Testar venda PDV com estoque suficiente.
- [ ] Testar venda PDV maior que estoque e confirmar bloqueio.
- [ ] Criar OS com foto.
- [ ] Alterar status de OS.
- [ ] Testar perfil sem permissão e confirmar 403.
- [ ] Testar POST sem CSRF e confirmar bloqueio.

## Mercado Livre

- [ ] Configurar Client ID e Client Secret.
- [ ] Cadastrar Redirect URI do painel no app Mercado Livre.
- [ ] Conectar conta.
- [ ] Configurar categoria padrão ou categoria no produto.
- [ ] Confirmar que `APP_URL` não é localhost.
- [ ] Testar URL pública da imagem no diagnóstico do produto.
- [ ] Publicar produto com categoria válida.
- [ ] Publicar produto sem categoria e confirmar erro claro.
- [ ] Ver erro real em `estoque.mercado_livre_last_error`.
- [ ] Ver histórico em `mercado_livre_logs`.

## Mercado Pago

- [ ] Configurar Public Key e Access Token.
- [ ] Finalizar checkout com produto real.
- [ ] Confirmar que preço usado é o do banco.
- [ ] Confirmar baixa de estoque só em pagamento aprovado.
- [ ] Confirmar que estoque não fica negativo.
- [ ] Ver logs em `mercado_pago_logs`.
