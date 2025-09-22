<?php
session_start();
if ($_SESSION['role'] !== 'admin') { header('Location: ../index.php'); exit; }

$id = $_GET['id'] ?? null;
$respostasPath = __DIR__ . '/../respostas.txt';
if ($id && file_exists($respostasPath)) {
    $lines = file($respostasPath, FILE_IGNORE_NEW_LINES);
    $new = [];
    foreach ($lines as $i => $line) {
        if ($i === 0 && stripos($line, 'Id;') === 0) { $new[] = $line; continue; }
        $cols = explode(';', $line);
        if ($cols[0] == $id) continue;
        $new[] = $line;
    }
    file_put_contents($respostasPath, implode(PHP_EOL, $new) . PHP_EOL);
}
header('Location: listar_respostas.php');
exit;
?>
