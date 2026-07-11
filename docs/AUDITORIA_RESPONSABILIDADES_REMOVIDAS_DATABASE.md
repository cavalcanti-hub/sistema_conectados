# Auditoria das responsabilidades removidas de Database

| Método antigo | Tabelas | Estrutura/dados | Idempotência anterior | Risco automático | Destino seguro |
|---|---|---|---|---|---|
| `seedAdminFromEnv` | usuarios | INSERT de administrador | `INSERT IGNORE` | credencial persistente e criação em request | `scripts/create_initial_admin.php`, CLI/transação/dry-run |
| `seedPhoneModels` | aparelho_modelos | INSERT de 516 modelos | `INSERT IGNORE` | centenas de queries em request | catálogo em `database/seeds/phone_models.php` e seed CLI |
| `syncApprovedMercadoPagoFinanceiro` | mercado_pago_pedidos, financeiro | INSERT/UPDATE retroativo | NOT EXISTS + marcador | DML financeiro ao abrir páginas | reconciliação CLI local, dry-run padrão |
| `normalizeSchemaData` | estoque, ordens_servico | dois ALTERs e UPDATE de status mojibake | parcialmente | DDL/DML silencioso | ALTERs já representados em migrations; banco atual tem zero variantes simples pendentes; não recriado |
| `ensurePdvVendaStatusSupportsPoint` | pdv_vendas | ALTER ENUM | consulta prévia parcial | lock/rebuild em request | migration condicional 2026-07-11 |
| `ensureMercadoPagoPointOrdersNullableOs` | mercado_pago_point_orders | ALTER nullability | consulta prévia | lock em webhook/Point | schema atual + migration Point existente |

Todos eram chamados por `ensureSchema()` em cada nova conexão quando a flag permitia. Nenhum voltou ao bootstrap, model, controller ou request.
