-- SQL Schema para Conectados - Gestão de Assistência Técnica

-- Usuários e Perfis
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('Administrador', 'Atendente', 'Técnico', 'Financeiro') NOT NULL,
    especialidade VARCHAR(100),
    comissao DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('Ativo', 'Inativo') DEFAULT 'Ativo',
    ultimo_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf_cnpj VARCHAR(20) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    whatsapp VARCHAR(20),
    email VARCHAR(100),
    endereco TEXT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Aparelhos
CREATE TABLE IF NOT EXISTS aparelhos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    imei VARCHAR(50),
    numero_serie VARCHAR(50),
    cor VARCHAR(30),
    senha_padrao VARCHAR(100),
    estado_fisico TEXT,
    acessorios TEXT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Serviços Padronizados
CREATE TABLE IF NOT EXISTS servicos_referencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    preco_sugerido DECIMAL(10,2),
    tempo_medio VARCHAR(50),
    garantia_padrao INT DEFAULT 90 -- dias
);

-- Estoque de Peças
CREATE TABLE IF NOT EXISTS estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_interno VARCHAR(50) UNIQUE,
    nome VARCHAR(150) NOT NULL,
    tipo ENUM('peca', 'produto') NOT NULL DEFAULT 'peca',
    categoria VARCHAR(50),
    marca_compativel VARCHAR(50),
    modelo_compativel VARCHAR(100),
    quantidade INT DEFAULT 0,
    estoque_minimo INT DEFAULT 5,
    custo DECIMAL(10,2),
    preco_venda DECIMAL(10,2),
    fornecedor VARCHAR(100),
    localizacao TEXT NULL,
    imagem VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categorias para peças e produtos
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('peca', 'produto') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_categorias_nome_tipo (nome, tipo)
);

-- Configurações gerais do sistema
CREATE TABLE IF NOT EXISTS configuracoes (
    chave VARCHAR(100) PRIMARY KEY,
    valor TEXT NULL
);

-- Ordens de Serviço (OS)
CREATE TABLE IF NOT EXISTS ordens_servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_os VARCHAR(20) UNIQUE NOT NULL,
    cliente_id INT NOT NULL,
    aparelho_id INT NOT NULL,
    tecnico_id INT,
    problema_relatado TEXT,
    diagnostico_tecnico TEXT,
    servico_realizar TEXT,
    prioridade ENUM('Baixa', 'Normal', 'Alta', 'Urgente') DEFAULT 'Normal',
    status ENUM('Recebido', 'Em análise', 'Aguardando aprovação', 'Aprovado', 'Reprovado', 'Em reparo', 'Aguardando peça', 'Pronto', 'Entregue', 'Cancelado') DEFAULT 'Recebido',
    prazo_estimado DATE,
    valor_mao_obra DECIMAL(10,2) DEFAULT 0.00,
    valor_pecas DECIMAL(10,2) DEFAULT 0.00,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    valor_total DECIMAL(10,2) GENERATED ALWAYS AS (valor_mao_obra + valor_pecas - desconto) STORED,
    forma_pagamento VARCHAR(50),
    situacao_pagamento ENUM('Pendente', 'Parcial', 'Pago') DEFAULT 'Pendente',
    garantia_expira DATE,
    termo_aceite BOOLEAN DEFAULT FALSE,
    fotos_entrada JSON, -- Caminhos das fotos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (aparelho_id) REFERENCES aparelhos(id),
    FOREIGN KEY (tecnico_id) REFERENCES usuarios(id)
);

-- Histórico de Movimentação da OS
CREATE TABLE IF NOT EXISTS os_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    usuario_id INT NOT NULL,
    status_anterior VARCHAR(50),
    status_novo VARCHAR(50),
    observacao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Financeiro (Fluxo de Caixa)
CREATE TABLE IF NOT EXISTS financeiro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('Receita', 'Despesa') NOT NULL,
    categoria VARCHAR(100),
    descricao TEXT,
    valor DECIMAL(10,2) NOT NULL,
    os_id INT,
    usuario_id INT,
    data_pagamento DATE,
    forma_pagamento VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Logs de Auditoria
CREATE TABLE IF NOT EXISTS auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao TEXT NOT NULL,
    tabela VARCHAR(50),
    registro_id INT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

INSERT IGNORE INTO configuracoes (chave, valor) VALUES
('nome_empresa', ''),
('whatsapp', ''),
('endereco', ''),
('website', ''),
('email_negocio', ''),
('instagram', ''),
('facebook', ''),
('tiktok', ''),
('linkedin', ''),
('mercadopago_public_key', ''),
('mercadopago_access_token', ''),
('mercado_livre_client_id', ''),
('mercado_livre_client_secret', ''),
('mercado_livre_access_token', ''),
('mercado_livre_refresh_token', ''),
('mercado_livre_token_expires_at', ''),
('mercado_livre_user_id', ''),
('mercado_livre_nickname', ''),
('mercado_livre_default_category_id', ''),
('mercado_livre_default_listing_type_id', ''),
('mercado_livre_default_condition', ''),
('mercado_livre_default_shipping_mode', 'me2'),
('chave_pix', '');

ALTER TABLE estoque
    MODIFY localizacao TEXT NULL,
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
