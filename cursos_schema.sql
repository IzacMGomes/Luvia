-- cursos_schema.sql
-- Execute este arquivo no phpMyAdmin (aba SQL) depois do database.sql original.
-- Ele cria a estrutura de Cursos -> Módulos -> Aulas.

USE plataforma_cursos;

-- Remove as tabelas antigas e simples de módulos/matérias (se existirem) para dar lugar à nova estrutura
DROP TABLE IF EXISTS materias;
DROP TABLE IF EXISTS modulos;

CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(100),
    nivel ENUM('iniciante', 'intermediario', 'avancado') NOT NULL DEFAULT 'iniciante',
    capa_imagem VARCHAR(255) DEFAULT NULL,
    status ENUM('rascunho', 'publicado') NOT NULL DEFAULT 'rascunho',
    criado_por INT DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS curso_modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS aulas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modulo_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT,
    video_url VARCHAR(255) DEFAULT NULL,
    duracao_minutos INT DEFAULT NULL,
    ordem INT NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (modulo_id) REFERENCES curso_modulos(id) ON DELETE CASCADE
);
