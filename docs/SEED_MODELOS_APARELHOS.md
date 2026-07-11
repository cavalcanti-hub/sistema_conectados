# Seed controlado de modelos de aparelhos

O catálogo foi extraído integralmente do antigo `seedPhoneModels`: 11 marcas e 516 modelos, sem acréscimos.

- Diagnóstico: `php scripts/seed_phone_models.php --dry-run`
- Aplicação autorizada: `php scripts/seed_phone_models.php --apply`
- Em cópia: adicionar `--database=NOME_DO_BANCO_TESTE`.

O script é CLI, transacional e compara marca+modelo normalizados. Não altera, apaga ou desativa registros existentes/customizados. Segundo apply insere zero. O rollback é automático em exceção; antes de apply em empresa real, manter backup e validar em cópia.
