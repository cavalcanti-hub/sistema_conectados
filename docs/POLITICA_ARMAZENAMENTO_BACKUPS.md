# Política de armazenamento de backups

## Local seguro

Backups não podem ficar em `public`, na raiz web ou em qualquer caminho servido diretamente pelo Apache. Configure por instalação:

```env
BACKUP_PATH=/home/usuario/backups_sistema_conectados
```

No ambiente Windows local foi configurado `C:\sistema_conectados_backups`, fora de `C:\xampp\htdocs`. O backup pré-correção e o acervo anterior foram preservados sob esse diretório externo.

## Regras operacionais

- Use diretório exclusivo para cada empresa/instalação.
- Restrinja o acesso ao usuário da aplicação e ao operador de backup.
- Não armazene `.env`, senha do banco ou tokens junto do dump.
- Use nomes não previsíveis; a aplicação adiciona sufixo aleatório às cópias armazenadas.
- Calcule SHA-256 após gerar/copiar e valide antes de restaurar.
- Registre somente nome, tamanho, hash, empresa, versão, data e resultado.
- Nunca registre conteúdo do dump.
- Transfira cópias externas por canal criptografado.
- Defina retenção e descarte seguro conforme obrigação contratual/legal.
- Teste restauração em ambiente isolado.

## Compatibilidade

Em hospedagem compartilhada, prefira um caminho dentro da conta, mas acima de `public_html`. Se isso não for possível, use diretório negado pelo servidor como medida temporária e migre para fora da raiz web assim que possível. Nunca confie apenas em nome difícil de adivinhar.

## Verificação do backup pré-correção

Após a movimentação, o arquivo válido manteve tamanho de 72.482 bytes e SHA-256 `37A2BC0EB338031DD9D7BDB8D37A5D239D040048C73489BE3311A89833EDAAAE`. O conteúdo não foi aberto nem reproduzido no relatório.

## Restauração

Antes de restaurar, valide checksum, empresa, versão e formato; crie novo backup do estado atual; restaure em teste; confirme explicitamente qualquer substituição de dados e registre o resultado sem dados sensíveis.
