<?php
require_once 'Database.php';

class Curso {
    public ?int $id = null;
    public string $titulo;
    public string $descricao;
    public string $nivel;

    public function listarTodos(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM cursos ORDER BY criado_em DESC');
        return $stmt->fetchAll();
    }

    public function cadastrar(string $titulo, string $descricao, string $nivel): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO cursos (titulo, descricao, nivel, status) VALUES (?, ?, ?, "publicado")');
        return $stmt->execute([$titulo, $descricao, $nivel]);
    }
}
?>