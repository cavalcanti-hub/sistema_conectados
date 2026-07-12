-- Executar somente pelo processo controlado de migrations, apos backup.
SET @schema_name = DATABASE();
SET @column_type = (SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@schema_name AND TABLE_NAME='aparelhos' AND COLUMN_NAME='senha_padrao' LIMIT 1);
SET @sql = IF(@column_type IS NULL,
    'SELECT ''Precondicao falhou: aparelhos.senha_padrao ausente'' AS erro',
    IF(@column_type='varchar(512)', 'SELECT ''Coluna ja adequada'' AS resultado',
       'ALTER TABLE aparelhos MODIFY senha_padrao VARCHAR(512) NULL'));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
