<?php
require_once 'auth.php';
exigir_login('aluno');
require_once 'config.php';

$curso_id = (int) ($_GET['curso_id'] ?? 0);
$voltar   = $_GET['voltar'] ?? 'dashboard';

if ($curso_id) {
    $stmt = $pdo->prepare('SELECT id FROM favoritos WHERE aluno_id = ? AND curso_id = ?');
    $stmt->execute([$_SESSION['usuario_id'], $curso_id]);

    if ($stmt->fetch()) {
        $del = $pdo->prepare('DELETE FROM favoritos WHERE aluno_id = ? AND curso_id = ?');
        $del->execute([$_SESSION['usuario_id'], $curso_id]);
    } else {
        $ins = $pdo->prepare('INSERT INTO favoritos (aluno_id, curso_id) VALUES (?, ?)');
        $ins->execute([$_SESSION['usuario_id'], $curso_id]);
    }
}

$destino = $voltar === 'favoritos' ? 'favoritos.php' : ($voltar === 'curso' ? 'curso_ver.php?id=' . $curso_id : 'dashboard_aluno.php');
header('Location: ' . $destino);
exit;
