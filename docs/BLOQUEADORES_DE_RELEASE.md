# Bloqueadores de release

## B1 — DDL durante requisições normais (alto/bloqueador)

`PdvModel::__construct()` chama `ensurePdvSchema()` e pode executar `CREATE TABLE` e `ALTER TABLE`. `MercadoPagoPointModel` possui comportamento equivalente. `DB_SKIP_AUTO_SCHEMA` protege `Database::ensureSchema()`, mas não esses models. Há risco de lock, privilégio excessivo e divergência entre empresas.

## B2 — Webhook sem validação criptográfica aplicada (crítico/bloqueador)

`mercadopago/pointWebhook` é público. A metadata `external_auth=mercadopago_signature` não é aplicada pelo Router nem pelo controller. O fluxo aceita payload, tenta consultar a ordem e, quando a consulta falha, continua com dados recebidos. Pode atingir aprovação financeira local.

## B3 — TLS desabilitado em operação Point (crítico/bloqueador)

`MercadoPagoController::liberarPoint()` define `CURLOPT_SSL_VERIFYPEER=false`. A operação não foi chamada nesta auditoria.

## B4 — Exclusão física de OS (alto/bloqueador)

`OsModel::delete()` remove pagamentos, histórico, OS e potencialmente aparelho; as views oferecem a ação. Apesar de POST/CSRF/perfil administrador, há perda irreversível e quebra de rastreabilidade.

## B5 — Fluxo legado de pagamento inconsistente (alto/bloqueador)

Criação/edição usa `registerOsPaymentFromPost()`, `float` e inserts diretos, sem nonce, `FOR UPDATE` ou limite explícito pelo saldo. Não reutiliza `ManualOsPaymentService`, podendo divergir do fluxo novo sob repetição/concorrência.

## Riscos não elevados isoladamente a bloqueador

PIN de aparelho em texto puro; payloads de integração em log; fallback CSRF com `uniqid`; segredos editáveis renderizados no formulário de configuração; whitespace em `git diff --check`; cobertura dinâmica incompleta de CRUD, uploads e PDFs.
