# Auditoria do pagamento manual de OS — v1.0.1-security

## Fluxo anterior

`OsController::registrarPagamento` lia OS e total pago fora de transação, convertia dinheiro para `float`, aceitava forma livre, não limitava pagamento ao saldo e gravava pagamento, financeiro e status em comandos independentes. Clique duplo e concorrência podiam duplicar receita ou superar o saldo. Não havia histórico da OS nem idempotência de servidor.

## Fonte oficial

`ordens_servico.valor_total` é coluna `DECIMAL(10,2)` gerada por `valor_mao_obra + valor_pecas - desconto`. O pago é a soma de `os_pagamentos.valor`. Saldo é `valor_total - total_pago`. Nenhum valor total/saldo vindo do navegador é aceito.

## Relações

- `os_pagamentos`: parcela recebida e usuário.
- `financeiro`: uma Receita/`Pagamento OS` para cada parcela.
- `ordens_servico.situacao_pagamento`: Pendente, Parcial ou Pago; status técnico não muda.
- `os_historico`: evento resumido com valor, forma e saldo.
- `pdv_caixas`: não possui FK/movimento próprio de OS. O comportamento validado anterior contabiliza pagamentos de OS pelo `financeiro`, usado no relatório diário de caixa; não se exige nem abre caixa automaticamente.
- vendas/estoque: não participam deste fluxo.

## Correção

O serviço `ManualOsPaymentService` usa centavos, transação única, `SELECT ... FOR UPDATE` na OS e pagamentos, recalcula saldo após o bloqueio, insere pagamento/financeiro/histórico e atualiza somente o status financeiro. Qualquer exceção executa rollback. Pix e Dinheiro são as formas manuais já oferecidas; cartões/QR continuam no fluxo Point.

Idempotência usa nonce aleatório de sessão vinculado a usuário e OS, expira em 15 minutos e é consumido após commit. O lock da sessão serializa cliques repetidos da mesma sessão; o lock da OS serializa usuários concorrentes.

Auditoria é feita após commit para não duplicar pagamento se o log secundário falhar. Registra IDs técnicos, valor, forma, origem manual e resultado, sem dados sensíveis.

## Compatibilidade e riscos históricos

Pagamentos anteriores continuam sendo somados/exibidos. Nenhum schema foi alterado. O diagnóstico somente leitura não encontrou totais negativos, pagamentos acima do total, status incoerentes ou receita de OS sem pagamento no banco local analisado. O fluxo legado de pagamento durante criação/edição de OS é separado desta rota e deverá ser consolidado futuramente para reutilizar o mesmo serviço.
