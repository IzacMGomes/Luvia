<?php
require_once 'auth.php';
exigir_login('aluno');
require_once 'config.php';

$curso_id = (int) ($_GET['curso_id'] ?? 0);
$aluno_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare('SELECT * FROM cursos WHERE id = ?');
$stmt->execute([$curso_id]);
$curso = $stmt->fetch();
if (!$curso) { header('Location: historico.php'); exit; }

// Confirma que o curso foi realmente concluído por esse aluno
$totalAulas = $pdo->prepare('SELECT COUNT(*) FROM aulas a INNER JOIN curso_modulos cm ON cm.id = a.modulo_id WHERE cm.curso_id = ?');
$totalAulas->execute([$curso_id]);
$total = (int) $totalAulas->fetchColumn();

$concluidas = $pdo->prepare('SELECT COUNT(*), MAX(concluida_em) FROM progresso_aulas p INNER JOIN aulas a ON a.id = p.aula_id INNER JOIN curso_modulos cm ON cm.id = a.modulo_id WHERE cm.curso_id = ? AND p.aluno_id = ? AND p.concluida = 1');
$concluidas->execute([$curso_id, $aluno_id]);
[$feitas, $data_conclusao] = $concluidas->fetch(PDO::FETCH_NUM);

if ($total === 0 || (int) $feitas !== $total) {
    header('Location: curso_ver.php?id=' . $curso_id);
    exit;
}

$data_formatada = $data_conclusao ? date('d/m/Y', strtotime($data_conclusao)) : date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Certificado - <?= htmlspecialchars($curso['titulo']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Inter', sans-serif; background: #F2F2F4; margin: 0; padding: 40px 20px; }
    .barra-topo { max-width: 800px; margin: 0 auto 20px; display: flex; justify-content: space-between; }
    .barra-topo a { color: #2F5FF6; text-decoration: none; font-size: 14px; }
    .barra-topo button { padding: 9px 16px; background: #16181D; color: #fff; border: none; border-radius: 7px; cursor: pointer; font-family: inherit; }
    .certificado {
        max-width: 800px; margin: 0 auto; background: #fff; border: 1px solid #E5E5EA;
        padding: 60px 50px; text-align: center; position: relative;
    }
    .certificado::before {
        content: ""; position: absolute; inset: 16px; border: 1px solid #D9D9E0;
    }
    .selo { color: #2F5FF6; font-weight: 600; letter-spacing: 0.08em; font-size: 12px; margin-bottom: 30px; }
    .certificado h1 { font-size: 15px; color: #6B7280; font-weight: 500; margin-bottom: 4px; }
    .nome-aluno { font-size: 34px; font-weight: 700; color: #16181D; margin: 6px 0 20px; }
    .certificado p.texto { color: #3F434E; font-size: 15px; max-width: 520px; margin: 0 auto 6px; line-height: 1.6; }
    .titulo-curso { font-weight: 700; color: #16181D; }
    .data { margin-top: 34px; font-size: 13px; color: #6B7280; }
    @media print {
        body { background: #fff; padding: 0; }
        .barra-topo { display: none; }
        .certificado { border: none; }
    }
</style>
</head>
<body>
    <div class="barra-topo">
        <a href="historico.php">&larr; Voltar ao histórico</a>
        <button onclick="window.print()">Salvar como PDF / Imprimir</button>
    </div>
    <div class="certificado">
        <div class="selo">CERTIFICADO DE CONCLUSÃO</div>
        <h1>Certificamos que</h1>
        <div class="nome-aluno"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></div>
        <p class="texto">concluiu com êxito o curso <span class="titulo-curso"><?= htmlspecialchars($curso['titulo']) ?></span>, cumprindo a carga horária e os conteúdos propostos na plataforma.</p>
        <div class="data">Concluído em <?= $data_formatada ?></div>
    </div>
</body>
</html>
