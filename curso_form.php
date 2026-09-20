<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$pagina_ativa = 'cursos';
$curso = ['id' => null, 'titulo' => '', 'descricao' => '', 'categoria' => '', 'nivel' => 'iniciante', 'capa_imagem' => null];
$erro = '';
$editando = false;

if (isset($_GET['id'])) {
    $editando = true;
    $stmt = $pdo->prepare('SELECT * FROM cursos WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $encontrado = $stmt->fetch();
    if ($encontrado) $curso = $encontrado;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo    = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $nivel     = $_POST['nivel'] ?? 'iniciante';
    $id        = $_POST['id'] ?? null;

    if ($titulo === '') {
        $erro = 'O título do curso é obrigatório.';
    } else {
        $nome_imagem = $id ? ($curso['capa_imagem'] ?? null) : null;
        if (!empty($_FILES['capa']['name'])) {
            $pasta_destino = __DIR__ . '/uploads/capas/';
            if (!is_dir($pasta_destino)) mkdir($pasta_destino, 0777, true);
            $extensao = strtolower(pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION));
            if (in_array($extensao, ['jpg', 'jpeg', 'png', 'webp'])) {
                $nome_imagem = uniqid('capa_') . '.' . $extensao;
                move_uploaded_file($_FILES['capa']['tmp_name'], $pasta_destino . $nome_imagem);
            }
        }

        if ($id) {
            $stmt = $pdo->prepare('UPDATE cursos SET titulo=?, descricao=?, categoria=?, nivel=?, capa_imagem=? WHERE id=?');
            $stmt->execute([$titulo, $descricao, $categoria, $nivel, $nome_imagem, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO cursos (titulo, descricao, categoria, nivel, capa_imagem, criado_por) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$titulo, $descricao, $categoria, $nivel, $nome_imagem, $_SESSION['usuario_id']]);
        }
        header('Location: cursos.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title><?= $editando ? 'Editar Curso' : 'Novo Curso' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    img.previa-capa { max-width: 160px; display: block; margin-top: 10px; border-radius: 7px; border: 1px solid var(--line); }
</style>
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_admin.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div>
                <h1><?= $editando ? 'Editar curso' : 'Novo curso' ?></h1>
                <p class="subtitulo"><a href="cursos.php">&larr; Voltar para a lista de cursos</a></p>
            </div>
        </div>

        <div class="cartao" style="max-width:560px;">
            <?php if ($erro): ?><p class="mensagem-erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars($curso['id'] ?? '') ?>">

                <div class="campo">
                    <label>Título do curso</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($curso['titulo']) ?>" required>
                </div>
                <div class="campo">
                    <label>Descrição</label>
                    <textarea name="descricao"><?= htmlspecialchars($curso['descricao']) ?></textarea>
                </div>
                <div class="campo">
                    <label>Categoria</label>
                    <input type="text" name="categoria" placeholder="Ex: Programação, Marketing, Design..." value="<?= htmlspecialchars($curso['categoria']) ?>">
                </div>
                <div class="campo">
                    <label>Nível</label>
                    <select name="nivel">
                        <option value="iniciante" <?= $curso['nivel'] === 'iniciante' ? 'selected' : '' ?>>Iniciante</option>
                        <option value="intermediario" <?= $curso['nivel'] === 'intermediario' ? 'selected' : '' ?>>Intermediário</option>
                        <option value="avancado" <?= $curso['nivel'] === 'avancado' ? 'selected' : '' ?>>Avançado</option>
                    </select>
                </div>
                <div class="campo">
                    <label>Imagem de capa</label>
                    <input type="file" name="capa" accept="image/*">
                    <?php if (!empty($curso['capa_imagem'])): ?>
                        <img class="previa-capa" src="uploads/capas/<?= htmlspecialchars($curso['capa_imagem']) ?>" alt="Capa atual">
                    <?php endif; ?>
                </div>

                <button type="submit" class="botao destaque"><?= $editando ? 'Salvar alterações' : 'Criar curso' ?></button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
