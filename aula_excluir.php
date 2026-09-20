<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$curso_id = (int) ($_GET['curso_id'] ?? 0);

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM aulas WHERE id = ?');
    $stmt->execute([$_GET['id']]);
}

header('Location: modulos.php?curso_id=' . $curso_id);
exit;
