# Guia de deploy com public_html

Este guia descreve como publicar o Sistema Conectados sem expor codigo interno, banco, scripts administrativos ou arquivos sensiveis.

## Cenario recomendado

Use uma estrutura fora de `public_html` e aponte o dominio diretamente para a pasta publica da aplicacao:

```text
/home/conta/sistema_conectados/
  app/
  database/
  docs/
  public/
  resources/
  scripts/
  .env
  .env.example
  .htaccess
  .user.ini
```

O document root do dominio deve apontar para:

```text
/home/conta/sistema_conectados/public
```

Neste cenario, apenas os arquivos de `public/` ficam acessiveis pela web. As pastas `app/`, `database/`, `resources/`, `scripts/`, `docs/`, backups e `.env` permanecem fora da area publica.

## Hospedagem compartilhada sem alteracao de document root

Quando a hospedagem nao permite alterar o document root, mantenha o nucleo fora de `public_html` e copie somente o conteudo da pasta `public/` para `public_html`:

```text
/home/conta/sistema_conectados_app/
  app/
  database/
  docs/
  resources/
  scripts/
  .env
  .env.example
  .htaccess
  .user.ini

/home/conta/public_html/
  index.php
  assets/
  uploads/
  sw.js
  manifest.webmanifest
```

Depois ajuste o bootstrap em `public_html/index.php`, se necessario, para que `$baseDir` aponte para a pasta privada:

```php
$baseDir = dirname(__DIR__) . '/sistema_conectados_app';
```

Nao coloque `app/`, `database/`, `resources/`, `scripts/`, `.env`, backups, dumps, logs ou arquivos de suporte administrativo dentro de `public_html`.

## Arquivos sensiveis

- `.env` deve existir somente no servidor e nunca ser publicado em repositorio ou ZIP publico.
- `database/` contem schemas e migrations, mas nao deve ficar acessivel pela web.
- `scripts/` deve ser executado apenas por CLI ou painel administrativo controlado.
- `public/uploads/` pode existir publicamente apenas para arquivos realmente publicos da aplicacao; nao inclua uploads reais no pacote de release.
