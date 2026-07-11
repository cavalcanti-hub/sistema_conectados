ALTER TABLE ordens_servico
    ADD COLUMN IF NOT EXISTS fotos_saida TEXT NULL AFTER fotos;
