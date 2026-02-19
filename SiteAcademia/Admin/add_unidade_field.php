<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Verificar se é admin
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    echo "<p style='color:red;'>Acesso negado. Você precisa ser um administrador para executar este script.</p>";
    exit;
}

$host = "localhost";
$user = "root";
$pass = "senaisp";
$db   = "Techfit";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("<p style='color:red;'>Erro na conexão com o banco: " . $conn->connect_error . "</p>");
}

$conn->set_charset("utf8");

echo "<h1>Adicionar Campo Unidade na Tabela Usuarios</h1>";

// Verificar se o campo já existe
$result = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'unidade'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color:orange;'>O campo 'unidade' já existe na tabela usuarios.</p>";
} else {
    // Adicionar o campo
    $sql = "ALTER TABLE usuarios ADD COLUMN unidade VARCHAR(100) NULL AFTER email";
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>Campo 'unidade' adicionado com sucesso na tabela usuarios!</p>";
    } else {
        echo "<p style='color:red;'>Erro ao adicionar campo: " . $conn->error . "</p>";
    }
}

$conn->close();
?>

