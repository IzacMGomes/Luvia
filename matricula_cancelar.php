<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

if (isset($_GET['id'])) {
    $pdo->prepare('DELETE FROM matriculas WHERE id = ?')->execute([$_GET['id']]);
}
header('Location: matriculas.php');
exit;
