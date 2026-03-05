#!/usr/bin/env php
<?php
/**
 * DIAGNÓSTICO INTELIFOOD
 * Verifica se todos os componentes estão operacionais
 * Execute: php diagnostico.php
 */

require 'config/config.php';

echo "\n" . str_repeat("=", 60) . "\n";
echo "   DIAGNÓSTICO - InteliFood v1.0\n";
echo str_repeat("=", 60) . "\n\n";

$erros = [];
$avisos = [];
$sucessos = [];

// 1. Verificar banco de dados
echo "[1/7] Verificando banco de dados SQLite...\n";
$dbPath = DB_SQLITE_PATH;
if (file_exists($dbPath)) {
    echo "     ✓ Arquivo SQLite encontrado: $dbPath\n";
    $sucessos[] = "Banco de dados existe";
    
    // Verificar tamanho
    $tamanho = filesize($dbPath);
    $tamanhoMB = $tamanho / (1024 * 1024);
    echo "     ✓ Tamanho: " . number_format($tamanhoMB, 2) . " MB\n";
    
    // Testar conexão
    try {
        $pdo = Database::getConnection();
        echo "     ✓ Conexão estabelecida com sucesso\n";
        $sucessos[] = "Conexão com banco funcionando";
    } catch (Exception $e) {
        echo "     ✗ ERRO: Não conseguiu conectar ao banco\n";
        echo "       " . $e->getMessage() . "\n";
        $erros[] = "Connexão com banco falhou: " . $e->getMessage();
    }
} else {
    echo "     ✗ ERRO: Arquivo SQLite não encontrado!\n";
    echo "       Caminho esperado: $dbPath\n";
    echo "       Execute: php instalar_sqlite.php\n";
    $erros[] = "Banco de dados não existe";
}

// 2. Verificar tabelas
echo "\n[2/7] Verificando tabelas do banco...\n";
try {
    $pdo = Database::getConnection();
    $tabelas = ['Usuario', 'Mesas', 'Produtos', 'Vendas', 'Venda_Itens', 'Contas_Pagar'];
    $tabelasOk = [];
    $tabelasErro = [];
    
    foreach ($tabelas as $tab) {
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
        $stmt->execute([$tab]);
        if ($stmt->fetch()) {
            $tabelasOk[] = $tab;
        } else {
            $tabelasErro[] = $tab;
        }
    }
    
    if (empty($tabelasErro)) {
        echo "     ✓ Todas as tabelas existem\n";
        foreach ($tabelasOk as $t) {
            echo "       ✓ $t\n";
        }
        $sucessos[] = "Todas as tabelas existem";
    } else {
        echo "     ✗ ERRO: Tabelas faltando:\n";
        foreach ($tabelasErro as $t) {
            echo "       ✗ $t\n";
        }
        $erros[] = "Tabelas faltando: " . implode(', ', $tabelasErro);
    }
} catch (Exception $e) {
    echo "     ✗ ERRO ao verificar tabelas: " . $e->getMessage() . "\n";
    $erros[] = "Erro ao verificar tabelas";
}

// 3. Verificar dados básicos
echo "\n[3/7] Verificando dados básicos...\n";
try {
    $pdo = Database::getConnection();
    
    $contas = [
        'Produtos' => 'SELECT COUNT(*) as cnt FROM Produtos',
        'Usuários' => 'SELECT COUNT(*) as cnt FROM Usuario',
        'Mesas' => 'SELECT COUNT(*) as cnt FROM Mesas',
        'Vendas' => 'SELECT COUNT(*) as cnt FROM Vendas',
    ];
    
    foreach ($contas as $nome => $sql) {
        $stmt = $pdo->query($sql);
        $count = $stmt->fetchColumn();
        echo "     ✓ $nome: $count registros\n";
    }
    $sucessos[] = "Dados básicos encontrados";
} catch (Exception $e) {
    echo "     ✗ ERRO ao contar dados: " . $e->getMessage() . "\n";
    $erros[] = "Erro ao contar dados";
}

// 4. Testar CRUD básico
echo "\n[4/7] Testando operações CRUD...\n";
try {
    // Criar produto
    $produtoModel = new Produto();
    $nomeTest = 'Teste Diagnóstico ' . time();
    $id = $produtoModel->criar($nomeTest, 'Teste', 10.00, 'Teste');
    echo "     ✓ CREATE: Produto criado com ID $id\n";
    
    // Ler produto
    $produto = $produtoModel->porId($id);
    if ($produto && $produto['nome'] === $nomeTest) {
        echo "     ✓ READ: Produto recuperado\n";
    } else {
        throw new Exception("Produto não foi encontrado após criação");
    }
    
    // Atualizar produto
    $produtoModel->atualizar($id, 'Teste Atualizado', 'Teste', 15.00, 'Teste', 1);
    $produtoAtualizado = $produtoModel->porId($id);
    if ($produtoAtualizado['preco'] == 15.00) {
        echo "     ✓ UPDATE: Produto atualizado\n";
    } else {
        throw new Exception("Produto não foi atualizado");
    }
    
    // Deletar produto
    $produtoModel->excluir($id);
    $produtoDeletado = $produtoModel->porId($id);
    if ($produtoDeletado === null) {
        echo "     ✓ DELETE: Produto deletado\n";
    } else {
        throw new Exception("Produto não foi deletado");
    }
    
    $sucessos[] = "Todas as operações CRUD funcionam";
} catch (Exception $e) {
    echo "     ✗ ERRO nas operações CRUD: " . $e->getMessage() . "\n";
    $erros[] = "CRUD falhou: " . $e->getMessage();
}

// 5. Testar fluxo de pedido
echo "\n[5/7] Testando fluxo de pedido...\n";
try {
    $mesaModel = new Mesa();
    $vendaModel = new Venda();
    $produtoModel = new Produto();
    
    // Obter mesa
    $mesas = $mesaModel->listar(true);
    if (empty($mesas)) {
        throw new Exception("Nenhuma mesa disponível");
    }
    $mesaId = $mesas[0]['id'];
    
    // Criar venda
    $vendaId = $vendaModel->criar(null, $mesaId);
    echo "     ✓ Venda criada com ID $vendaId\n";
    
    // Adicionar item
    $produtos = $produtoModel->listar(true);
    if (empty($produtos)) {
        throw new Exception("Nenhum produto no cardápio");
    }
    $produtoId = $produtos[0]['id'];
    $vendaModel->adicionarItem($vendaId, $produtoId, 2, 10.00);
    echo "     ✓ Item adicionado à venda\n";
    
    // Fechar venda
    $vendaModel->fechar($vendaId);
    $vendaFechada = $vendaModel->porId($vendaId);
    if ($vendaFechada['status'] === 'fechado') {
        echo "     ✓ Venda fechada\n";
    }
    
    // Liberar mesa
    $mesaModel->liberar($mesaId);
    echo "     ✓ Mesa liberada\n";
    
    $sucessos[] = "Fluxo completo de pedido funciona";
} catch (Exception $e) {
    echo "     ✗ ERRO no fluxo de pedido: " . $e->getMessage() . "\n";
    $erros[] = "Fluxo de pedido falhou: " . $e->getMessage();
}

// 6. Verificar permissões
echo "\n[6/7] Verificando permissões de arquivo...\n";
if (is_writable($dbPath)) {
    echo "     ✓ Arquivo SQLite é writeável\n";
    $sucessos[] = "Arquivo com permissões corretas";
} else {
    echo "     ✗ AVISO: Arquivo SQLite NÃO é writeável\n";
    $avisos[] = "Arquivo SQLite pode estar com permissões insuficientes";
}

$dataDir = dirname($dbPath);
if (is_writable($dataDir)) {
    echo "     ✓ Diretório data é writeável\n";
} else {
    echo "     ✗ AVISO: Diretório data NÃO é writeável\n";
    $avisos[] = "Diretório data pode estar com permissões insuficientes";
}

// 7. Verificar configuração
echo "\n[7/7] Verificando configuração...\n";
echo "     Driver: " . DB_DRIVER . "\n";
echo "     Base URL: " . BASE_URL . "\n";
echo "     APP Name: " . APP_NAME . "\n";
if (DB_DRIVER === 'sqlite') {
    echo "     ✓ Usando SQLite corretamente\n";
    $sucessos[] = "Configuração correta";
}

// Resumo
echo "\n" . str_repeat("=", 60) . "\n";
echo "RESUMO:\n";
echo "  ✓ Sucessos: " . count($sucessos) . "\n";
echo "  ⚠ Avisos: " . count($avisos) . "\n";  
echo "  ✗ Erros: " . count($erros) . "\n";

if (count($erros) === 0) {
    echo "\n✓✓✓ SISTEMA OPERACIONAL - Tudo funcionando! ✓✓✓\n";
} else {
    echo "\n✗✗✗ PROBLEMAS ENCONTRADOS ✗✗✗\n";
    echo "\nErros:\n";
    foreach ($erros as $e) {
        echo "  - $e\n";
    }
}

if (count($avisos) > 0) {
    echo "\nAvisos:\n";
    foreach ($avisos as $a) {
        echo "  ⚠ $a\n";
    }
}

echo str_repeat("=", 60) . "\n";
echo "\nPróximas ações:\n";
echo "1. Teste através do navegador: http://localhost:8097\n";
echo "2. Limpe cache do navegador (Ctrl+Shift+Del)\n";
echo "3. Tente criar um novo produto através da Admin\n";
echo "4. Execute este diagnóstico novamente: php diagnostico.php\n";
echo "\nPara mais ajuda, execute: php -S localhost:8097 router.php\n";
echo "\n";
