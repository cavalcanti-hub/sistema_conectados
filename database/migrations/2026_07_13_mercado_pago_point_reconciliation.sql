SET @table_exists := (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mercado_pago_point_orders');
SET @col_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mercado_pago_point_orders' AND COLUMN_NAME='installments_requested');

SET @sql := IF(@table_exists=1 AND @col_exists=0, '
ALTER TABLE mercado_pago_point_orders
    ADD COLUMN installments_requested INT NULL,
    ADD COLUMN installments_confirmed INT NULL,
    ADD COLUMN installments_cost VARCHAR(20) NULL,
    ADD COLUMN customer_total_paid DECIMAL(10,2) NULL,
    ADD COLUMN customer_financing_cost DECIMAL(10,2) NULL,
    ADD COLUMN seller_processing_fee DECIMAL(10,2) NULL,
    ADD COLUMN seller_financing_cost DECIMAL(10,2) NULL,
    ADD COLUMN seller_total_fee DECIMAL(10,2) NULL,
    ADD COLUMN seller_net_received DECIMAL(10,2) NULL,
    ADD COLUMN values_source ENUM(''estimated'', ''confirmed'') DEFAULT ''estimated'',
    ADD COLUMN reconciliation_status VARCHAR(40) NULL,
    ADD COLUMN reconciliation_date DATETIME NULL
', 'SELECT ''columns already exist or table missing''');

PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
