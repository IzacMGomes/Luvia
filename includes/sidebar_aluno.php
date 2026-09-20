<?php
$pagina_ativa = $pagina_ativa ?? '';
?>
<div class="sidebar">
    <div class="marca">Trilha<span></span></div>
    <nav>
        <a href="dashboard_aluno.php" class="<?= $pagina_ativa === 'dashboard' ? 'ativo' : '' ?>">📖 Meus cursos</a>
        <a href="catalogo.php" class="<?= $pagina_ativa === 'catalogo' ? 'ativo' : '' ?>">🔍 Explorar</a>
        <a href="historico.php" class="<?= $pagina_ativa === 'historico' ? 'ativo' : '' ?>">📜 Certificados</a>
        <a href="favoritos.php" class="<?= $pagina_ativa === 'favoritos' ? 'ativo' : '' ?>">♡ Favoritos</a>
        <a href="perfil.php" class="<?= $pagina_ativa === 'perfil' ? 'ativo' : '' ?>">⚙ Meu perfil</a>
    </nav>
    <div class="rodape-sidebar">
        <span class="usuario"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
        <a href="logout.php">Sair da conta</a>
    </div>
</div>
