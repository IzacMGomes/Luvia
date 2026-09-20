<?php
require_once 'auth.php';
exigir_login('aluno');
require_once 'config.php';

$pagina_ativa = 'favoritos';
$aluno_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare('SELECT c.* FROM cursos c INNER JOIN favoritos f ON f.curso_id = c.id WHERE f.aluno_id = ? ORDER BY f.criado_em DESC');
$stmt->execute([$aluno_id]);
$cursos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Favoritos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    .grade-cursos { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 18px; }
    .curso-capa { width: 100%; height: 110px; border-radius: 8px 8px 0 0; object-fit: cover; background: var(--bg-soft); display: block; border-bottom: 1px solid var(--line); }
    .curso-capa-vazia { width: 100%; height: 110px; border-radius: 8px 8px 0 0; background: var(--bg-soft); border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: center; color: var(--muted); font-weight: 600; font-size: 24px; }
    .curso-item { padding: 0; overflow: hidden; position: relative; }
    .curso-corpo { padding: 14px 16px; }
    .fav-botao { position: absolute; top: 10px; right: 10px; background: var(--surface-2); border: 1px solid var(--line); border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; text-decoration: none; }
</style>
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_aluno.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div><h1>Favoritos</h1><p class="subtitulo">Cursos que você marcou para acessar rapidamente.</p></div>
        </div>
        <?php if (empty($cursos)): ?>
            <div class="cartao"><p class="vazio">Você ainda não favoritou nenhum curso.</p></div>
        <?php else: ?>
        <div class="grade-cursos">
            <?php foreach ($cursos as $curso): ?>
            <div class="cartao curso-item">
                <a class="fav-botao" href="favorito_toggle.php?curso_id=<?= $curso['id'] ?>&voltar=favoritos" title="Remover dos favoritos">⭐</a>
                <a href="curso_ver.php?id=<?= $curso['id'] ?>" style="text-decoration:none; color:inherit;">
                    <?php if (!empty($curso['capa_imagem'])): ?>
                        <img class="curso-capa" src="uploads/capas/<?= htmlspecialchars($curso['capa_imagem']) ?>" alt="">
                    <?php else: ?>
                        <div class="curso-capa-vazia"><?= htmlspecialchars(mb_substr($curso['titulo'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <div class="curso-corpo"><h3 style="font-size:15px;"><?= htmlspecialchars($curso['titulo']) ?></h3></div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
