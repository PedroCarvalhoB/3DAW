<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Lê perguntas do arquivo (pulando header)
$perguntasPath = __DIR__ . '/../perguntas.txt';
$perguntas = [];
if (file_exists($perguntasPath)) {
    $lines = file($perguntasPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $i => $line) {
        if ($i === 0 && stripos($line, 'Id;') === 0) continue; // pula header
        $cols = explode(';', $line);
        if (count($cols) >= 4) {
            $perguntas[] = ['id' => $cols[0], 'texto' => $cols[1], 'tipo' => $cols[2], 'certa' => $cols[3]];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Listar Perguntas</title>
</head>
<body>
    <p><a href="../usuario/listar_usuarios.php">Voltar Usuários</a> | <a href="../index.php?logout=1">Logout</a></p>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="criar_pergunta.php">Criar nova pergunta</a><br>
<?php endif; ?>
<h1>Listar Perguntas</h1>
<table border="1" cellpadding="6">
    <tr><th>ID</th><th>Pergunta</th><th>Tipo</th><th>Resposta Certa</th><th>Ações</th></tr>
    <?php foreach ($perguntas as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['id']) ?></td>
            <td><?= htmlspecialchars($p['texto']) ?></td>
            <td><?= htmlspecialchars($p['tipo']) ?></td>
            <td><?= htmlspecialchars($p['certa']) ?></td>
            <td>
                <a href="../resposta/listar_respostas.php?idPergunta=<?= urlencode($p['id']) ?>">Ver</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    | <a href="editar_pergunta.php?id=<?= urlencode($p['id']) ?>">Editar</a>
                    | <a href="excluir_pergunta.php?id=<?= urlencode($p['id']) ?>" onclick="return confirm('Confirma exclusão?')">Excluir</a>
                    | <a href="../resposta/listar_respostas.php?idPergunta=<?= urlencode($p['id']) ?>">Respostas</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>

