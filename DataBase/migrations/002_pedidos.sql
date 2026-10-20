-- Migração 002 — Carrinho de compras / Pedidos
-- Cria as tabelas que guardam os pedidos feitos pelos usuários logados
-- e o andamento (status) de cada um.
--
-- Como aplicar (XAMPP):
--   phpMyAdmin  ->  banco `nexus_db`  ->  aba "SQL"  ->  cole este arquivo  ->  Executar
--
-- É seguro rodar mais de uma vez (usa IF NOT EXISTS).

USE `nexus_db`;

CREATE TABLE IF NOT EXISTS `pedidos` (
  `id`             INT(11)      NOT NULL AUTO_INCREMENT,
  `usuario_email`  VARCHAR(255) NOT NULL,
  `usuario_nome`   VARCHAR(255) NOT NULL,
  `usuario_cpf`    VARCHAR(14)  DEFAULT NULL,
  `status`         ENUM('pendente','em_analise','em_producao','concluido','cancelado')
                                NOT NULL DEFAULT 'pendente',
  `total`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `observacoes`    TEXT         DEFAULT NULL,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  `updated_at`     TIMESTAMP    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pedidos_email`  (`usuario_email`),
  KEY `idx_pedidos_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `pedido_itens` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `pedido_id`   INT(11)      NOT NULL,
  `pacote_key`  VARCHAR(50)  NOT NULL,
  `pacote_nome` VARCHAR(150) NOT NULL,
  `preco`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `quantidade`  INT(11)      NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_pedido_itens_pedido` (`pedido_id`),
  CONSTRAINT `fk_pedido_itens_pedido`
    FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
