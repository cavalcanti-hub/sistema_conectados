# Normalização de dados legada

`normalizeSchemaData()` misturava dois ALTERs com UPDATEs de três grafias antigas de status de OS. Os ALTERs estão preservados no schema/migrations. A consulta somente leitura no banco atual encontrou zero variantes simples (`Em analise`, `Aguardando aprovacao`, `Aguardando peca`).

Nenhum script de escrita foi recriado nesta correção porque não há pendência comprovada no banco atual. Instalações antigas devem primeiro diagnosticar contagens em cópia; qualquer normalização futura exige script CLI separado, dry-run, backup, transação e autorização.
