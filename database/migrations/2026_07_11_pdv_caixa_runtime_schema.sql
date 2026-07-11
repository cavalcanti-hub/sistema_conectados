-- Executar somente por processo administrativo controlado, nunca por request.
CREATE TABLE IF NOT EXISTS pdv_caixas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_caixa DATE NOT NULL,
    status ENUM('aberto','fechado') NOT NULL DEFAULT 'aberto',
    abertura_tipo ENUM('automatica','manual') NOT NULL DEFAULT 'automatica',
    fechamento_tipo ENUM('automatico','manual') NULL,
    aberto_por INT NULL,
    fechado_por INT NULL,
    aberto_em DATETIME NOT NULL,
    fechado_em DATETIME NULL,
    valor_inicial DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_suprimento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_sangria DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_informado DECIMAL(10,2) NULL,
    observacoes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_pdv_caixas_data (data_caixa),
    INDEX idx_pdv_caixas_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @pdv_table := (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pdv_vendas');
SET @caixa_col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pdv_vendas' AND COLUMN_NAME='caixa_id');
SET @sql := IF(@pdv_table=1 AND @caixa_col=0, 'ALTER TABLE pdv_vendas ADD COLUMN caixa_id INT NULL AFTER numero_venda', 'SELECT ''caixa_id sem alteracao''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @status_col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pdv_vendas' AND COLUMN_NAME='status');
SET @status_type := (SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pdv_vendas' AND COLUMN_NAME='status' LIMIT 1);
SET @status_nullable := (SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pdv_vendas' AND COLUMN_NAME='status' LIMIT 1);
SET @status_default := (SELECT COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pdv_vendas' AND COLUMN_NAME='status' LIMIT 1);
SET @sql := IF(@status_col=1, 'SELECT COUNT(*) INTO @status_invalid FROM pdv_vendas WHERE status NOT IN (''aberta'',''aguardando_point'',''finalizada'',''cancelada'')', 'SET @status_invalid=0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @status_ok := @status_type="enum('aberta','aguardando_point','finalizada','cancelada')" AND @status_nullable='YES' AND @status_default='aberta';
SET @sql := CASE
  WHEN @status_col=0 THEN 'SELECT ''status ausente; migration base obrigatoria'''
  WHEN @status_invalid>0 THEN 'SELECT * FROM MIGRATION_BLOCKED_PDV_STATUS_INCOMPATIVEL'
  WHEN @status_ok THEN 'SELECT ''status sem alteracao'''
  ELSE 'ALTER TABLE pdv_vendas MODIFY status ENUM(''aberta'',''aguardando_point'',''finalizada'',''cancelada'') NULL DEFAULT ''aberta'''
END;
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @caixa_idx := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pdv_vendas' AND INDEX_NAME='idx_pdv_caixa');
SET @sql := IF(@pdv_table=1 AND @caixa_col=1 AND @caixa_idx=0, 'ALTER TABLE pdv_vendas ADD INDEX idx_pdv_caixa (caixa_id)', 'SELECT ''idx_pdv_caixa sem alteracao''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
