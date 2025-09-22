<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$perguntasPath = __DIR__ . '/../perguntas.txt';
$respostasPath = __DIR__ . '/../respostas.txt';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: listar_perguntas.php'); exit; }

// carrega perguntas
$lines = file_exists($perguntasPath) ? file($perguntasPath, FILE_IGNORE_NEW_LINES) : [];
$header = $lines[0] ?? "Id;Pergunta;TipoResposta;Certa;";
$found = null;
foreach ($lines as $i => $line) {
    if ($i === 0) continue;
    $cols = explode(';', $line);
    if ($cols[0] == $id) { $found = ['index' => $i, 'cols' => $cols]; break; }
}
if (!$found) { header('Location: listar_perguntas.php'); exit; }

$perguntaTexto = $found['cols'][1] ?? '';
$tipo = $found['cols'][2] ?? 'Texto';
$certa = $found['cols'][3] ?? '';

// carrega respostas desta pergunta
$resps = [];
if (file_exists($respostasPath)) {
    $rlines = file($respostasPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($rlines as $i => $rline) {
        if ($i === 0 && stripos($rline, 'Id;') === 0) continue;
        $rcols = explode(';', $rline);
        if (isset($rcols[1]) && $rcols[1] == $id) {
            $resps[$rcols[3]] = ['id' => $rcols[0], 'texto' => $rcols[2], 'letra' => $rcols[3]];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto = trim($_POST['texto'] ?? '');
    $ntipo = $_POST['tipo'] ?? 'Texto';
    $ncerta = trim($_POST['certa'] ?? '');
    if ($texto === '') $error = 'Pergunta não pode ficar vazia.';
    else {
        // atualiza linha de pergunta
        $newLine = $id . ';' . str_replace(["\r","\n"], ['',''], $texto) . ';' . $ntipo . ';' . $ncerta . ';';
        $lines[$found['index']] = $newLine;
        file_put_contents($perguntasPath, implode(PHP_EOL, $lines) . PHP_EOL);

        // se Multi, substituir respostas dela
        if ($ntipo === 'Multi') {
            // rebuild respostas file sem as antigas desta pergunta
            $rall = file_exists($respostasPath) ? file($respostasPath, FILE_IGNORE_NEW_LINES) : [];
            $newR = [];
            if (count($rall) > 0 && stripos($rall[0], 'Id;') === 0) $newR[] = $rall[0];
            foreach ($rall as $i => $rline) {
                if ($i === 0 && stripos($rline, 'Id;') === 0) continue;
                $rcols = explode(';', $rline);
                if ($rcols[1] == $id) continue; // pula antigas
                $newR[] = $rline;
            }
            // adiciona novas passadas no form
            $letters = ['a','b','c','d'];
            $nextRid = 1;
            // calcula next id disponível
            foreach ($newR as $i => $rv) {
                if ($i === 0 && stripos($rv, 'Id;') === 0) continue;
                $rc = explode(';', $rv);
                if (is_numeric($rc[0])) $nextRid = max($nextRid, intval($rc[0]) + 1);
            }
            foreach ($letters as $ltr) {
                $txt = trim($_POST['resposta_' . $ltr] ?? '');
                if ($txt === '') continue;
                $newR[] = $nextRid . ';' . $id . ';' . str_replace(["\r","\n"], ['',''], $txt) . ';' . $ltr;
                $nextRid++;
            }
            // escreve respostas
            file_put_contents($respostasPath, implode(PHP_EOL, $newR) . PHP_EOL);
        } else {
            // se mudou para Texto, remover respostas associadas
            if (file_exists($respostasPath)) {
                $rall = file($respostasPath, FILE_IGNORE_NEW_LINES);
                $newR = [];
                if (count($rall) > 0 && stripos($rall[0], 'Id;') === 0) $newR[] = $rall[0];
                foreach ($rall as $i => $rline) {
                    if ($i === 0 && stripos($rline, 'Id;') === 0) continue;
                    $rcols = explode(';', $rline);
                    if ($rcols[1] == $id) continue;
                    $newR[] = $rline;
                }
                file_put_contents($respostasPath, implode(PHP_EOL, $newR) . PHP_EOL);
            }
        }

        header('Location: listar_perguntas.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Editar Pergunta</title>
</head>
<body>
<h1>Editar Pergunta #<?= htmlspecialchars($id) ?></h1>
<form method="post">
    <label>Pergunta:<br><textarea name="texto" rows="4" cols="60"><?= htmlspecialchars($perguntaTexto) ?></textarea></label><br>
    <label>Tipo:
        <label><input type="radio" name="tipo" value="Multi" <?= ($tipo === 'Multi') ? 'checked' : '' ?>> Múltipla escolha</label>
        <label><input type="radio" name="tipo" value="Texto" <?= ($tipo === 'Texto') ? 'checked' : '' ?>> Texto</label>
    </label><br>
    <div>
        <strong>Opções (para Multi)</strong><br>
        <?php foreach (['a','b','c','d'] as $ltr): ?>
            <label><?= strtoupper($ltr) ?>: <input type="text" name="resposta_<?= $ltr ?>" value="<?= htmlspecialchars($resps[$ltr]['texto'] ?? '') ?>"></label><br>
        <?php endforeach; ?>
        <label>Letra correta: <input type="text" name="certa" value="<?= htmlspecialchars($certa) ?>"></label><br>
    </div>
    <button type="submit">Salvar</button>
</form>
<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
</body>
</html>
