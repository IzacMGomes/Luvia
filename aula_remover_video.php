<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$curso_id = (int) ($_GET['curso_id'] ?? 0);

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT video_arquivo FROM aulas WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $aula = $stmt->fetch();

    if ($aula) {
        // Apaga o arquivo de vídeo do disco, se existir
        if (!empty($aula['video_arquivo'])) {
            $caminho = __DIR__ . '/uploads/videos/' . $aula['video_arquivo'];
            if (file_exists($caminho)) {
                unlink($caminho);
            }
        }
        // Limpa tanto o arquivo enviado quanto o link, removendo o vídeo da aula
        $stmt = $pdo->prepare('UPDATE aulas SET video_arquivo = NULL, video_url = NULL WHERE id = ?');
        $stmt->execute([$_GET['id']]);
    }
}

header('Location: modulos.php?curso_id=' . $curso_id);
exit;
