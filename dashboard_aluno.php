<?php
require_once 'auth.php';
exigir_login('aluno');
require_once 'config.php';

$pagina_ativa = 'dashboard';
$aluno_id = $_SESSION['usuario_id'];

// Cursos em que o aluno está matriculado
$stmt = $pdo->prepare('SELECT c.* FROM cursos c INNER JOIN matriculas m ON m.curso_id = c.id WHERE m.aluno_id = ? ORDER BY m.data_matricula DESC');
$stmt->execute([$aluno_id]);
$cursos = $stmt->fetchAll();

$total_aulas_geral = 0;
$total_concluidas_geral = 0;

foreach ($cursos as &$curso) {
    $totalAulas = $pdo->prepare('SELECT COUNT(*) FROM aulas a INNER JOIN curso_modulos cm ON cm.id = a.modulo_id WHERE cm.curso_id = ?');
    $totalAulas->execute([$curso['id']]);
    $total = (int) $totalAulas->fetchColumn();

    $concluidas = $pdo->prepare('SELECT COUNT(*) FROM progresso_aulas p INNER JOIN aulas a ON a.id = p.aula_id INNER JOIN curso_modulos cm ON cm.id = a.modulo_id WHERE cm.curso_id = ? AND p.aluno_id = ? AND p.concluida = 1');
    $concluidas->execute([$curso['id'], $aluno_id]);
    $feitas = (int) $concluidas->fetchColumn();

    $curso['progresso'] = $total > 0 ? round(($feitas / $total) * 100) : 0;
    $curso['total_aulas'] = $total;
    $curso['feitas'] = $feitas;

    $total_aulas_geral += $total;
    $total_concluidas_geral += $feitas;
}
unset($curso);

// Estatísticas gerais
$total_cursos = count($cursos);
$certificados = count(array_filter($cursos, fn($c) => $c['total_aulas'] > 0 && $c['progresso'] >= 100));
$progresso_geral = $total_aulas_geral > 0 ? round(($total_concluidas_geral / $total_aulas_geral) * 100) : 0;

$stmt = $pdo->prepare('SELECT COUNT(*) FROM favoritos WHERE aluno_id = ?');
$stmt->execute([$aluno_id]);
$total_favoritos = (int) $stmt->fetchColumn();

// Cursos em andamento (não concluídos ainda), pra seção "Continuar estudando"
$em_andamento = array_values(array_filter($cursos, fn($c) => $c['total_aulas'] > 0 && $c['progresso'] < 100));

// Foto de perfil pra barra de topo
$stmt = $pdo->prepare('SELECT foto_perfil FROM usuarios WHERE id = ?');
$stmt->execute([$aluno_id]);
$foto_perfil = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Meus Cursos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    .colunas-dashboard { display: grid; grid-template-columns: 1.7fr 1fr; gap: 20px; align-items: start; }
    @media (max-width: 980px) { .colunas-dashboard { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_aluno.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div>
                <h1>Olá, <?= htmlspecialchars(explode(' ', $_SESSION['usuario_nome'])[0]) ?>! 👋</h1>
                <p class="subtitulo">Continue sua jornada de aprendizado e conquiste novas habilidades.</p>
            </div>
            <div class="barra-topo">
                <form class="busca" method="GET" action="catalogo.php">
                    <span>🔍</span>
                    <input type="text" name="busca" placeholder="Buscar cursos...">
                </form>
                <a class="icone-topo" href="favoritos.php" title="Favoritos">🔔<span class="ponto" style="display:<?= $total_favoritos ? 'block' : 'none' ?>;"></span></a>
                <?php if (!empty($foto_perfil)): ?>
                    <img class="avatar-topo" src="uploads/perfil/<?= htmlspecialchars($foto_perfil) ?>" alt="">
                <?php else: ?>
                    <div class="avatar-topo-vazio"><?= htmlspecialchars(strtoupper(substr($_SESSION['usuario_nome'], 0, 1))) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grade-estat">
            <div class="cartao estat-cartao">
                <div class="estat-icone azul">📘</div>
                <div class="estat-rotulo-topo">Cursos matriculados</div>
                <div class="estat-valor"><?= $total_cursos ?></div>
                <div class="estat-rotulo-baixo">Cursos ativos</div>
            </div>
            <div class="cartao estat-cartao">
                <div class="estat-icone roxo">🏆</div>
                <div class="estat-rotulo-topo">Certificados</div>
                <div class="estat-valor"><?= $certificados ?></div>
                <div class="estat-rotulo-baixo">Continue assim!</div>
            </div>
            <div class="cartao estat-cartao">
                <div class="estat-icone azul">✅</div>
                <div class="estat-rotulo-topo">Aulas concluídas</div>
                <div class="estat-valor"><?= $total_concluidas_geral ?></div>
                <div class="estat-rotulo-baixo">de <?= $total_aulas_geral ?> no total</div>
            </div>
            <div class="cartao estat-cartao">
                <div class="estat-icone roxo">⭐</div>
                <div class="estat-rotulo-topo">Favoritos</div>
                <div class="estat-valor"><?= $total_favoritos ?></div>
                <div class="estat-rotulo-baixo">Cursos salvos</div>
            </div>
        </div>

        <?php if (empty($cursos)): ?>
            <div class="cartao estado-vazio-ilustrado">
                <div class="icone">📚</div>
                <h2>Nenhum curso iniciado ainda</h2>
                <p>Você ainda não se matriculou em nenhum curso. Explore nosso catálogo e comece sua jornada de aprendizado.</p>
                <a class="botao destaque" href="catalogo.php">Explorar catálogo →</a>
            </div>
        <?php else: ?>
        <div class="colunas-dashboard">
            <div class="cartao">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h2 style="font-size:17px; margin:0;">Continuar estudando</h2>
                    <a href="catalogo.php" style="font-size:13px; color:var(--accent); font-weight:600; text-decoration:none;">Ver catálogo →</a>
                </div>
                <div class="lista-continuar">
                    <?php if (empty($em_andamento)): ?>
                        <p class="vazio">Todos os seus cursos matriculados estão concluídos. 🎉</p>
                    <?php endif; ?>
                    <?php foreach ($em_andamento as $curso): ?>
                    <div class="item">
                        <?php if (!empty($curso['capa_imagem'])): ?>
                            <img class="capa-mini" src="uploads/capas/<?= htmlspecialchars($curso['capa_imagem']) ?>" alt="">
                        <?php else: ?>
                            <div class="capa-mini-vazia"><?= htmlspecialchars(mb_substr($curso['titulo'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <div class="info">
                            <h4><?= htmlspecialchars($curso['titulo']) ?></h4>
                            <div class="categoria"><?= htmlspecialchars($curso['categoria'] ?: ucfirst($curso['nivel'])) ?> · <?= ucfirst($curso['nivel']) ?></div>
                            <div class="barra-mini"><div class="barra-mini-preenchida" style="width:<?= $curso['progresso'] ?>%;"></div></div>
                            <div class="pct"><?= $curso['progresso'] ?>% concluído</div>
                        </div>
                        <a class="play" href="curso_ver.php?id=<?= $curso['id'] ?>">▶</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="cartao">
                <h2 style="font-size:17px; margin-bottom:6px;">Progresso geral</h2>
                <div class="donut-wrap">
                    <div class="donut" style="--pct: <?= $progresso_geral ?>;">
                        <div class="donut-centro">
                            <div class="pct-grande"><?= $progresso_geral ?>%</div>
                            <div class="pct-legenda">Concluído</div>
                        </div>
                    </div>
                    <div class="legenda-progresso">
                        <div class="linha"><span class="bolinha roxo"></span> <?= $total_concluidas_geral ?> aulas concluídas</div>
                        <div class="linha"><span class="bolinha cinza"></span> <?= max($total_aulas_geral - $total_concluidas_geral, 0) ?> aulas restantes</div>
                        <div class="linha"><span class="bolinha azul"></span> <?= $certificados ?> curso(s) concluído(s)</div>
                    </div>
                </div>
                <div class="aviso-motivacional">⭐ <?= $progresso_geral >= 50 ? 'Bom progresso! Continue assim.' : 'Continue estudando, você está indo bem!' ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
