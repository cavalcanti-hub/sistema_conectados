# Deploy Conectados

## Estrutura publicada

Publique apenas estes itens na raiz do domínio/subdomínio:

- `app/`
- `database/`
- `public/`
- `resources/`
- `.env.example`
- `.htaccess`
- `.user.ini`
- `index.php`
- `DEPLOY.md`
- `CHECKLIST_PRODUCAO.md`

Nao publique:

- `.env` local ou arquivos `.env.*.production`
- `production_backups/`
- `sistema_conectados/` duplicado
- arquivos `.zip`, `.bak`, `.log`, `.old`
- backups com senhas
- dumps de banco com dados reais

## Ambientes

Sistema interno:

```env
APP_ENV=production
APP_DEBUG=false
APP_MODE=system
APP_URL=https://sistema.seudominio.com.br
APP_BASE_PATH=
```

Vitrine publica:

```env
APP_ENV=production
APP_DEBUG=false
APP_MODE=vitrine
APP_URL=https://seudominio.com.br
APP_BASE_PATH=
```

Banco:

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario_do_banco
DB_PASSWORD=senha_do_banco
```

## Ordem de deploy

1. Faça backup dos arquivos atuais e do banco.
2. Envie os arquivos do projeto limpo.
3. Crie o `.env` real no servidor a partir de `.env.example`.
4. Confirme permissões de escrita em:
   - `public/uploads`
   - `public/uploads/estoque`
   - `public/uploads/os`
5. Acesse o sistema uma vez para executar as migrações automáticas compatíveis.
6. Entre como Administrador e abra `Configurações > Diagnostico`.
7. Corrija qualquer alerta de extensões PHP, uploads ou `APP_URL`.

## Hotfix: remover banners laterais da vitrine

Se os banners laterais "50% Off" aparecerem ao reduzir o zoom, envie no minimo estes arquivos:

- `app/Views/layout/public_header.php`
- `app/Views/layout/public_footer.php`
- `public/assets/css/index.css`
- `public/assets/img/banner-conectados-promo.svg`
- `public/sw.js`

Depois de enviar, abra a vitrine e use `Ctrl + F5` para forcar o navegador a buscar a versao nova.

## Mercado Livre

1. Em `Configurações > Marketplace`, informe `Client ID` e `Client Secret`.
2. Cadastre no app do Mercado Livre a Redirect URI exibida no painel.
3. Clique em `Conectar Mercado Livre`.
4. Em cada produto, informe uma categoria final do Mercado Livre. A publicacao nao usa categoria automatica.
5. Preencha descrição e preço/estoque válidos.
6. Use o bloco `Diagnostico Mercado Livre` no produto para ver último erro, item_id e URL pública da imagem.

## Mercado Pago

O checkout publico aceita do navegador apenas produto e quantidade. O preço e estoque são sempre buscados no banco.

Configure em `Configurações > Pagamentos`:

- Public Key
- Access Token

Pagamentos aprovados só baixam estoque uma vez. Falhas ficam em `mercado_pago_logs`.

## PHP necessário

Obrigatório:

- `pdo_mysql`
- `curl`
- `fileinfo`

Recomendado:

- `gd`
- `mbstring`

Se `gd` estiver ausente, imagens válidas ainda são salvas, mas sem redimensionamento.
