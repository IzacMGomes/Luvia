<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT ativo FROM usuarios WHERE id = ? AND tipo = "aluno"');
    $stmt->execute([$_GET['id']]);
    $aluno = $stmt->fetch();
    if ($aluno) {
        $novo = $aluno['ativo'] ? 0 : 1;
        $pdo->prepare('UPDATE usuarios SET ativo = ? WHERE id = ?')->execute([$novo, $_GET['id']]);
    }
}
header('Location: alunos.php');
exit;
