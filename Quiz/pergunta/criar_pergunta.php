<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$perguntasPath = __DIR__ . '/../perguntas.txt';
$respostasPath = __DIR__ . '/../respostas.txt';

function nextIdFromFile($path) {
    if (!file_exists($path)) return 1;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $max = 0;
    foreach ($lines as $i => $line) {
        if ($i === 0 && stripos($line, 'Id;') === 0) continue;
        $cols = explode(';', $line);
        if (isset($cols[0]) && is_numeric($cols[0])) $max = max($max, intval($cols[0]));
    }
    return $max + 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto = trim($_POST['texto'] ?? '');
    $tipo = $_POST['tipo'] ?? 'Texto';
    $certa = trim($_POST['certa'] ?? '');

    if ($texto === '') {
        $error = 'Pergunta não pode ficar vazia.';
    } else {
        // garante cabeçalho em perguntas.txt
        if (!file_exists($perguntasPath) || filesize($perguntasPath) === 0) {
            file_put_contents($perguntasPath, "Id;Pergunta;TipoResposta;Certa;" . PHP_EOL);
        }
        $id = nextIdFromFile($perguntasPath);
        $line = $id . ';' . str_replace(["\r","\n"], ['',''], $texto) . ';' . $tipo . ';' . $certa . ';' . PHP_EOL;
        file_put_contents($perguntasPath, $line, FILE_APPEND);

        // se tipo Multi, escreve respostas A-D
        if ($tipo === 'Multi') {
            // garante cabeçalho em respostas.txt
            if (!file_exists($respostasPath) || filesize($respostasPath) === 0) {
                file_put_contents($respostasPath, "Id;IdPergunta;Resposta;Letra" . PHP_EOL);
            }
            $letters = ['a','b','c','d'];
            foreach ($letters as $ltr) {
                $text = trim($_POST['resposta_' . $ltr] ?? '');
                if ($text === '') continue;
                $rid = nextIdFromFile($respostasPath);
                $rline = $rid . ';' . $id . ';' . str_replace(["\r","\n"], ['',''], $text) . ';' . $ltr . PHP_EOL;
                file_put_contents($respostasPath, $rline, FILE_APPEND);
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
    <title>Criar Pergunta</title>
</head>
<body>
<h1>Criar Pergunta</h1>
<form method="post">
    <label>Pergunta:<br><textarea name="texto" rows="4" cols="60"><?= htmlspecialchars($_POST['texto'] ?? '') ?></textarea></label><br>
    <label>Tipo:
        <label><input type="radio" name="tipo" value="Multi" <?= (($_POST['tipo'] ?? '') === 'Multi') ? 'checked' : '' ?>> Múltipla escolha</label>
        <label><input type="radio" name="tipo" value="Texto" <?= (($_POST['tipo'] ?? '') === 'Texto' || $_POST === []) ? 'checked' : '' ?>> Texto</label>
    </label><br>
    <div id="multi_fields" style="margin-top:10px;">
        <strong>Opções (para Multi)</strong><br>
        <label>A: <input type="text" name="resposta_a" value="<?= htmlspecialchars($_POST['resposta_a'] ?? '') ?>"></label><br>
        <label>B: <input type="text" name="resposta_b" value="<?= htmlspecialchars($_POST['resposta_b'] ?? '') ?>"></label><br>
        <label>C: <input type="text" name="resposta_c" value="<?= htmlspecialchars($_POST['resposta_c'] ?? '') ?>"></label><br>
        <label>D: <input type="text" name="resposta_d" value="<?= htmlspecialchars($_POST['resposta_d'] ?? '') ?>"></label><br>
        <label>Letra correta: <input type="text" name="certa" value="<?= htmlspecialchars($_POST['certa'] ?? '') ?>"></label><br>
    </div>
    <button type="submit">Salvar</button>
</form>
<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
</body>
</html>

