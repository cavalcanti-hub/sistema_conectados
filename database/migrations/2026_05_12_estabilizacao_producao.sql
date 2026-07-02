-- Estabilizacao de producao Conectados
-- Execute em backup/teste antes de producao se o auto-upgrade do Database.php estiver desativado.

ALTER TABLE estoque MODIFY localizacao TEXT NULL;

ALTER TABLE estoque
    ADD COLUMN IF NOT EXISTS imagem VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS imagem_mime VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS imagem_blob LONGBLOB NULL,
    ADD COLUMN IF NOT EXISTS numero_serie VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS unidade VARCHAR(20) DEFAULT 'unid';

ALTER TABLE estoque
    ADD COLUMN IF NOT EXISTS mercado_livre_item_id VARCHAR(40) NULL,
    ADD COLUMN IF NOT EXISTS mercado_livre_permalink VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS mercado_livre_status VARCHAR(40) NULL,
    ADD COLUMN IF NOT EXISTS mercado_livre_category_id VARCHAR(40) NULL,
    ADD COLUMN IF NOT EXISTS mercado_livre_listing_type_id VARCHAR(40) NULL,
    ADD COLUMN IF NOT EXISTS mercado_livre_condition VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS mercado_livre_attributes TEXT NULL,
    ADD COLUMN IF NOT EXISTS mercado_livre_last_sync_at DATETIME NULL,
    ADD COLUMN IF NOT EXISTS mercado_livre_last_error TEXT NULL;

CREATE TABLE IF NOT EXISTS mercado_livre_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NULL,
    item_id VARCHAR(40) NULL,
    acao VARCHAR(60) NOT NULL,
    status VARCHAR(40) NOT NULL,
    mensagem TEXT NULL,
    payload MEDIUMTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ml_logs_produto (produto_id),
    CONSTRAINT fk_mercado_livre_logs_produto FOREIGN KEY (produto_id) REFERENCES estoque(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estoque_imagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    imagem_mime VARCHAR(100) NULL,
    ordem INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_estoque_imagens_produto (produto_id, ordem, id),
    CONSTRAINT fk_estoque_imagens_produto FOREIGN KEY (produto_id) REFERENCES estoque(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS os_pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    forma_pagamento VARCHAR(50) NULL,
    data_pagamento DATE NOT NULL,
    observacao VARCHAR(255) NULL,
    usuario_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_os_pagamentos_os (os_id, data_pagamento, id),
    CONSTRAINT fk_os_pagamentos_os FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    CONSTRAINT fk_os_pagamentos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mercado_pago_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    external_reference VARCHAR(80) NULL,
    payment_id VARCHAR(80) NULL,
    status VARCHAR(40) NULL,
    mensagem TEXT NULL,
    payload MEDIUMTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mp_logs_ref (external_reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE ordens_servico MODIFY status VARCHAR(50) NOT NULL DEFAULT 'Recebido';

UPDATE ordens_servico SET status = 'Em análise' WHERE status IN ('Em analise','Em anÃ¡lise','Em anÃƒÂ¡lise');
UPDATE ordens_servico SET status = 'Aguardando aprovação' WHERE status IN ('Aguardando aprovacao','Aguardando aprovaÃ§Ã£o','Aguardando aprovaÃƒÂ§ÃƒÂ£o');
UPDATE ordens_servico SET status = 'Aguardando peça' WHERE status IN ('Aguardando peca','Aguardando peÃ§a','Aguardando peÃƒÂ§a');

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'estoque' AND index_name = 'idx_estoque_tipo');
SET @sql := IF(@idx = 0, 'ALTER TABLE estoque ADD INDEX idx_estoque_tipo (tipo)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'estoque' AND index_name = 'idx_estoque_ml_status');
SET @sql := IF(@idx = 0, 'ALTER TABLE estoque ADD INDEX idx_estoque_ml_status (mercado_livre_status)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'ordens_servico' AND index_name = 'idx_os_status');
SET @sql := IF(@idx = 0, 'ALTER TABLE ordens_servico ADD INDEX idx_os_status (status)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'mercado_pago_pedidos' AND index_name = 'idx_mp_status');
SET @sql := IF(@idx = 0, 'ALTER TABLE mercado_pago_pedidos ADD INDEX idx_mp_status (status)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
