<?php
// Manifesto estrutural controlado; atualizar somente ap?s migration revisada.
return array (
  'aparelhos' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'cliente_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'marca' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'modelo' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'imei' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'numero_serie' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'cor' => 
      array (
        'type' => 'varchar(30)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'senha_padrao' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'estado_fisico' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'acessorios' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'observacoes' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_aparelhos_cliente',
      1 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_aparelhos_cliente' => 
      array (
        0 => 'cliente_id',
        1 => 'clientes',
        2 => 'id',
      ),
    ),
  ),
  'aparelho_modelos' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'marca' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'modelo' => 
      array (
        'type' => 'varchar(140)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'idx_aparelho_modelos_marca',
      1 => 'PRIMARY',
      2 => 'uk_aparelho_modelos_marca_modelo',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'auditoria_logs' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'usuario_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'usuario_nome' => 
      array (
        'type' => 'varchar(120)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'acao' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'entidade' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'entidade_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'descricao' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'dados' => 
      array (
        'type' => 'longtext',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'ip' => 
      array (
        'type' => 'varchar(64)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'idx_auditoria_logs_created_at',
      1 => 'idx_auditoria_logs_entidade',
      2 => 'idx_auditoria_logs_usuario',
      3 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_auditoria_logs_usuario' => 
      array (
        0 => 'usuario_id',
        1 => 'usuarios',
        2 => 'id',
      ),
    ),
  ),
  'categorias' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'nome' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'tipo' => 
      array (
        'type' => 'enum(\'peca\',\'produto\')',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'PRIMARY',
      1 => 'uk_categorias_nome_tipo',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'clientes' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'nome' => 
      array (
        'type' => 'varchar(150)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'cpf_cnpj' => 
      array (
        'type' => 'varchar(20)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'telefone' => 
      array (
        'type' => 'varchar(20)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'whatsapp' => 
      array (
        'type' => 'varchar(20)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'email' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'endereco' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'observacoes' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'cpf_cnpj',
      1 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'compras_notas' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'fornecedor_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'os_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'numero' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'data_emissao' => 
      array (
        'type' => 'date',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'data_vencimento' => 
      array (
        'type' => 'date',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'valor_total' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => '0.00',
      ),
      'forma_pagamento' => 
      array (
        'type' => 'varchar(60)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'status' => 
      array (
        'type' => 'enum(\'aberta\',\'baixada\',\'cancelada\')',
        'nullable' => 'NO',
        'default' => '\'Aberta\'',
      ),
      'observacoes' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'usuario_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'baixado_at' => 
      array (
        'type' => 'datetime',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'financeiro_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
      'updated_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_compras_notas_financeiro',
      1 => 'fk_compras_notas_fornecedor',
      2 => 'fk_compras_notas_usuario',
      3 => 'idx_compras_notas_data',
      4 => 'idx_compras_notas_os',
      5 => 'idx_compras_notas_status',
      6 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_compras_notas_financeiro' => 
      array (
        0 => 'financeiro_id',
        1 => 'financeiro',
        2 => 'id',
      ),
      'fk_compras_notas_fornecedor' => 
      array (
        0 => 'fornecedor_id',
        1 => 'fornecedores',
        2 => 'id',
      ),
      'fk_compras_notas_os' => 
      array (
        0 => 'os_id',
        1 => 'ordens_servico',
        2 => 'id',
      ),
      'fk_compras_notas_usuario' => 
      array (
        0 => 'usuario_id',
        1 => 'usuarios',
        2 => 'id',
      ),
    ),
  ),
  'compras_nota_itens' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'nota_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'produto_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'descricao' => 
      array (
        'type' => 'varchar(180)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'tipo' => 
      array (
        'type' => 'enum(\'peca\',\'produto\')',
        'nullable' => 'NO',
        'default' => '\'peca\'',
      ),
      'quantidade' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => '1',
      ),
      'valor_unitario' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => '0.00',
      ),
      'total' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => '0.00',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'idx_compras_nota_itens_nota',
      1 => 'idx_compras_nota_itens_produto',
      2 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_compras_nota_itens_nota' => 
      array (
        0 => 'nota_id',
        1 => 'compras_notas',
        2 => 'id',
      ),
      'fk_compras_nota_itens_produto' => 
      array (
        0 => 'produto_id',
        1 => 'estoque',
        2 => 'id',
      ),
    ),
  ),
  'compras_solicitacoes' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'item_nome' => 
      array (
        'type' => 'varchar(180)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'tipo' => 
      array (
        'type' => 'enum(\'peca\',\'produto\')',
        'nullable' => 'NO',
        'default' => '\'peca\'',
      ),
      'quantidade' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => '1',
      ),
      'fornecedor' => 
      array (
        'type' => 'varchar(150)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'prioridade' => 
      array (
        'type' => 'enum(\'baixa\',\'normal\',\'alta\',\'urgente\')',
        'nullable' => 'NO',
        'default' => '\'Normal\'',
      ),
      'status' => 
      array (
        'type' => 'enum(\'pendente\',\'solicitado\',\'comprado\',\'recebido\',\'cancelado\')',
        'nullable' => 'NO',
        'default' => '\'Pendente\'',
      ),
      'observacoes' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'usuario_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'data_solicitacao' => 
      array (
        'type' => 'date',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'data_atualizacao' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_compras_usuario',
      1 => 'idx_compras_data',
      2 => 'idx_compras_status',
      3 => 'idx_compras_tipo',
      4 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_compras_usuario' => 
      array (
        0 => 'usuario_id',
        1 => 'usuarios',
        2 => 'id',
      ),
    ),
  ),
  'configuracoes' => 
  array (
    'columns' => 
    array (
      'chave' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'valor' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
    ),
    'indexes' => 
    array (
      0 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'contas_publicas' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'nome' => 
      array (
        'type' => 'varchar(150)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'email' => 
      array (
        'type' => 'varchar(150)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'whatsapp' => 
      array (
        'type' => 'varchar(20)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'senha' => 
      array (
        'type' => 'varchar(255)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'email',
      1 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'estoque' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'codigo_interno' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'nome' => 
      array (
        'type' => 'varchar(150)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'tipo' => 
      array (
        'type' => 'enum(\'peca\',\'produto\')',
        'nullable' => 'NO',
        'default' => '\'peca\'',
      ),
      'categoria' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'marca_compativel' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'modelo_compativel' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'quantidade' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => '0',
      ),
      'estoque_minimo' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => '5',
      ),
      'custo' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'preco_venda' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'fornecedor' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'localizacao' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'imagem' => 
      array (
        'type' => 'varchar(255)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'imagem_mime' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'imagem_blob' => 
      array (
        'type' => 'longblob',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'numero_serie' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'unidade' => 
      array (
        'type' => 'varchar(20)',
        'nullable' => 'YES',
        'default' => '\'unid\'',
      ),
      'mercado_livre_item_id' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'mercado_livre_permalink' => 
      array (
        'type' => 'varchar(255)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'mercado_livre_status' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'mercado_livre_category_id' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'mercado_livre_listing_type_id' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'mercado_livre_condition' => 
      array (
        'type' => 'varchar(20)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'mercado_livre_last_sync_at' => 
      array (
        'type' => 'datetime',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'mercado_livre_last_error' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'mercado_livre_attributes' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'codigo_interno',
      1 => 'idx_estoque_categoria',
      2 => 'idx_estoque_ml_status',
      3 => 'idx_estoque_tipo',
      4 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'estoque_imagens' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'produto_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'imagem' => 
      array (
        'type' => 'varchar(255)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'imagem_mime' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'ordem' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => '0',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'idx_estoque_imagens_produto',
      1 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_estoque_imagens_produto' => 
      array (
        0 => 'produto_id',
        1 => 'estoque',
        2 => 'id',
      ),
    ),
  ),
  'estoque_movimentacoes' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'produto_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'tipo' => 
      array (
        'type' => 'enum(\'entrada\',\'saida\',\'ajuste\')',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'quantidade' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'motivo' => 
      array (
        'type' => 'varchar(255)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'os_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'usuario_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_estoque_movimentacoes_produto',
      1 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_estoque_movimentacoes_produto' => 
      array (
        0 => 'produto_id',
        1 => 'estoque',
        2 => 'id',
      ),
    ),
  ),
  'financeiro' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'tipo' => 
      array (
        'type' => 'enum(\'receita\',\'despesa\')',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'categoria' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'descricao' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'valor' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'os_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'usuario_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'data_pagamento' => 
      array (
        'type' => 'date',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'forma_pagamento' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_financeiro_usuario',
      1 => 'idx_financeiro_data_pagamento',
      2 => 'idx_financeiro_os',
      3 => 'idx_financeiro_tipo',
      4 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_financeiro_os' => 
      array (
        0 => 'os_id',
        1 => 'ordens_servico',
        2 => 'id',
      ),
      'fk_financeiro_usuario' => 
      array (
        0 => 'usuario_id',
        1 => 'usuarios',
        2 => 'id',
      ),
    ),
  ),
  'fornecedores' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'nome' => 
      array (
        'type' => 'varchar(160)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'documento' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'telefone' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'email' => 
      array (
        'type' => 'varchar(120)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'endereco' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'observacoes' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'ativo' => 
      array (
        'type' => 'tinyint(1)',
        'nullable' => 'NO',
        'default' => '1',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
      'updated_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'idx_fornecedores_ativo',
      1 => 'idx_fornecedores_nome',
      2 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'gastos_pessoais' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'tipo' => 
      array (
        'type' => 'enum(\'receita\',\'despesa\')',
        'nullable' => 'NO',
        'default' => '\'Despesa\'',
      ),
      'categoria' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'descricao' => 
      array (
        'type' => 'varchar(255)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'valor' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'data_lancamento' => 
      array (
        'type' => 'date',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'forma_pagamento' => 
      array (
        'type' => 'varchar(60)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'recorrente' => 
      array (
        'type' => 'tinyint(1)',
        'nullable' => 'NO',
        'default' => '0',
      ),
      'observacoes' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'usuario_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
      'updated_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_gastos_pessoais_usuario',
      1 => 'idx_gastos_pessoais_data',
      2 => 'idx_gastos_pessoais_tipo',
      3 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_gastos_pessoais_usuario' => 
      array (
        0 => 'usuario_id',
        1 => 'usuarios',
        2 => 'id',
      ),
    ),
  ),
  'gastos_pessoais_categorias' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'nome' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'tipo' => 
      array (
        'type' => 'enum(\'receita\',\'despesa\',\'ambos\')',
        'nullable' => 'NO',
        'default' => '\'Despesa\'',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'PRIMARY',
      1 => 'uk_gastos_pessoais_categoria',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'mercado_livre_logs' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'produto_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'item_id' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'acao' => 
      array (
        'type' => 'varchar(60)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'status' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'mensagem' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'payload' => 
      array (
        'type' => 'mediumtext',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'idx_ml_logs_produto',
      1 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_mercado_livre_logs_produto' => 
      array (
        0 => 'produto_id',
        1 => 'estoque',
        2 => 'id',
      ),
    ),
  ),
  'mercado_pago_logs' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'external_reference' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'payment_id' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'status' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'mensagem' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'payload' => 
      array (
        'type' => 'mediumtext',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'idx_mp_logs_ref',
      1 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'mercado_pago_pedidos' => 
  array (
    'columns' => 
    array (
      'external_reference' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'preference_id' => 
      array (
        'type' => 'varchar(120)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'payment_id' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'status' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'NO',
        'default' => '\'created\'',
      ),
      'status_detail' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'items_json' => 
      array (
        'type' => 'mediumtext',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'total' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => '0.00',
      ),
      'customer_name' => 
      array (
        'type' => 'varchar(150)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'customer_email' => 
      array (
        'type' => 'varchar(150)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'email_sent_at' => 
      array (
        'type' => 'datetime',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'estoque_baixado_at' => 
      array (
        'type' => 'datetime',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'financeiro_lancado_at' => 
      array (
        'type' => 'datetime',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
      'updated_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'idx_mp_status',
      1 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'mercado_pago_point_orders' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'os_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'pdv_venda_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'numero_os' => 
      array (
        'type' => 'varchar(20)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'external_reference' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'mp_order_id' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'payment_id' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'status' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'NO',
        'default' => '\'created\'',
      ),
      'status_detail' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'amount' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => '0.00',
      ),
      'payment_method' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'terminal_id' => 
      array (
        'type' => 'varchar(120)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'payload' => 
      array (
        'type' => 'mediumtext',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'financeiro_lancado_at' => 
      array (
        'type' => 'datetime',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
      'updated_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'external_reference',
      1 => 'idx_mp_point_orders_os',
      2 => 'idx_mp_point_orders_pdv',
      3 => 'idx_mp_point_orders_status',
      4 => 'mp_order_id',
      5 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_mp_point_orders_os' => 
      array (
        0 => 'os_id',
        1 => 'ordens_servico',
        2 => 'id',
      ),
    ),
  ),
  'ordens_servico' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'numero_os' => 
      array (
        'type' => 'varchar(20)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'cliente_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'aparelho_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'tecnico_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'problema_relatado' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'diagnostico_tecnico' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'servico_realizar' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'prioridade' => 
      array (
        'type' => 'enum(\'baixa\',\'normal\',\'alta\',\'urgente\')',
        'nullable' => 'YES',
        'default' => '\'Normal\'',
      ),
      'status' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'NO',
        'default' => '\'Recebido\'',
      ),
      'prazo_estimado' => 
      array (
        'type' => 'date',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'valor_mao_obra' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => '0.00',
      ),
      'valor_pecas' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => '0.00',
      ),
      'desconto' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => '0.00',
      ),
      'valor_total' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'forma_pagamento' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'situacao_pagamento' => 
      array (
        'type' => 'enum(\'pendente\',\'parcial\',\'pago\')',
        'nullable' => 'YES',
        'default' => '\'Pendente\'',
      ),
      'garantia_expira' => 
      array (
        'type' => 'date',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'termo_aceite' => 
      array (
        'type' => 'tinyint(1)',
        'nullable' => 'YES',
        'default' => '0',
      ),
      'fotos' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'fotos_saida' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
      'updated_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_os_aparelho',
      1 => 'fk_os_cliente',
      2 => 'fk_os_tecnico',
      3 => 'idx_os_status',
      4 => 'numero_os',
      5 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_os_aparelho' => 
      array (
        0 => 'aparelho_id',
        1 => 'aparelhos',
        2 => 'id',
      ),
      'fk_os_cliente' => 
      array (
        0 => 'cliente_id',
        1 => 'clientes',
        2 => 'id',
      ),
      'fk_os_tecnico' => 
      array (
        0 => 'tecnico_id',
        1 => 'usuarios',
        2 => 'id',
      ),
    ),
  ),
  'os_historico' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'os_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'usuario_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'status_anterior' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'status_novo' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'observacao' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_historico_os',
      1 => 'fk_historico_usuario',
      2 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_historico_os' => 
      array (
        0 => 'os_id',
        1 => 'ordens_servico',
        2 => 'id',
      ),
      'fk_historico_usuario' => 
      array (
        0 => 'usuario_id',
        1 => 'usuarios',
        2 => 'id',
      ),
    ),
  ),
  'os_pagamentos' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'os_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'valor' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'forma_pagamento' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'data_pagamento' => 
      array (
        'type' => 'date',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'observacao' => 
      array (
        'type' => 'varchar(255)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'usuario_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_os_pagamentos_usuario',
      1 => 'idx_os_pagamentos_os',
      2 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_os_pagamentos_os' => 
      array (
        0 => 'os_id',
        1 => 'ordens_servico',
        2 => 'id',
      ),
      'fk_os_pagamentos_usuario' => 
      array (
        0 => 'usuario_id',
        1 => 'usuarios',
        2 => 'id',
      ),
    ),
  ),
  'pdv_caixas' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'data_caixa' => 
      array (
        'type' => 'date',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'status' => 
      array (
        'type' => 'enum(\'aberto\',\'fechado\')',
        'nullable' => 'NO',
        'default' => '\'aberto\'',
      ),
      'abertura_tipo' => 
      array (
        'type' => 'enum(\'automatica\',\'manual\')',
        'nullable' => 'NO',
        'default' => '\'automatica\'',
      ),
      'fechamento_tipo' => 
      array (
        'type' => 'enum(\'automatico\',\'manual\')',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'aberto_por' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'fechado_por' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'aberto_em' => 
      array (
        'type' => 'datetime',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'fechado_em' => 
      array (
        'type' => 'datetime',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'valor_inicial' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => '0.00',
      ),
      'valor_suprimento' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => '0.00',
      ),
      'valor_sangria' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => '0.00',
      ),
      'valor_informado' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'observacoes' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
      'updated_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'idx_pdv_caixas_status',
      1 => 'PRIMARY',
      2 => 'uk_pdv_caixas_data',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'pdv_itens' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'venda_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'produto_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'descricao' => 
      array (
        'type' => 'varchar(255)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'quantidade' => 
      array (
        'type' => 'decimal(10,3)',
        'nullable' => 'NO',
        'default' => '1.000',
      ),
      'preco_unitario' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'total' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_pdv_item_produto',
      1 => 'fk_pdv_item_venda',
      2 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_pdv_item_produto' => 
      array (
        0 => 'produto_id',
        1 => 'estoque',
        2 => 'id',
      ),
      'fk_pdv_item_venda' => 
      array (
        0 => 'venda_id',
        1 => 'pdv_vendas',
        2 => 'id',
      ),
    ),
  ),
  'pdv_vendas' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'numero_venda' => 
      array (
        'type' => 'varchar(20)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'caixa_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'cliente_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'os_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'usuario_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'subtotal' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => '0.00',
      ),
      'desconto' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => '0.00',
      ),
      'total' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => '0.00',
      ),
      'taxa_cartao_percentual' => 
      array (
        'type' => 'decimal(5,2)',
        'nullable' => 'YES',
        'default' => '0.00',
      ),
      'taxa_cartao_valor' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => '0.00',
      ),
      'total_liquido' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => '0.00',
      ),
      'forma_pagamento' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'status' => 
      array (
        'type' => 'enum(\'aberta\',\'aguardando_point\',\'finalizada\',\'cancelada\')',
        'nullable' => 'YES',
        'default' => '\'aberta\'',
      ),
      'observacoes' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_pdv_usuario',
      1 => 'idx_pdv_caixa',
      2 => 'idx_pdv_cliente',
      3 => 'idx_pdv_created_at',
      4 => 'idx_pdv_os',
      5 => 'idx_pdv_status',
      6 => 'numero_venda',
      7 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_pdv_cliente' => 
      array (
        0 => 'cliente_id',
        1 => 'clientes',
        2 => 'id',
      ),
      'fk_pdv_os' => 
      array (
        0 => 'os_id',
        1 => 'ordens_servico',
        2 => 'id',
      ),
      'fk_pdv_usuario' => 
      array (
        0 => 'usuario_id',
        1 => 'usuarios',
        2 => 'id',
      ),
    ),
  ),
  'recados' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'nome' => 
      array (
        'type' => 'varchar(150)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'telefone' => 
      array (
        'type' => 'varchar(30)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'mensagem' => 
      array (
        'type' => 'text',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'status' => 
      array (
        'type' => 'enum(\'pendente\',\'lido\',\'respondido\',\'arquivado\')',
        'nullable' => 'NO',
        'default' => '\'Pendente\'',
      ),
      'usuario_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'data_recado' => 
      array (
        'type' => 'date',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
      'updated_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_recados_usuario',
      1 => 'idx_recados_data',
      2 => 'idx_recados_status',
      3 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_recados_usuario' => 
      array (
        0 => 'usuario_id',
        1 => 'usuarios',
        2 => 'id',
      ),
    ),
  ),
  'servicos_referencia' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'nome' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'preco_sugerido' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'tempo_medio' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'garantia_padrao' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => '90',
      ),
    ),
    'indexes' => 
    array (
      0 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
  'termos_compra_venda' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'numero_termo' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'vendedor_nome' => 
      array (
        'type' => 'varchar(160)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'vendedor_contato' => 
      array (
        'type' => 'varchar(60)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'vendedor_cpf' => 
      array (
        'type' => 'varchar(30)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'vendedor_rg' => 
      array (
        'type' => 'varchar(30)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'vendedor_endereco' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'data_entrada' => 
      array (
        'type' => 'date',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'equipamento_tipo' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'NO',
        'default' => '\'Smartphone\'',
      ),
      'marca_modelo' => 
      array (
        'type' => 'varchar(180)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'imei1' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'imei2' => 
      array (
        'type' => 'varchar(80)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'senha_autorizada' => 
      array (
        'type' => 'tinyint(1)',
        'nullable' => 'NO',
        'default' => '0',
      ),
      'chip_ssd_card' => 
      array (
        'type' => 'tinyint(1)',
        'nullable' => 'NO',
        'default' => '0',
      ),
      'bateria' => 
      array (
        'type' => 'tinyint(1)',
        'nullable' => 'NO',
        'default' => '0',
      ),
      'acessorios' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'estado_aparelho' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'valor_compra' => 
      array (
        'type' => 'decimal(10,2)',
        'nullable' => 'NO',
        'default' => '0.00',
      ),
      'comprador_nome' => 
      array (
        'type' => 'varchar(160)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'comprador_contato' => 
      array (
        'type' => 'varchar(60)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'comprador_documento' => 
      array (
        'type' => 'varchar(40)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'comprador_endereco' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'observacoes' => 
      array (
        'type' => 'text',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'usuario_id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
      'updated_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'fk_termos_usuario',
      1 => 'idx_termos_data',
      2 => 'idx_termos_equipamento',
      3 => 'idx_termos_vendedor',
      4 => 'numero_termo',
      5 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
      'fk_termos_usuario' => 
      array (
        0 => 'usuario_id',
        1 => 'usuarios',
        2 => 'id',
      ),
    ),
  ),
  'usuarios' => 
  array (
    'columns' => 
    array (
      'id' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'nome' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'email' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'senha' => 
      array (
        'type' => 'varchar(255)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'perfil' => 
      array (
        'type' => 'varchar(50)',
        'nullable' => 'NO',
        'default' => NULL,
      ),
      'especialidade' => 
      array (
        'type' => 'varchar(100)',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'comissao' => 
      array (
        'type' => 'decimal(5,2)',
        'nullable' => 'YES',
        'default' => '0.00',
      ),
      'meta_os_mes' => 
      array (
        'type' => 'int(11)',
        'nullable' => 'YES',
        'default' => '30',
      ),
      'status' => 
      array (
        'type' => 'enum(\'ativo\',\'inativo\')',
        'nullable' => 'YES',
        'default' => '\'Ativo\'',
      ),
      'ultimo_login' => 
      array (
        'type' => 'datetime',
        'nullable' => 'YES',
        'default' => 'NULL',
      ),
      'created_at' => 
      array (
        'type' => 'timestamp',
        'nullable' => 'NO',
        'default' => 'current_timestamp()',
      ),
    ),
    'indexes' => 
    array (
      0 => 'email',
      1 => 'PRIMARY',
    ),
    'foreign_keys' => 
    array (
    ),
  ),
);
