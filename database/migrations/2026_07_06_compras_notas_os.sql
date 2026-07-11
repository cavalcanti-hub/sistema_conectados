SET @col := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'compras_notas'
      AND COLUMN_NAME = 'os_id'
);
SET @sql := IF(@col = 0, 'ALTER TABLE compras_notas ADD COLUMN os_id INT NULL AFTER fornecedor_id', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'compras_notas'
      AND INDEX_NAME = 'idx_compras_notas_os'
);
SET @sql := IF(@idx = 0, 'ALTER TABLE compras_notas ADD INDEX idx_compras_notas_os (os_id)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'compras_notas'
      AND CONSTRAINT_NAME = 'fk_compras_notas_os'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @sql := IF(@fk = 0, 'ALTER TABLE compras_notas ADD CONSTRAINT fk_compras_notas_os FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE SET NULL', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
