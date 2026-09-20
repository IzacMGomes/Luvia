<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$pagina_ativa = 'cursos';
$cursos = $pdo->query('SELECT * FROM cursos ORDER BY criado_em DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Cursos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_admin.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div>
                <h1>Cursos</h1>
                <p class="subtitulo">Crie, edite e publique os cursos da plataforma.</p>
            </div>
            <a class="botao destaque" href="curso_form.php">+ Novo curso</a>
        </div>

        <div class="cartao">
            <?php if (empty($cursos)): ?>
                <p class="vazio">Nenhum curso cadastrado ainda. Clique em "+ Novo curso" para começar.</p>
            <?php else: ?>
            <table class="tabela">
                <tr>
                    <th>Título</th>
                    <th>Categoria</th>
                    <th>Nível</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
                <?php foreach ($cursos as $curso): ?>
                <tr>
                    <td><?= htmlspecialchars($curso['titulo']) ?></td>
                    <td><?= htmlspecialchars($curso['categoria'] ?: '-') ?></td>
                    <td><?= htmlspecialchars(ucfirst($curso['nivel'])) ?></td>
                    <td><span class="selo <?= $curso['status'] ?>"><?= $curso['status'] === 'publicado' ? 'Publicado' : 'Rascunho' ?></span></td>
                    <td class="acoes-linha">
                        <a href="curso_form.php?id=<?= $curso['id'] ?>">Editar</a>
                        <a href="modulos.php?curso_id=<?= $curso['id'] ?>">Módulos/Aulas</a>
                        <a href="curso_ver.php?id=<?= $curso['id'] ?>">Pré-visualizar</a>
                        <a href="curso_publicar.php?id=<?= $curso['id'] ?>"><?= $curso['status'] === 'publicado' ? 'Despublicar' : 'Publicar' ?></a>
                        <a class="excluir" href="curso_excluir.php?id=<?= $curso['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este curso?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
