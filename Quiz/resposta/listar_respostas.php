<?php
session_start();

$respostasPath = __DIR__ . '/../respostas.txt';
$idPergunta = $_GET['idPergunta'] ?? null;

$respostas = [];
if (file_exists($respostasPath)) {
    $lines = file($respostasPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $i => $line) {
        if ($i === 0 && stripos($line, 'Id;') === 0) continue;
        $cols = explode(';', $line);
        if (count($cols) >= 4) {
            if ($idPergunta === null || $cols[1] == $idPergunta) {
                $respostas[] = ['id' => $cols[0], 'idPergunta' => $cols[1], 'texto' => $cols[2], 'letra' => $cols[3]];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Listar Respostas</title>
</head>
<body>
<h1>Respostas <?= $idPergunta ? "da pergunta #".htmlspecialchars($idPergunta) : '' ?></h1>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && $idPergunta): ?>
    <a href="criar_resposta.php?idPergunta=<?= urlencode($idPergunta) ?>">Adicionar resposta</a><br>
<?php endif; ?>
<table border="1">
    <tr><th>ID</th><th>IdPergunta</th><th>Resposta</th><th>Letra</th><th>Ações</th></tr>
    <?php foreach ($respostas as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['id']) ?></td>
            <td><?= htmlspecialchars($r['idPergunta']) ?></td>
            <td><?= htmlspecialchars($r['texto']) ?></td>
            <td><?= htmlspecialchars($r['letra']) ?></td>
            <td>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="editar_resposta.php?id=<?= urlencode($r['id']) ?>">Editar</a> |
                    <a href="excluir_resposta.php?id=<?= urlencode($r['id']) ?>" onclick="return confirm('Confirma?')">Excluir</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
