# Reconciliação Mercado Pago–financeiro

O script usa somente dados locais; não chama API nem lê token.

- Padrão: `php scripts/reconcile_mercado_pago_financeiro.php --dry-run`
- Limite: `--limit=20`; filtro opcional: `--payment-id=...`.
- Aplicação exige `--apply --confirm=RECONCILIAR`.

Somente pedidos `approved` e positivos são analisados. Receita existente ou `financeiro_lancado_at` torna o item conciliado. Pendentes/rejeitados/cancelados são ignorados. O lote é transacional e idempotente. Nunca executar apply sem backup, cópia testada e autorização por empresa.
