<?php
session_start();
if ($_SESSION['role'] !== 'admin') { header('Location: ../index.php'); exit; }

$idPergunta = $_GET['idPergunta'] ?? null;
$respostasPath = __DIR__ . '/../respostas.txt';

function nextId($path) {
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
    $letra = trim($_POST['letra'] ?? '');
    if ($texto === '' || $letra === '' || !$idPergunta) $error = 'Preencha todos os campos.';
    else {
        if (!file_exists($respostasPath) || filesize($respostasPath) === 0) {
            file_put_contents($respostasPath, "Id;IdPergunta;Resposta;Letra" . PHP_EOL);
        }
        $rid = nextId($respostasPath);
        $line = $rid . ';' . $idPergunta . ';' . str_replace(["\r","\n"], ['',''], $texto) . ';' . $letra . PHP_EOL;
        file_put_contents($respostasPath, $line, FILE_APPEND);
        header('Location: listar_respostas.php?idPergunta=' . urlencode($idPergunta));
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Criar Resposta</title></head>
<body>
<h1>Criar Resposta para Pergunta #<?= htmlspecialchars($idPergunta) ?></h1>
<form method="post">
    <label>Resposta: <input type="text" name="texto" value="<?= htmlspecialchars($_POST['texto'] ?? '') ?>"></label><br>
    <label>Letra: <input type="text" name="letra" value="<?= htmlspecialchars($_POST['letra'] ?? '') ?>"></label><br>
    <button type="submit">Salvar</button>
</form>
<?php if (!empty($error)) echo "<p style='color:red;'>".htmlspecialchars($error)."</p>"; ?>
</body>
</html>
