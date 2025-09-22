<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$usuariosPath = __DIR__ . '/../usuarios.txt';

// obtém o usuário original: primeiro tenta GET, se for POST pega campo hidden
$usuarioAntigo = $_GET['usuario'] ?? $_POST['usuario_original'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioNovo = trim($_POST['usuario'] ?? '');
    if (!empty($usuarioNovo) && $usuarioAntigo !== '') {
        $usuarios = file($usuariosPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $usuarios = array_map(function($u) use ($usuarioAntigo, $usuarioNovo) {
            return $u === $usuarioAntigo ? $usuarioNovo : $u;
        }, $usuarios);
        file_put_contents($usuariosPath, implode(PHP_EOL, $usuarios) . PHP_EOL);
        header('Location: listar_usuarios.php');
        exit;
    } else {
        $error = 'O nome do usuário não pode estar vazio.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Editar Usuário</title>
</head>
<body>
    <h1>Editar Usuário</h1>
    <p><a href="listar_usuarios.php">Voltar à lista</a></p>
    <form method="POST">
        <input type="hidden" name="usuario_original" value="<?= htmlspecialchars($usuarioAntigo) ?>">
        <label>Nome do Usuário: <input type="text" name="usuario" value="<?= htmlspecialchars($usuarioAntigo) ?>"></label><br>
        <button type="submit">Salvar</button>
    </form>
    <?php if (!empty($error)) echo "<p style='color:red;'>".htmlspecialchars($error)."</p>"; ?>
</body>
</html>
