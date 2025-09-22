<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$usuariosPath = __DIR__ . '/../usuarios.txt';
$usuarios = [];
if (file_exists($usuariosPath)) {
    $usuarios = file($usuariosPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Listar Usuários</title>
</head>
<body>
    <h1>Usuários</h1>

    <!-- Navegação administrativa -->
    <p>
        <a href="criar_usuario.php">Criar Novo Usuário</a> |
        <a href="../pergunta/listar_perguntas.php">Perguntas</a> |
        <a href="../resposta/listar_respostas.php">Respostas</a> |
        <a href="../index.php?logout=1">Logout</a>
    </p>

    <?php if (empty($usuarios)): ?>
        <p>Nenhum usuário cadastrado.</p>
    <?php endif; ?>

    <table border="1">
        <tr><th>Usuário</th><th>Ações</th></tr>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= htmlspecialchars($usuario) ?></td>
                <td>
                    <a href="editar_usuario.php?usuario=<?= urlencode($usuario) ?>">Editar</a> |
                    <a href="excluir_usuario.php?usuario=<?= urlencode($usuario) ?>" onclick="return confirm('Excluir usuário?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
