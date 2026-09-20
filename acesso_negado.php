<?php require_once 'auth.php'; exigir_login(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Acesso Negado</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;">
    <div class="cartao" style="max-width:380px;text-align:center;">
        <h2>Acesso negado</h2>
        <p style="color:var(--muted);">Você não tem permissão para acessar esta página.</p>
        <a class="botao destaque" href="logout.php">Voltar ao login</a>
    </div>
</body>
</html>
