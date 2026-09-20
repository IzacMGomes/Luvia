-- Banco de dados da plataforma de cursos online
CREATE DATABASE IF NOT EXISTS plataforma_cursos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE plataforma_cursos;

-- Tabela de usuários (alunos e administradores)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('aluno', 'administrador') NOT NULL DEFAULT 'aluno',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Exemplo de estrutura para módulos e matérias (para você expandir depois)
CREATE TABLE IF NOT EXISTS modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modulo_id INT NOT NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
);

-- Inserindo um administrador padrão de exemplo
-- Senha: admin123 (troque depois de testar!)
-- Gere o hash real rodando: password_hash('admin123', PASSWORD_DEFAULT) no PHP
-- Ou use o script gerar_hash.php incluso para gerar o hash correto.
