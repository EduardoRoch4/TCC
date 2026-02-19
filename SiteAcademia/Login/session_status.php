<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$logged = isset($_SESSION['usuario']);
$perfil = isset($_SESSION['perfil']) ? $_SESSION['perfil'] : null;
$usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;

echo json_encode([
  'logged' => $logged,
  'perfil' => $perfil,
  'usuario' => $usuario
], JSON_UNESCAPED_UNICODE);

?>
