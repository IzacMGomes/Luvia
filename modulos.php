<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$pagina_ativa = 'cursos';

if (!isset($_GET['curso_id'])) { header('Location: cursos.php'); exit; }
$curso_id = (int) $_GET['curso_id'];

$stmt = $pdo->prepare('SELECT * FROM cursos WHERE id = ?');
$stmt->execute([$curso_id]);
$curso = $stmt->fetch();
if (!$curso) { header('Location: cursos.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM curso_modulos WHERE curso_id = ? ORDER BY ordem, id');
$stmt->execute([$curso_id]);
$modulos = $stmt->fetchAll();

foreach ($modulos as &$modulo) {
    $stmtAulas = $pdo->prepare('SELECT * FROM aulas WHERE modulo_id = ? ORDER BY ordem, id');
    $stmtAulas->execute([$modulo['id']]);
    $modulo['aulas'] = $stmtAulas->fetchAll();
}
unset($modulo);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Módulos e Aulas - <?= htmlspecialchars($curso['titulo']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    .modulo-bloco { border: 1px solid var(--line); border-radius: 8px; padding: 16px 18px; margin-bottom: 14px; }
    .modulo-bloco:last-child { margin-bottom: 0; }
    .modulo-topo { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
    .modulo-topo h3 { font-size: 16px; margin: 0; }
    .aula-linha { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-top: 1px solid var(--line); font-size: 14px; }
    .aula-linha .fonte-video { font-size: 12px; color: var(--muted); margin-left: 6px; }
</style>
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_admin.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div>
                <h1>Módulos e aulas</h1>
                <p class="subtitulo"><a href="cursos.php">&larr; Voltar</a> · Curso: <strong><?= htmlspecialchars($curso['titulo']) ?></strong></p>
            </div>
            <a class="botao destaque" href="modulo_form.php?curso_id=<?= $curso_id ?>">+ Novo módulo</a>
        </div>

        <?php if (empty($modulos)): ?>
            <div class="cartao"><p class="vazio">Nenhum módulo cadastrado ainda para este curso.</p></div>
        <?php endif; ?>

        <?php foreach ($modulos as $modulo): ?>
        <div class="cartao">
            <div class="modulo-bloco">
                <div class="modulo-topo">
                    <h3><?= htmlspecialchars($modulo['titulo']) ?></h3>
                    <div class="acoes-linha">
                        <a href="modulo_form.php?curso_id=<?= $curso_id ?>&id=<?= $modulo['id'] ?>">Editar módulo</a>
                        <a class="excluir" href="modulo_excluir.php?id=<?= $modulo['id'] ?>&curso_id=<?= $curso_id ?>" onclick="return confirm('Excluir este módulo e todas as suas aulas?')">Excluir módulo</a>
                    </div>
                </div>

                <?php if (empty($modulo['aulas'])): ?>
                    <p class="vazio" style="padding:14px 0; text-align:left;">Nenhuma aula neste módulo ainda.</p>
                <?php else: ?>
                    <?php foreach ($modulo['aulas'] as $aula): ?>
                    <div class="aula-linha">
                        <span>
                            🎬 <?= htmlspecialchars($aula['titulo']) ?><?= $aula['duracao_minutos'] ? ' ('.$aula['duracao_minutos'].' min)' : '' ?>
                            <?php if (!empty($aula['video_arquivo'])): ?><span class="fonte-video">· vídeo enviado</span>
                            <?php elseif (!empty($aula['video_url'])): ?><span class="fonte-video">· link externo</span>
                            <?php else: ?><span class="fonte-video">· sem vídeo</span><?php endif; ?>
                        </span>
                        <span class="acoes-linha">
                            <a href="aula_form.php?modulo_id=<?= $modulo['id'] ?>&curso_id=<?= $curso_id ?>&id=<?= $aula['id'] ?>">Editar</a>
                            <?php if (!empty($aula['video_arquivo']) || !empty($aula['video_url'])): ?>
                                <a class="excluir" href="aula_remover_video.php?id=<?= $aula['id'] ?>&curso_id=<?= $curso_id ?>" onclick="return confirm('Excluir o vídeo desta aula? A aula continua existindo, só sem vídeo.')">Excluir vídeo</a>
                            <?php endif; ?>
                            <a class="excluir" href="aula_excluir.php?id=<?= $aula['id'] ?>&curso_id=<?= $curso_id ?>" onclick="return confirm('Excluir esta aula?')">Excluir aula</a>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <p style="margin-top:14px; margin-bottom:0;">
                    <a class="botao pequeno fantasma" href="aula_form.php?modulo_id=<?= $modulo['id'] ?>&curso_id=<?= $curso_id ?>">+ Nova aula neste módulo</a>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
