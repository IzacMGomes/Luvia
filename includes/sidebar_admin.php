<?php
$pagina_ativa = $pagina_ativa ?? '';
require_once __DIR__ . '/../config.php';
$stmtFoto = $pdo->prepare('SELECT foto_perfil FROM usuarios WHERE id = ?');
$stmtFoto->execute([$_SESSION['usuario_id']]);
$foto = $stmtFoto->fetchColumn();
?>
<div class="sidebar">
    <div class="marca">Trilha<span></span></div>
    <nav>
        <a href="dashboard_admin.php" class="<?= $pagina_ativa === 'dashboard' ? 'ativo' : '' ?>">🏠 Dashboard</a>
        <a href="cursos.php" class="<?= $pagina_ativa === 'cursos' ? 'ativo' : '' ?>">📚 Cursos</a>
        <a href="alunos.php" class="<?= $pagina_ativa === 'alunos' ? 'ativo' : '' ?>">🎓 Alunos</a>
        <a href="matriculas.php" class="<?= $pagina_ativa === 'matriculas' ? 'ativo' : '' ?>">📝 Matrículas</a>
        <a href="perfil.php" class="<?= $pagina_ativa === 'perfil' ? 'ativo' : '' ?>">⚙ Meu perfil</a>
    </nav>
    <div class="rodape-sidebar">
        <span class="usuario"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
        <a href="logout.php">Sair da conta</a>
    </div>
</div>
