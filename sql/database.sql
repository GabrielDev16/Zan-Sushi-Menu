CREATE DATABASE IF NOT EXISTS zan_sushi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zan_sushi;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  ordem INT NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NOT NULL,
  nome VARCHAR(120) NOT NULL,
  descricao VARCHAR(300) NULL,
  preco DECIMAL(8,2) NULL,
  imagem VARCHAR(80) NULL,
  ordem INT NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- Login inicial: admin / zan@2026  (TROQUE depois do primeiro acesso)
INSERT INTO admins (usuario, senha_hash) VALUES ('admin', '$2y$10$CJAfnL45KcrnpD54dg8mwup8N2S6D89kOQITLu648zW/iR/VAo7L.');

INSERT INTO categorias (nome, ordem) VALUES ('Entradas',1),('Sushis',2),('Sashimis',3),('Hot Rolls',4),('Bebidas',5);
