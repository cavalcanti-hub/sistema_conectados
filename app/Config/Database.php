<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $conn;

    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;

    private function __construct()
    {
        $this->host = \app_env('DB_HOST', '127.0.0.1');
        $this->port = \app_env('DB_PORT', '3306');
        $this->db_name = \app_env('DB_DATABASE', 'sistema-conectados');
        $this->username = \app_env('DB_USERNAME', 'root');
        $this->password = \app_env('DB_PASSWORD', '');

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("SET NAMES utf8mb4");
            $this->ensureSchema();
        } catch (PDOException $e) {
            http_response_code(500);
            $debug = filter_var(\app_env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
            die($debug ? "Erro na conexao: " . $e->getMessage() : "Erro ao conectar ao banco de dados.");
        }
    }

    private function ensureSchema()
    {
        $statements = [
            "CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                senha VARCHAR(255) NOT NULL,
                perfil VARCHAR(50) NOT NULL,
                especialidade VARCHAR(100),
                comissao DECIMAL(5,2) DEFAULT 0.00,
                meta_os_mes INT DEFAULT 30,
                status ENUM('Ativo', 'Inativo') DEFAULT 'Ativo',
                ultimo_login DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS clientes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                cpf_cnpj VARCHAR(20) UNIQUE NOT NULL,
                telefone VARCHAR(20),
                whatsapp VARCHAR(20),
                email VARCHAR(100),
                endereco TEXT,
                observacoes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS aparelhos (
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
                CONSTRAINT fk_aparelhos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS aparelho_modelos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                marca VARCHAR(80) NOT NULL,
                modelo VARCHAR(140) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_aparelho_modelos_marca_modelo (marca, modelo),
                INDEX idx_aparelho_modelos_marca (marca)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS servicos_referencia (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                preco_sugerido DECIMAL(10,2),
                tempo_medio VARCHAR(50),
                garantia_padrao INT DEFAULT 90
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS categorias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                tipo ENUM('peca', 'produto') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_categorias_nome_tipo (nome, tipo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS configuracoes (
                chave VARCHAR(100) PRIMARY KEY,
                valor TEXT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS estoque (
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
                imagem_mime VARCHAR(100) NULL,
                imagem_blob LONGBLOB NULL,
                numero_serie VARCHAR(100) NULL,
                unidade VARCHAR(20) DEFAULT 'unid',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS ordens_servico (
                id INT AUTO_INCREMENT PRIMARY KEY,
                numero_os VARCHAR(20) UNIQUE NOT NULL,
                cliente_id INT NOT NULL,
                aparelho_id INT NOT NULL,
                tecnico_id INT NULL,
                problema_relatado TEXT,
                diagnostico_tecnico TEXT,
                servico_realizar TEXT,
                prioridade ENUM('Baixa', 'Normal', 'Alta', 'Urgente') DEFAULT 'Normal',
                status ENUM('Recebido', 'Em análise', 'Em anÃ¡lise', 'Aguardando aprovação', 'Aguardando aprovaÃ§Ã£o', 'Aprovado', 'Reprovado', 'Em reparo', 'Aguardando peça', 'Aguardando peÃ§a', 'Pronto', 'Entregue', 'Cancelado') DEFAULT 'Recebido',
                prazo_estimado DATE,
                valor_mao_obra DECIMAL(10,2) DEFAULT 0.00,
                valor_pecas DECIMAL(10,2) DEFAULT 0.00,
                desconto DECIMAL(10,2) DEFAULT 0.00,
                valor_total DECIMAL(10,2) GENERATED ALWAYS AS (valor_mao_obra + valor_pecas - desconto) STORED,
                forma_pagamento VARCHAR(50),
                situacao_pagamento ENUM('Pendente', 'Parcial', 'Pago') DEFAULT 'Pendente',
                garantia_expira DATE,
                termo_aceite BOOLEAN DEFAULT FALSE,
                fotos TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_os_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
                CONSTRAINT fk_os_aparelho FOREIGN KEY (aparelho_id) REFERENCES aparelhos(id),
                CONSTRAINT fk_os_tecnico FOREIGN KEY (tecnico_id) REFERENCES usuarios(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS os_historico (
                id INT AUTO_INCREMENT PRIMARY KEY,
                os_id INT NOT NULL,
                usuario_id INT NULL,
                status_anterior VARCHAR(50),
                status_novo VARCHAR(50),
                observacao TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_historico_os FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
                CONSTRAINT fk_historico_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS os_pagamentos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                os_id INT NOT NULL,
                valor DECIMAL(10,2) NOT NULL,
                forma_pagamento VARCHAR(50) NULL,
                data_pagamento DATE NOT NULL,
                observacao VARCHAR(255) NULL,
                usuario_id INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_os_pagamentos_os FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
                CONSTRAINT fk_os_pagamentos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
                INDEX idx_os_pagamentos_os (os_id, data_pagamento, id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS financeiro (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tipo ENUM('Receita', 'Despesa') NOT NULL,
                categoria VARCHAR(100),
                descricao TEXT,
                valor DECIMAL(10,2) NOT NULL,
                os_id INT NULL,
                usuario_id INT NULL,
                data_pagamento DATE,
                forma_pagamento VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_financeiro_os FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE SET NULL,
                CONSTRAINT fk_financeiro_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS gastos_pessoais (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tipo ENUM('Receita', 'Despesa') NOT NULL DEFAULT 'Despesa',
                categoria VARCHAR(100) NULL,
                descricao VARCHAR(255) NOT NULL,
                valor DECIMAL(10,2) NOT NULL,
                data_lancamento DATE NOT NULL,
                forma_pagamento VARCHAR(60) NULL,
                recorrente TINYINT(1) NOT NULL DEFAULT 0,
                observacoes TEXT NULL,
                usuario_id INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_gastos_pessoais_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
                INDEX idx_gastos_pessoais_data (data_lancamento),
                INDEX idx_gastos_pessoais_tipo (tipo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS gastos_pessoais_categorias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                tipo ENUM('Receita', 'Despesa', 'Ambos') NOT NULL DEFAULT 'Despesa',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_gastos_pessoais_categoria (nome)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS compras_solicitacoes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                item_nome VARCHAR(180) NOT NULL,
                tipo ENUM('peca', 'produto') NOT NULL DEFAULT 'peca',
                quantidade INT NOT NULL DEFAULT 1,
                fornecedor VARCHAR(150) NULL,
                prioridade ENUM('Baixa', 'Normal', 'Alta', 'Urgente') NOT NULL DEFAULT 'Normal',
                status ENUM('Pendente', 'Solicitado', 'Comprado', 'Recebido', 'Cancelado') NOT NULL DEFAULT 'Pendente',
                observacoes TEXT NULL,
                usuario_id INT NULL,
                data_solicitacao DATE NOT NULL,
                data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_compras_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
                INDEX idx_compras_status (status),
                INDEX idx_compras_tipo (tipo),
                INDEX idx_compras_data (data_solicitacao)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS fornecedores (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(160) NOT NULL,
                documento VARCHAR(40) NULL,
                telefone VARCHAR(40) NULL,
                email VARCHAR(120) NULL,
                endereco TEXT NULL,
                observacoes TEXT NULL,
                ativo TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_fornecedores_nome (nome),
                INDEX idx_fornecedores_ativo (ativo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS compras_notas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fornecedor_id INT NULL,
                numero VARCHAR(80) NULL,
                data_emissao DATE NOT NULL,
                data_vencimento DATE NULL,
                valor_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                forma_pagamento VARCHAR(60) NULL,
                status ENUM('Aberta', 'Baixada', 'Cancelada') NOT NULL DEFAULT 'Aberta',
                observacoes TEXT NULL,
                usuario_id INT NULL,
                baixado_at DATETIME NULL,
                financeiro_id INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_compras_notas_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL,
                CONSTRAINT fk_compras_notas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
                CONSTRAINT fk_compras_notas_financeiro FOREIGN KEY (financeiro_id) REFERENCES financeiro(id) ON DELETE SET NULL,
                INDEX idx_compras_notas_status (status),
                INDEX idx_compras_notas_data (data_emissao)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS compras_nota_itens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nota_id INT NOT NULL,
                produto_id INT NULL,
                descricao VARCHAR(180) NOT NULL,
                tipo ENUM('peca', 'produto') NOT NULL DEFAULT 'peca',
                quantidade INT NOT NULL DEFAULT 1,
                valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_compras_nota_itens_nota FOREIGN KEY (nota_id) REFERENCES compras_notas(id) ON DELETE CASCADE,
                CONSTRAINT fk_compras_nota_itens_produto FOREIGN KEY (produto_id) REFERENCES estoque(id) ON DELETE SET NULL,
                INDEX idx_compras_nota_itens_nota (nota_id),
                INDEX idx_compras_nota_itens_produto (produto_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS termos_compra_venda (
                id INT AUTO_INCREMENT PRIMARY KEY,
                numero_termo VARCHAR(40) NOT NULL UNIQUE,
                vendedor_nome VARCHAR(160) NOT NULL,
                vendedor_contato VARCHAR(60) NULL,
                vendedor_cpf VARCHAR(30) NULL,
                vendedor_rg VARCHAR(30) NULL,
                vendedor_endereco TEXT NULL,
                data_entrada DATE NOT NULL,
                equipamento_tipo VARCHAR(50) NOT NULL DEFAULT 'Smartphone',
                marca_modelo VARCHAR(180) NOT NULL,
                imei1 VARCHAR(80) NULL,
                imei2 VARCHAR(80) NULL,
                senha_autorizada TINYINT(1) NOT NULL DEFAULT 0,
                chip_ssd_card TINYINT(1) NOT NULL DEFAULT 0,
                bateria TINYINT(1) NOT NULL DEFAULT 0,
                acessorios TEXT NULL,
                estado_aparelho TEXT NULL,
                valor_compra DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                comprador_nome VARCHAR(160) NOT NULL,
                comprador_contato VARCHAR(60) NULL,
                comprador_documento VARCHAR(40) NULL,
                comprador_endereco TEXT NULL,
                observacoes TEXT NULL,
                usuario_id INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_termos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
                INDEX idx_termos_data (data_entrada),
                INDEX idx_termos_vendedor (vendedor_nome),
                INDEX idx_termos_equipamento (marca_modelo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS recados (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                telefone VARCHAR(30) NULL,
                mensagem TEXT NOT NULL,
                status ENUM('Pendente', 'Lido', 'Respondido', 'Arquivado') NOT NULL DEFAULT 'Pendente',
                usuario_id INT NULL,
                data_recado DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_recados_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
                INDEX idx_recados_status (status),
                INDEX idx_recados_data (data_recado)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS pdv_vendas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                numero_venda VARCHAR(20) UNIQUE NOT NULL,
                cliente_id INT NULL,
                os_id INT NULL,
                usuario_id INT NOT NULL,
                subtotal DECIMAL(10,2) DEFAULT 0.00,
                desconto DECIMAL(10,2) DEFAULT 0.00,
                total DECIMAL(10,2) DEFAULT 0.00,
                taxa_cartao_percentual DECIMAL(5,2) DEFAULT 0.00,
                taxa_cartao_valor DECIMAL(10,2) DEFAULT 0.00,
                total_liquido DECIMAL(10,2) DEFAULT 0.00,
                forma_pagamento VARCHAR(50),
                status ENUM('aberta','finalizada','cancelada') DEFAULT 'aberta',
                observacoes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_pdv_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
                CONSTRAINT fk_pdv_os FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE SET NULL,
                CONSTRAINT fk_pdv_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS pdv_itens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                venda_id INT NOT NULL,
                produto_id INT NULL,
                descricao VARCHAR(255) NOT NULL,
                quantidade DECIMAL(10,3) NOT NULL DEFAULT 1,
                preco_unitario DECIMAL(10,2) NOT NULL,
                total DECIMAL(10,2) GENERATED ALWAYS AS (quantidade * preco_unitario) STORED,
                CONSTRAINT fk_pdv_item_venda FOREIGN KEY (venda_id) REFERENCES pdv_vendas(id) ON DELETE CASCADE,
                CONSTRAINT fk_pdv_item_produto FOREIGN KEY (produto_id) REFERENCES estoque(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS estoque_movimentacoes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                produto_id INT NOT NULL,
                tipo ENUM('entrada','saida','ajuste') NOT NULL,
                quantidade INT NOT NULL,
                motivo VARCHAR(255),
                os_id INT NULL,
                usuario_id INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_estoque_movimentacoes_produto
                    FOREIGN KEY (produto_id) REFERENCES estoque(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS estoque_imagens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                produto_id INT NOT NULL,
                imagem VARCHAR(255) NOT NULL,
                imagem_mime VARCHAR(100) NULL,
                ordem INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_estoque_imagens_produto
                    FOREIGN KEY (produto_id) REFERENCES estoque(id) ON DELETE CASCADE,
                INDEX idx_estoque_imagens_produto (produto_id, ordem, id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS contas_publicas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                whatsapp VARCHAR(20) NULL,
                senha VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS mercado_pago_pedidos (
                external_reference VARCHAR(80) PRIMARY KEY,
                preference_id VARCHAR(120) NULL,
                payment_id VARCHAR(80) NULL,
                status VARCHAR(40) NOT NULL DEFAULT 'created',
                status_detail VARCHAR(100) NULL,
                items_json MEDIUMTEXT NOT NULL,
                total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                customer_name VARCHAR(150) NULL,
                customer_email VARCHAR(150) NULL,
                email_sent_at DATETIME NULL,
                estoque_baixado_at DATETIME NULL,
                financeiro_lancado_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS mercado_livre_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                produto_id INT NULL,
                item_id VARCHAR(40) NULL,
                acao VARCHAR(60) NOT NULL,
                status VARCHAR(40) NOT NULL,
                mensagem TEXT NULL,
                payload MEDIUMTEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_mercado_livre_logs_produto
                    FOREIGN KEY (produto_id) REFERENCES estoque(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS mercado_pago_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                external_reference VARCHAR(80) NULL,
                payment_id VARCHAR(80) NULL,
                status VARCHAR(40) NULL,
                mensagem TEXT NULL,
                payload MEDIUMTEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_mp_logs_ref (external_reference)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        foreach ($statements as $sql) {
            $this->conn->exec($sql);
        }

        $this->ensureColumn('estoque', 'tipo', "ALTER TABLE estoque ADD COLUMN tipo ENUM('peca', 'produto') NOT NULL DEFAULT 'peca' AFTER nome");
        $this->ensureColumn('estoque', 'imagem', "ALTER TABLE estoque ADD COLUMN imagem VARCHAR(255) NULL AFTER localizacao");
        $this->ensureColumn('estoque', 'imagem_mime', "ALTER TABLE estoque ADD COLUMN imagem_mime VARCHAR(100) NULL AFTER imagem");
        $this->ensureColumn('estoque', 'imagem_blob', "ALTER TABLE estoque ADD COLUMN imagem_blob LONGBLOB NULL AFTER imagem_mime");
        $this->ensureColumn('estoque', 'numero_serie', "ALTER TABLE estoque ADD COLUMN numero_serie VARCHAR(100) NULL AFTER imagem");
        $this->ensureColumn('estoque', 'unidade', "ALTER TABLE estoque ADD COLUMN unidade VARCHAR(20) DEFAULT 'unid' AFTER numero_serie");
        $this->ensureColumn('usuarios', 'meta_os_mes', "ALTER TABLE usuarios ADD COLUMN meta_os_mes INT DEFAULT 30 AFTER comissao");
        $this->ensureColumn('pdv_vendas', 'taxa_cartao_percentual', "ALTER TABLE pdv_vendas ADD COLUMN taxa_cartao_percentual DECIMAL(5,2) DEFAULT 0.00 AFTER total");
        $this->ensureColumn('pdv_vendas', 'taxa_cartao_valor', "ALTER TABLE pdv_vendas ADD COLUMN taxa_cartao_valor DECIMAL(10,2) DEFAULT 0.00 AFTER taxa_cartao_percentual");
        $this->ensureColumn('pdv_vendas', 'total_liquido', "ALTER TABLE pdv_vendas ADD COLUMN total_liquido DECIMAL(10,2) DEFAULT 0.00 AFTER taxa_cartao_valor");
        $this->ensureColumn('ordens_servico', 'fotos', "ALTER TABLE ordens_servico ADD COLUMN fotos TEXT NULL AFTER termo_aceite");
        $this->ensureColumn('mercado_pago_pedidos', 'estoque_baixado_at', "ALTER TABLE mercado_pago_pedidos ADD COLUMN estoque_baixado_at DATETIME NULL AFTER email_sent_at");
        $this->ensureColumn('mercado_pago_pedidos', 'financeiro_lancado_at', "ALTER TABLE mercado_pago_pedidos ADD COLUMN financeiro_lancado_at DATETIME NULL AFTER estoque_baixado_at");
        $this->ensureColumn('estoque_movimentacoes', 'os_id', "ALTER TABLE estoque_movimentacoes ADD COLUMN os_id INT NULL AFTER motivo");
        $this->ensureColumn('estoque', 'mercado_livre_item_id', "ALTER TABLE estoque ADD COLUMN mercado_livre_item_id VARCHAR(40) NULL AFTER unidade");
        $this->ensureColumn('estoque', 'mercado_livre_permalink', "ALTER TABLE estoque ADD COLUMN mercado_livre_permalink VARCHAR(255) NULL AFTER mercado_livre_item_id");
        $this->ensureColumn('estoque', 'mercado_livre_status', "ALTER TABLE estoque ADD COLUMN mercado_livre_status VARCHAR(40) NULL AFTER mercado_livre_permalink");
        $this->ensureColumn('estoque', 'mercado_livre_category_id', "ALTER TABLE estoque ADD COLUMN mercado_livre_category_id VARCHAR(40) NULL AFTER mercado_livre_status");
        $this->ensureColumn('estoque', 'mercado_livre_listing_type_id', "ALTER TABLE estoque ADD COLUMN mercado_livre_listing_type_id VARCHAR(40) NULL AFTER mercado_livre_category_id");
        $this->ensureColumn('estoque', 'mercado_livre_condition', "ALTER TABLE estoque ADD COLUMN mercado_livre_condition VARCHAR(20) NULL AFTER mercado_livre_listing_type_id");
        $this->ensureColumn('estoque', 'mercado_livre_attributes', "ALTER TABLE estoque ADD COLUMN mercado_livre_attributes TEXT NULL AFTER mercado_livre_condition");
        $this->ensureColumn('estoque', 'mercado_livre_last_sync_at', "ALTER TABLE estoque ADD COLUMN mercado_livre_last_sync_at DATETIME NULL AFTER mercado_livre_condition");
        $this->ensureColumn('estoque', 'mercado_livre_last_error', "ALTER TABLE estoque ADD COLUMN mercado_livre_last_error TEXT NULL AFTER mercado_livre_last_sync_at");
        $this->normalizeSchemaData();
        $this->ensureIndex('estoque', 'idx_estoque_tipo', 'tipo');
        $this->ensureIndex('estoque', 'idx_estoque_ml_status', 'mercado_livre_status');
        $this->ensureIndex('mercado_livre_logs', 'idx_ml_logs_produto', 'produto_id');
        $this->ensureIndex('ordens_servico', 'idx_os_status', 'status');
        $this->ensureIndex('mercado_pago_pedidos', 'idx_mp_status', 'status');
        $this->ensureIndex('recados', 'idx_recados_status', 'status');
        $this->ensureIndex('recados', 'idx_recados_data', 'data_recado');
        $this->ensureIndex('os_pagamentos', 'idx_os_pagamentos_os', 'os_id');
        $this->ensureIndex('financeiro', 'idx_financeiro_tipo', 'tipo');
        $this->ensureIndex('financeiro', 'idx_financeiro_data_pagamento', 'data_pagamento');
        $this->ensureIndex('financeiro', 'idx_financeiro_os', 'os_id');
        $this->ensureIndex('pdv_vendas', 'idx_pdv_status', 'status');
        $this->ensureIndex('pdv_vendas', 'idx_pdv_created_at', 'created_at');
        $this->ensureIndex('pdv_vendas', 'idx_pdv_cliente', 'cliente_id');
        $this->ensureIndex('pdv_vendas', 'idx_pdv_os', 'os_id');
        $this->ensureIndex('estoque', 'idx_estoque_categoria', 'categoria');
        $this->ensureIndex('termos_compra_venda', 'idx_termos_data', 'data_entrada');
        $this->ensureIndex('termos_compra_venda', 'idx_termos_vendedor', 'vendedor_nome');
        $this->ensureIndex('termos_compra_venda', 'idx_termos_equipamento', 'marca_modelo');

        $this->seedPhoneModels();
        $this->seedAdminFromEnv();
        $this->syncApprovedMercadoPagoFinanceiro();

        $defaultConfigs = [
            'nome_empresa',
            'whatsapp',
            'endereco',
            'website',
            'email_negocio',
            'instagram',
            'facebook',
            'tiktok',
            'linkedin',
            'vitrine_hero_kicker',
            'vitrine_hero_titulo',
            'vitrine_hero_texto',
            'vitrine_offer_1_texto',
            'vitrine_offer_1_icone',
            'vitrine_offer_2_texto',
            'vitrine_offer_2_icone',
            'vitrine_offer_3_texto',
            'vitrine_offer_3_icone',
            'vitrine_offer_4_texto',
            'vitrine_offer_4_icone',
            'vitrine_offer_5_texto',
            'vitrine_offer_5_icone',
            'vitrine_offer_6_texto',
            'vitrine_offer_6_icone',
            'vitrine_benefit_1_titulo',
            'vitrine_benefit_1_texto',
            'vitrine_benefit_2_titulo',
            'vitrine_benefit_2_texto',
            'vitrine_benefit_3_titulo',
            'vitrine_benefit_3_texto',
            'vitrine_benefit_4_titulo',
            'vitrine_benefit_4_texto',
            'vitrine_categorias_texto',
            'vitrine_lancamentos_titulo',
            'vitrine_lancamentos_texto',
            'vitrine_mais_procurados_titulo',
            'vitrine_mais_procurados_texto',
            'vitrine_servicos_titulo',
            'vitrine_servicos_texto',
            'vitrine_servico_1_titulo',
            'vitrine_servico_1_texto',
            'vitrine_servico_2_titulo',
            'vitrine_servico_2_texto',
            'vitrine_servico_3_titulo',
            'vitrine_servico_3_texto',
            'vitrine_servico_4_titulo',
            'vitrine_servico_4_texto',
            'vitrine_depoimentos_titulo',
            'vitrine_depoimentos_texto',
            'vitrine_depoimento_1_nome',
            'vitrine_depoimento_1_texto',
            'vitrine_depoimento_2_nome',
            'vitrine_depoimento_2_texto',
            'vitrine_depoimento_3_nome',
            'vitrine_depoimento_3_texto',
            'vitrine_localizacao_titulo',
            'vitrine_localizacao_texto',
            'vitrine_localizacao_descricao',
            'vitrine_mapa_embed_url',
            'vitrine_localizacao_mapa_titulo',
            'vitrine_localizacao_mapa_texto',
            'vitrine_cta_texto',
            'mercadopago_public_key',
            'mercadopago_access_token',
            'mercado_livre_client_id',
            'mercado_livre_client_secret',
            'mercado_livre_access_token',
            'mercado_livre_refresh_token',
            'mercado_livre_token_expires_at',
            'mercado_livre_user_id',
            'mercado_livre_nickname',
            'mercado_livre_default_category_id',
            'mercado_livre_default_listing_type_id',
            'mercado_livre_default_condition',
            'mercado_livre_default_shipping_mode',
            'chave_pix',
            'taxa_cartao_debito',
            'taxa_cartao_credito',
        ];

        $stmt = $this->conn->prepare("INSERT IGNORE INTO configuracoes (chave, valor) VALUES (:chave, '')");
        foreach ($defaultConfigs as $chave) {
            $stmt->execute([':chave' => $chave]);
        }
    }

    private function seedAdminFromEnv(): void
    {
        $email = trim((string) \app_env('ADMIN_EMAIL', ''));
        $password = (string) \app_env('ADMIN_PASSWORD', '');
        $name = trim((string) \app_env('ADMIN_NAME', 'Admin Conectados')) ?: 'Admin Conectados';

        if ($email === '' || $password === '') {
            return;
        }

        $stmt = $this->conn->prepare("INSERT IGNORE INTO usuarios (nome, email, senha, perfil, status)
            VALUES (:nome, :email, :senha, 'Administrador', 'Ativo')");
        $stmt->execute([
            ':nome' => $name,
            ':email' => $email,
            ':senha' => password_hash($password, PASSWORD_BCRYPT),
        ]);
    }

    private function seedPhoneModels(): void
    {
        $modelsByBrand = [
            'Apple' => [
                'iPhone 7', 'iPhone 7 Plus', 'iPhone 8', 'iPhone 8 Plus', 'iPhone X', 'iPhone XR',
                'iPhone XS', 'iPhone XS Max', 'iPhone 11', 'iPhone 11 Pro', 'iPhone 11 Pro Max',
                'iPhone 12', 'iPhone 12 Mini', 'iPhone 12 Pro', 'iPhone 12 Pro Max',
                'iPhone 13', 'iPhone 13 Mini', 'iPhone 13 Pro', 'iPhone 13 Pro Max',
                'iPhone 14', 'iPhone 14 Plus', 'iPhone 14 Pro', 'iPhone 14 Pro Max',
                'iPhone 15', 'iPhone 15 Plus', 'iPhone 15 Pro', 'iPhone 15 Pro Max',
            ],
            'Samsung' => [
                'Galaxy Core', 'Galaxy Core 2', 'Galaxy Core Prime', 'Galaxy Grand Prime',
                'Galaxy Grand Prime Duos', 'Galaxy Gran Prime', 'Galaxy J1', 'Galaxy J1 Ace',
                'Galaxy J1 Mini', 'Galaxy J1 Mini Prime', 'Galaxy J2', 'Galaxy J2 Core',
                'Galaxy J2 Prime', 'Galaxy J2 Pro', 'Galaxy J3', 'Galaxy J3 Prime',
                'Galaxy J4', 'Galaxy J4 Core', 'Galaxy J4 Plus', 'Galaxy J5',
                'Galaxy J5 Metal', 'Galaxy J5 Prime', 'Galaxy J5 Pro', 'Galaxy J6',
                'Galaxy J6 Plus', 'Galaxy J7', 'Galaxy J7 Duo', 'Galaxy J7 Metal',
                'Galaxy J7 Neo', 'Galaxy J7 Prime', 'Galaxy J7 Prime 2', 'Galaxy J7 Pro',
                'Galaxy J8', 'Galaxy On5', 'Galaxy On7',
                'Galaxy A3', 'Galaxy A5', 'Galaxy A6', 'Galaxy A6 Plus', 'Galaxy A7',
                'Galaxy A8', 'Galaxy A8 Plus', 'Galaxy A9', 'Galaxy A9 Pro',
                'Galaxy A01', 'Galaxy A02', 'Galaxy A02s', 'Galaxy A03', 'Galaxy A03 Core',
                'Galaxy A03s', 'Galaxy A04', 'Galaxy A04e', 'Galaxy A04s', 'Galaxy A05',
                'Galaxy A05s', 'Galaxy A06', 'Galaxy A06 5G', 'Galaxy A10', 'Galaxy A10s',
                'Galaxy A11', 'Galaxy A12', 'Galaxy A13', 'Galaxy A14', 'Galaxy A14 5G',
                'Galaxy A15', 'Galaxy A15 5G', 'Galaxy A16', 'Galaxy A16 5G', 'Galaxy A20',
                'Galaxy A20s', 'Galaxy A21s', 'Galaxy A22', 'Galaxy A22 5G', 'Galaxy A23',
                'Galaxy A23 5G', 'Galaxy A24', 'Galaxy A25', 'Galaxy A26 5G', 'Galaxy A30',
                'Galaxy A30s', 'Galaxy A31', 'Galaxy A32', 'Galaxy A32 5G', 'Galaxy A33',
                'Galaxy A34', 'Galaxy A35', 'Galaxy A36 5G', 'Galaxy A50', 'Galaxy A50s',
                'Galaxy A51', 'Galaxy A52', 'Galaxy A52s', 'Galaxy A53', 'Galaxy A54',
                'Galaxy A55', 'Galaxy A56 5G', 'Galaxy A70', 'Galaxy A71', 'Galaxy A72',
                'Galaxy A73', 'Galaxy A80', 'Galaxy M12', 'Galaxy M13', 'Galaxy M14',
                'Galaxy M15', 'Galaxy M21', 'Galaxy M22', 'Galaxy M23', 'Galaxy M31',
                'Galaxy M31s', 'Galaxy M32', 'Galaxy M33', 'Galaxy M34', 'Galaxy M35',
                'Galaxy M51', 'Galaxy M52', 'Galaxy M53', 'Galaxy M54', 'Galaxy M55',
                'Galaxy F12', 'Galaxy F13', 'Galaxy F14', 'Galaxy F23', 'Galaxy F34',
                'Galaxy S6', 'Galaxy S6 Edge', 'Galaxy S6 Edge Plus', 'Galaxy S7',
                'Galaxy S7 Edge', 'Galaxy S8', 'Galaxy S8 Plus',
                'Galaxy S9', 'Galaxy S9 Plus', 'Galaxy S10', 'Galaxy S10 Plus', 'Galaxy S10e',
                'Galaxy S20', 'Galaxy S20 Plus', 'Galaxy S20 Ultra', 'Galaxy S20 FE',
                'Galaxy S21', 'Galaxy S21 Plus', 'Galaxy S21 Ultra', 'Galaxy S21 FE',
                'Galaxy S22', 'Galaxy S22 Plus', 'Galaxy S22 Ultra', 'Galaxy S23',
                'Galaxy S23 Plus', 'Galaxy S23 Ultra', 'Galaxy S23 FE', 'Galaxy S24',
                'Galaxy S24 Plus', 'Galaxy S24 Ultra', 'Galaxy S24 FE', 'Galaxy S25',
                'Galaxy S25 Plus', 'Galaxy S25 Ultra', 'Galaxy Note 8', 'Galaxy Note 9',
                'Galaxy Note 10', 'Galaxy Note 10 Plus', 'Galaxy Note 20', 'Galaxy Note 20 Ultra',
                'Galaxy Z Flip', 'Galaxy Z Flip 3', 'Galaxy Z Flip 4', 'Galaxy Z Flip 5',
                'Galaxy Z Flip 6', 'Galaxy Z Fold 2', 'Galaxy Z Fold 3', 'Galaxy Z Fold 4',
                'Galaxy Z Fold 5', 'Galaxy Z Fold 6',
            ],
            'Motorola' => [
                'Moto C', 'Moto C Plus', 'Moto E', 'Moto E2', 'Moto E3', 'Moto E3 Power',
                'Moto E4', 'Moto E4 Plus', 'Moto E4 Play',
                'Moto E5', 'Moto E5 Plus', 'Moto E6', 'Moto E6 Play', 'Moto E6 Plus',
                'Moto E5 Play', 'Moto E6i', 'Moto E6s', 'Moto E7', 'Moto E7 Plus',
                'Moto E7 Power', 'Moto E13',
                'Moto E14', 'Moto E20', 'Moto E22', 'Moto E22i', 'Moto E30', 'Moto E32',
                'Moto E32s', 'Moto E40', 'Moto G', 'Moto G2', 'Moto G3',
                'Moto G4', 'Moto G4 Plus', 'Moto G4 Play', 'Moto G5', 'Moto G5 Plus', 'Moto G5s',
                'Moto G5s Plus', 'Moto G6', 'Moto G6 Plus', 'Moto G6 Play', 'Moto G7',
                'Moto G7 Plus', 'Moto G7 Play', 'Moto G7 Power', 'Moto G8',
                'Moto G8 Plus', 'Moto G8 Play', 'Moto G8 Power', 'Moto G9',
                'Moto G9 Plus', 'Moto G9 Play', 'Moto G9 Power', 'Moto G10',
                'Moto G10 Power', 'Moto G14', 'Moto G15', 'Moto G20', 'Moto G22',
                'Moto G23', 'Moto G24', 'Moto G24 Power', 'Moto G30', 'Moto G31',
                'Moto G32', 'Moto G34 5G', 'Moto G35 5G', 'Moto G41', 'Moto G42',
                'Moto G50', 'Moto G51', 'Moto G52', 'Moto G53', 'Moto G54',
                'Moto G55', 'Moto G60', 'Moto G62', 'Moto G71', 'Moto G72',
                'Moto G73', 'Moto G82', 'Moto G84', 'Moto G85', 'Moto G100',
                'Moto G200', 'Moto X4', 'Moto Z', 'Moto Z Play', 'Moto Z2 Force',
                'Moto Z2 Play', 'Moto Z3 Play', 'Motorola One', 'Motorola One Action',
                'Motorola One Fusion', 'Motorola One Fusion Plus', 'Motorola One Hyper',
                'Motorola One Macro', 'Motorola One Vision', 'Motorola One Zoom',
                'Motorola Edge', 'Motorola Edge Plus', 'Motorola Edge 20', 'Motorola Edge 20 Lite',
                'Motorola Edge 20 Pro', 'Motorola Edge 30', 'Motorola Edge 30 Fusion',
                'Motorola Edge 30 Neo', 'Motorola Edge 30 Pro', 'Motorola Edge 30 Ultra',
                'Motorola Edge 40', 'Motorola Edge 40 Neo', 'Motorola Edge 40 Pro',
                'Motorola Edge 50', 'Motorola Edge 50 Fusion', 'Motorola Edge 50 Neo',
                'Motorola Edge 50 Pro', 'Motorola Edge 50 Ultra',
                'Motorola Edge 60', 'Motorola Edge 60 Fusion', 'Motorola Edge 60 Neo',
                'Motorola Edge 60 Pro', 'Motorola Razr 40', 'Motorola Razr 40 Ultra',
                'Motorola Razr 50', 'Motorola Razr 50 Ultra',
            ],
            'Xiaomi' => [
                'Redmi 4', 'Redmi 4A', 'Redmi 4X', 'Redmi 5', 'Redmi 5 Plus',
                'Redmi 5A', 'Redmi 6', 'Redmi 6 Pro', 'Redmi 6A', 'Redmi 7',
                'Redmi 7A', 'Redmi 8', 'Redmi 8A', 'Redmi 8A Pro', 'Redmi 9',
                'Redmi 9 Prime', 'Redmi 9A', 'Redmi 9C', 'Redmi 9T',
                'Redmi 10', 'Redmi 10A', 'Redmi 10C', 'Redmi 11 Prime', 'Redmi 12',
                'Redmi 12 5G', 'Redmi 12C', 'Redmi 13', 'Redmi 13C', 'Redmi 14C',
                'Redmi Go', 'Redmi S2', 'Redmi Note 4', 'Redmi Note 4X', 'Redmi Note 5',
                'Redmi Note 5 Pro', 'Redmi Note 6 Pro', 'Redmi Note 7', 'Redmi Note 7 Pro',
                'Redmi Note 8', 'Redmi Note 8 Pro', 'Redmi Note 9', 'Redmi Note 9 Pro',
                'Redmi Note 9S', 'Redmi Note 10', 'Redmi Note 10 5G', 'Redmi Note 10 Pro',
                'Redmi Note 10S', 'Redmi Note 11', 'Redmi Note 11 Pro',
                'Redmi Note 11 Pro 5G', 'Redmi Note 11S', 'Redmi Note 12',
                'Redmi Note 12 5G', 'Redmi Note 12 Pro', 'Redmi Note 12 Pro 5G',
                'Redmi Note 12 Pro Plus 5G', 'Redmi Note 12S', 'Redmi Note 13',
                'Redmi Note 13 5G', 'Redmi Note 13 Pro', 'Redmi Note 13 Pro 5G',
                'Redmi Note 13 Pro Plus 5G', 'Redmi Note 14', 'Redmi Note 14 5G',
                'Redmi Note 14 Pro 5G', 'Redmi Note 14 Pro Plus 5G', 'Pocophone F1',
                'Poco C3', 'Poco C31', 'Poco C40',
                'Poco C65', 'Poco C75', 'Poco F2 Pro', 'Poco F3', 'Poco F4', 'Poco F5',
                'Poco F5 Pro', 'Poco F6', 'Poco F6 Pro', 'Poco M3', 'Poco M4 Pro',
                'Poco M3 Pro 5G', 'Poco M4 Pro 5G', 'Poco M5', 'Poco M5s',
                'Poco M6', 'Poco M6 Pro', 'Poco X2', 'Poco X3',
                'Poco X3 NFC', 'Poco X3 Pro', 'Poco X4 Pro', 'Poco X5', 'Poco X5 Pro',
                'Poco X6', 'Poco X6 Pro', 'Poco X7', 'Poco X7 Pro',
                'Mi A1', 'Mi A2', 'Mi A2 Lite', 'Mi A3', 'Mi 6', 'Mi 8',
                'Mi 8 Lite', 'Mi 9', 'Mi 9 Lite', 'Mi 9 SE', 'Mi 9T', 'Mi 9T Pro',
                'Mi 10', 'Mi 10 Lite', 'Mi 10T', 'Mi 10T Lite', 'Mi 10T Pro',
                'Mi 11', 'Mi 11 Lite', 'Mi 11T', 'Mi 11T Pro', 'Xiaomi 11 Lite 5G NE',
                'Xiaomi 12', 'Xiaomi 12 Lite', 'Xiaomi 12T', 'Xiaomi 12T Pro',
                'Xiaomi 13', 'Xiaomi 13 Lite', 'Xiaomi 13T', 'Xiaomi 13T Pro',
                'Xiaomi 14', 'Xiaomi 14T', 'Xiaomi 14T Pro', 'Xiaomi 15',
            ],
            'LG' => ['K10', 'K11', 'K12', 'K22', 'K41S', 'K50S', 'K51S', 'K61', 'Q6', 'Q7', 'Velvet'],
            'Asus' => ['Zenfone 4', 'Zenfone 5', 'Zenfone 6', 'Zenfone 8', 'Zenfone 9', 'ROG Phone 5', 'ROG Phone 6'],
            'Realme' => ['Realme C11', 'Realme C21Y', 'Realme C25Y', 'Realme C35', 'Realme 7', 'Realme 8', 'Realme 9', 'Realme 10'],
            'Huawei' => ['P20 Lite', 'P30 Lite', 'P40 Lite', 'Y6', 'Y7', 'Y9', 'Nova 5T'],
            'Nokia' => ['Nokia 2.4', 'Nokia 3.4', 'Nokia 5.4', 'Nokia G10', 'Nokia G20', 'Nokia C20'],
            'Positivo' => ['Twist 3', 'Twist 4', 'Twist 5', 'Twist Tab'],
            'Multilaser' => ['MS50', 'MS60', 'G Max', 'G Pro'],
        ];

        $stmt = $this->conn->prepare("INSERT IGNORE INTO aparelho_modelos (marca, modelo) VALUES (:marca, :modelo)");
        foreach ($modelsByBrand as $brand => $models) {
            foreach ($models as $model) {
                $stmt->execute([
                    ':marca' => $brand,
                    ':modelo' => $model,
                ]);
            }
        }
    }

    private function ensureColumn(string $table, string $column, string $alterSql): void
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
            return;
        }

        $stmt = $this->conn->query("SHOW TABLES LIKE " . $this->conn->quote($table));
        if (!$stmt || !$stmt->fetchColumn()) {
            return;
        }

        $stmt = $this->conn->query("SHOW COLUMNS FROM `$table` LIKE " . $this->conn->quote($column));
        if ($stmt && $stmt->fetchColumn()) {
            return;
        }

        $this->conn->exec($alterSql);
    }

    private function ensureIndex(string $table, string $index, string $column): void
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table . $index . $column)) {
            return;
        }

        $stmt = $this->conn->query("SHOW INDEX FROM `$table` WHERE Key_name = " . $this->conn->quote($index));
        if ($stmt && $stmt->fetchColumn()) {
            return;
        }

        $this->conn->exec("ALTER TABLE `$table` ADD INDEX `$index` (`$column`)");
    }

    private function normalizeSchemaData(): void
    {
        try {
            $this->conn->exec("ALTER TABLE estoque MODIFY localizacao TEXT NULL");
        } catch (\Throwable $e) {
            error_log('Nao foi possivel ajustar descricao/localizacao do estoque: ' . $e->getMessage());
        }

        try {
            $this->conn->exec("ALTER TABLE ordens_servico MODIFY status VARCHAR(50) NOT NULL DEFAULT 'Recebido'");
        } catch (\Throwable $e) {
            error_log('Nao foi possivel ajustar status da OS: ' . $e->getMessage());
        }

        $updates = [
            "UPDATE ordens_servico SET status = 'Em análise' WHERE status IN ('Em analise','Em anÃ¡lise','Em anÃƒÂ¡lise')",
            "UPDATE ordens_servico SET status = 'Aguardando aprovação' WHERE status IN ('Aguardando aprovacao','Aguardando aprovaÃ§Ã£o','Aguardando aprovaÃƒÂ§ÃƒÂ£o')",
            "UPDATE ordens_servico SET status = 'Aguardando peça' WHERE status IN ('Aguardando peca','Aguardando peÃ§a','Aguardando peÃƒÂ§a')",
        ];

        foreach ($updates as $sql) {
            try {
                $this->conn->exec($sql);
            } catch (\Throwable $e) {
                error_log('Nao foi possivel normalizar status da OS: ' . $e->getMessage());
            }
        }
    }

    private function syncApprovedMercadoPagoFinanceiro(): void
    {
        try {
            $this->conn->exec("
                INSERT INTO financeiro (tipo, categoria, descricao, valor, os_id, usuario_id, data_pagamento, forma_pagamento)
                SELECT
                    'Receita',
                    'Venda Site (Mercado Pago)',
                    CONCAT('Venda site Mercado Pago ', mp.external_reference,
                        CASE
                            WHEN COALESCE(mp.payment_id, '') <> '' THEN CONCAT(' - Pagamento ', mp.payment_id)
                            ELSE ''
                        END
                    ),
                    mp.total,
                    NULL,
                    NULL,
                    DATE(COALESCE(mp.updated_at, mp.created_at, NOW())),
                    'Mercado Pago'
                FROM mercado_pago_pedidos mp
                WHERE mp.status = 'approved'
                  AND mp.total > 0
                  AND mp.financeiro_lancado_at IS NULL
                  AND NOT EXISTS (
                      SELECT 1
                      FROM financeiro f
                      WHERE f.tipo = 'Receita'
                        AND f.categoria = 'Venda Site (Mercado Pago)'
                        AND f.descricao LIKE CONCAT('%', mp.external_reference, '%')
                  )
            ");

            $this->conn->exec("
                UPDATE mercado_pago_pedidos mp
                SET mp.financeiro_lancado_at = NOW()
                WHERE mp.status = 'approved'
                  AND mp.financeiro_lancado_at IS NULL
                  AND EXISTS (
                      SELECT 1
                      FROM financeiro f
                      WHERE f.tipo = 'Receita'
                        AND f.categoria = 'Venda Site (Mercado Pago)'
                        AND f.descricao LIKE CONCAT('%', mp.external_reference, '%')
                  )
            ");
        } catch (\Throwable $e) {
            error_log('Nao foi possivel sincronizar vendas Mercado Pago no financeiro: ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->getConnection();
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
