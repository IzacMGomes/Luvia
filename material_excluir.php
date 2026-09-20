<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$curso_id  = (int) ($_GET['curso_id'] ?? 0);
$modulo_id = (int) ($_GET['modulo_id'] ?? 0);
$aula_id   = (int) ($_GET['aula_id'] ?? 0);

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT arquivo FROM materiais WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $mat = $stmt->fetch();
    if ($mat) {
        $caminho = __DIR__ . '/uploads/materiais/' . $mat['arquivo'];
        if (file_exists($caminho)) unlink($caminho);
        $pdo->prepare('DELETE FROM materiais WHERE id = ?')->execute([$_GET['id']]);
    }
}

header('Location: aula_form.php?curso_id=' . $curso_id . '&modulo_id=' . $modulo_id . '&id=' . $aula_id);
exit;
