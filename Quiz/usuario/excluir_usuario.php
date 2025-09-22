<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$usuariosPath = __DIR__ . '/../usuarios.txt';
$usuario = $_GET['usuario'] ?? '';
if (!empty($usuario) && file_exists($usuariosPath)) {
    $usuarios = file($usuariosPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $usuarios = array_filter($usuarios, fn($u) => $u !== $usuario);
    file_put_contents($usuariosPath, implode(PHP_EOL, $usuarios) . PHP_EOL);
}
header('Location: listar_usuarios.php');
exit;
?>
