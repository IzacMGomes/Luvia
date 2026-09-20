<?php
require_once 'auth.php';
exigir_login('aluno');
require_once 'config.php';

$pagina_ativa = 'catalogo';

$busca     = trim($_GET['busca'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');
$nivel     = trim($_GET['nivel'] ?? '');

$sql = "SELECT * FROM cursos WHERE status = 'publicado'";
$params = [];
if ($busca !== '')     { $sql .= " AND titulo LIKE ?"; $params[] = '%' . $busca . '%'; }
if ($categoria !== '') { $sql .= " AND categoria = ?"; $params[] = $categoria; }
if ($nivel !== '')     { $sql .= " AND nivel = ?"; $params[] = $nivel; }
$sql .= " ORDER BY criado_em DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cursos = $stmt->fetchAll();

$categorias_disponiveis = $pdo->query(
    "SELECT DISTINCT categoria FROM cursos WHERE status = 'publicado' AND categoria IS NOT NULL AND categoria != '' ORDER BY categoria"
)->fetchAll(PDO::FETCH_COLUMN);

// Quais cursos o aluno já está matriculado
$stmt = $pdo->prepare('SELECT curso_id FROM matriculas WHERE aluno_id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$matriculados = $stmt->fetchAll(PDO::FETCH_COLUMN);

$filtros_ativos = $busca !== '' || $categoria !== '' || $nivel !== '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Catálogo de Cursos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    .grade-cursos { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 18px; }
    .curso-capa { width: 100%; height: 120px; border-radius: 8px 8px 0 0; object-fit: cover; background: var(--bg-soft); display: block; border-bottom: 1px solid var(--line); }
    .curso-capa-vazia { width: 100%; height: 120px; border-radius: 8px 8px 0 0; background: var(--bg-soft); border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: center; color: var(--muted); font-weight: 600; font-size: 26px; }
    .curso-item { padding: 0; overflow: hidden; }
    .curso-corpo { padding: 16px 18px; }
    .curso-corpo h3 { font-size: 16px; margin-bottom: 4px; }
    .curso-corpo p { font-size: 13px; color: var(--muted); margin: 0 0 12px; }
    .barra-filtros { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 22px; }
    .barra-filtros input[type=text] { flex: 1; min-width: 180px; padding: 9px 12px; border: 1px solid var(--line); border-radius: 7px; font-family: inherit; font-size: 14px; }
    .barra-filtros select { padding: 9px 12px; border: 1px solid var(--line); border-radius: 7px; font-family: inherit; font-size: 14px; background: var(--surface); color: var(--ink); }
    .barra-filtros .limpar { font-size: 13px; color: var(--muted); text-decoration: none; }
</style>
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_aluno.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div>
                <h1>Catálogo de cursos</h1>
                <p class="subtitulo">Explore os cursos disponíveis e matricule-se.</p>
            </div>
        </div>

        <form method="GET" class="barra-filtros">
            <input type="text" name="busca" placeholder="Buscar curso pelo título..." value="<?= htmlspecialchars($busca) ?>">
            <select name="categoria">
                <option value="">Todas as categorias</option>
                <?php foreach ($categorias_disponiveis as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="nivel">
                <option value="">Todos os níveis</option>
                <option value="iniciante" <?= $nivel === 'iniciante' ? 'selected' : '' ?>>Iniciante</option>
                <option value="intermediario" <?= $nivel === 'intermediario' ? 'selected' : '' ?>>Intermediário</option>
                <option value="avancado" <?= $nivel === 'avancado' ? 'selected' : '' ?>>Avançado</option>
            </select>
            <button type="submit" class="botao destaque">Filtrar</button>
            <?php if ($filtros_ativos): ?><a class="limpar" href="catalogo.php">Limpar filtros</a><?php endif; ?>
        </form>

        <?php if (empty($cursos)): ?>
            <div class="cartao"><p class="vazio">Nenhum curso encontrado.</p></div>
        <?php else: ?>
        <div class="grade-cursos">
            <?php foreach ($cursos as $curso): $ja_matriculado = in_array($curso['id'], $matriculados); ?>
            <div class="cartao curso-item">
                <?php if (!empty($curso['capa_imagem'])): ?>
                    <img class="curso-capa" src="uploads/capas/<?= htmlspecialchars($curso['capa_imagem']) ?>" alt="">
                <?php else: ?>
                    <div class="curso-capa-vazia"><?= htmlspecialchars(mb_substr($curso['titulo'], 0, 1)) ?></div>
                <?php endif; ?>
                <div class="curso-corpo">
                    <h3><?= htmlspecialchars($curso['titulo']) ?></h3>
                    <p><?= htmlspecialchars($curso['categoria'] ?: ucfirst($curso['nivel'])) ?></p>
                    <?php if ($ja_matriculado): ?>
                        <a class="botao pequeno" href="curso_ver.php?id=<?= $curso['id'] ?>" style="width:100%; justify-content:center;">Já matriculado — Abrir</a>
                    <?php else: ?>
                        <form method="POST" action="matricular.php">
                            <input type="hidden" name="curso_id" value="<?= $curso['id'] ?>">
                            <button type="submit" class="botao destaque pequeno" style="width:100%; justify-content:center;">Matricular-se</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
