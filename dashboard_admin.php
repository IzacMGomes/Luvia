<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$pagina_ativa = 'dashboard';

$total_alunos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'aluno'")->fetchColumn();
$total_cursos = $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
$cursos_publicados = $pdo->query("SELECT COUNT(*) FROM cursos WHERE status = 'publicado'")->fetchColumn();
$total_matriculas = $pdo->query("SELECT COUNT(*) FROM matriculas")->fetchColumn();
$total_aulas = $pdo->query("SELECT COUNT(*) FROM aulas")->fetchColumn();

$stmt = $pdo->prepare('SELECT foto_perfil FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$foto_perfil = $stmt->fetchColumn();

// Matrículas dos últimos 6 meses (pra gráfico de barras)
$meses_labels = [];
$meses_valores = [];
for ($i = 5; $i >= 0; $i--) {
    $chave = date('Y-m', strtotime("-$i months"));
    $meses_labels[$chave] = ucfirst(strftime_pt((int) date('n', strtotime("-$i months"))));
    $meses_valores[$chave] = 0;
}
$stmt = $pdo->query("SELECT DATE_FORMAT(data_matricula, '%Y-%m') AS mes, COUNT(*) AS total FROM matriculas WHERE data_matricula >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY mes");
foreach ($stmt->fetchAll() as $linha) {
    if (isset($meses_valores[$linha['mes']])) {
        $meses_valores[$linha['mes']] = (int) $linha['total'];
    }
}

function strftime_pt(int $mes): string {
    $nomes = [1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez'];
    return $nomes[$mes] ?? '';
}

$cursos_rascunho = $total_cursos - $cursos_publicados;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Painel do Administrador</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    .colunas-dashboard-graficos { display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; align-items: start; }
    @media (max-width: 980px) { .colunas-dashboard-graficos { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_admin.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div>
                <h1>Olá, <?= htmlspecialchars(explode(' ', $_SESSION['usuario_nome'])[0]) ?>! 👋</h1>
                <p class="subtitulo">Aqui está a visão geral da sua plataforma hoje.</p>
            </div>
            <div class="barra-topo">
                <a class="botao destaque" href="curso_form.php">+ Novo curso</a>
                <?php if (!empty($foto_perfil)): ?>
                    <img class="avatar-topo" src="uploads/perfil/<?= htmlspecialchars($foto_perfil) ?>" alt="">
                <?php else: ?>
                    <div class="avatar-topo-vazio"><?= htmlspecialchars(strtoupper(substr($_SESSION['usuario_nome'], 0, 1))) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grade-estat">
            <div class="cartao estat-cartao">
                <div class="estat-icone azul">🎓</div>
                <div class="estat-rotulo-topo">Alunos</div>
                <div class="estat-valor"><?= $total_alunos ?></div>
                <div class="estat-rotulo-baixo">Cadastrados</div>
            </div>
            <div class="cartao estat-cartao">
                <div class="estat-icone roxo">📚</div>
                <div class="estat-rotulo-topo">Cursos</div>
                <div class="estat-valor"><?= $total_cursos ?></div>
                <div class="estat-rotulo-baixo"><?= $cursos_publicados ?> publicado(s)</div>
            </div>
            <div class="cartao estat-cartao">
                <div class="estat-icone azul">📝</div>
                <div class="estat-rotulo-topo">Matrículas</div>
                <div class="estat-valor"><?= $total_matriculas ?></div>
                <div class="estat-rotulo-baixo">No total</div>
            </div>
            <div class="cartao estat-cartao">
                <div class="estat-icone roxo">🎬</div>
                <div class="estat-rotulo-topo">Aulas</div>
                <div class="estat-valor"><?= $total_aulas ?></div>
                <div class="estat-rotulo-baixo">Cadastradas</div>
            </div>
        </div>

        <div class="colunas-dashboard-graficos">
            <div class="cartao">
                <h2 style="font-size:17px; margin-bottom:14px;">Matrículas nos últimos 6 meses</h2>
                <canvas id="graficoMatriculas" height="220"></canvas>
            </div>
            <div class="cartao">
                <h2 style="font-size:17px; margin-bottom:14px;">Cursos por status</h2>
                <canvas id="graficoStatus" height="220"></canvas>
            </div>
        </div>

        <div class="cartao">
            <h2 style="font-size:17px;">Atalhos</h2>
            <p style="color:var(--muted); font-size:14px; margin-bottom:0;">
                <a href="cursos.php">Gerenciar cursos</a> · <a href="alunos.php">Gerenciar alunos</a> · <a href="matriculas.php">Ver matrículas</a> · <a href="perfil.php">Editar meu perfil</a>
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const corAzul = '#2F6FED';
    const corRoxo = '#7C5CFC';
    const corCinza = '#EAECF5';

    new Chart(document.getElementById('graficoMatriculas'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_values($meses_labels)) ?>,
            datasets: [{
                label: 'Matrículas',
                data: <?= json_encode(array_values($meses_valores)) ?>,
                backgroundColor: corAzul,
                borderRadius: 6,
                maxBarThickness: 34
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: corCinza } },
                x: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('graficoStatus'), {
        type: 'doughnut',
        data: {
            labels: ['Publicados', 'Rascunho'],
            datasets: [{
                data: [<?= $cursos_publicados ?>, <?= $cursos_rascunho ?>],
                backgroundColor: [corRoxo, corCinza],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '68%',
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 12 } } } }
        }
    });
</script>
</body>
</html>
