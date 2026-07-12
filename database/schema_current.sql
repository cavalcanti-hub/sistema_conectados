SET FOREIGN_KEY_CHECKS=0;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aparelho_modelos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marca` varchar(80) NOT NULL,
  `modelo` varchar(140) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_aparelho_modelos_marca_modelo` (`marca`,`modelo`),
  KEY `idx_aparelho_modelos_marca` (`marca`)
) ENGINE=InnoDB AUTO_INCREMENT=9682 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `tipo` enum('peca','produto') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categorias_nome_tipo` (`nome`,`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `cpf_cnpj` varchar(20) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf_cnpj` (`cpf_cnpj`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracoes` (
  `chave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contas_publicas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estoque` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_interno` varchar(50) DEFAULT NULL,
  `nome` varchar(150) NOT NULL,
  `tipo` enum('peca','produto') NOT NULL DEFAULT 'peca',
  `categoria` varchar(50) DEFAULT NULL,
  `marca_compativel` varchar(50) DEFAULT NULL,
  `modelo_compativel` varchar(100) DEFAULT NULL,
  `quantidade` int(11) DEFAULT 0,
  `estoque_minimo` int(11) DEFAULT 5,
  `custo` decimal(10,2) DEFAULT NULL,
  `preco_venda` decimal(10,2) DEFAULT NULL,
  `fornecedor` varchar(100) DEFAULT NULL,
  `localizacao` text DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `imagem_mime` varchar(100) DEFAULT NULL,
  `imagem_blob` longblob DEFAULT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `unidade` varchar(20) DEFAULT 'unid',
  `mercado_livre_item_id` varchar(40) DEFAULT NULL,
  `mercado_livre_permalink` varchar(255) DEFAULT NULL,
  `mercado_livre_status` varchar(40) DEFAULT NULL,
  `mercado_livre_category_id` varchar(40) DEFAULT NULL,
  `mercado_livre_listing_type_id` varchar(40) DEFAULT NULL,
  `mercado_livre_condition` varchar(20) DEFAULT NULL,
  `mercado_livre_last_sync_at` datetime DEFAULT NULL,
  `mercado_livre_last_error` text DEFAULT NULL,
  `mercado_livre_attributes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_interno` (`codigo_interno`),
  KEY `idx_estoque_tipo` (`tipo`),
  KEY `idx_estoque_ml_status` (`mercado_livre_status`),
  KEY `idx_estoque_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(160) NOT NULL,
  `documento` varchar(40) DEFAULT NULL,
  `telefone` varchar(40) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fornecedores_nome` (`nome`),
  KEY `idx_fornecedores_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gastos_pessoais_categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `tipo` enum('Receita','Despesa','Ambos') NOT NULL DEFAULT 'Despesa',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_gastos_pessoais_categoria` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mercado_pago_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_reference` varchar(80) DEFAULT NULL,
  `payment_id` varchar(80) DEFAULT NULL,
  `status` varchar(40) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `payload` mediumtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mp_logs_ref` (`external_reference`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mercado_pago_pedidos` (
  `external_reference` varchar(80) NOT NULL,
  `preference_id` varchar(120) DEFAULT NULL,
  `payment_id` varchar(80) DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'created',
  `status_detail` varchar(100) DEFAULT NULL,
  `items_json` mediumtext NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `customer_name` varchar(150) DEFAULT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `email_sent_at` datetime DEFAULT NULL,
  `estoque_baixado_at` datetime DEFAULT NULL,
  `financeiro_lancado_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`external_reference`),
  KEY `idx_mp_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pdv_caixas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_caixa` date NOT NULL,
  `status` enum('aberto','fechado') NOT NULL DEFAULT 'aberto',
  `abertura_tipo` enum('automatica','manual') NOT NULL DEFAULT 'automatica',
  `fechamento_tipo` enum('automatico','manual') DEFAULT NULL,
  `aberto_por` int(11) DEFAULT NULL,
  `fechado_por` int(11) DEFAULT NULL,
  `aberto_em` datetime NOT NULL,
  `fechado_em` datetime DEFAULT NULL,
  `valor_inicial` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_suprimento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_sangria` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_informado` decimal(10,2) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pdv_caixas_data` (`data_caixa`),
  KEY `idx_pdv_caixas_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servicos_referencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `preco_sugerido` decimal(10,2) DEFAULT NULL,
  `tempo_medio` varchar(50) DEFAULT NULL,
  `garantia_padrao` int(11) DEFAULT 90,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` varchar(50) NOT NULL,
  `especialidade` varchar(100) DEFAULT NULL,
  `comissao` decimal(5,2) DEFAULT 0.00,
  `meta_os_mes` int(11) DEFAULT 30,
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aparelhos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `imei` varchar(50) DEFAULT NULL,
  `numero_serie` varchar(50) DEFAULT NULL,
  `cor` varchar(30) DEFAULT NULL,
  `senha_padrao` varchar(512) DEFAULT NULL,
  `estado_fisico` text DEFAULT NULL,
  `acessorios` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_aparelhos_cliente` (`cliente_id`),
  CONSTRAINT `fk_aparelhos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auditoria_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `usuario_nome` varchar(120) DEFAULT NULL,
  `acao` varchar(80) NOT NULL,
  `entidade` varchar(80) NOT NULL,
  `entidade_id` int(11) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `dados` longtext DEFAULT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_auditoria_logs_created_at` (`created_at`),
  KEY `idx_auditoria_logs_entidade` (`entidade`,`entidade_id`),
  KEY `idx_auditoria_logs_usuario` (`usuario_id`),
  CONSTRAINT `fk_auditoria_logs_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compras_solicitacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_nome` varchar(180) NOT NULL,
  `tipo` enum('peca','produto') NOT NULL DEFAULT 'peca',
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `fornecedor` varchar(150) DEFAULT NULL,
  `prioridade` enum('Baixa','Normal','Alta','Urgente') NOT NULL DEFAULT 'Normal',
  `status` enum('Pendente','Solicitado','Comprado','Recebido','Cancelado') NOT NULL DEFAULT 'Pendente',
  `observacoes` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_solicitacao` date NOT NULL,
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_compras_usuario` (`usuario_id`),
  KEY `idx_compras_status` (`status`),
  KEY `idx_compras_tipo` (`tipo`),
  KEY `idx_compras_data` (`data_solicitacao`),
  CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estoque_imagens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `imagem` varchar(255) NOT NULL,
  `imagem_mime` varchar(100) DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_estoque_imagens_produto` (`produto_id`,`ordem`,`id`),
  CONSTRAINT `fk_estoque_imagens_produto` FOREIGN KEY (`produto_id`) REFERENCES `estoque` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estoque_movimentacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `tipo` enum('entrada','saida','ajuste') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `os_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_estoque_movimentacoes_produto` (`produto_id`),
  CONSTRAINT `fk_estoque_movimentacoes_produto` FOREIGN KEY (`produto_id`) REFERENCES `estoque` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gastos_pessoais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('Receita','Despesa') NOT NULL DEFAULT 'Despesa',
  `categoria` varchar(100) DEFAULT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_lancamento` date NOT NULL,
  `forma_pagamento` varchar(60) DEFAULT NULL,
  `recorrente` tinyint(1) NOT NULL DEFAULT 0,
  `observacoes` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_gastos_pessoais_usuario` (`usuario_id`),
  KEY `idx_gastos_pessoais_data` (`data_lancamento`),
  KEY `idx_gastos_pessoais_tipo` (`tipo`),
  CONSTRAINT `fk_gastos_pessoais_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mercado_livre_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) DEFAULT NULL,
  `item_id` varchar(40) DEFAULT NULL,
  `acao` varchar(60) NOT NULL,
  `status` varchar(40) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `payload` mediumtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ml_logs_produto` (`produto_id`),
  CONSTRAINT `fk_mercado_livre_logs_produto` FOREIGN KEY (`produto_id`) REFERENCES `estoque` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ordens_servico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_os` varchar(20) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `aparelho_id` int(11) NOT NULL,
  `tecnico_id` int(11) DEFAULT NULL,
  `problema_relatado` text DEFAULT NULL,
  `diagnostico_tecnico` text DEFAULT NULL,
  `servico_realizar` text DEFAULT NULL,
  `prioridade` enum('Baixa','Normal','Alta','Urgente') DEFAULT 'Normal',
  `status` varchar(50) NOT NULL DEFAULT 'Recebido',
  `prazo_estimado` date DEFAULT NULL,
  `valor_mao_obra` decimal(10,2) DEFAULT 0.00,
  `valor_pecas` decimal(10,2) DEFAULT 0.00,
  `desconto` decimal(10,2) DEFAULT 0.00,
  `valor_total` decimal(10,2) GENERATED ALWAYS AS (`valor_mao_obra` + `valor_pecas` - `desconto`) STORED,
  `forma_pagamento` varchar(50) DEFAULT NULL,
  `situacao_pagamento` enum('Pendente','Parcial','Pago') DEFAULT 'Pendente',
  `garantia_expira` date DEFAULT NULL,
  `termo_aceite` tinyint(1) DEFAULT 0,
  `fotos` text DEFAULT NULL,
  `fotos_saida` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_os` (`numero_os`),
  KEY `fk_os_cliente` (`cliente_id`),
  KEY `fk_os_aparelho` (`aparelho_id`),
  KEY `fk_os_tecnico` (`tecnico_id`),
  KEY `idx_os_status` (`status`),
  CONSTRAINT `fk_os_aparelho` FOREIGN KEY (`aparelho_id`) REFERENCES `aparelhos` (`id`),
  CONSTRAINT `fk_os_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `fk_os_tecnico` FOREIGN KEY (`tecnico_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financeiro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('Receita','Despesa') NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `os_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_pagamento` date DEFAULT NULL,
  `forma_pagamento` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_financeiro_usuario` (`usuario_id`),
  KEY `idx_financeiro_tipo` (`tipo`),
  KEY `idx_financeiro_data_pagamento` (`data_pagamento`),
  KEY `idx_financeiro_os` (`os_id`),
  CONSTRAINT `fk_financeiro_os` FOREIGN KEY (`os_id`) REFERENCES `ordens_servico` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_financeiro_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `status` enum('Pendente','Lido','Respondido','Arquivado') NOT NULL DEFAULT 'Pendente',
  `usuario_id` int(11) DEFAULT NULL,
  `data_recado` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_recados_usuario` (`usuario_id`),
  KEY `idx_recados_status` (`status`),
  KEY `idx_recados_data` (`data_recado`),
  CONSTRAINT `fk_recados_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `termos_compra_venda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_termo` varchar(40) NOT NULL,
  `vendedor_nome` varchar(160) NOT NULL,
  `vendedor_contato` varchar(60) DEFAULT NULL,
  `vendedor_cpf` varchar(30) DEFAULT NULL,
  `vendedor_rg` varchar(30) DEFAULT NULL,
  `vendedor_endereco` text DEFAULT NULL,
  `data_entrada` date NOT NULL,
  `equipamento_tipo` varchar(50) NOT NULL DEFAULT 'Smartphone',
  `marca_modelo` varchar(180) NOT NULL,
  `imei1` varchar(80) DEFAULT NULL,
  `imei2` varchar(80) DEFAULT NULL,
  `senha_autorizada` tinyint(1) NOT NULL DEFAULT 0,
  `chip_ssd_card` tinyint(1) NOT NULL DEFAULT 0,
  `bateria` tinyint(1) NOT NULL DEFAULT 0,
  `acessorios` text DEFAULT NULL,
  `estado_aparelho` text DEFAULT NULL,
  `valor_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `comprador_nome` varchar(160) NOT NULL,
  `comprador_contato` varchar(60) DEFAULT NULL,
  `comprador_documento` varchar(40) DEFAULT NULL,
  `comprador_endereco` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_termo` (`numero_termo`),
  KEY `fk_termos_usuario` (`usuario_id`),
  KEY `idx_termos_data` (`data_entrada`),
  KEY `idx_termos_vendedor` (`vendedor_nome`),
  KEY `idx_termos_equipamento` (`marca_modelo`),
  CONSTRAINT `fk_termos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compras_notas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fornecedor_id` int(11) DEFAULT NULL,
  `os_id` int(11) DEFAULT NULL,
  `numero` varchar(80) DEFAULT NULL,
  `data_emissao` date NOT NULL,
  `data_vencimento` date DEFAULT NULL,
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `forma_pagamento` varchar(60) DEFAULT NULL,
  `status` enum('Aberta','Baixada','Cancelada') NOT NULL DEFAULT 'Aberta',
  `observacoes` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `baixado_at` datetime DEFAULT NULL,
  `financeiro_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_compras_notas_fornecedor` (`fornecedor_id`),
  KEY `fk_compras_notas_usuario` (`usuario_id`),
  KEY `fk_compras_notas_financeiro` (`financeiro_id`),
  KEY `idx_compras_notas_status` (`status`),
  KEY `idx_compras_notas_data` (`data_emissao`),
  KEY `idx_compras_notas_os` (`os_id`),
  CONSTRAINT `fk_compras_notas_financeiro` FOREIGN KEY (`financeiro_id`) REFERENCES `financeiro` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_compras_notas_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_compras_notas_os` FOREIGN KEY (`os_id`) REFERENCES `ordens_servico` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_compras_notas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mercado_pago_point_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `os_id` int(11) DEFAULT NULL,
  `pdv_venda_id` int(11) DEFAULT NULL,
  `numero_os` varchar(20) DEFAULT NULL,
  `external_reference` varchar(80) NOT NULL,
  `mp_order_id` varchar(80) DEFAULT NULL,
  `payment_id` varchar(80) DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'created',
  `status_detail` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(80) DEFAULT NULL,
  `terminal_id` varchar(120) DEFAULT NULL,
  `payload` mediumtext DEFAULT NULL,
  `financeiro_lancado_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_reference` (`external_reference`),
  UNIQUE KEY `mp_order_id` (`mp_order_id`),
  KEY `idx_mp_point_orders_os` (`os_id`),
  KEY `idx_mp_point_orders_status` (`status`),
  KEY `idx_mp_point_orders_pdv` (`pdv_venda_id`),
  CONSTRAINT `fk_mp_point_orders_os` FOREIGN KEY (`os_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `os_historico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `os_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `status_anterior` varchar(50) DEFAULT NULL,
  `status_novo` varchar(50) DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_historico_os` (`os_id`),
  KEY `fk_historico_usuario` (`usuario_id`),
  CONSTRAINT `fk_historico_os` FOREIGN KEY (`os_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_historico_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `os_pagamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `os_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `forma_pagamento` varchar(50) DEFAULT NULL,
  `data_pagamento` date NOT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_os_pagamentos_usuario` (`usuario_id`),
  KEY `idx_os_pagamentos_os` (`os_id`,`data_pagamento`,`id`),
  CONSTRAINT `fk_os_pagamentos_os` FOREIGN KEY (`os_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_os_pagamentos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pdv_vendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_venda` varchar(20) NOT NULL,
  `caixa_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `os_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `desconto` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT 0.00,
  `taxa_cartao_percentual` decimal(5,2) DEFAULT 0.00,
  `taxa_cartao_valor` decimal(10,2) DEFAULT 0.00,
  `total_liquido` decimal(10,2) DEFAULT 0.00,
  `forma_pagamento` varchar(50) DEFAULT NULL,
  `status` enum('aberta','aguardando_point','finalizada','cancelada') DEFAULT 'aberta',
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_venda` (`numero_venda`),
  KEY `fk_pdv_usuario` (`usuario_id`),
  KEY `idx_pdv_status` (`status`),
  KEY `idx_pdv_created_at` (`created_at`),
  KEY `idx_pdv_cliente` (`cliente_id`),
  KEY `idx_pdv_os` (`os_id`),
  KEY `idx_pdv_caixa` (`caixa_id`),
  CONSTRAINT `fk_pdv_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pdv_os` FOREIGN KEY (`os_id`) REFERENCES `ordens_servico` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pdv_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compras_nota_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nota_id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `descricao` varchar(180) NOT NULL,
  `tipo` enum('peca','produto') NOT NULL DEFAULT 'peca',
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `valor_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_compras_nota_itens_nota` (`nota_id`),
  KEY `idx_compras_nota_itens_produto` (`produto_id`),
  CONSTRAINT `fk_compras_nota_itens_nota` FOREIGN KEY (`nota_id`) REFERENCES `compras_notas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_compras_nota_itens_produto` FOREIGN KEY (`produto_id`) REFERENCES `estoque` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pdv_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `descricao` varchar(255) NOT NULL,
  `quantidade` decimal(10,3) NOT NULL DEFAULT 1.000,
  `preco_unitario` decimal(10,2) NOT NULL,
  `total` decimal(10,2) GENERATED ALWAYS AS (`quantidade` * `preco_unitario`) STORED,
  PRIMARY KEY (`id`),
  KEY `fk_pdv_item_venda` (`venda_id`),
  KEY `fk_pdv_item_produto` (`produto_id`),
  CONSTRAINT `fk_pdv_item_produto` FOREIGN KEY (`produto_id`) REFERENCES `estoque` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pdv_item_venda` FOREIGN KEY (`venda_id`) REFERENCES `pdv_vendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
SET FOREIGN_KEY_CHECKS=1;
