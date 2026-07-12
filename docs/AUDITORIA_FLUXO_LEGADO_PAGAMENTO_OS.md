# Auditoria do fluxo legado de pagamento de OS

## Escopo

Auditoria realizada apos o checkpoint local `49fac8c`, sem alterar webhook, Mercado Pago Point, TLS, exclusao de OS, PIN/senha do aparelho, estoque, PDV, caixa ou layout fora do necessario para impedir duplicidade financeira da OS.

## Fluxos encontrados

| Fluxo | Arquivo | Classe | Metodo | Rota | Momento | Campos recebidos | Calculo | Transacao | Financeiro | Historico | Risco | Diferenca para ManualOsPaymentService |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Pagamento manual novo | `app/Controllers/OsController.php` | `OsController` | `registrarPagamento` | `POST os/registrarPagamento` | Acao explicita na visualizacao da OS | `id`, `valor`, `forma_pagamento`, `observacao`, `payment_nonce` | Valor convertido para centavos pelo servico | Sim, no servico | Uma receita por parcela no servico | Um historico por parcela no servico | Baixo | Ja usa `ManualOsPaymentService`, `FOR UPDATE`, saldo no servidor e nonce |
| Criacao de OS sem pagamento | `app/Controllers/OsController.php` | `OsController` | `store` | `POST os/store` | Abertura da OS | Dados de cliente/aparelho/OS, valores de mao de obra e pecas | Valores normalizados para decimal de centavos; sem parcela vazia | Sim, no controller | Nao cria receita | Historico de abertura | Baixo | Sem pagamento manual solicitado, nao chama o servico |
| Criacao de OS com pagamento inicial | `app/Controllers/OsController.php` | `OsController` | `store` | `POST os/store` | Abertura da OS com parcela explicita | `pagamento_inicial_valor`, `pagamento_inicial_forma`, `pagamento_inicial_observacao`, nonce de operacao | Valor da parcela convertido em centavos pelo servico; total/saldo recalculados no banco | Sim, transacao externa do controller reutilizada pelo servico | Uma receita por parcela no servico | Historico criado pelo servico | Baixo | Agora usa o mesmo servico transacional, sem insert direto no controller |
| Edicao de OS sem nova parcela | `app/Controllers/OsController.php` | `OsController` | `update` | `POST os/update` | Salvar alteracoes cadastrais/tecnicas | Dados da OS e campos financeiros de total | Status financeiro recalculado por total pago real; campo acumulado da tela nao e aceito como parcela | Sim, no controller | Nao cria receita de pagamento | Historico tecnico apenas se status tecnico mudou | Baixo | O submit da edicao nao interpreta total pago/situacao como novo pagamento |
| Edicao de OS com nova parcela | `app/Controllers/OsController.php` | `OsController` | `update` | `POST os/update` | Salvar edicao com `pagamento_valor` preenchido | `pagamento_valor`, `pagamento_forma`, `pagamento_observacao`, `payment_nonce` | Valor da parcela convertido em centavos pelo servico; saldo recalculado com `FOR UPDATE` | Sim, transacao externa do controller reutilizada pelo servico | Uma receita por parcela no servico | Historico criado pelo servico | Baixo | Substituiu `registerOsPaymentFromPost`, que usava float, insert direto e nao tinha idempotencia |
| Baixa automatica por situacao | `app/Controllers/OsController.php` | `OsController` | `syncFinanceiroFromOs` | `POST os/update` | Edicao quando `situacao_pagamento=Pago` | `situacao_pagamento`, `forma_pagamento`, totais | Calculava total no controller e criava pagamento integral | Sim, mas fora do servico | Criava ou sincronizava receita `Pagamento OS` | Nao criava historico de parcela | Alto | Removido; situacao enviada pelo navegador nao gera parcela |
| Mercado Pago Point | `app/Models/MercadoPagoPointModel.php` | `MercadoPagoPointModel` | sincronizacao de pagamento Point | Rotas Point existentes | Confirmacao Point | Dados do provedor | Regra propria do Point | Propria do modelo | Receita de origem Point | Proprio fluxo | Fora do escopo | Nao alterado conforme restricao |

## Resultado

- Escrita manual de pagamento de OS fica centralizada em `app/Services/ManualOsPaymentService.php`.
- `OsController` nao executa `INSERT INTO os_pagamentos` nem cria receita manual diretamente.
- Edicao nao aceita mais `situacao_pagamento` enviada pelo navegador como baixa financeira.
- Pagamento inicial e nova parcela exigem nonce, usuario e sessao.
- OS sem pagamento continua sem gerar pagamento zero.
- O fluxo Point foi apenas classificado e nao modificado.
