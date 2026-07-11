INSERT INTO configuracoes (chave, valor) VALUES
('taxa_point_debito_qr_saldo', '1,99'),
('taxa_point_credito_hora', '4,74'),
('taxa_point_credito_14d', '3,79'),
('taxa_point_credito_30d', '3,03'),
('taxa_point_parcelamento_2x', '4,59'),
('taxa_point_parcelamento_3x', '0,57'),
('taxa_point_parcelamento_12x', '17,28')
ON DUPLICATE KEY UPDATE valor = VALUES(valor);
