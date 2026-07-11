# Processo seguro de deploy

Este projeto não possui e não deve possuir atualização, extração de ZIP ou migração acionável por URL pública. Deploy é uma operação administrativa fora da aplicação.

## Antes de atualizar

1. Identifique a empresa/instalação e confirme que o `.env`, banco e uploads pertencem somente a ela.
2. Registre a versão instalada.
3. Coloque a aplicação em janela de manutenção.
4. Gere backup lógico consistente do banco.
5. Copie os uploads para armazenamento protegido.
6. Calcule e guarde SHA-256 dos backups.
7. Restaure os backups em ambiente separado e valide a restauração.
8. Teste o pacote e as migrações nessa cópia.

## Pacote permitido

Inclua código, assets padrão, dependências necessárias, migrations, documentação e `.env.example`. Não inclua `.env`, dumps, backups, uploads reais, cookies, sessões, logs, arquivos de IDE, `.git`, scripts de teste, `unzip.php`, ZIPs de deploy ou credenciais.

## Atualização por hospedagem compartilhada

1. Faça upload do pacote limpo pelo painel/FTP/SFTP para uma pasta temporária não pública.
2. Valide o SHA-256 do pacote antes de publicar.
3. Extraia pelo painel da hospedagem ou estação administrativa, nunca por PHP/URL da aplicação.
4. Preserve o `.env` existente sem copiá-lo para o pacote.
5. Preserve uploads e diretórios graváveis da instalação.
6. Substitua somente arquivos de código previstos no release.
7. Execute migrações pelo CLI. Se a hospedagem não oferecer CLI, use apenas o executor administrativo temporário previsto na futura Fase 10, protegido e auto-bloqueável.
8. Nunca deixe ZIPs ou ferramentas de extração na raiz pública.
9. Remova imediatamente a pasta temporária e o pacote enviado.

## Validação após atualização

Verifique login, logout, criação/edição/impressão de OS, pagamento parcial e integral, estoque, PDV, caixa, financeiro, uploads, vitrine, Mercado Pago/Point quando configurados e geração de backup. Consulte logs protegidos sem expor tokens ou dados de clientes.

## Rollback

1. Interrompa o uso da instalação.
2. Restaure os arquivos da versão anterior.
3. Restaure o banco somente a partir do backup correspondente e após confirmação explícita, pois isso substitui dados.
4. Restaure uploads apenas nos caminhos permitidos.
5. Valide checksum, empresa e versão antes da restauração.
6. Registre o motivo e resultado do rollback.

## Sequência para as três empresas

Atualize uma empresa por vez. Faça regressão operacional completa na primeira e acompanhe seu funcionamento antes de iniciar a segunda; repita o processo antes da terceira. Nunca reutilize `.env`, backup, banco, uploads ou tokens entre instalações.
