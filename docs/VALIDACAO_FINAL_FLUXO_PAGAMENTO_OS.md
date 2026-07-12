# Validacao final do fluxo de pagamento de OS

## Razao do bloqueio anterior

O bloqueio anterior ocorreu por validacoes pendentes, nao por uma inconsistencia financeira entao demonstrada. A matriz HTTP posterior encontrou e permitiu corrigir duas falhas concretas: autoload de `PaymentException` em rejeicoes anteriores ao carregamento do servico e rejeicao indevida do segundo nonce emitido no mesmo segundo para a mesma OS.

Depois das correcoes e da repeticao integral dos testes, nao foi encontrado caminho manual acessivel fora de `ManualOsPaymentService`.

## Caminhos que inserem em `os_pagamentos`

| Arquivo | Classe/metodo | Rota alcancavel | Origem | Manual | Point | Teste/schema | Usa servico central | Criacao/edicao comum alcanca |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `app/Services/ManualOsPaymentService.php` | `ManualOsPaymentService::register` | `POST os/store`, `POST os/update`, `POST os/registrarPagamento` | Manual | Sim | Nao | Nao | E o servico central | Sim, exclusivamente pelo servico |
| `app/Models/OsModel.php` | `OsModel::addPagamento` | Fluxo Point iniciado por `POST os/receberPoint` e sincronizacao Point | Mercado Pago Point | Nao | Sim | Nao | Nao, dominio Point preservado | Nao |
| `tests/OsLegacyPaymentConcurrencyTest.php` | fixtures/consultas de teste | CLI de teste | Teste isolado | Nao | Nao | Sim | Exercita o servico | Nao |
| `tests/OsLegacyPaymentHttpTest.php` | verificacoes e limpeza | HTTP local autenticado | Teste isolado | Nao | Nao | Sim | Exercita rotas reais | Somente fixtures |
| `database/schema_current.sql` e configuracao de schema | definicao da tabela | Instalacao controlada | Schema | Nao | Nao | Sim | Nao aplicavel | Nao |

Nao existe `INSERT INTO os_pagamentos` em controller, view ou helper.

## Isolamento do insert Point

- Metodo exato: `App\Models\OsModel::addPagamento(array $data)`.
- Unica referencia de chamada encontrada: `App\Models\MercadoPagoPointModel`, na confirmacao local de pagamento Point.
- Nenhuma chamada existe em `OsController::store`, `OsController::update` ou `OsController::registrarPagamento`.
- Nenhuma rota manual chama o metodo.
- Campos comuns do formulario de criacao ou edicao nao selecionam nem despacham para esse metodo.
- A observacao gravada pelo chamador identifica `Pagamento Mercado Pago Point` e o fluxo usa os dados confirmados da ordem Point.
- O metodo e a integracao Point nao foram alterados nesta tarefa.

## Prova HTTP e transacional

`tests/OsLegacyPaymentHttpTest.php` usa Apache e os endpoints reais, com usuario, cliente, aparelhos, OS, sessao, CSRF e nonces temporarios prefixados por `TESTE_PAGAMENTO_LEGADO_`.

Foram validados:

- criacao sem pagamento, parcial e integral;
- zero, negativo, notacao cientifica, acima do saldo, forma invalida, CSRF invalido e nonce invalido;
- edicao sem pagamento repetida;
- nova parcela e repeticao do mesmo nonce;
- rejeicao da reducao do total abaixo do valor pago;
- duas requisicoes simultaneas com o mesmo nonce;
- duas requisicoes simultaneas com nonces diferentes;
- rollback externo mediante falha no servico depois da criacao transacional da OS;
- dashboard, clientes, OS, financeiro, PDV, vitrine, visualizacao, impressao, 404 e 405.

Na criacao, `OsController::store` inicia a transacao. `ManualOsPaymentService::register` detecta `PDO::inTransaction()`, define que nao e dono da transacao, nao executa commit nem rollback proprio e devolve o controle. O controller realiza o unico commit final. Forma invalida e demais falhas provaram rollback de OS, aparelho, pagamento, receita e historico.

## Concorrencia

- Mesmo nonce e mesma sessao: dois processos HTTP simultaneos resultaram em uma OS, um pagamento, uma receita e um historico financeiro.
- Nonces diferentes: representam operacoes distintas; cada OS resultou em no maximo um pagamento, uma receita e um historico.
- OS existente: os cenarios 100+100 e 60+60 usam dois processos e `FOR UPDATE`; apenas uma requisicao e aceita e o saldo nunca e ultrapassado.

## Limpeza

As fixtures sao removidas em `finally`, em ordem referencial. A busca final por `TESTE_PAGAMENTO_LEGADO_` retorna zero usuarios, clientes, aparelhos e ordens de servico. Nenhum dado operacional real permanece alterado.
