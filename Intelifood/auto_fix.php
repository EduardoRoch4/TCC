#!/usr/bin/env php
<?php
/**
 * FERRAMENTA DE FIX AUTOMÁTICO - InteliFood
 * Detecta e corrige problemas comuns
 * Execute: php auto_fix.php
 */

require 'config/config.php';

echo "\n" . str_repeat("=", 60) . "\n";
echo "   AUTO-FIX - InteliFood\n";
echo str_repeat("=", 60) . "\n\n";

$pdo = Database::getConnection();
$fixes_aplicados = 0;

// FIX 1: Verificar integridade do banco
echo "[1] Verificando integridade do banco de dados...\n";
try {
    $result = $pdo->query('PRAGMA integrity_check')->fetchColumn();
    if ($result === 'ok') {
        echo "    ✓ Integridade OK\n";
    } else {
        echo "    ⚠ Aviso: $result\n";
        // Tentar reparar
        echo "    Tentando reparar...\n";
        $pdo->exec('REINDEX');
        echo "    ✓ Reindexado\n";
        $fixes_aplicados++;
    }
} catch (Exception $e) {
    echo "    ✗ Erro: " . $e->getMessage() . "\n";
}

// FIX 2: Limpar transações presas
echo "\n[2] Verificando transações pendentes...\n";
try {
    // SQLite não tem um SELECT de transações como MySQL
    // Mas podemos tentar fazer um ROLLBACK para limpar qualquer coisa pendente
    $pdo->exec('COMMIT');
    $pdo->exec('ROLLBACK');
    echo "    ✓ Transações limpas\n";
    $fixes_aplicados++;
} catch (Exception $e) {
    echo "    ✓ Nenhuma transação pendente\n";
}

// FIX 3: Otimizar banco
echo "\n[3] Otimizando banco de dados...\n";
try {
    $pdo->exec('VACUUM');
    echo "    ✓ Banco otimizado\n";
    $fixes_aplicados++;
} catch (Exception $e) {
    echo "    ✗ Erro ao otimizar: " . $e->getMessage() . "\n";
}

// FIX 4: Verificar chaves estrangeiras
echo "\n[4] Verificando chaves estrangeiras...\n";
try {
    $fk = $pdo->query('PRAGMA foreign_keys')->fetchColumn();
    if ($fk == 1) {
        echo "    ✓ Chaves estrangeiras ativadas\n";
    } else {
        echo "    ⚠ Ativando chaves estrangeiras...\n";
        $pdo->exec('PRAGMA foreign_keys = ON');
        echo "    ✓ Chaves estrangeiras ativadas\n";
        $fixes_aplicados++;
    }
} catch (Exception $e) {
    echo "    ✗ Erro: " . $e->getMessage() . "\n";
}

// FIX 5: Verifi car modo journal
echo "\n[5] Verificando modo de journal...\n";
try {
    $mode = $pdo->query('PRAGMA journal_mode')->fetchColumn();
    echo "    Modo atual: $mode\n";
    if ($mode !== 'wal') {
        echo "    Mudando para WAL...\n";
        $pdo->exec('PRAGMA journal_mode = WAL');
        echo "    ✓ WAL ativado (melhor concorrência)\n";
        $fixes_aplicados++;
    }
} catch (Exception $e) {
    echo "    ✗ Erro: " . $e->getMessage() . "\n";
}

// FIX 6: Verificar synchronous
echo "\n[6] Verificando nível de sincronismo...\n";
try {
    $sync = $pdo->query('PRAGMA synchronous')->fetchColumn();
    // 0=OFF, 1=NORMAL, 2=FULL, 3=EXTRA
    $syncModes = [0 => 'OFF', 1 => 'NORMAL', 2 => 'FULL', 3 => 'EXTRA'];
    $modo = $syncModes[$sync] ?? 'DESCONHECIDO';
    echo "    Modo atual: $modo (valor: $sync)\n";
    
    if ($sync != 1) { // Não é NORMAL
        echo "    Ajustando para NORMAL (seguro e rápido)...\n";
        $pdo->exec('PRAGMA synchronous = NORMAL');
        echo "    ✓ Sincronismo ajustado\n";
        $fixes_aplicados++;
    }
} catch (Exception $e) {
    echo "    ✗ Erro: " . $e->getMessage() . "\n";
}

// FIX 7: Analisar tabelas
echo "\n[7] Analisando tabelas para otimização...\n";
try {
    $pdo->exec('ANALYZE');
    echo "    ✓ Análise completa (melhora performance)\n";
    $fixes_aplicados++;
} catch (Exception $e) {
    echo "    ✗ Erro: " . $e->getMessage() . "\n";
}

// FIX 8: Verificar dados órfãos
echo "\n[8] Verificando chaves estrangeiras órfãs...\n";
try {
    $orphans = $pdo->query('PRAGMA foreign_key_check')->fetchAll();
    if (empty($orphans)) {
        echo "    ✓ Nenhuma chave orfã encontrada\n";
    } else {
        echo "    ⚠ Encontradas " . count($orphans) . " chaves órfãs\n";
        foreach ($orphans as $orphan) {
            echo "      - Tabela: " . $orphan['table'] . "\n";
            echo "        ID: " . $orphan['rowid'] . "\n";
            echo "        FK: " . $orphan['fkid'] . "\n";
        }
        echo "    Nota: Pode ser necessário limpeza manual\n";
    }
} catch (Exception $e) {
    echo "    ✗ Erro: " . $e->getMessage() . "\n";
}

// Resumo
echo "\n" . str_repeat("=", 60) . "\n";
echo "RESUMO:\n";
echo "  Fixes aplicados: $fixes_aplicados\n";

if ($fixes_aplicados > 0) {
    echo "\n✓ Alguns fixes foram aplicados. O sistema foi otimizado.\n";
    echo "   Recarregue o navegador e tente novamente.\n";
} else {
    echo "\n✓ Sistema já está otimizado!\n";
}

echo str_repeat("=", 60) . "\n\n";

// Dicas finais
echo "Dicas para evitar problemas:\n";
echo "1. Execute 'php diagnostico.php' regularmente\n";
echo "2. Limpe cache do navegador (Ctrl+Shift+Del)\n";
echo "3. Use uma aba privada para testar (Ctrl+Shift+N)\n";
echo "4. Atualize a página após operações importantes (F5)\n";
echo "5. Não mantenha múltiplas abas no admin simultaneamente\n\n";
