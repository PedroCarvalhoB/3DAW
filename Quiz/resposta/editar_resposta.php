<?php
session_start();
if ($_SESSION['role'] !== 'admin') { header('Location: ../index.php'); exit; }

$id = $_GET['id'] ?? null;
$respostasPath = __DIR__ . '/../respostas.txt';
if (!$id) header('Location: listar_respostas.php');

$lines = file_exists($respostasPath) ? file($respostasPath, FILE_IGNORE_NEW_LINES) : [];
$header = $lines[0] ?? "Id;IdPergunta;Resposta;Letra";
$found = null;
foreach ($lines as $i => $line) {
    if ($i === 0) continue;
    $cols = explode(';', $line);
    if ($cols[0] == $id) { $found = ['index' => $i, 'cols' => $cols]; break; }
}
if (!$found) header('Location: listar_respostas.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto = trim($_POST['texto'] ?? '');
    $letra = trim($_POST['letra'] ?? '');
    if ($texto === '' || $letra === '') $error = 'Preencha os campos.';
    else {
        $newLine = $found['cols'][0] . ';' . $found['cols'][1] . ';' . str_replace(["\r","\n"], ['',''], $texto) . ';' . $letra;
        $lines[$found['index']] = $newLine;
        file_put_contents($respostasPath, implode(PHP_EOL, $lines) . PHP_EOL);
        header('Location: listar_respostas.php?idPergunta=' . urlencode($found['cols'][1]));
        exit;
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Editar Resposta</title></head>
<body>
<h1>Editar Resposta #<?= htmlspecialchars($id) ?></h1>
<form method="post">
    <label>Resposta: <input type="text" name="texto" value="<?= htmlspecialchars($found['cols'][2]) ?>"></label><br>
    <label>Letra: <input type="text" name="letra" value="<?= htmlspecialchars($found['cols'][3]) ?>"></label><br>
    <button type="submit">Salvar</button>
</form>
<?php if (!empty($error)) echo "<p style='color:red;'>".htmlspecialchars($error)."</p>"; ?>
</body></html>
