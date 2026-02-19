<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Verificar se é admin
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    echo "<p style='color:red;'>Acesso negado. Você precisa ser um administrador para executar este script.</p>";
    exit;
}

require_once __DIR__ . '/../app/config/autoload.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h1>Adicionar Status 'Cancelado' na Tabela Pagamentos</h1>";

// Verificar valores atuais do ENUM
$result = $conn->query("SHOW COLUMNS FROM pagamentos WHERE Field = 'status'");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $type = $row['Type'];
    echo "<p>Status atual do ENUM: <code>{$type}</code></p>";
    
    // Verificar se 'Cancelado' já existe
    if (strpos($type, 'Cancelado') !== false) {
        echo "<p style='color:orange;'>O valor 'Cancelado' já existe no ENUM.</p>";
    } else {
        // Adicionar 'Cancelado' ao ENUM
        // Extrair valores atuais e adicionar 'Cancelado'
        $sql = "ALTER TABLE pagamentos MODIFY COLUMN status ENUM('Pago', 'Pendente', 'Cancelado') DEFAULT 'Pendente'";
        
        if ($conn->query($sql)) {
            echo "<p style='color:green;'>Status 'Cancelado' adicionado com sucesso ao ENUM!</p>";
        } else {
            echo "<p style='color:red;'>Erro ao adicionar status: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p style='color:red;'>Erro ao verificar coluna 'status'.</p>";
}

$conn->close();
?>

