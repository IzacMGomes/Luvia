<?php
session_start();
require_once 'config.php';

$erro = '';

if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['usuario_tipo'] === 'administrador') {
        header('Location: dashboard_admin.php');
    } else {
        header('Location: dashboard_aluno.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nome, email, senha, tipo, ativo FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            if ((int) $usuario['ativo'] === 0) {
                $erro = 'Sua conta está bloqueada. Fale com um administrador.';
            } else {
                session_regenerate_id(true);
                $_SESSION['usuario_id']   = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_tipo'] = $usuario['tipo'];

                if ($usuario['tipo'] === 'administrador') {
                    header('Location: dashboard_admin.php');
                } else {
                    header('Location: dashboard_aluno.php');
                }
                exit;
            }
        } else {
            $erro = 'E-mail ou senha inválidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Entrar - Plataforma de Cursos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-shell">
    <div class="marca-topo">Trilha</div>
    <div class="auth-card">
        <h1>Entrar</h1>
        <p class="ajuda">Acesse sua conta para continuar.</p>
        <?php if ($erro): ?><p class="mensagem-erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
        <form method="POST" action="login.php">
            <div class="campo">
                <label>E-mail</label>
                <input type="email" name="email" required autofocus>
            </div>
            <div class="campo">
                <label>Senha</label>
                <input type="password" name="senha" required>
            </div>
            <button type="submit" class="botao destaque">Entrar</button>
        </form>
        <p class="link-baixo">Ainda não tem conta? <a href="cadastro.php">Criar conta de aluno</a></p>
    </div>
</div>
</body>
</html>
