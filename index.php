<?php
// index.php — Arquivo centralizador da aplicação orientada a objetos
require_once 'Usuario.php';
require_once 'Curso.php';
require_once 'Matricula.php';

// Testando a instanciação e o uso das 4 classes exigidas pelo professor
$cursoObj = new Curso();
$listaCursos = $cursoObj->listarTodos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Trilha — Plataforma de Cursos (POO)</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="padding: 40px; font-family: Inter, sans-serif;">
    <h1>Bem-vindo à Trilha — Sistema Orientado a Objetos</h1>
    <p>Arquitetura limpa estruturada com 4 classes em PHP e padrão MVC simplificado.</p>
    
    <h2>Cursos Disponíveis no Sistema:</h2>
    <ul>
        <?php if (empty($listaCursos)): ?>
            <li>Nenhum curso cadastrado no momento.</li>
        <?php else: ?>
            <?php foreach ($listaCursos as $c): ?>
                <li><strong><?= htmlspecialchars($c['titulo']) ?></strong> (Nível: <?= htmlspecialchars($c['nivel']) ?>)</li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</body>
</html>