<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$pagina_ativa = 'cursos';
$curso_id = (int) ($_GET['curso_id'] ?? $_POST['curso_id'] ?? 0);
$modulo = ['id' => null, 'titulo' => '', 'ordem' => 0];
$editando = false;

if (isset($_GET['id'])) {
    $editando = true;
    $stmt = $pdo->prepare('SELECT * FROM curso_modulos WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $encontrado = $stmt->fetch();
    if ($encontrado) $modulo = $encontrado;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $ordem  = (int) ($_POST['ordem'] ?? 0);
    $id     = $_POST['id'] ?? null;

    if ($titulo !== '') {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE curso_modulos SET titulo=?, ordem=? WHERE id=?');
            $stmt->execute([$titulo, $ordem, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO curso_modulos (curso_id, titulo, ordem) VALUES (?, ?, ?)');
            $stmt->execute([$curso_id, $titulo, $ordem]);
        }
        header('Location: modulos.php?curso_id=' . $curso_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title><?= $editando ? 'Editar Módulo' : 'Novo Módulo' ?></title>
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
                <h1><?= $editando ? 'Editar módulo' : 'Novo módulo' ?></h1>
                <p class="subtitulo"><a href="modulos.php?curso_id=<?= $curso_id ?>">&larr; Voltar</a></p>
            </div>
        </div>
        <div class="cartao" style="max-width:440px;">
            <form method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($modulo['id'] ?? '') ?>">
                <input type="hidden" name="curso_id" value="<?= $curso_id ?>">
                <div class="campo">
                    <label>Título do módulo</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($modulo['titulo']) ?>" required>
                </div>
                <div class="campo">
                    <label>Ordem de exibição (0 = primeiro)</label>
                    <input type="number" name="ordem" value="<?= htmlspecialchars($modulo['ordem']) ?>">
                </div>
                <button type="submit" class="botao destaque"><?= $editando ? 'Salvar alterações' : 'Criar módulo' ?></button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
