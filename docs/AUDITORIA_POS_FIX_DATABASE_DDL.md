# Auditoria pós-correção — Database e DDL runtime

Data: 2026-07-11. Escopo exclusivo: validar a remoção de DDL runtime. Nenhuma correção adicional foi executada.

## Resumo executivo

O arquivo imediatamente anterior tinha 1.073 linhas; o atual tem 62. A redução líquida medida é de 1.011 linhas (não 1.034). O DDL runtime foi efetivamente eliminado e conexão, PDO, transações e módulos funcionam. Porém, três responsabilidades de dados foram removidas junto com `ensureSchema()` sem preservação controlada: seed administrativo, catálogo padrão de modelos e sincronização retroativa Mercado Pago → financeiro. Por isso, esta auditoria fica bloqueada até decisão explícita sobre preservá-las, substituir por scripts administrativos ou declará-las obsoletas.

## Diff de Database.php

### Responsabilidades preservadas

- Leitura de ambiente por `app_env`: host, porta, banco, usuário e senha.
- Validação de configuração em produção via `config_status`.
- PDO MySQL/XAMPP/hospedagem compartilhada; singleton `getInstance()` e `reconnect()`.
- `utf8mb4` passou de `SET NAMES` para `charset=utf8mb4` no DSN.
- `PDO::ATTR_ERRMODE=EXCEPTION` e `PDO::ATTR_DEFAULT_FETCH_MODE=ASSOC` preservados.
- Prepared statements continuam nativos; `ATTR_EMULATE_PREPARES=false` foi explicitado.
- Falha de conexão continua genérica em produção; detalhes somente com debug.
- Timezone nunca foi responsabilidade de `Database`; permanece no front controller.
- Nenhum helper transacional foi removido: transações sempre foram métodos do próprio PDO retornado.

### Métodos removidos

| Método | Linhas aproximadas | Finalidade anterior | Preservação/chamadas atuais | Risco |
|---|---:|---|---|---|
| `ensureSchema` | 660 | 31 CREATEs, defaults, alterações e orquestração de helpers/seeds | Estrutura em `schema_current.sql` e migrations; zero chamadas | DDL resolvido; carregava também DML legítimo |
| `seedAdminFromEnv` | 19 | Criar primeiro administrador a partir do ambiente | Não preservado; zero chamadas | Instalação nova não cria admin automaticamente |
| `seedPhoneModels` | 142 | Popular catálogo de marcas/modelos | Não preservado; zero chamadas | Instalação nova perde catálogo padrão |
| `ensureColumn` | 19 | Detectar/adicionar coluna | Schemas/migrations; zero chamadas | Baixo após diagnóstico/migrations |
| `ensureIndex` | 14 | Detectar/adicionar índice | `schema_current.sql`/migrations; zero chamadas | Baixo |
| `ensureForeignKey` | 25 | Detectar/adicionar FK | `schema_current.sql`/migrations; zero chamadas | Baixo |
| `ensurePdvVendaStatusSupportsPoint` | 21 | Ajustar ENUM de venda | Migration de PDV/Point; zero chamadas | Migration usa MODIFY não estritamente condicional |
| `ensureMercadoPagoPointOrdersNullableOs` | 21 | Tornar campos Point anuláveis | Migration/schema atual; zero chamadas | Baixo |
| `normalizeSchemaData` | 29 | Normalizar status/localização existentes | Não transferido; zero chamadas | Instalações antigas podem exigir migração de dados explícita |
| `syncApprovedMercadoPagoFinanceiro` | 64 | Backfill de vendas aprovadas no financeiro | Não preservado; zero chamadas | Alto: backfill implícito deixou de ocorrer |

Também foram removidas propriedades privadas duplicando configuração (`host`, `port`, `db_name`, `username`, `password`); nenhuma era acessível externamente. `getConnection`, `getInstance` e `reconnect` permanecem.

## Referências quebradas

Busca global pelos dez métodos removidos encontrou apenas o nome `ensureSchema` no teste que proíbe seu retorno. Nenhum controller, model, script ou teste chama API inexistente. Todas as 35 referências à API pública usam `Database::getInstance()` ou `reconnect()`, ambas preservadas. Não há acesso externo às propriedades removidas.

Não há quebra silenciosa por chamada inexistente; há, contudo, perda funcional de instalação/backfill pelas três rotinas não transferidas.

## Matriz de preservação do schema

`schema_current.sql` foi gerado somente com estrutura e contém as mesmas 31 tabelas que o `ensureSchema()` antigo. Colunas, índices, defaults, ENUMs e constraints do banco atual estão integralmente representados nele.

| Objeto antigo | Tipo | Preservado em | Diagnóstico | Situação |
|---|---|---|---|---|
| usuarios | tabela/colunas/perfil-status | schema_current + schemas | parcial | preservado |
| clientes | tabela/constraints | schema_current + schemas | parcial | preservado |
| aparelhos | tabela/FK cliente | schema_current + schemas | parcial | preservado |
| aparelho_modelos | tabela/unique marca-modelo | schema_current | não | estrutura preservada; seed não |
| servicos_referencia | tabela | schema_current + schemas | não | preservado |
| categorias | tabela | schema_current + schemas | não | preservado |
| configuracoes | tabela/unique chave | schema_current + schemas | não | estrutura preservada; defaults dependem de migrations existentes |
| estoque | tabela/colunas ML/imagem/índices | schema_current + migrations 2026-05-12/15 | parcial | preservado |
| ordens_servico | tabela/status/defaults/FKs/fotos | schema_current + migrations | parcial | preservado |
| os_historico | tabela/FKs | schema_current | não | preservado |
| os_pagamentos | tabela/FKs/índice | schema_current + migration 2026-05-12 | parcial | preservado |
| financeiro | tabela/FKs/índices | schema_current + migrations | parcial | preservado; backfill não |
| gastos_pessoais | tabela | schema_current | não | preservado |
| gastos_pessoais_categorias | tabela | schema_current | não | preservado |
| compras_solicitacoes | tabela | schema_current | não | preservado |
| fornecedores | tabela | schema_current | não | preservado |
| compras_notas | tabela/anexo/os/FK/índice | schema_current + migration 2026-07-06 | não | preservado |
| compras_nota_itens | tabela/FK | schema_current | não | preservado |
| termos_compra_venda | tabela/índices | schema_current | não | preservado |
| recados | tabela/índices | schema_current | não | preservado |
| pdv_vendas | tabela/caixa/status/índices/FKs | schema_current + migrations Point/caixa | sim | preservado |
| pdv_caixas | tabela/defaults/unique/índice | schema_current + migration 2026-07-11 | sim | preservado |
| pdv_itens | tabela/FK | schema_current + schemas | sim | preservado |
| estoque_movimentacoes | tabela/os/FK | schema_current + schemas | não | preservado |
| estoque_imagens | tabela/FK | schema_current + migration 2026-05-12 | não | preservado |
| contas_publicas | tabela | schema_current | não | preservado |
| mercado_pago_pedidos | tabela/financeiro/estoque | schema_current | não | preservado |
| mercado_pago_point_orders | tabela/nullability/FKs/índices | schema_current + migration 2026-07-06 | sim | preservado |
| mercado_livre_logs | tabela/índice | schema_current + migrations | não | preservado |
| mercado_pago_logs | tabela | schema_current + migration 2026-05-12 | não | preservado |
| auditoria_logs | tabela/FK/índices | schema_current | não | preservado |

O diagnóstico é propositalmente mínimo e não valida todos os 31 objetos. Isso não mascara ausência nos módulos PDV/Point, mas não é um verificador integral da instalação.

## Migration 2026-07-11

- Não contém DROP, TRUNCATE, DELETE, INSERT, dados de demonstração ou credenciais.
- CREATE TABLE usa `IF NOT EXISTS`; ADD COLUMN e ADD INDEX usam `IF NOT EXISTS`.
- Preserva dados e índices existentes.
- `MODIFY status` não tem precondição e pode reconstruir a coluna; representa o ENUM esperado, mas não é plenamente idempotente do ponto de vista operacional e pode falhar se existirem valores fora do conjunto.
- A migration não foi executada.

## RequiredSchema

Somente executa SELECT preparado em `information_schema.COLUMNS` e `STATISTICS`; não possui cache, DDL ou DML. Falha com exceção segura e log sem SQL/credenciais. A verificação ocorre somente quando `PdvModel` ou `MercadoPagoPointModel` é construído: quatro consultas para PDV e duas para Point. Não afeta login/dashboard diretamente. Tem custo repetido por instanciação, observado dentro dos tempos HTTP abaixo, sem evidência de lentidão crítica local.

## Router

A única alteração desta correção foi capturar `SchemaOutdatedException`, renderizar 503 e interromper o despacho. Não houve mudança em autenticação, CSRF, perfis, aliases, normalização, logout, 404, 405 ou AJAX.

## Evidências de execução

- Banco isolado: conexão, SELECT, prepared statement, begin/INSERT/rollback, exceção e nova conexão passaram; fixture restante = 0; charset `utf8mb4` e texto acentuado íntegro.
- HTTP médio em três chamadas: dashboard 209,3 ms; clientes 228,3 ms; OS 61,3 ms; PDV/caixa/vendas 102,7 ms; financeiro 73,7 ms; vitrine 64,3 ms; configurações 88 ms; consulta Point 60 ms. Todos 200; login visitante 200.
- Schema completo: diagnóstico OK.
- Schema incompleto isolado: página PDV 503, mensagem amigável, tabela não recriada, banco temporário removido.
- Log dinâmico: zero DDL e zero DML inesperado.
- NoRuntimeDdlTest, RouterTest, LogoutTest, testes de pagamento manual e integração com rollback: OK.
- PHP lint: 148 arquivos, zero falhas.

## Diff check

O `git diff --check` informa 194 linhas em sete arquivos já apontados antes da correção: ClientesController, PdvModel e views de financeiro, OS, PDV e impressão A4. Nenhum arquivo novo desta correção contém whitespace final. A remoção de métodos deslocou linhas antigas de PdvModel, mas não criou o whitespace. Nada foi limpo por estar fora do escopo.

## Conclusão

DDL runtime foi removido sem quebrar a conexão ou os módulos, mas a redução eliminou também rotinas de dados que precisam de decisão/preservação controlada. A auditoria não pode afirmar que somente schema automático foi retirado.
