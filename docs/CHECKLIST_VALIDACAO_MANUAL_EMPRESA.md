# Checklist de validação manual por empresa

Executar em cópia restaurada antes de qualquer implantação e registrar apenas status, horários e hashes.

- [ ] Conferir empresa, versão, branch, commit e hashes de banco/uploads.
- [ ] Validar `.env`, URL/base path e cookie sem expor valores sensíveis.
- [ ] Login válido/inválido, cinco refreshes, duas abas, expiração e logout.
- [ ] Confirmar menus e URLs diretas com Administrador, Financeiro, Atendente e Técnico.
- [ ] Dashboard: cards, totais, links, vazio e responsividade.
- [ ] Fixtures `TESTE_REGRESSAO_`: usuário, cliente, aparelho, OS e produto; remover e pesquisar ao final.
- [ ] OS: criar, editar, checklist, status, anexos e impressões; não usar exclusão física.
- [ ] Pagamento novo e legado em banco isolado; parcial, integral, acima do saldo e clique duplo.
- [ ] Concorrência: 100+100 sobre saldo 100 e 60+60 sobre saldo 100; nunca exceder saldo.
- [ ] Estoque: entrada/saída/OS/venda/estorno com rollback e sem quantidade negativa.
- [ ] PDV/caixa: venda temporária, estoque, financeiro, impressão e duplo clique; não chamar Point real.
- [ ] Financeiro: filtros, somas, órfãos e duplicidades; não corrigir histórico automaticamente.
- [ ] Vitrine: catálogo, produto ativo/inativo, imagem, estoque, conta/sessão pública e WhatsApp.
- [ ] Uploads: MIME falso, PHP renomeado, grande, corrompido, traversal e dupla extensão.
- [ ] PDFs: OS, orçamento, comprovante, estoque, vendas e financeiro com acentos/quebra de página.
- [ ] Mockar Mercado Pago/Mercado Livre; nunca enviar cobrança, webhook ou publicação real.
- [ ] Restaurar backup em banco isolado e comparar contagens.
- [ ] Rodar lint, testes automatizados e busca final por `TESTE_REGRESSAO_`.
- [ ] Bloquear implantação se qualquer B1–B5 permanecer.
