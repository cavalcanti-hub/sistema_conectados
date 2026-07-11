# Diagnóstico financeiro de OS

Execute somente por CLI: `php scripts/diagnose_os_payments.php`.

O script é somente leitura e informa apenas IDs de OS, valores técnicos e contagens. Verifica total pago negativo/acima do total, status Pago com saldo, Pendente sem saldo e receita de OS sem pagamento correspondente. Não corrige nem remove registros.

Resultado em 11/07/2026: zero inconsistências nas cinco categorias verificadas.
