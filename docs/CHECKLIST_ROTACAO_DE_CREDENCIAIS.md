# Checklist de rotação de credenciais

Não registre senhas, tokens ou chaves neste documento.

| Serviço | Ambiente | Responsável | Precisa ser trocada? | Data da troca | Testada após troca? | Observações |
|---|---|---|---|---|---|---|
| Banco de dados | Empresa 1 / produção |  |  |  |  |  |
| Banco de dados | Empresa 2 / produção |  |  |  |  |  |
| Banco de dados | Empresa 3 / produção |  |  |  |  |  |
| Mercado Pago | Por instalação |  |  |  |  |  |
| Mercado Livre | Por instalação |  |  |  |  |  |
| Hospedagem | Por conta |  |  |  |  |  |
| FTP/SFTP | Por conta |  |  |  |  |  |
| cPanel/painel | Por conta |  |  |  |  |  |
| Chave da aplicação | Por instalação |  |  |  |  | Será introduzida na fase de criptografia. |
| Serviço de e-mail | Por instalação |  |  |  |  |  |
| APIs externas | Por integração |  |  |  |  |  |
| Contas administrativas de teste | Por instalação |  |  |  |  | Avaliar inclusive contas criadas durante validação. |

## Procedimento

1. Atualize uma instalação por vez e mantenha backup/rollback disponível.
2. Gere credencial longa, única e aleatória; não reutilize senha.
3. Ative autenticação em duas etapas quando disponível.
4. Atualize o segredo somente no `.env` ou painel protegido da instalação correspondente.
5. Teste login ou integração sem registrar o valor usado.
6. Monitore falhas e confirme que não houve impacto operacional.
7. Invalide a credencial antiga após validar a nova.
8. Registre responsável, data e resultado, nunca o segredo.
