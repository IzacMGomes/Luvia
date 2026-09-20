<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT status FROM cursos WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $curso = $stmt->fetch();

    if ($curso) {
        $novo_status = $curso['status'] === 'publicado' ? 'rascunho' : 'publicado';
        $stmt = $pdo->prepare('UPDATE cursos SET status = ? WHERE id = ?');
        $stmt->execute([$novo_status, $_GET['id']]);
    }
}

header('Location: cursos.php');
exit;
