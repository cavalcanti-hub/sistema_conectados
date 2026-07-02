-- Indices complementares para seguranca/performance.
-- Idempotente para MySQL/MariaDB em hospedagem compartilhada.

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'financeiro' AND index_name = 'idx_financeiro_tipo');
SET @sql := IF(@idx = 0, 'ALTER TABLE financeiro ADD INDEX idx_financeiro_tipo (tipo)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'financeiro' AND index_name = 'idx_financeiro_data_pagamento');
SET @sql := IF(@idx = 0, 'ALTER TABLE financeiro ADD INDEX idx_financeiro_data_pagamento (data_pagamento)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'financeiro' AND index_name = 'idx_financeiro_os');
SET @sql := IF(@idx = 0, 'ALTER TABLE financeiro ADD INDEX idx_financeiro_os (os_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'pdv_vendas' AND index_name = 'idx_pdv_status');
SET @sql := IF(@idx = 0, 'ALTER TABLE pdv_vendas ADD INDEX idx_pdv_status (status)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'pdv_vendas' AND index_name = 'idx_pdv_created_at');
SET @sql := IF(@idx = 0, 'ALTER TABLE pdv_vendas ADD INDEX idx_pdv_created_at (created_at)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'pdv_vendas' AND index_name = 'idx_pdv_cliente');
SET @sql := IF(@idx = 0, 'ALTER TABLE pdv_vendas ADD INDEX idx_pdv_cliente (cliente_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'pdv_vendas' AND index_name = 'idx_pdv_os');
SET @sql := IF(@idx = 0, 'ALTER TABLE pdv_vendas ADD INDEX idx_pdv_os (os_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'estoque' AND index_name = 'idx_estoque_categoria');
SET @sql := IF(@idx = 0, 'ALTER TABLE estoque ADD INDEX idx_estoque_categoria (categoria)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
