<?php
require_once 'auth.php';
exigir_login('aluno');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['curso_id'])) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO matriculas (aluno_id, curso_id) VALUES (?, ?)');
    $stmt->execute([$_SESSION['usuario_id'], (int) $_POST['curso_id']]);
    header('Location: curso_ver.php?id=' . (int) $_POST['curso_id']);
    exit;
}

header('Location: catalogo.php');
exit;
