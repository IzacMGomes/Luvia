<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

if (isset($_GET['id'])) {
    // Busca o nome do arquivo de capa para apagar do disco também
    $stmt = $pdo->prepare('SELECT capa_imagem FROM cursos WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $curso = $stmt->fetch();

    if ($curso && !empty($curso['capa_imagem'])) {
        $caminho = __DIR__ . '/uploads/capas/' . $curso['capa_imagem'];
        if (file_exists($caminho)) {
            unlink($caminho);
        }
    }

    // Excluir o curso também exclui módulos e aulas automaticamente (ON DELETE CASCADE)
    $stmt = $pdo->prepare('DELETE FROM cursos WHERE id = ?');
    $stmt->execute([$_GET['id']]);
}

header('Location: cursos.php');
exit;
