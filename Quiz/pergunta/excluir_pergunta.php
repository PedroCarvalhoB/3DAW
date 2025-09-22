<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: listar_perguntas.php'); exit; }

$perguntasPath = __DIR__ . '/../perguntas.txt';
$respostasPath = __DIR__ . '/../respostas.txt';

// remover pergunta
if (file_exists($perguntasPath)) {
    $lines = file($perguntasPath, FILE_IGNORE_NEW_LINES);
    $new = [];
    foreach ($lines as $i => $line) {
        if ($i === 0 && stripos($line, 'Id;') === 0) { $new[] = $line; continue; }
        $cols = explode(';', $line);
        if ($cols[0] == $id) continue;
        $new[] = $line;
    }
    file_put_contents($perguntasPath, implode(PHP_EOL, $new) . PHP_EOL);
}

// remover respostas da pergunta
if (file_exists($respostasPath)) {
    $rlines = file($respostasPath, FILE_IGNORE_NEW_LINES);
    $newr = [];
    foreach ($rlines as $i => $rline) {
        if ($i === 0 && stripos($rline, 'Id;') === 0) { $newr[] = $rline; continue; }
        $rcols = explode(';', $rline);
        if ($rcols[1] == $id) continue;
        $newr[] = $rline;
    }
    file_put_contents($respostasPath, implode(PHP_EOL, $newr) . PHP_EOL);
}

header('Location: listar_perguntas.php');
exit;
?>
