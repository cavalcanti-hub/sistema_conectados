# Checklist de teste do pagamento de OS

| Cenário | Pré-condição | Passos | Resultado esperado | Resultado obtido | Aprovado/Reprovado | Observações |
|---|---|---|---|---|---|---|
| Valor inválido | OS temporária | Enviar vazio/texto/zero/negativo | 422, nenhuma gravação |  |  |  |
| Parcial | Saldo positivo | Pagar menos que saldo | Parcela, receita, histórico, status Parcial |  |  |  |
| Integral | Saldo positivo | Pagar saldo exato | Saldo zero e status Pago |  |  |  |
| Acima do saldo | Saldo conhecido | Enviar valor maior | Rejeição e zero alterações |  |  |  |
| Duplicado | Nonce válido | Repetir POST | Segundo POST rejeitado |  |  |  |
| Concorrência | OS temporária | Duas sessões pagam mesmo saldo | Apenas saldo disponível é aceito |  |  |  |
| Rollback | Falha simulada em banco de teste | Interromper cada etapa | Nenhum registro órfão |  |  |  |
| Permissão/CSRF | Sessões temporárias | Testar perfis e tokens | 401/403 sem alteração |  |  |  |
