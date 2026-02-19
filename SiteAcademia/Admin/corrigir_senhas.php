<?php
/**
 * Script para corrigir senhas no banco de dados
 * Este script atualiza todas as senhas que estão em texto simples para hash
 * e define senhas padrão para usuários sem senha
 */

session_start();

// Verificar se é admin
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    die("Acesso negado. Apenas administradores podem executar este script.");
}

$host = "localhost";
$user = "root";
$pass = "senaisp";
$db   = "Techfit";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Senha padrão para usuários sem senha
$senha_padrao = "123456"; // Altere esta senha se desejar
$senha_padrao_hash = password_hash($senha_padrao, PASSWORD_DEFAULT);

echo "<h2>Correção de Senhas - TechFit</h2>";
echo "<p>Este script irá:</p>";
echo "<ul>";
echo "<li>Atualizar senhas em texto simples para hash</li>";
echo "<li>Definir senha padrão '{$senha_padrao}' para usuários sem senha</li>";
echo "</ul>";

// Buscar todos os usuários
$query = "SELECT id_usuario, nome, email, senha FROM usuarios";
$result = $conn->query($query);

if (!$result) {
    die("Erro ao buscar usuários: " . $conn->error);
}

$atualizados = 0;
$criados = 0;
$erros = [];

while ($row = $result->fetch_assoc()) {
    $id = $row['id_usuario'];
    $nome = $row['nome'];
    $senha_atual = $row['senha'];
    
    $senha_nova = null;
    $acao = "";
    
    // Verificar se a senha está vazia ou null
    if (empty($senha_atual) || $senha_atual === null) {
        // Criar senha padrão
        $senha_nova = $senha_padrao_hash;
        $acao = "CRIADA";
        $criados++;
    } 
    // Verificar se a senha não está em formato hash (não começa com $2y$)
    elseif (!preg_match('/^\$2[ayb]\$.{56}$/', $senha_atual)) {
        // Senha em texto simples, criar hash
        $senha_nova = password_hash($senha_atual, PASSWORD_DEFAULT);
        $acao = "ATUALIZADA (texto simples -> hash)";
        $atualizados++;
    } else {
        // Senha já está em hash, pular
        continue;
    }
    
    // Atualizar no banco
    $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id_usuario = ?");
    $stmt->bind_param("si", $senha_nova, $id);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✓ Usuário '{$nome}' (ID: {$id}) - Senha {$acao}</p>";
    } else {
        $erros[] = "Erro ao atualizar usuário '{$nome}' (ID: {$id}): " . $stmt->error;
        echo "<p style='color: red;'>✗ Erro ao atualizar usuário '{$nome}' (ID: {$id})</p>";
    }
    
    $stmt->close();
}

echo "<hr>";
echo "<h3>Resumo:</h3>";
echo "<p><strong>Senhas atualizadas (texto simples -> hash):</strong> {$atualizados}</p>";
echo "<p><strong>Senhas criadas (usuários sem senha):</strong> {$criados}</p>";
echo "<p><strong>Total processado:</strong> " . ($atualizados + $criados) . "</p>";

if (!empty($erros)) {
    echo "<h3>Erros:</h3>";
    foreach ($erros as $erro) {
        echo "<p style='color: red;'>{$erro}</p>";
    }
}

echo "<hr>";
echo "<p><strong>Senha padrão para novos usuários:</strong> <code>{$senha_padrao}</code></p>";
echo "<p style='color: orange;'><strong>⚠️ IMPORTANTE:</strong> Altere esta senha após o primeiro login!</p>";

$conn->close();
?>

