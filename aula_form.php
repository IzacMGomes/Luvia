<?php
require_once 'auth.php';
exigir_login('administrador');
require_once 'config.php';

$pagina_ativa = 'cursos';
$curso_id  = (int) ($_GET['curso_id'] ?? $_POST['curso_id'] ?? 0);
$modulo_id = (int) ($_GET['modulo_id'] ?? $_POST['modulo_id'] ?? 0);
$aula = ['id' => null, 'titulo' => '', 'descricao' => '', 'video_url' => '', 'video_arquivo' => null, 'duracao_minutos' => '', 'ordem' => 0];
$editando = false;
$erro = '';

if (isset($_GET['id'])) {
    $editando = true;
    $stmt = $pdo->prepare('SELECT * FROM aulas WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $encontrado = $stmt->fetch();
    if ($encontrado) $aula = $encontrado;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo     = trim($_POST['titulo'] ?? '');
    $descricao  = trim($_POST['descricao'] ?? '');
    $video_url  = trim($_POST['video_url'] ?? '');
    $duracao    = $_POST['duracao_minutos'] !== '' ? (int) $_POST['duracao_minutos'] : null;
    $ordem      = (int) ($_POST['ordem'] ?? 0);
    $id         = $_POST['id'] ?? null;
    $video_arquivo = $id ? ($aula['video_arquivo'] ?? null) : null;
    $remover_video = isset($_POST['remover_video']);

    if ($remover_video) {
        // Apaga o arquivo de vídeo do disco, se houver, e limpa os dois campos
        if (!empty($aula['video_arquivo'])) {
            $caminho_antigo = __DIR__ . '/uploads/videos/' . $aula['video_arquivo'];
            if (file_exists($caminho_antigo)) unlink($caminho_antigo);
        }
        $video_arquivo = null;
        $video_url = '';
    }

    if ($titulo === '') {
        $erro = 'O título da aula é obrigatório.';
    } else {
        // Upload de arquivo de vídeo (opcional, alternativa ao link)
        if (!empty($_FILES['video']['name'])) {
            if ($_FILES['video']['error'] !== UPLOAD_ERR_OK) {
                $mensagens_erro = [
                    UPLOAD_ERR_INI_SIZE => 'O vídeo é maior que o limite permitido pelo servidor (upload_max_filesize no php.ini).',
                    UPLOAD_ERR_FORM_SIZE => 'O vídeo é maior que o limite permitido pelo formulário.',
                    UPLOAD_ERR_PARTIAL => 'O upload foi interrompido no meio do envio. Tente novamente.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta uma pasta temporária no servidor para receber o upload.',
                    UPLOAD_ERR_CANT_WRITE => 'Não foi possível gravar o arquivo no disco (permissão de pasta).',
                ];
                $erro = $mensagens_erro[$_FILES['video']['error']] ?? 'Falha desconhecida ao enviar o vídeo (código ' . $_FILES['video']['error'] . ').';
            } else {
                $pasta = __DIR__ . '/uploads/videos/';
                if (!is_dir($pasta)) mkdir($pasta, 0777, true);
                $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
                $permitidas = ['mp4', 'webm', 'ogg'];
                if (!in_array($ext, $permitidas)) {
                    $erro = "Formato .$ext não suportado. Envie um vídeo em MP4, WebM ou OGG.";
                } else {
                    if (!empty($aula['video_arquivo']) && file_exists($pasta . $aula['video_arquivo'])) {
                        unlink($pasta . $aula['video_arquivo']);
                    }
                    $novo_nome = uniqid('video_') . '.' . $ext;
                    if (move_uploaded_file($_FILES['video']['tmp_name'], $pasta . $novo_nome)) {
                        $video_arquivo = $novo_nome;
                        $video_url = ''; // upload tem prioridade sobre o link
                    } else {
                        $erro = 'Não foi possível salvar o vídeo na pasta uploads/videos (verifique permissão de escrita).';
                    }
                }
            }
        }

        if ($erro === '') {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE aulas SET titulo=?, descricao=?, video_url=?, video_arquivo=?, duracao_minutos=?, ordem=? WHERE id=?');
                $stmt->execute([$titulo, $descricao, $video_url, $video_arquivo, $duracao, $ordem, $id]);
                $aula_id_final = $id;
            } else {
                $stmt = $pdo->prepare('INSERT INTO aulas (modulo_id, titulo, descricao, video_url, video_arquivo, duracao_minutos, ordem) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$modulo_id, $titulo, $descricao, $video_url, $video_arquivo, $duracao, $ordem]);
                $aula_id_final = $pdo->lastInsertId();
            }

            // Upload de materiais complementares (PDFs, apostilas, etc.) — pode enviar vários de uma vez
            if (!empty($_FILES['materiais']['name'][0])) {
                $pastaMat = __DIR__ . '/uploads/materiais/';
                if (!is_dir($pastaMat)) mkdir($pastaMat, 0777, true);
                $extensoesMat = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'txt'];
                foreach ($_FILES['materiais']['name'] as $i => $nomeOriginal) {
                    if ($_FILES['materiais']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $extMat = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
                    if (!in_array($extMat, $extensoesMat)) continue;
                    $nomeSalvo = uniqid('mat_') . '.' . $extMat;
                    if (move_uploaded_file($_FILES['materiais']['tmp_name'][$i], $pastaMat . $nomeSalvo)) {
                        $insMat = $pdo->prepare('INSERT INTO materiais (aula_id, nome, arquivo) VALUES (?, ?, ?)');
                        $insMat->execute([$aula_id_final, $nomeOriginal, $nomeSalvo]);
                    }
                }
            }

            header('Location: modulos.php?curso_id=' . $curso_id);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title><?= $editando ? 'Editar Aula' : 'Nova Aula' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    .separador-ou { display: flex; align-items: center; gap: 10px; color: var(--muted); font-size: 12px; margin: 18px 0; }
    .separador-ou::before, .separador-ou::after { content: ""; flex: 1; height: 1px; background: var(--line); }
    video.previa-video { width: 100%; border-radius: 7px; margin-top: 8px; background: #000; }
    .aviso-atual { font-size: 13px; color: var(--muted); margin-top: 6px; }
</style>
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar_admin.php'; ?>
    <div class="conteudo">
        <div class="cabecalho-pagina">
            <div>
                <h1><?= $editando ? 'Editar aula' : 'Nova aula' ?></h1>
                <p class="subtitulo"><a href="modulos.php?curso_id=<?= $curso_id ?>">&larr; Voltar para módulos e aulas</a></p>
            </div>
        </div>

        <div class="cartao" style="max-width:540px;">
            <?php if ($erro): ?><p class="mensagem-erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars($aula['id'] ?? '') ?>">
                <input type="hidden" name="curso_id" value="<?= $curso_id ?>">
                <input type="hidden" name="modulo_id" value="<?= $modulo_id ?>">

                <div class="campo">
                    <label>Título da aula</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($aula['titulo']) ?>" required>
                </div>
                <div class="campo">
                    <label>Descrição</label>
                    <textarea name="descricao"><?= htmlspecialchars($aula['descricao']) ?></textarea>
                </div>

                <div class="campo">
                    <label>Enviar arquivo de vídeo (MP4, WebM ou OGG)</label>
                    <input type="file" name="video" accept="video/mp4,video/webm,video/ogg">
                    <?php if (!empty($aula['video_arquivo'])): ?>
                        <p class="aviso-atual">Vídeo atual:</p>
                        <video class="previa-video" src="uploads/videos/<?= htmlspecialchars($aula['video_arquivo']) ?>" controls></video>
                    <?php endif; ?>
                    <?php if (!empty($aula['video_arquivo']) || !empty($aula['video_url'])): ?>
                        <label style="display:flex; align-items:center; gap:7px; font-weight:400; margin-top:10px; color:var(--danger); font-size:13px;">
                            <input type="checkbox" name="remover_video" value="1" style="width:auto;">
                            Remover o vídeo desta aula
                        </label>
                    <?php endif; ?>
                </div>

                <div class="separador-ou">ou use um link</div>

                <div class="campo">
                    <label>URL do vídeo (YouTube, Vimeo, etc)</label>
                    <input type="text" name="video_url" placeholder="https://..." value="<?= htmlspecialchars($aula['video_url']) ?>">
                </div>

                <div class="campo">
                    <label>Duração (minutos)</label>
                    <input type="number" name="duracao_minutos" value="<?= htmlspecialchars($aula['duracao_minutos']) ?>">
                </div>
                <div class="campo">
                    <label>Ordem de exibição (0 = primeira)</label>
                    <input type="number" name="ordem" value="<?= htmlspecialchars($aula['ordem']) ?>">
                </div>

                <div class="campo">
                    <label>Materiais complementares (PDF, DOC, PPT, XLS, ZIP...)</label>
                    <input type="file" name="materiais[]" multiple>
                    <?php if ($editando):
                        $stmtMat = $pdo->prepare('SELECT * FROM materiais WHERE aula_id = ? ORDER BY id');
                        $stmtMat->execute([$aula['id']]);
                        $materiaisExistentes = $stmtMat->fetchAll();
                    ?>
                        <?php if (!empty($materiaisExistentes)): ?>
                        <div style="margin-top:10px;">
                            <?php foreach ($materiaisExistentes as $mat): ?>
                                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 10px; border:1px solid var(--line); border-radius:7px; margin-bottom:6px; font-size:13px;">
                                    <span>📎 <?= htmlspecialchars($mat['nome']) ?></span>
                                    <a class="excluir" style="font-size:12px;" href="material_excluir.php?id=<?= $mat['id'] ?>&curso_id=<?= $curso_id ?>&modulo_id=<?= $modulo_id ?>&aula_id=<?= $aula['id'] ?>" onclick="return confirm('Excluir este material?')">Excluir</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="aviso-atual">Salve a aula primeiro; depois edite-a para anexar materiais.</p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="botao destaque"><?= $editando ? 'Salvar alterações' : 'Criar aula' ?></button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
