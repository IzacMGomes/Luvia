<?php
// config.php - Configuração de conexão com o banco de dados

$host = 'localhost';
$dbname = 'plataforma_cursos';
$usuario_db = 'root';       // troque pelo seu usuário do MySQL
$senha_db = '';             // troque pela sua senha do MySQL

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $usuario_db,
        $senha_db,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}
