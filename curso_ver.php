<?php
require_once 'auth.php';
exigir_login();
require_once 'config.php';

$pagina_ativa = 'dashboard';
$eh_admin = $_SESSION['usuario_tipo'] === 'administrador';
$aluno_id = $_SESSION['usuario_id'];

if (!isset($_GET['id'])) { header('Location: dashboard_aluno.php'); exit; }
$curso_id = (int) $_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM cursos WHERE id = ?');
$stmt->execute([$curso_id]);
$curso = $stmt->fetch();
if (!$curso) { header('Location: dashboard_aluno.php'); exit; }

if ($curso['status'] !== 'publicado' && !$eh_admin) { header('Location: dashboard_aluno.php'); exit; }

// Aluno precisa estar matriculado (administrador pode pré-visualizar sem matrícula)
$matriculado = true;
if (!$eh_admin) {
    $stmt = $pdo->prepare('SELECT id FROM matriculas WHERE aluno_id = ? AND curso_id = ?');
    $stmt->execute([$aluno_id, $curso_id]);
    $matriculado = (bool) $stmt->fetch();
    if (!$matriculado) { header('Location: catalogo.php'); exit; }
}

// Marcar aula como concluída/reabrir (só aluno matriculado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aula_id']) && !$eh_admin) {
    $aula_id_post = (int) $_POST['aula_id'];
    $concluir = isset($_POST['concluir']) && $_POST['concluir'] === '1';
    $stmt = $pdo->prepare('SELECT id FROM progresso_aulas WHERE aluno_id = ? AND aula_id = ?');
    $stmt->execute([$aluno_id, $aula_id_post]);
    if ($stmt->fetch()) {
        $upd = $pdo->prepare('UPDATE progresso_aulas SET concluida = ?, concluida_em = ? WHERE aluno_id = ? AND aula_id = ?');
        $upd->execute([$concluir ? 1 : 0, $concluir ? date('Y-m-d H:i:s') : null, $aluno_id, $aula_id_post]);
    } else {
        $ins = $pdo->prepare('INSERT INTO progresso_aulas (aluno_id, aula_id, concluida, concluida_em) VALUES (?, ?, ?, ?)');
        $ins->execute([$aluno_id, $aula_id_post, $concluir ? 1 : 0, $concluir ? date('Y-m-d H:i:s') : null]);
    }
    header('Location: curso_ver.php?id=' . $curso_id . '&aula=' . $aula_id_post);
    exit;
}

// Carrega módulos + aulas + progresso de cada aula
$stmt = $pdo->prepare('SELECT * FROM curso_modulos WHERE curso_id = ? ORDER BY ordem, id');
$stmt->execute([$curso_id]);
$modulos = $stmt->fetchAll();

$total_aulas = 0;
$total_concluidas = 0;
$primeira_aula = null;
$ultima_vista = null;

foreach ($modulos as &$modulo) {
    $stmtAulas = $pdo->prepare('SELECT * FROM aulas WHERE modulo_id = ? ORDER BY ordem, id');
    $stmtAulas->execute([$modulo['id']]);
    $modulo['aulas'] = $stmtAulas->fetchAll();

    foreach ($modulo['aulas'] as &$aula) {
        $stmtP = $pdo->prepare('SELECT concluida, visto_em FROM progresso_aulas WHERE aluno_id = ? AND aula_id = ?');
        $stmtP->execute([$aluno_id, $aula['id']]);
        $p = $stmtP->fetch();
        $aula['concluida'] = $p ? (bool) $p['concluida'] : false;

        if (!$primeira_aula) $primeira_aula = $aula;
        $total_aulas++;
        if ($aula['concluida']) $total_concluidas++;

        if ($p && (!$ultima_vista || $p['visto_em'] > $ultima_vista['visto_em'])) {
            $ultima_vista = ['aula' => $aula, 'visto_em' => $p['visto_em']];
        }
    }
    unset($aula);
}
unset($modulo);

$progresso_pct = $total_aulas > 0 ? round(($total_concluidas / $total_aulas) * 100) : 0;

// Define qual aula mostrar: a da URL, ou a última vista (continuar), ou a primeira do curso
$aula_atual = $primeira_aula;
if (isset($_GET['aula'])) {
    foreach ($modulos as $modulo) {
        foreach ($modulo['aulas'] as $aula) {
            if ($aula['id'] == $_GET['aula']) { $aula_atual = $aula; break 2; }
        }
    }
} elseif ($ultima_vista) {
    $aula_atual = $ultima_vista['aula'];
}

// Marca essa aula como "vista agora" (pra funcionar o "continuar de onde parou" da próxima vez)
if ($aula_atual && !$eh_admin) {
    $stmt = $pdo->prepare('SELECT id FROM progresso_aulas WHERE aluno_id = ? AND aula_id = ?');
    $stmt->execute([$aluno_id, $aula_atual['id']]);
    if ($stmt->fetch()) {
        $pdo->prepare('UPDATE progresso_aulas SET visto_em = NOW() WHERE aluno_id = ? AND aula_id = ?')->execute([$aluno_id, $aula_atual['id']]);
    } else {
        $pdo->prepare('INSERT INTO progresso_aulas (aluno_id, aula_id, concluida) VALUES (?, ?, 0)')->execute([$aluno_id, $aula_atual['id']]);
    }
}

// Materiais complementares da aula atual
$materiais = [];
if ($aula_atual) {
    $stmt = $pdo->prepare('SELECT * FROM materiais WHERE aula_id = ? ORDER BY id');
    $stmt->execute([$aula_atual['id']]);
    $materiais = $stmt->fetchAll();
}

function url_embed_youtube(string $url): ?string {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/watch\?v=|youtube\.com\/embed\/)([A-Za-z0-9_-]{6,})/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (strpos($url, 'vimeo.com/') !== false && preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($curso['titulo']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    .player-layout { display: flex; gap: 24px; align-items: flex-start; }
    .player-principal { flex: 1; min-width: 0; }
    .player-video { width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 8px; overflow: hidden; }
    .player-video video, .player-video iframe { width: 100%; height: 100%; border: 0; display: block; }
    .player-video-vazio { width: 100%; aspect-ratio: 16/9; background: var(--bg-soft); border: 1px solid var(--line); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--muted); font-size: 14px; }
    .player-lista { width: 300px; flex-shrink: 0; border: 1px solid var(--line); border-radius: 8px; overflow: hidden; }
    .player-lista .modulo-cab { padding: 10px 14px; font-size: 13px; font-weight: 600; background: var(--bg-soft); border-bottom: 1px solid var(--line); border-top: 1px solid var(--line); }
    .player-lista .modulo-cab:first-child { border-top: none; }
    .player-lista a.aula-item { display: flex; justify-content: space-between; gap: 8px; padding: 10px 14px; font-size: 13px; text-decoration: none; color: var(--ink); border-bottom: 1px solid var(--line); }
    .player-lista a.aula-item:hover { background: #FAFAFA; }
    .player-lista a.aula-item.ativa { background: #EEF2FF; color: var(--accent); font-weight: 600; }
    .player-lista a.aula-item .duracao { color: var(--muted); font-size: 12px; white-space: nowrap; }
    .barra-progresso-topo { height: 8px; background: var(--line); border-radius: 100px; overflow: hidden; margin: 10px 0 4px; max-width: 420px; }
    .barra-progresso-topo-preenchida { height: 100%; background: var(--accent-grad); }
    .materiais-lista { margin-top: 14px; }
    .materiais-lista a { display: flex; align-items: center; gap: 8px; padding: 9px 12px; border: 1px solid var(--line); border-radius: 7px; text-decoration: none; color: var(--ink); font-size: 13px; margin-bottom: 8px; }
    .materiais-lista a:hover { border-color: #C9C9CE; background: #FAFAFA; }
    @media (max-width: 860px) { .player-layout { flex-direction: column; } .player-lista { width: 100%; } }
</style>
</head>
<body>
<div class="app">
    <?php include 'includes/' . ($eh_admin ? 'sidebar_admin.php' : 'sidebar_aluno.php'); ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div>
                <h1><?= htmlspecialchars($curso['titulo']) ?></h1>
                <p class="subtitulo"><a href="<?= $eh_admin ? 'cursos.php' : 'dashboard_aluno.php' ?>">&larr; Voltar</a></p>
                <?php if (!$eh_admin && $total_aulas > 0): ?>
                    <div class="barra-progresso-topo"><div class="barra-progresso-topo-preenchida" style="width:<?= $progresso_pct ?>%;"></div></div>
                    <p class="subtitulo" style="margin-top:0;">
                        <?= $progresso_pct ?>% concluído
                        <?php if ($progresso_pct >= 100): ?> · 🎓 <a href="certificado.php?curso_id=<?= $curso_id ?>">Ver certificado</a><?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$aula_atual): ?>
            <div class="cartao"><p class="vazio">Este curso ainda não tem aulas publicadas.</p></div>
        <?php else: ?>
        <div class="player-layout">
            <div class="player-principal">
                <?php $embed = !empty($aula_atual['video_url']) ? url_embed_youtube($aula_atual['video_url']) : null; ?>
                <?php if (!empty($aula_atual['video_arquivo'])): ?>
                    <div class="player-video"><video src="uploads/videos/<?= htmlspecialchars($aula_atual['video_arquivo']) ?>" controls></video></div>
                <?php elseif ($embed): ?>
                    <div class="player-video"><iframe src="<?= htmlspecialchars($embed) ?>" allowfullscreen allow="autoplay; encrypted-media"></iframe></div>
                <?php elseif (!empty($aula_atual['video_url'])): ?>
                    <div class="player-video-vazio"><a href="<?= htmlspecialchars($aula_atual['video_url']) ?>" target="_blank" rel="noopener">Abrir vídeo em outra aba &rarr;</a></div>
                <?php else: ?>
                    <div class="player-video-vazio">Esta aula ainda não tem vídeo.</div>
                <?php endif; ?>

                <div class="cartao" style="margin-top:18px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:14px;">
                        <div>
                            <h2 style="font-size:18px;"><?= htmlspecialchars($aula_atual['titulo']) ?></h2>
                            <?php if (!empty($aula_atual['descricao'])): ?>
                                <p style="color:var(--muted); font-size:14px;"><?= nl2br(htmlspecialchars($aula_atual['descricao'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (!$eh_admin): ?>
                        <form method="POST" style="flex-shrink:0;">
                            <input type="hidden" name="aula_id" value="<?= $aula_atual['id'] ?>">
                            <input type="hidden" name="concluir" value="<?= $aula_atual['concluida'] ? '0' : '1' ?>">
                            <button type="submit" class="botao <?= $aula_atual['concluida'] ? '' : 'destaque' ?>">
                                <?= $aula_atual['concluida'] ? '✓ Concluída' : 'Marcar como concluída' ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($materiais)): ?>
                        <div class="materiais-lista">
                            <label style="display:block; font-size:13px; font-weight:600; margin-bottom:8px;">Materiais complementares</label>
                            <?php foreach ($materiais as $mat): ?>
                                <a href="uploads/materiais/<?= htmlspecialchars($mat['arquivo']) ?>" download>📎 <?= htmlspecialchars($mat['nome']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="player-lista">
                <?php foreach ($modulos as $modulo): ?>
                    <?php if (empty($modulo['aulas'])) continue; ?>
                    <div class="modulo-cab"><?= htmlspecialchars($modulo['titulo']) ?></div>
                    <?php foreach ($modulo['aulas'] as $aula): ?>
                        <a class="aula-item <?= $aula['id'] == $aula_atual['id'] ? 'ativa' : '' ?>" href="curso_ver.php?id=<?= $curso_id ?>&aula=<?= $aula['id'] ?>">
                            <span><?= $aula['concluida'] ? '✅' : '🎬' ?> <?= htmlspecialchars($aula['titulo']) ?></span>
                            <?php if ($aula['duracao_minutos']): ?><span class="duracao"><?= $aula['duracao_minutos'] ?> min</span><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
