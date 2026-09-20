<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$pagina_ativa = 'matriculas';

$stmt = $pdo->query('
    SELECT m.id, m.data_matricula, u.nome AS aluno_nome, u.email AS aluno_email, c.titulo AS curso_titulo
    FROM matriculas m
    INNER JOIN usuarios u ON u.id = m.aluno_id
    INNER JOIN cursos c ON c.id = m.curso_id
    ORDER BY m.data_matricula DESC
');
$matriculas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Matrículas</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_admin.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div><h1>Matrículas</h1><p class="subtitulo">Todas as matrículas realizadas na plataforma.</p></div>
        </div>
        <div class="cartao">
            <?php if (empty($matriculas)): ?>
                <p class="vazio">Nenhuma matrícula realizada ainda.</p>
            <?php else: ?>
            <table class="tabela">
                <tr><th>Aluno</th><th>E-mail</th><th>Curso</th><th>Data</th><th>Ações</th></tr>
                <?php foreach ($matriculas as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['aluno_nome']) ?></td>
                    <td><?= htmlspecialchars($m['aluno_email']) ?></td>
                    <td><?= htmlspecialchars($m['curso_titulo']) ?></td>
                    <td><?= date('d/m/Y', strtotime($m['data_matricula'])) ?></td>
                    <td class="acoes-linha"><a class="excluir" href="matricula_cancelar.php?id=<?= $m['id'] ?>" onclick="return confirm('Cancelar esta matrícula?')">Cancelar</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
