# Relatorio de Validacao

Data: 2026-05-12

## Ambiente local validado

- Raiz: `c:\xampp\htdocs\sistema_conectados`
- URL local: `http://localhost/sistema_conectados`
- Banco local: `127.0.0.1:3307`
- PHP lint: aprovado

## Testes aprovados

- Acesso local ao painel.
- Login real com CSRF.
- Dashboard.
- Tela `Configuracoes > Diagnostico`.
- Migration `database/migrations/2026_05_12_estabilizacao_producao.sql`.
- Cadastro de produto sem imagem.
- Cadastro de produto com JPG.
- Cadastro de produto com PNG.
- Cadastro de produto com WEBP.
- Bloqueio amigavel para HEIC/HEIF.
- Edicao de produto.
- Exibicao da imagem no painel e na vitrine.
- Vitrine publica.
- PDV com preco real do banco.
- Baixa de estoque no PDV.
- Bloqueio de venda maior que estoque.
- Criacao de OS com foto.
- Alteracao de status padronizados de OS.
- Bloqueio de POST sem CSRF com `403 Forbidden`.
- Mercado Pago sem credencial retorna erro claro.
- Mercado Livre sem configuracao retorna erro claro.

## Correcoes feitas durante a validacao

- CSRF passou de status nao padrao `419` para `403`.
- PDV passou a aceitar melhor o JSON do carrinho e informar erro amigavel.
- Diagnostico passou a exibir `conexao_banco=ok`.

## Pendencias para producao

- Criar `.env` real no servidor a partir dos exemplos.
- Confirmar `APP_DEBUG=false` e `APP_ENV=production`.
- Confirmar `APP_URL` publico com HTTPS.
- Ativar/confirmar extensoes PHP: `pdo_mysql`, `curl`, `fileinfo`, `mbstring`; `gd` recomendado.
- Configurar credenciais reais do Mercado Pago e testar pagamento aprovado.
- Configurar Mercado Livre e testar publicacao real.
- Testar upload em celular real na hospedagem.
- Fazer backup do banco antes do primeiro acesso em producao.
