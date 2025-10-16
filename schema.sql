-- Schema for jogo3 personal game catalog

CREATE DATABASE IF NOT EXISTS jogo3_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jogo3_db;

-- users
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha TEXT NOT NULL,
  avatar VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- If you already ran the schema without `avatar`, run this to add the column:
-- ALTER TABLE usuarios ADD COLUMN avatar VARCHAR(255) DEFAULT NULL;

-- generos
CREATE TABLE IF NOT EXISTS generos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- plataformas
CREATE TABLE IF NOT EXISTS plataformas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- jogos
CREATE TABLE IF NOT EXISTS jogos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  genero_id INT,
  plataforma_id INT,
  nota DECIMAL(3,1) DEFAULT 0.0,
  status ENUM('Zerado','Jogando','Quero jogar') DEFAULT 'Quero jogar',
  favorito TINYINT(1) DEFAULT 0,
  capa VARCHAR(255) DEFAULT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  usuario_id INT NOT NULL,
  FOREIGN KEY (genero_id) REFERENCES generos(id) ON DELETE SET NULL,
  FOREIGN KEY (plataforma_id) REFERENCES plataformas(id) ON DELETE SET NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- seed data for generos and plataformas
INSERT INTO generos (nome) VALUES ('Ação'), ('Aventura'), ('RPG'), ('Esporte'), ('Estratégia') ON DUPLICATE KEY UPDATE nome=VALUES(nome);
INSERT INTO plataformas (nome) VALUES ('PC'), ('PS5'), ('Xbox'), ('Nintendo Switch'), ('Mobile') ON DUPLICATE KEY UPDATE nome=VALUES(nome);
