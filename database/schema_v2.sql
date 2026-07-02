-- Adições ao schema para PDV e funcionalidades completas
-- Técnicos (já são usuários, tabela extra para config)
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS meta_os_mes INT DEFAULT 30;

-- Estoque com mais campos
ALTER TABLE estoque ADD COLUMN IF NOT EXISTS numero_serie VARCHAR(100);
ALTER TABLE estoque ADD COLUMN IF NOT EXISTS unidade VARCHAR(20) DEFAULT 'unid';

-- Movimentações de estoque
CREATE TABLE IF NOT EXISTS estoque_movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    tipo ENUM('entrada','saida','ajuste') NOT NULL,
    quantidade INT NOT NULL,
    motivo VARCHAR(255),
    os_id INT,
    usuario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES estoque(id) ON DELETE CASCADE
);

-- Serviços padrão com mais dados
INSERT IGNORE INTO servicos_referencia (nome, preco_sugerido, tempo_medio, garantia_padrao) VALUES
('Troca de Tela', 250.00, '2 horas', 90),
('Troca de Bateria', 120.00, '1 hora', 90),
('Conector de Carga', 80.00, '1 hora', 90),
('Câmera', 150.00, '2 horas', 90),
('Alto-falante', 70.00, '1 hora', 90),
('Microfone', 70.00, '1 hora', 90),
('Software / Formatação', 60.00, '1 hora', 30),
('Atualização de Sistema', 40.00, '30 min', 30),
('Desbloqueio', 80.00, '1 hora', 30),
('Reparo em Placa', 400.00, '3 horas', 60),
('Higienização', 30.00, '30 min', 0),
('Troca de Vidro Traseiro', 180.00, '2 horas', 90);

-- PDV - Vendas balcão
CREATE TABLE IF NOT EXISTS pdv_vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_venda VARCHAR(20) UNIQUE NOT NULL,
    cliente_id INT,
    os_id INT,
    usuario_id INT NOT NULL,
    subtotal DECIMAL(10,2) DEFAULT 0.00,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) DEFAULT 0.00,
    forma_pagamento VARCHAR(50),
    status ENUM('aberta','finalizada','cancelada') DEFAULT 'aberta',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE SET NULL
);

-- Itens do PDV
CREATE TABLE IF NOT EXISTS pdv_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venda_id INT NOT NULL,
    produto_id INT,
    descricao VARCHAR(255) NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) GENERATED ALWAYS AS (quantidade * preco_unitario) STORED,
    FOREIGN KEY (venda_id) REFERENCES pdv_vendas(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES estoque(id) ON DELETE SET NULL
);

-- Garantias registradas
CREATE TABLE IF NOT EXISTS garantias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    servico_nome VARCHAR(150),
    data_inicio DATE,
    data_expira DATE,
    status ENUM('vigente','expirada','acionada') DEFAULT 'vigente',
    observacoes TEXT,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE
);

-- Mensagens enviadas
CREATE TABLE IF NOT EXISTS mensagens_enviadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT,
    cliente_id INT,
    tipo ENUM('whatsapp','email') DEFAULT 'whatsapp',
    template VARCHAR(100),
    conteudo TEXT,
    status ENUM('enviada','falha') DEFAULT 'enviada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
);

-- Integracao Mercado Livre
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
    FOREIGN KEY (produto_id) REFERENCES estoque(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS mercado_pago_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    external_reference VARCHAR(80) NULL,
    payment_id VARCHAR(80) NULL,
    status VARCHAR(40) NULL,
    mensagem TEXT NULL,
    payload MEDIUMTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mp_logs_ref (external_reference)
);
