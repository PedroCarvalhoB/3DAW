<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$usuariosPath = __DIR__ . '/../usuarios.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoUsuario = trim($_POST['usuario'] ?? '');
    if (!empty($novoUsuario)) {
        file_put_contents($usuariosPath, $novoUsuario . PHP_EOL, FILE_APPEND);
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
    <title>Criar Usuário</title>
</head>
<body>
    <h1>Criar Usuário</h1>
    <p><a href="listar_usuarios.php">Voltar à lista</a></p>
    <form method="POST">
        <label>Nome do Usuário: <input type="text" name="usuario" value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"></label><br>
        <button type="submit">Salvar</button>
    </form>
    <?php if (!empty($error)) echo "<p style='color:red;'>".htmlspecialchars($error)."</p>"; ?>
</body>
</html>
