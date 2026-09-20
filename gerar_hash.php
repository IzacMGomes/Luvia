<?php
// gerar_hash.php
// Use este script UMA VEZ para criar o primeiro administrador do sistema.
// Depois de usar, apague este arquivo do servidor por segurança.

require_once 'config.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($nome && $email && $senha) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nome, $email, $hash, 'administrador']);
        $mensagem = 'Administrador criado com sucesso! Agora apague este arquivo (gerar_hash.php) do servidor.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Criar Administrador</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;">
    <div class="cartao" style="max-width:380px;width:100%;">
        <h2>Criar administrador inicial</h2>
        <p style="color:var(--muted);font-size:13px;">Use este formulário só uma vez. Depois, apague este arquivo do servidor.</p>
        <?php if ($mensagem): ?><p class="mensagem-sucesso"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
        <form method="POST">
            <div class="campo"><label>Nome</label><input type="text" name="nome" required></div>
            <div class="campo"><label>E-mail</label><input type="email" name="email" required></div>
            <div class="campo"><label>Senha</label><input type="password" name="senha" required></div>
            <button type="submit" class="botao destaque">Criar administrador</button>
        </form>
    </div>
</body>
</html>
