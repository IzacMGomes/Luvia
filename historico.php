<?php
require_once 'auth.php';
exigir_login('aluno');
require_once 'config.php';

$pagina_ativa = 'historico';
$aluno_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare('SELECT c.* FROM cursos c INNER JOIN matriculas m ON m.curso_id = c.id WHERE m.aluno_id = ?');
$stmt->execute([$aluno_id]);
$todos = $stmt->fetchAll();

$concluidos = [];
foreach ($todos as $curso) {
    $totalAulas = $pdo->prepare('SELECT COUNT(*) FROM aulas a INNER JOIN curso_modulos cm ON cm.id = a.modulo_id WHERE cm.curso_id = ?');
    $totalAulas->execute([$curso['id']]);
    $total = (int) $totalAulas->fetchColumn();
    if ($total === 0) continue;

    $concluidas = $pdo->prepare('SELECT COUNT(*), MAX(concluida_em) FROM progresso_aulas p INNER JOIN aulas a ON a.id = p.aula_id INNER JOIN curso_modulos cm ON cm.id = a.modulo_id WHERE cm.curso_id = ? AND p.aluno_id = ? AND p.concluida = 1');
    $concluidas->execute([$curso['id'], $aluno_id]);
    [$feitas, $data_conclusao] = $concluidas->fetch(PDO::FETCH_NUM);

    if ((int) $feitas === $total) {
        $curso['data_conclusao'] = $data_conclusao;
        $concluidos[] = $curso;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Histórico</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_aluno.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div><h1>Histórico de cursos concluídos</h1><p class="subtitulo">Cursos que você já terminou 100%.</p></div>
        </div>
        <div class="cartao">
            <?php if (empty($concluidos)): ?>
                <p class="vazio">Você ainda não concluiu nenhum curso.</p>
            <?php else: ?>
            <table class="tabela">
                <tr><th>Curso</th><th>Concluído em</th><th>Ações</th></tr>
                <?php foreach ($concluidos as $curso): ?>
                <tr>
                    <td><?= htmlspecialchars($curso['titulo']) ?></td>
                    <td><?= $curso['data_conclusao'] ? date('d/m/Y', strtotime($curso['data_conclusao'])) : '-' ?></td>
                    <td class="acoes-linha">
                        <a href="curso_ver.php?id=<?= $curso['id'] ?>">Rever curso</a>
                        <a href="certificado.php?curso_id=<?= $curso['id'] ?>">Ver certificado</a>
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
