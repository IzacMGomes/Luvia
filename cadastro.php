<?php
require_once 'config.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = 'Preencha todos os campos.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nome, $email, $hash, 'aluno']);
            $sucesso = 'Conta criada com sucesso! Você já pode fazer login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Criar Conta - Plataforma de Cursos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-shell">
    <div class="marca-topo">Trilha</div>
    <div class="auth-card">
        <h1>Criar conta</h1>
        <p class="ajuda">Leva menos de um minuto.</p>
        <?php if ($erro): ?><p class="mensagem-erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
        <?php if ($sucesso): ?><p class="mensagem-sucesso"><?= htmlspecialchars($sucesso) ?></p><?php endif; ?>
        <form method="POST" action="cadastro.php">
            <div class="campo">
                <label>Nome completo</label>
                <input type="text" name="nome" required autofocus>
            </div>
            <div class="campo">
                <label>E-mail</label>
                <input type="email" name="email" required>
            </div>
            <div class="campo">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="Mínimo 6 caracteres" required>
            </div>
            <button type="submit" class="botao destaque">Cadastrar</button>
        </form>
        <p class="link-baixo">Já tem conta? <a href="login.php">Entrar</a></p>
    </div>
</div>
</body>
</html>
