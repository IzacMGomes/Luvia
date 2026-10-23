<?php
require_once 'Database.php';

class Usuario {
    public ?int $id = null;
    public string $nome;
    public string $email;
    private string $senha;
    public string $tipo;

    public function cadastrar(string $nome, string $email, string $senha, string $tipo = 'aluno'): bool {
        $pdo = Database::getConnection();
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$nome, $email, $hash, $tipo]);
    }

    public function autenticar(string $email, string $senha): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            return $usuario;
        }
        return null;
    }
}
?>