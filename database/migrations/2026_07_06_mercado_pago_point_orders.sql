CREATE TABLE IF NOT EXISTS mercado_pago_point_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NULL,
    pdv_venda_id INT NULL,
    numero_os VARCHAR(20) NULL,
    external_reference VARCHAR(80) NOT NULL UNIQUE,
    mp_order_id VARCHAR(80) NULL UNIQUE,
    payment_id VARCHAR(80) NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'created',
    status_detail VARCHAR(100) NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method VARCHAR(80) NULL,
    terminal_id VARCHAR(120) NULL,
    payload MEDIUMTEXT NULL,
    financeiro_lancado_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mp_point_orders_os FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    CONSTRAINT fk_mp_point_orders_pdv FOREIGN KEY (pdv_venda_id) REFERENCES pdv_vendas(id) ON DELETE CASCADE,
    INDEX idx_mp_point_orders_os (os_id),
    INDEX idx_mp_point_orders_pdv (pdv_venda_id),
    INDEX idx_mp_point_orders_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE pdv_vendas
    MODIFY status ENUM('aberta','aguardando_point','finalizada','cancelada') DEFAULT 'aberta';

INSERT INTO configuracoes (chave, valor) VALUES
('mercadopago_point_terminal_id', ''),
('mercadopago_point_default_payment_type', 'credit_card'),
('mercadopago_point_default_installments', '1'),
('mercadopago_point_installments_cost', 'seller'),
('mercadopago_point_print_on_terminal', 'no_ticket')
ON DUPLICATE KEY UPDATE valor = IF(valor IS NULL OR valor = '', VALUES(valor), valor);
