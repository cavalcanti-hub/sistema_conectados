# Fluxo de pagamento manual da OS

- Perfis: Administrador, Financeiro e Atendente.
- Formas manuais: Pix e Dinheiro. Cartões, QR e saldo Mercado Pago usam Point.
- O servidor recalcula total pago e saldo dentro de transação.
- Pagamento menor que o saldo deixa a OS como Parcial; igual ao saldo marca Pago.
- Valor zero, negativo, inválido, acima do saldo ou em OS quitada/cancelada é rejeitado.
- Cada parcela cria um pagamento, uma Receita em financeiro e histórico; não altera status técnico, estoque ou venda.
- Não há abertura automática nem vínculo direto com `pdv_caixas`; o caixa diário lê o financeiro.
- Nonce de uso único impede reenvio/clique duplo.
- Falha em qualquer gravação causa rollback completo.
- Em produção, atualize a página se o nonce expirar e nunca registre cartão como pagamento manual.
