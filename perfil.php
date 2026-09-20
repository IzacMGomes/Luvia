<?php
require_once 'auth.php';
exigir_login(); // qualquer usuário logado, aluno ou administrador
require_once 'config.php';

$pagina_ativa = 'perfil';
$erro = '';
$sucesso = '';

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome        = trim($_POST['nome'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $senha_atual = $_POST['senha_atual'] ?? '';
    $senha_nova  = $_POST['senha_nova'] ?? '';

    if ($nome === '' || $email === '') {
        $erro = 'Nome e e-mail são obrigatórios.';
    } else {
        // Se o e-mail mudou, confirma que não está em uso por outra conta
        if ($email !== $usuario['email']) {
            $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
            $check->execute([$email, $usuario['id']]);
            if ($check->fetch()) {
                $erro = 'Este e-mail já está sendo usado por outra conta.';
            }
        }

        if ($erro === '') {
            // Trata upload da foto de perfil
            $foto_perfil = $usuario['foto_perfil'];
            if (!empty($_FILES['foto']['name'])) {
                $pasta = __DIR__ . '/uploads/perfil/';
                if (!is_dir($pasta)) mkdir($pasta, 0777, true);
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $foto_perfil = uniqid('perfil_') . '.' . $ext;
                    move_uploaded_file($_FILES['foto']['tmp_name'], $pasta . $foto_perfil);
                }
            }

            // Se o usuário quis trocar a senha, confirma a senha atual primeiro
            if ($senha_nova !== '') {
                if (!password_verify($senha_atual, $usuario['senha'])) {
                    $erro = 'Senha atual incorreta. A senha não foi alterada.';
                } elseif (strlen($senha_nova) < 6) {
                    $erro = 'A nova senha deve ter pelo menos 6 caracteres.';
                } else {
                    $hash = password_hash($senha_nova, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE usuarios SET nome=?, email=?, senha=?, foto_perfil=? WHERE id=?');
                    $stmt->execute([$nome, $email, $hash, $foto_perfil, $usuario['id']]);
                }
            } else {
                $stmt = $pdo->prepare('UPDATE usuarios SET nome=?, email=?, foto_perfil=? WHERE id=?');
                $stmt->execute([$nome, $email, $foto_perfil, $usuario['id']]);
            }

            if ($erro === '') {
                $_SESSION['usuario_nome'] = $nome;
                $sucesso = 'Perfil atualizado com sucesso!';
                // Recarrega os dados atualizados
                $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
                $stmt->execute([$usuario['id']]);
                $usuario = $stmt->fetch();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Meu Perfil</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    .avatar-atual { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; background: var(--bg-soft); border: 1px solid var(--line); display: block; margin-bottom: 14px; }
    .avatar-vazio { width: 72px; height: 72px; border-radius: 50%; background: var(--bg-soft); border: 1px solid var(--line); color: var(--ink); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 24px; margin-bottom: 14px; }
    .selo-tipo { display: inline-block; padding: 3px 10px; border-radius: 100px; font-size: 12px; font-weight: 600; background: var(--draft-bg); color: var(--navy-800); margin-bottom: 18px; }
    .divisor { border: none; border-top: 1px solid var(--line); margin: 24px 0; }
</style>
</head>
<body>
<div class="app">
    <?php include 'includes/' . ($_SESSION['usuario_tipo'] === 'administrador' ? 'sidebar_admin.php' : 'sidebar_aluno.php'); ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div>
                <h1>Meu perfil</h1>
                <p class="subtitulo">Atualize seus dados pessoais e sua senha de acesso.</p>
            </div>
        </div>

        <div class="cartao" style="max-width:480px;">
            <?php if (!empty($usuario['foto_perfil'])): ?>
                <img class="avatar-atual" src="uploads/perfil/<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
            <?php else: ?>
                <div class="avatar-vazio"><?= htmlspecialchars(strtoupper(substr($usuario['nome'], 0, 1))) ?></div>
            <?php endif; ?>

            <span class="selo-tipo"><?= $usuario['tipo'] === 'administrador' ? 'Administrador' : 'Aluno' ?></span>

            <?php if ($erro): ?><p class="mensagem-erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
            <?php if ($sucesso): ?><p class="mensagem-sucesso"><?= htmlspecialchars($sucesso) ?></p><?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="campo">
                    <label>Foto de perfil</label>
                    <input type="file" name="foto" accept="image/*">
                </div>
                <div class="campo">
                    <label>Nome completo</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                </div>
                <div class="campo">
                    <label>E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                </div>

                <hr class="divisor">
                <p style="font-size:13px; color:var(--muted); margin-top:0;">Preencha os campos abaixo apenas se quiser trocar sua senha.</p>

                <div class="campo">
                    <label>Senha atual</label>
                    <input type="password" name="senha_atual" placeholder="Necessária só para trocar a senha">
                </div>
                <div class="campo">
                    <label>Nova senha</label>
                    <input type="password" name="senha_nova" placeholder="Deixe em branco para manter a atual">
                </div>

                <button type="submit" class="botao destaque">Salvar alterações</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
