<?php
session_start();

if (isset($_GET['logout'])) {
    // logout
    session_unset();
    session_destroy();
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sempre loga como administrador
    $_SESSION['role'] = 'admin';
    header('Location: usuario/listar_usuarios.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form method="POST">
        <button type="submit">Entrar como Administrador</button>
    </form>
</body>
</html>
