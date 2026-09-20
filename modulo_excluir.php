<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$curso_id = (int) ($_GET['curso_id'] ?? 0);

if (isset($_GET['id'])) {
    // Excluir o módulo também exclui suas aulas automaticamente (ON DELETE CASCADE)
    $stmt = $pdo->prepare('DELETE FROM curso_modulos WHERE id = ?');
    $stmt->execute([$_GET['id']]);
}

header('Location: modulos.php?curso_id=' . $curso_id);
exit;
