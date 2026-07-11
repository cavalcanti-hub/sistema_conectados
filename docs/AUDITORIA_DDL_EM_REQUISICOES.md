# Auditoria de DDL em requisições

## Inventário e classificação

| Arquivo/classe/método | Comando | Execução anterior | Categoria/risco | Destino |
|---|---|---|---|---|
| `app/Config/Database.php`, `Database::__construct → ensureSchema` | CREATE TABLE, ALTER TABLE, ADD/MODIFY, índices/FKs | Toda nova conexão quando a flag permitia | bootstrap/request; crítico | removido do runtime; preservado em `database/schema_current.sql` e migrations |
| `app/Models/PdvModel.php`, `__construct → ensurePdvSchema` | CREATE `pdv_caixas`; ALTER `pdv_vendas`; índice | Toda instanciação do PDV/caixa | construtor/request; crítico | `2026_07_11_pdv_caixa_runtime_schema.sql` |
| `app/Models/MercadoPagoPointModel.php`, `__construct → ensurePointOrderSchema` | CREATE/ALTER/índices Point | Toda instanciação Point/webhook | construtor/request; crítico | migration existente `2026_07_06_mercado_pago_point_orders.sql` |
| `database/migrations/*.sql` | CREATE/ALTER/índices | Somente execução manual | migration; permitido | manter |
| `database/schema.sql`, `schema_v2.sql` | CREATE/ALTER | Referência/instalação | schema de instalação; permitido | manter |
| `app/Models/BackupModel.php` | SHOW CREATE e texto DROP no dump | Backup administrativo; não executa DROP | gerador de dump | manter; SQL é conteúdo do backup |
| `tests`, `scripts/check_required_schema.php` | nenhum DDL runtime | CLI/teste | permitido | manter somente leitura |

Não foram encontradas chamadas DDL em controllers, Router, helpers ou front controller. Financeiro, estoque, configurações, autenticação e dashboard não possuem construtores de reparo estrutural. A flag `DB_SKIP_AUTO_SCHEMA` tornou-se obsoleta: seu valor não reativa DDL, pois a conexão não chama mais `ensureSchema()`.

## Fluxo corrigido

Conexão apenas abre PDO e configura charset/erros. Os models PDV e Point recebem a conexão e fazem consultas somente leitura ao `information_schema`. Schema ausente gera `SchemaOutdatedException`, log técnico sem SQL/credenciais e resposta 503 amigável. Nenhuma migration é disparada automaticamente.
