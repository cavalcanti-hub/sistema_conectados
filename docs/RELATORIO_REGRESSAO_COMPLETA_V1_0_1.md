# Relatório de regressão completa — v1.0.1-security

## Resumo executivo

O sistema está acessível e os testes automatizados existentes passaram, mas a versão está **BLOQUEADA PARA RELEASE** por cinco riscos comprovados: DDL em requisição, webhook sem assinatura aplicada, TLS Point desabilitado, exclusão física de OS e fluxo legado de pagamento inconsistente. Nenhum deles foi corrigido nesta auditoria.

## Evidências executadas

- Preservação: status/stat/check, branch/base, patch e SHA-256.
- Backup: dump `--single-transaction`, uploads externos e restauração isolada com 31 tabelas; banco temporário removido.
- Rotas: mapa regenerado com 110 rotas; RouterTest OK; 404/405/CSRF anteriormente validados.
- Testes: `RouterTest`, `LogoutTest`, `OsManualPaymentTest` e integração com rollback: OK.
- Financeiro: negativo, acima do total, status divergente e receita/pagamento órfão: zero ocorrências nas cinco consultas.
- HTTP: dashboard, usuários, clientes, OS, estoque, produtos, financeiro, fornecedores, compras, relatórios e config: 200 (65–99 ms); vitrine/catálogo/papelaria: 200; alias legado: 302 correto.
- Sintaxe: aplicação 135/0 falhas; testes 5/0; scripts 2/0; FPDF 26/0. Total único: 143 PHP, zero falhas.
- Fixtures: teste de pagamento revertido; banco de restauração removido; busca final `TESTE_REGRESSAO_` = 0.

## Limitações deliberadas

Não foram executados CRUD destrutivo, upload HTTP, PDF visual, concorrência multiprocesso, perfis temporários completos, venda/estoque reais, Point, webhook, Mercado Livre ou deploy. Essas áreas não recebem aprovação plena. A auditoria estática foi suficiente para reprovar os bloqueadores sem expor dados reais.

## Resultado por domínio

Consulte `MATRIZ_FUNCIONAL_MODULOS.md`, `BLOQUEADORES_DE_RELEASE.md` e `PLANO_CORRECOES_MINIMAS.md`. O banco operacional não recebeu fixtures persistentes, migrations ou correções.

## Status

**BLOQUEADO PARA RELEASE**
