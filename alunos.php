<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$pagina_ativa = 'alunos';
$busca = trim($_GET['busca'] ?? '');

$sql = "SELECT * FROM usuarios WHERE tipo = 'aluno'";
$params = [];
if ($busca !== '') {
    $sql .= " AND (nome LIKE ? OR email LIKE ?)";
    $params[] = '%' . $busca . '%';
    $params[] = '%' . $busca . '%';
}
$sql .= " ORDER BY nome";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alunos = $stmt->fetchAll();

// Conta quantos cursos cada aluno tem matrícula
foreach ($alunos as &$aluno) {
    $c = $pdo->prepare('SELECT COUNT(*) FROM matriculas WHERE aluno_id = ?');
    $c->execute([$aluno['id']]);
    $aluno['total_cursos'] = (int) $c->fetchColumn();
}
unset($aluno);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Alunos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    .barra-filtros { display: flex; gap: 10px; margin-bottom: 18px; }
    .barra-filtros input { flex: 1; max-width: 320px; padding: 9px 12px; border: 1px solid var(--line); border-radius: 7px; font-family: inherit; font-size: 14px; }
</style>
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_admin.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div><h1>Alunos</h1><p class="subtitulo">Gerencie os alunos cadastrados na plataforma.</p></div>
        </div>

        <form method="GET" class="barra-filtros">
            <input type="text" name="busca" placeholder="Buscar por nome ou e-mail..." value="<?= htmlspecialchars($busca) ?>">
            <button type="submit" class="botao destaque">Buscar</button>
        </form>

        <div class="cartao">
            <?php if (empty($alunos)): ?>
                <p class="vazio">Nenhum aluno encontrado.</p>
            <?php else: ?>
            <table class="tabela">
                <tr><th>Nome</th><th>E-mail</th><th>Cursos</th><th>Status</th><th>Ações</th></tr>
                <?php foreach ($alunos as $aluno): ?>
                <tr>
                    <td><?= htmlspecialchars($aluno['nome']) ?></td>
                    <td><?= htmlspecialchars($aluno['email']) ?></td>
                    <td><?= $aluno['total_cursos'] ?></td>
                    <td><span class="selo <?= $aluno['ativo'] ? 'publicado' : 'rascunho' ?>"><?= $aluno['ativo'] ? 'Ativo' : 'Bloqueado' ?></span></td>
                    <td class="acoes-linha">
                        <a href="aluno_progresso.php?id=<?= $aluno['id'] ?>">Ver progresso</a>
                        <a href="aluno_status.php?id=<?= $aluno['id'] ?>" onclick="return confirm('<?= $aluno['ativo'] ? 'Bloquear' : 'Reativar' ?> esse aluno?')"><?= $aluno['ativo'] ? 'Bloquear' : 'Reativar' ?></a>
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
