# Inventário de arquivos sensíveis — Fase 2

Data: 11/07/2026. Nenhum valor secreto foi reproduzido neste documento.

| Arquivo ou diretório | Categoria de risco | Versionado | Necessário em produção | Ação adotada/recomendada |
|---|---|---:|---:|---|
| `.env` | Configuração local e credenciais | Não | Sim, por instalação | Mantido local, ignorado pelo Git e excluído de pacotes. |
| `env.production` | Configuração antiga com credencial | Não | Não para o runtime atual | Movido para `C:\sistema_conectados_local_config\env.production`; usar `.env` no servidor. |
| `.env.example` | Modelo sem segredos | Sim | Sim | Atualizado somente com valores vazios ou fictícios. |
| `.env.sistema.example`, `.env.vitrine.example` | Modelos de ambiente | Sim | Opcional | Manter somente enquanto contiverem placeholders. |
| `production_backups/` | Dumps, cópias de arquivos e dados operacionais | Não | Não dentro da aplicação | Movido integralmente para `C:\sistema_conectados_backups\production_backups`. |
| `build/` | Pacotes antigos, perfis de navegador, cookies, sessões e logs | Não | Não | Movido para `C:\sistema_conectados_private_artifacts\build`; não distribuir. |
| `php-server-*.log` | Logs locais | Não | Não | Movidos para diretório privado externo. |
| `database/database.sqlite` | Banco/artefato local potencialmente sensível | Não | Não no runtime MySQL atual | Movido para diretório privado externo. |
| `database/schema*.sql` | Schema sem dados operacionais | Sim | Sim, como referência atual | Manter; revisar na futura fase de migrações. |
| `database/migrations/*.sql` | Migrações de produto | Sim | Sim | Manter; não confundir com dumps de dados. |
| `public/uploads/` | Uploads operacionais de clientes/produtos | Somente proteções e `.gitkeep` | Sim | Preservado no servidor, ignorado no Git e excluído de pacotes padrão. |
| `public/uploads/.htaccess` | Proteção do diretório de uploads | Sim | Sim | Manter versionado. |
| Configurações do Mercado Pago/Livre no banco | Tokens de integração | Não como arquivo | Conforme uso | Nunca exportar em relatórios/logs; avaliar rotação manual. |
| `C:\sistema_conectados_private_artifacts` | Quarentena local de artefatos antigos | Fora do Git | Não | Revisar manualmente antes de eventual descarte; nunca publicar. |

## Resultado da verificação do Git

O Git foi encontrado em `C:\Program Files\Git\cmd\git.exe`. `env.production`, `.env`, backups, build, logs, SQLite e uploads reais não estavam rastreados. Estavam rastreados apenas os modelos de ambiente, arquivos `.gitkeep` e `.htaccess` de uploads, que são necessários e não contêm dados operacionais.

O repositório já possuía diversas alterações locais anteriores à Fase 2. Elas foram preservadas. Nenhum histórico foi reescrito, nenhum commit/push foi realizado e nenhum arquivo sensível precisou de `git rm --cached`.

## Padrões de conteúdo revisados

Foram pesquisadas referências a senhas de banco, access tokens, client secrets, chaves de aplicação, integrações Mercado Pago/Mercado Livre e credenciais de hospedagem. As ocorrências válidas são nomes de variáveis, leitura de configuração, campos administrativos ou schemas. O arquivo de ambiente real e artefatos privados foram mantidos fora do relatório e fora da distribuição.
