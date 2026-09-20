<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$pagina_ativa = 'alunos';
$aluno_id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ? AND tipo = "aluno"');
$stmt->execute([$aluno_id]);
$aluno = $stmt->fetch();
if (!$aluno) { header('Location: alunos.php'); exit; }

$stmt = $pdo->prepare('SELECT c.* FROM cursos c INNER JOIN matriculas m ON m.curso_id = c.id WHERE m.aluno_id = ?');
$stmt->execute([$aluno_id]);
$cursos = $stmt->fetchAll();

foreach ($cursos as &$curso) {
    $t = $pdo->prepare('SELECT COUNT(*) FROM aulas a INNER JOIN curso_modulos cm ON cm.id = a.modulo_id WHERE cm.curso_id = ?');
    $t->execute([$curso['id']]);
    $total = (int) $t->fetchColumn();
    $c = $pdo->prepare('SELECT COUNT(*) FROM progresso_aulas p INNER JOIN aulas a ON a.id = p.aula_id INNER JOIN curso_modulos cm ON cm.id = a.modulo_id WHERE cm.curso_id = ? AND p.aluno_id = ? AND p.concluida = 1');
    $c->execute([$curso['id'], $aluno_id]);
    $feitas = (int) $c->fetchColumn();
    $curso['progresso'] = $total > 0 ? round(($feitas / $total) * 100) : 0;
}
unset($curso);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Progresso de <?= htmlspecialchars($aluno['nome']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_admin.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div><h1><?= htmlspecialchars($aluno['nome']) ?></h1><p class="subtitulo"><a href="alunos.php">&larr; Voltar</a> · <?= htmlspecialchars($aluno['email']) ?></p></div>
        </div>
        <div class="cartao">
            <?php if (empty($cursos)): ?>
                <p class="vazio">Este aluno ainda não está matriculado em nenhum curso.</p>
            <?php else: ?>
            <table class="tabela">
                <tr><th>Curso</th><th>Progresso</th></tr>
                <?php foreach ($cursos as $curso): ?>
                <tr><td><?= htmlspecialchars($curso['titulo']) ?></td><td><?= $curso['progresso'] ?>%</td></tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
