#!/usr/bin/env php
<?php
/**
 * LIMPADOR DE DADOS DE TESTE
 * Remove produtos e usuários criados durante testes
 * Execute: php limpar_testes.php
 */

require 'config/config.php';

echo "\n" . str_repeat("=", 60) . "\n";
echo "   LIMPADOR DE DADOS - InteliFood\n";
echo str_repeat("=", 60) . "\n\n";

echo "Aviso: Esta ferramenta vai limpar dados de teste do banco.\n";
echo "Se você tem dados importantes que começam com 'Teste' ou 'Produto Teste',\n";
echo "faça um backup primeiro!\n\n";

$pdo = Database::getConnection();

// Funções de limpeza
$limpezas = [
    [
        'nome' => 'Produtos de teste',
        'pattern' => 'Teste%',
        'tabela' => 'Produtos',
        'coluna' => 'nome',
    ],
    [
        'nome' => 'Usuários de teste', 
        'pattern' => 'testhttp%',
        'tabela' => 'Usuario',
        'coluna' => 'email',
    ],
    [
        'nome' => 'Usuários Teste User',
        'pattern' => 'Teste User%',
        'tabela' => 'Usuario',
        'coluna' => 'nome',
    ],
];

$totalRemovido = 0;

foreach ($limpezas as $limpeza) {
    $nome = $limpeza['nome'];
    $tabela = $limpeza['tabela'];
    $coluna = $limpeza['coluna'];
    $pattern = $limpeza['pattern'];
    
    try {
        // Contar
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM $tabela WHERE $coluna LIKE ?");
        $stmt->execute([$pattern]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            // Remover
            $deleteStmt = $pdo->prepare("DELETE FROM $tabela WHERE $coluna LIKE ?");
            $deleteStmt->execute([$pattern]);
            echo "✓ $nome: Removidos $count registros\n";
            $totalRemovido += $count;
        } else {
            echo "  $nome: Nenhum registro encontrado\n";
        }
    } catch (Exception $e) {
        echo "✗ Erro ao limpar $nome: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Total de registros removidos: $totalRemovido\n";
echo str_repeat("=", 60) . "\n\n";

// Mostrar dados atuais
echo "Dados atuais no banco:\n\n";
try {
    $contas = [
        'Produtos' => 'SELECT COUNT(*) as cnt FROM Produtos',
        'Usuários' => 'SELECT COUNT(*) as cnt FROM Usuario',
        'Mesas' => 'SELECT COUNT(*) as cnt FROM Mesas',
        'Vendas' => 'SELECT COUNT(*) as cnt FROM Vendas',
    ];
    
    foreach ($contas as $nome => $sql) {
        $stmt = $pdo->query($sql);
        $count = $stmt->fetchColumn();
        echo "  $nome: $count registros\n";
    }
} catch (Exception $e) {
    echo "Erro ao contar registros: " . $e->getMessage() . "\n";
}

echo "\n";
