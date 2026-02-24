<?php
require_once 'config/config.php';
requireLogin();

$pageTitle = 'Minha Carteira';

$pdo = getConnection();
$usuarioId = $_SESSION['usuario_id'];

// Detectar qual coluna de data existe (data_compra ou data_ultima_atualizacao)
$colData = 'data_compra';
try {
    $cols = $pdo->query("SHOW COLUMNS FROM carteira_investimentos")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('data_ultima_atualizacao', $cols) && !in_array('data_compra', $cols)) {
        $colData = 'data_ultima_atualizacao';
    }
} catch (PDOException $e) { /* usa data_compra */ }

// Mensagem de feedback
$mensagem = '';
if (isset($_GET['sucesso'])) {
    $mensagem = '<div class="alert alert-success">Operação realizada com sucesso!</div>';
} elseif (isset($_GET['erro'])) {
    $mensagem = '<div class="alert alert-error">Erro: valores inválidos ou operação não permitida.</div>';
}

// Processar adição de ativo via GET (para abrir modal)
$ativoParaComprar = null;
if (isset($_GET['Comprar'])) {
    $ativoParaComprar = (int)$_GET['Comprar'];
}

// Processar formulário (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // ---------- Renda Fixa ----------
    if ($acao === 'renda_fixa_adicionar') {
        $emissor = trim($_POST['emissor'] ?? '');
        $tipoTitulo = trim($_POST['tipo_titulo'] ?? '');
        $indexador = trim($_POST['indexador'] ?? '');
        $taxa = (float)str_replace([',', '%'], ['.', ''], $_POST['taxa'] ?? 0);
        $forma = $_POST['forma'] === 'PRE_FIXADO' ? 'PRE_FIXADO' : 'POS_FIXADO';
        $valorInvestido = (float)str_replace(',', '.', $_POST['valor_investido'] ?? 0);
        $dataCompra = $_POST['data_compra_rf'] ?? date('Y-m-d');
        $dataVencimento = !empty($_POST['data_vencimento_rf']) ? $_POST['data_vencimento_rf'] : null;
        $liquidezDiaria = isset($_POST['liquidez_diaria']) ? 1 : 0;
        $notasRf = trim($_POST['notas_rf'] ?? '');
        if ($emissor !== '' && $tipoTitulo !== '' && $valorInvestido > 0) {
            try {
                $st = $pdo->prepare("INSERT INTO carteira_renda_fixa (usuario_id, emissor, tipo_titulo, indexador, taxa, forma, valor_investido, data_compra, data_vencimento, liquidez_diaria, notas) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $st->execute([$usuarioId, $emissor, $tipoTitulo, $indexador, $taxa, $forma, $valorInvestido, $dataCompra, $dataVencimento, $liquidezDiaria, $notasRf]);
                header('Location: carteira.php?sucesso=1');
                exit;
            } catch (PDOException $e) { $mensagem = '<div class="alert alert-error">Erro ao salvar renda fixa. Execute database/migration_renda_fixa.sql</div>'; }
        }
    }
    if ($acao === 'renda_fixa_remover') {
        $idRf = (int)($_POST['id_renda_fixa'] ?? 0);
        if ($idRf > 0) {
            try {
                $pdo->prepare("DELETE FROM carteira_renda_fixa WHERE id = ? AND usuario_id = ?")->execute([$idRf, $usuarioId]);
                header('Location: carteira.php?sucesso=1');
                exit;
            } catch (PDOException $e) { }
        }
    }

    // ---------- Renda variável (ações, FIIs, etc.) ----------

    if ($acao === 'Comprar' || $acao === 'atualizar') {
        // Ativo pode vir do hidden (modal com ativo fixo) ou do select (modal "Comprar" na carteira)
        $ativoId         = (int)($_POST['ativo_id'] ?? $_POST['ativo_id_select'] ?? 0);
        $qtdNova         = (float)str_replace(',', '.', $_POST['quantidade'] ?? 0);
        $precoNovo       = (float)str_replace(',', '.', $_POST['preco_medio'] ?? 0);
        $dataAtualizacao = $_POST['data_ultima_atualizacao'] ?? date('Y-m-d');
        $notas           = trim($_POST['notas'] ?? '');

        if ($ativoId <= 0 || $qtdNova <= 0 || $precoNovo <= 0 || empty($dataAtualizacao)) {
            header('Location: carteira.php?erro=1');
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmtCheck = $pdo->prepare("
                SELECT id, quantidade, preco_medio 
                FROM carteira_investimentos 
                WHERE usuario_id = ? AND ativo_id = ?
            ");
            $stmtCheck->execute([$usuarioId, $ativoId]);
            $posicao = $stmtCheck->fetch();
            $idInvestimento = (int)($_POST['investimento_id'] ?? 0);
            if ($idInvestimento > 0 && $acao === 'atualizar') {
                $st2 = $pdo->prepare("SELECT id, ativo_id, quantidade, preco_medio FROM carteira_investimentos WHERE id = ? AND usuario_id = ?");
                $st2->execute([$idInvestimento, $usuarioId]);
                $posicao = $st2->fetch() ?: $posicao;
            }

            if ($posicao && $acao === 'Comprar') {
                // COMPRA ADICIONAL: soma quantidade e recalcula preço médio (uma única linha por ativo)
                $qtdTotal        = $posicao['quantidade'] + $qtdNova;
                $valorAntigo     = $posicao['quantidade'] * $posicao['preco_medio'];
                $valorNovo       = $qtdNova * $precoNovo;
                $precoMedioFinal = ($valorAntigo + $valorNovo) / $qtdTotal;

                $stmt = $pdo->prepare("
                    UPDATE carteira_investimentos 
                    SET quantidade = :qtd, preco_medio = :preco, {$colData} = LEAST({$colData}, :data), notas = COALESCE(NULLIF(TRIM(:notas), ''), notas), updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':qtd' => $qtdTotal, ':preco' => round($precoMedioFinal, 4), ':data' => $dataAtualizacao, ':notas' => $notas, ':id' => $posicao['id']
                ]);
            } elseif ($acao === 'atualizar' && $posicao) {
                $stmt = $pdo->prepare("
                    UPDATE carteira_investimentos 
                    SET quantidade = :qtd, preco_medio = :preco, {$colData} = :data, notas = :notas, updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id AND usuario_id = :uid
                ");
                $stmt->execute([
                    ':qtd' => $qtdNova, ':preco' => round($precoNovo, 4), ':data' => $dataAtualizacao, ':notas' => $notas, ':id' => $posicao['id'], ':uid' => $usuarioId
                ]);
                // Remove outras linhas duplicadas do mesmo ativo (mantém só esta)
                $pdo->prepare("DELETE FROM carteira_investimentos WHERE usuario_id = ? AND ativo_id = ? AND id != ?")->execute([$usuarioId, $ativoId, $posicao['id']]);
            } else {
                // Novo ativo: INSERT com ON DUPLICATE KEY = uma única linha por (usuario, ativo)
                $stmtInsert = $pdo->prepare("
                    INSERT INTO carteira_investimentos 
                    (usuario_id, ativo_id, quantidade, preco_medio, {$colData}, notas)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        quantidade = quantidade + VALUES(quantidade),
                        preco_medio = (quantidade * preco_medio + VALUES(quantidade) * VALUES(preco_medio)) / (quantidade + VALUES(quantidade)),
                        {$colData} = LEAST({$colData}, VALUES({$colData})),
                        notas = COALESCE(NULLIF(TRIM(VALUES(notas)), ''), notas),
                        updated_at = CURRENT_TIMESTAMP
                ");
                $stmtInsert->execute([$usuarioId, $ativoId, $qtdNova, round($precoNovo, 4), $dataAtualizacao, $notas]);
            }

            $pdo->commit();
            header('Location: carteira.php?sucesso=1');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensagem = '<div class="alert alert-error">Erro no banco: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    if ($acao === 'Venda') {
        $ativoIdVenda = (int)($_POST['ativo_id'] ?? 0);
        $qtdVenda = (float)str_replace(',', '.', $_POST['quantidade_venda'] ?? 0);
        if ($ativoIdVenda > 0 && $qtdVenda > 0) {
            // Buscar posição agregada (mesma lógica da query principal)
            $st = $pdo->prepare("
                SELECT SUM(ci.quantidade) as quantidade_total,
                       SUM(ci.quantidade * ci.preco_medio) / NULLIF(SUM(ci.quantidade), 0) as preco_medio,
                       a.preco_atual
                FROM carteira_investimentos ci
                JOIN ativos a ON ci.ativo_id = a.id
                WHERE ci.usuario_id = ? AND ci.ativo_id = ?
                GROUP BY ci.usuario_id, ci.ativo_id, a.preco_atual
            ");
            $st->execute([$usuarioId, $ativoIdVenda]);
            $pos = $st->fetch();
            
            if ($pos && $pos['quantidade_total'] > 0) {
                $qtdAtual = (float)$pos['quantidade_total'];
                $precoMedio = (float)$pos['preco_medio'];
                $precoVenda = (float)$pos['preco_atual'];
                
                // Calcular lucro/prejuízo da venda
                $lucroVenda = ($precoVenda - $precoMedio) * $qtdVenda;
                
                // Buscar todos os IDs para deletar ou atualizar
                $stIds = $pdo->prepare("SELECT id FROM carteira_investimentos WHERE usuario_id = ? AND ativo_id = ? ORDER BY id");
                $stIds->execute([$usuarioId, $ativoIdVenda]);
                $ids = $stIds->fetchAll(PDO::FETCH_COLUMN);
                
                if ($qtdVenda >= $qtdAtual) {
                    // Vende tudo - deleta todas as linhas
                    $pdo->prepare("DELETE FROM carteira_investimentos WHERE usuario_id = ? AND ativo_id = ?")->execute([$usuarioId, $ativoIdVenda]);
                } else {
                    // Vende parcialmente - distribui a venda proporcionalmente entre as linhas
                    $qtdRestante = $qtdVenda;
                    foreach ($ids as $id) {
                        if ($qtdRestante <= 0) break;
                        
                        $stLinha = $pdo->prepare("SELECT quantidade FROM carteira_investimentos WHERE id = ?");
                        $stLinha->execute([$id]);
                        $linha = $stLinha->fetch();
                        $qtdLinha = (float)$linha['quantidade'];
                        
                        if ($qtdRestante >= $qtdLinha) {
                            // Remove toda a linha
                            $pdo->prepare("DELETE FROM carteira_investimentos WHERE id = ?")->execute([$id]);
                            $qtdRestante -= $qtdLinha;
                        } else {
                            // Reduz quantidade da linha
                            $pdo->prepare("UPDATE carteira_investimentos SET quantidade = quantidade - ? WHERE id = ?")->execute([$qtdRestante, $id]);
                            $qtdRestante = 0;
                        }
                    }
                }
                
                // Registrar lucro/prejuízo no histórico
                // O lucro será refletido automaticamente quando recalcular o valor_total da carteira
                // Mas vamos criar um registro de operação de venda para histórico
                try {
                    $hoje = date('Y-m-d');
                    // Recalcular valor total atual da carteira após a venda
                    $stRecalc = $pdo->prepare("
                        SELECT SUM(ci.quantidade * a.preco_atual) as valor_total,
                               SUM(ci.quantidade * ci.preco_medio) as valor_investido
                        FROM carteira_investimentos ci
                        JOIN ativos a ON ci.ativo_id = a.id
                        WHERE ci.usuario_id = ?
                    ");
                    $stRecalc->execute([$usuarioId]);
                    $recalc = $stRecalc->fetch();
                    
                    if ($recalc) {
                        $valorTotalAtual = (float)($recalc['valor_total'] ?? 0);
                        $valorInvestidoAtual = (float)($recalc['valor_investido'] ?? 0);
                        
                        // Atualizar histórico com valores recalculados
                        $stHist = $pdo->prepare("
                            INSERT INTO historico_valor_carteira (usuario_id, data_ref, valor_total, valor_aplicado)
                            VALUES (?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE 
                                valor_total = VALUES(valor_total),
                                valor_aplicado = VALUES(valor_aplicado)
                        ");
                        $stHist->execute([$usuarioId, $hoje, $valorTotalAtual, $valorInvestidoAtual]);
                    }
                } catch (PDOException $e) {
                    // Tabela pode não existir ainda, ignora
                    error_log("Erro ao atualizar histórico após venda: " . $e->getMessage());
                }
            }
            header('Location: carteira.php?sucesso=1');
            exit;
        }
    }

    if ($acao === 'remover') {
        $ativoIdRemover = (int)($_POST['ativo_id'] ?? 0);
        $investimentoId = (int)($_POST['investimento_id'] ?? 0);
        if ($ativoIdRemover > 0) {
            $stmt = $pdo->prepare("DELETE FROM carteira_investimentos WHERE usuario_id = ? AND ativo_id = ?");
            $stmt->execute([$usuarioId, $ativoIdRemover]);
        } elseif ($investimentoId > 0) {
            $stmt = $pdo->prepare("DELETE FROM carteira_investimentos WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$investimentoId, $usuarioId]);
        }
        header('Location: carteira.php?sucesso=1');
        exit;
    }
}

// Buscar carteira: subquery agrega primeiro (uma linha por ativo), evita duplicata na tela
// Usa DISTINCT no final para garantir que não há duplicatas mesmo se houver problema no JOIN
$stmt = $pdo->prepare("
    SELECT DISTINCT agg.id, agg.usuario_id, agg.ativo_id, agg.quantidade, agg.preco_medio, agg.data_compra, agg.notas,
           a.codigo, a.nome, a.preco_atual, a.variacao_dia, COALESCE(t.nome, 'Outros') as tipo
    FROM (
        SELECT MIN(ci.id) as id, ci.usuario_id, ci.ativo_id,
               SUM(ci.quantidade) as quantidade,
               SUM(ci.quantidade * ci.preco_medio) / NULLIF(SUM(ci.quantidade), 0) as preco_medio,
               MIN(ci.{$colData}) as data_compra,
               MAX(ci.notas) as notas
        FROM carteira_investimentos ci
        WHERE ci.usuario_id = ?
        GROUP BY ci.usuario_id, ci.ativo_id
    ) agg
    JOIN ativos a ON agg.ativo_id = a.id
    LEFT JOIN tipos_ativo t ON a.tipo_id = t.id
    ORDER BY agg.quantidade * a.preco_atual DESC
");
$stmt->execute([$usuarioId]);
$investimentos = $stmt->fetchAll();

// Garantir que não há duplicatas por ativo_id (última verificação)
$investimentosUnicos = [];
$ativosVistos = [];
foreach ($investimentos as $inv) {
    $key = (int)$inv['ativo_id'];
    if (!isset($ativosVistos[$key])) {
        $ativosVistos[$key] = true;
        $investimentosUnicos[] = $inv;
    }
}
$investimentos = $investimentosUnicos;

$valorTotal = 0;
$valorInvestido = 0;
foreach ($investimentos as &$inv) {
    $inv['valor_atual']     = $inv['quantidade'] * $inv['preco_atual'];
    $inv['valor_investido'] = $inv['quantidade'] * $inv['preco_medio'];
    $inv['resultado']       = $inv['valor_atual'] - $inv['valor_investido'];
    $inv['resultado_pct']   = $inv['valor_investido'] > 0 ? (($inv['valor_atual'] / $inv['valor_investido']) - 1) * 100 : 0;
    $valorTotal            += $inv['valor_atual'];
    $valorInvestido        += $inv['valor_investido'];
}
$resultadoTotal    = $valorTotal - $valorInvestido;
$resultadoTotalPct = $valorInvestido > 0 ? (($valorTotal / $valorInvestido) - 1) * 100 : 0;

// Buscar ativo específico para modal (se veio via GET)
$ativosDisponiveis = [];
if ($ativoParaComprar) {
    $stmt = $pdo->prepare("SELECT * FROM ativos WHERE id = ?");
    $stmt->execute([$ativoParaComprar]);
    $ativosDisponiveis = $stmt->fetchAll();
}

// Renda fixa (se a tabela existir)
$rendaFixaLista = [];
try {
    $stmtRf = $pdo->prepare("SELECT * FROM carteira_renda_fixa WHERE usuario_id = ? ORDER BY data_vencimento ASC, emissor");
    $stmtRf->execute([$usuarioId]);
    $rendaFixaLista = $stmtRf->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { /* tabela não existe */ }

$valorTotalRendaFixa = array_sum(array_column($rendaFixaLista, 'valor_investido'));
$valorTotalGeral = $valorTotal + $valorTotalRendaFixa;

// Filtros para carteira
$filtroBusca = trim($_GET['busca_carteira'] ?? '');
$filtroTipo = $_GET['tipo_carteira'] ?? '';
$filtroRentabilidade = $_GET['rentabilidade'] ?? '';
$filtroOrdenacao = $_GET['ordenar_carteira'] ?? 'valor_desc';

// Aplicar filtros aos investimentos
$investimentosFiltrados = $investimentos;

if ($filtroBusca) {
    $investimentosFiltrados = array_filter($investimentosFiltrados, function($inv) use ($filtroBusca) {
        return stripos($inv['codigo'], $filtroBusca) !== false || 
               stripos($inv['nome'], $filtroBusca) !== false;
    });
}

if ($filtroTipo) {
    $investimentosFiltrados = array_filter($investimentosFiltrados, function($inv) use ($filtroTipo) {
        return $inv['tipo'] == $filtroTipo;
    });
}

if ($filtroRentabilidade === 'lucro') {
    $investimentosFiltrados = array_filter($investimentosFiltrados, function($inv) {
        return $inv['resultado'] > 0;
    });
} elseif ($filtroRentabilidade === 'prejuizo') {
    $investimentosFiltrados = array_filter($investimentosFiltrados, function($inv) {
        return $inv['resultado'] < 0;
    });
}

// Ordenação
switch ($filtroOrdenacao) {
    case 'codigo_asc':
        usort($investimentosFiltrados, function($a, $b) { return strcmp($a['codigo'], $b['codigo']); });
        break;
    case 'codigo_desc':
        usort($investimentosFiltrados, function($a, $b) { return strcmp($b['codigo'], $a['codigo']); });
        break;
    case 'rentabilidade_asc':
        usort($investimentosFiltrados, function($a, $b) { return $a['resultado_pct'] <=> $b['resultado_pct']; });
        break;
    case 'rentabilidade_desc':
        usort($investimentosFiltrados, function($a, $b) { return $b['resultado_pct'] <=> $a['resultado_pct']; });
        break;
    case 'valor_asc':
        usort($investimentosFiltrados, function($a, $b) { return $a['valor_atual'] <=> $b['valor_atual']; });
        break;
    default:
        usort($investimentosFiltrados, function($a, $b) { return $b['valor_atual'] <=> $a['valor_atual']; });
}

$investimentosFiltrados = array_values($investimentosFiltrados); // Reindexar

// Buscar tipos de ativo para filtro
$stmtTipos = $pdo->query("SELECT DISTINCT nome FROM tipos_ativo ORDER BY nome");
$tiposAtivoCarteira = $stmtTipos->fetchAll(PDO::FETCH_COLUMN);

include 'includes/header.php';
?>

<main class="main main-carteira investidor10-style">
    <section class="page-header carteira-header">
        <div class="container">
            <h1>Minha Carteira</h1>
            <p>Resumo dos seus investimentos</p>
        </div>
    </section>

    <?php if ($mensagem): ?>
        <section class="container"><?php echo $mensagem; ?></section>
    <?php endif; ?>

    <section class="carteira-metricas">
        <div class="container">
            <div class="metricas-grid">
                <div class="metrica-card principal">
                    <span class="metrica-label">Patrimônio total</span>
                    <span class="metrica-valor">R$ <?php echo number_format($valorTotalGeral, 2, ',', '.'); ?></span>
                    <span class="metrica-sub <?php echo $resultadoTotalPct >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo ($resultadoTotalPct >= 0 ? '+' : '') . number_format($resultadoTotalPct, 2, ',', '.'); ?>%
                    </span>
                    <span class="metrica-hint">Variável: R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?> · Renda fixa: R$ <?php echo number_format($valorTotalRendaFixa, 2, ',', '.'); ?></span>
                </div>
                <div class="metrica-card">
                    <span class="metrica-label">Lucro total</span>
                    <span class="metrica-valor <?php echo $resultadoTotal >= 0 ? 'positive' : 'negative'; ?>">
                        R$ <?php echo number_format($resultadoTotal, 2, ',', '.'); ?>
                    </span>
                    <span class="metrica-sub <?php echo $resultadoTotalPct >= 0 ? 'positive' : 'negative'; ?>">
                        (<?php echo ($resultadoTotalPct >= 0 ? '+' : '') . number_format($resultadoTotalPct, 2, ',', '.'); ?>%)
                    </span>
                </div>
                <div class="metrica-card">
                    <span class="metrica-label">Rentabilidade</span>
                    <span class="metrica-valor <?php echo $resultadoTotalPct >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo ($resultadoTotalPct >= 0 ? '+' : '') . number_format($resultadoTotalPct, 2, ',', '.'); ?>%
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="carteira-filters">
        <div class="container">
            <div class="filters-card">
                <h3>Filtros</h3>
                <form method="GET" class="filters-form">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>Buscar ativo</label>
                            <input type="text" name="busca_carteira" value="<?php echo htmlspecialchars($filtroBusca); ?>" placeholder="Código ou nome...">
                        </div>
                        <div class="filter-group">
                            <label>Tipo</label>
                            <select name="tipo_carteira">
                                <option value="">Todos</option>
                                <?php foreach ($tiposAtivoCarteira as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo $filtroTipo == $tipo ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Rentabilidade</label>
                            <select name="rentabilidade">
                                <option value="">Todas</option>
                                <option value="lucro" <?php echo $filtroRentabilidade == 'lucro' ? 'selected' : ''; ?>>Com lucro</option>
                                <option value="prejuizo" <?php echo $filtroRentabilidade == 'prejuizo' ? 'selected' : ''; ?>>Com prejuízo</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Ordenar por</label>
                            <select name="ordenar_carteira">
                                <option value="valor_desc" <?php echo $filtroOrdenacao == 'valor_desc' ? 'selected' : ''; ?>>Valor (maior)</option>
                                <option value="valor_asc" <?php echo $filtroOrdenacao == 'valor_asc' ? 'selected' : ''; ?>>Valor (menor)</option>
                                <option value="rentabilidade_desc" <?php echo $filtroOrdenacao == 'rentabilidade_desc' ? 'selected' : ''; ?>>Rentabilidade (maior)</option>
                                <option value="rentabilidade_asc" <?php echo $filtroOrdenacao == 'rentabilidade_asc' ? 'selected' : ''; ?>>Rentabilidade (menor)</option>
                                <option value="codigo_asc" <?php echo $filtroOrdenacao == 'codigo_asc' ? 'selected' : ''; ?>>Código (A-Z)</option>
                                <option value="codigo_desc" <?php echo $filtroOrdenacao == 'codigo_desc' ? 'selected' : ''; ?>>Código (Z-A)</option>
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">Aplicar</button>
                            <a href="carteira.php" class="btn btn-outline">Limpar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="carteira-ativos">
        <div class="container">
            <div class="ativos-header">
                <h2>Meus Ativos (<?php echo count($investimentosFiltrados) + count($rendaFixaLista); ?>)</h2>
                <button type="button" class="btn btn-primary btn-add" onclick="abrirModalLancamento()">+ Adicionar lançamento</button>
            </div>

            <?php if (empty($investimentos) && empty($rendaFixaLista)): ?>
            <div class="empty-state carteira-empty">
                <div class="empty-icon">📊</div>
                <h3>Sua carteira está vazia</h3>
                <p>Adicione seus primeiros investimentos para começar a acompanhar.</p>
                <button type="button" class="btn btn-primary" onclick="abrirModalLancamento()">Adicionar lançamento</button>
            </div>
            <?php else: ?>
            <!-- Renda variável -->
            <?php if (!empty($investimentosFiltrados)): ?>
            <div class="categoria-ativos">
                <span class="categoria-titulo">Renda variável</span>
            </div>
            <div class="ativos-resumo">
                <span>Ativos <?php echo count($investimentosFiltrados); ?></span>
                <span>Valor total R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?></span>
                <span class="<?php echo $resultadoTotalPct >= 0 ? 'positive' : 'negative'; ?>">Variação <?php echo ($resultadoTotalPct >= 0 ? '+' : '') . number_format($resultadoTotalPct, 2, ',', '.'); ?>%</span>
                <span class="<?php echo $resultadoTotalPct >= 0 ? 'positive' : 'negative'; ?>">Rentabilidade <?php echo ($resultadoTotalPct >= 0 ? '+' : '') . number_format($resultadoTotalPct, 2, ',', '.'); ?>%</span>
                <span>% na carteira <?php echo $valorTotalGeral > 0 ? number_format(($valorTotal / $valorTotalGeral) * 100, 0) : 0; ?>%</span>
            </div>
            <div class="table-responsive table-carteira-wrapper">
                <table class="table table-carteira table-modelo">
                    <thead>
                        <tr>
                            <th>Ativo</th>
                            <th>Quant.</th>
                            <th>Preço Médio</th>
                            <th>Preço Atual</th>
                            <th>Variação</th>
                            <th>Rentab.</th>
                            <th>Saldo</th>
                            <th>Nota</th>
                            <th>% Carteira</th>
                            <th>% Ideal</th>
                            <th>Comprar?</th>
                            <th>Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($investimentosFiltrados as $inv):
                            $pctCarteira = $valorTotalGeral > 0 ? ($inv['valor_atual'] / $valorTotalGeral) * 100 : 0;
                        ?>
                        <tr>
                            <td class="ativo-cell">
                                <strong><?php echo htmlspecialchars($inv['codigo']); ?></strong>
                                <small><?php echo htmlspecialchars($inv['nome']); ?></small>
                            </td>
                            <td><?php echo number_format($inv['quantidade'], 0, ',', '.'); ?></td>
                            <td class="preco-edit-cell">
                                R$ <?php echo number_format($inv['preco_medio'], 2, ',', '.'); ?>
                                <button type="button" class="btn-edit-inline" onclick="editarInvestimento(<?php echo htmlspecialchars(json_encode($inv)); ?>)" title="Editar">✎</button>
                            </td>
                            <td>R$ <?php echo number_format($inv['preco_atual'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="variacao <?php echo $inv['variacao_dia'] >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo ($inv['variacao_dia'] >= 0 ? '+' : '') . number_format($inv['variacao_dia'], 2, ',', '.'); ?>%
                                </span>
                            </td>
                            <td>
                                <span class="<?php echo $inv['resultado_pct'] >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo ($inv['resultado_pct'] >= 0 ? '+' : '') . number_format($inv['resultado_pct'], 2, ',', '.'); ?>%
                                </span>
                            </td>
                            <td class="saldo-cell">R$ <?php echo number_format($inv['valor_atual'], 2, ',', '.'); ?></td>
                            <td>—</td>
                            <td><?php echo number_format($pctCarteira, 1, ',', '.'); ?>%</td>
                            <td>—</td>
                            <td><span class="comprar-nao">Não</span></td>
                            <td class="opcoes-cell">
                                <div class="dropdown-opcoes">
                                    <button type="button" class="btn-opcoes" title="Opções">⋯</button>
                                    <div class="dropdown-menu">
                                        <button type="button" onclick="editarInvestimento(<?php echo htmlspecialchars(json_encode($inv)); ?>)">Editar</button>
                                        <form method="POST" onsubmit="return confirm('Remover este ativo?');">
                                            <input type="hidden" name="acao" value="remover">
                                            <input type="hidden" name="ativo_id" value="<?php echo (int)$inv['ativo_id']; ?>">
                                            <button type="submit">Remover</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Renda fixa -->
            <?php if (!empty($rendaFixaLista)): ?>
            <div class="categoria-ativos">
                <span class="categoria-titulo">Renda fixa (CDB/LCI/LCA/...)</span>
            </div>
            <div class="ativos-resumo">
                <span>Ativos <?php echo count($rendaFixaLista); ?></span>
                <span>Valor total R$ <?php echo number_format($valorTotalRendaFixa, 2, ',', '.'); ?></span>
                <span>% na carteira <?php echo $valorTotalGeral > 0 ? number_format(($valorTotalRendaFixa / $valorTotalGeral) * 100, 0) : 0; ?>%</span>
            </div>
            <div class="table-responsive table-carteira-wrapper">
                <table class="table table-carteira table-renda-fixa">
                    <thead>
                        <tr>
                            <th>Emissor</th>
                            <th>Tipo</th>
                            <th>Indexador</th>
                            <th>Taxa</th>
                            <th>Forma</th>
                            <th>Valor</th>
                            <th>Data compra</th>
                            <th>Vencimento</th>
                            <th>Liquidez diária</th>
                            <th>Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rendaFixaLista as $rf): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($rf['emissor']); ?></strong></td>
                            <td><?php echo htmlspecialchars($rf['tipo_titulo']); ?></td>
                            <td><?php echo htmlspecialchars($rf['indexador']); ?></td>
                            <td><?php echo $rf['taxa'] !== null ? number_format($rf['taxa'], 2, ',', '.') . '%' : '—'; ?></td>
                            <td><?php echo $rf['forma'] === 'PRE_FIXADO' ? 'Pré-fixado' : 'Pós-fixado'; ?></td>
                            <td>R$ <?php echo number_format($rf['valor_investido'], 2, ',', '.'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($rf['data_compra'])); ?></td>
                            <td><?php echo $rf['data_vencimento'] ? date('d/m/Y', strtotime($rf['data_vencimento'])) : '—'; ?></td>
                            <td><?php echo $rf['liquidez_diaria'] ? 'Sim' : 'Não'; ?></td>
                            <td>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Remover este lançamento?');">
                                    <input type="hidden" name="acao" value="renda_fixa_remover">
                                    <input type="hidden" name="id_renda_fixa" value="<?php echo (int)$rf['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Remover</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <div class="carteira-footer-actions">
                <a href="dashboard.php" class="btn-footer">Gráficos</a>
                <button type="button" class="btn btn-primary btn-add" onclick="abrirModalLancamento()">+ Adicionar lançamento</button>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- Modal Adicionar Lançamento (estilo imagem 3) -->
<div id="modalLancamento" class="modal">
    <div class="modal-content modal-lancamento">
        <span class="modal-close" onclick="fecharModalLancamento()">×</span>
        <h2 class="modal-titulo">Adicionar Lançamento</h2>
        
        <div class="tabs-lancamento">
            <button type="button" class="tab active" data-tab="compra">Compra</button>
            <button type="button" class="tab" data-tab="venda">Venda</button>
        </div>

        <div id="painelCompra" class="painel-tipo">
            <div class="form-group">
                <label>Tipo de ativo</label>
                <select id="tipoAtivoModal" onchange="trocarTipoAtivo()">
                    <option value="variavel">Renda variável (Ações, FIIs, ETF...)</option>
                    <option value="renda_fixa">Renda Fixa (CDB/LCI/LCA/LC/LF...)</option>
                </select>
            </div>

            <!-- Painel Renda variável (compra) -->
            <div id="painelVariavel" class="painel-tipo">
                <form method="POST" id="formInvestimento">
                    <input type="hidden" name="acao" value="Comprar">
                    <input type="hidden" name="ativo_id" id="formAtivoId">
                    <div class="form-group" id="grupoAtivo">
                        <label>Ativo</label>
                        <select name="ativo_id_select" id="ativoSelect" required>
                            <option value="">Selecione um ativo</option>
                            <?php $stmtAtivos = $pdo->query("SELECT id, codigo, nome, preco_atual FROM ativos ORDER BY codigo"); while ($a = $stmtAtivos->fetch()): ?>
                            <option value="<?php echo $a['id']; ?>" data-preco="<?php echo $a['preco_atual']; ?>"><?php echo htmlspecialchars($a['codigo'] . ' - ' . $a['nome']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantidade">Quantidade</label>
                        <input type="text" name="quantidade" id="quantidade" required placeholder="0,00">
                    </div>
                    <div class="form-group">
                        <label for="preco_medio">Preço Médio (R$)</label>
                        <input type="text" name="preco_medio" id="preco_medio" required placeholder="0,00">
                    </div>
                    <div class="form-group">
                        <label for="data_compra">Data da compra</label>
                        <input type="date" name="data_ultima_atualizacao" id="data_compra" required>
                    </div>
                    <div class="form-group">
                        <label for="notas">Notas (opcional)</label>
                        <textarea name="notas" id="notas" rows="2" placeholder="Observações..."></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-outline" onclick="fecharModalLancamento()">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-add">Adicionar lançamento</button>
                    </div>
                </form>
            </div>

            <!-- Painel Renda fixa (compra) - dentro de painelCompra -->
            <div id="painelRendaFixa" class="painel-tipo painel-renda-fixa" style="display:none">
                <form method="POST" id="formRendaFixa" class="form-renda-fixa">
                    <input type="hidden" name="acao" value="renda_fixa_adicionar">
                    <div class="form-grid-rf">
                        <div class="form-group">
                            <label>Emissor</label>
                            <input type="text" name="emissor" required placeholder="Ex: Banco XYZ">
                        </div>
                        <div class="form-group">
                            <label>Tipo de título</label>
                            <select name="tipo_titulo" required>
                                <option value="">Selecione</option>
                                <option value="CDB">CDB</option>
                                <option value="LCI">LCI</option>
                                <option value="LCA">LCA</option>
                                <option value="LC">LC (Letra de Câmbio)</option>
                                <option value="LF">LF (Letra Financeira)</option>
                                <option value="Debênture">Debênture</option>
                                <option value="CRI">CRI</option>
                                <option value="CRA">CRA</option>
                                <option value="LIG">LIG</option>
                                <option value="Tesouro Direto">Tesouro Direto</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Indexador</label>
                            <select name="indexador">
                                <option value="CDI">CDI</option>
                                <option value="IPCA+">IPCA+</option>
                                <option value="CDI+">CDI+</option>
                                <option value="Pré-fixado">Pré-fixado</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Taxa (%)</label>
                            <input type="text" name="taxa" placeholder="0,00" id="taxaRf">
                        </div>
                        <div class="form-group">
                            <label>Forma</label>
                            <select name="forma">
                                <option value="POS_FIXADO">Pós-fixado</option>
                                <option value="PRE_FIXADO">Pré-fixado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Valor em R$</label>
                            <input type="text" name="valor_investido" required placeholder="0,00" id="valorInvestidoRf">
                        </div>
                        <div class="form-group">
                            <label>Data da compra</label>
                            <input type="date" name="data_compra_rf" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Data de vencimento</label>
                            <input type="date" name="data_vencimento_rf" id="dataVencimentoRf">
                        </div>
                        <div class="form-group form-group-liquidez">
                            <label class="toggle-label">
                                <input type="checkbox" name="liquidez_diaria" value="1"> Liquidez diária
                            </label>
                        </div>
                        <div class="form-group form-group-full">
                            <label>Notas (opcional)</label>
                            <textarea name="notas_rf" rows="2" placeholder="Observações..."></textarea>
                        </div>
                    </div>
                    <div class="valor-total-bar">Valor total R$ <span id="valorTotalRf">0,00</span></div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-outline" onclick="fecharModalLancamento()">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-add">Adicionar lançamento</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Painel Venda -->
        <div id="painelVenda" class="painel-tipo" style="display:none">
            <?php if (empty($investimentos)): ?>
            <p class="empty-text">Você não possui ativos de renda variável para vender.</p>
            <button type="button" class="btn btn-outline" onclick="fecharModalLancamento()">Fechar</button>
            <?php else: ?>
            <form method="POST" id="formVenda">
                <input type="hidden" name="acao" value="Venda">
                <div class="form-group">
                    <label>Ativo na carteira</label>
                    <select name="ativo_id" id="vendaAtivoSelect" required>
                        <option value="">Selecione o ativo a vender</option>
                        <?php 
                        // Garantir que não há duplicatas no select de venda
                        $ativosVenda = [];
                        $ativosVistosVenda = [];
                        foreach ($investimentos as $inv) {
                            $key = (int)$inv['ativo_id'];
                            if (!isset($ativosVistosVenda[$key])) {
                                $ativosVistosVenda[$key] = true;
                                $ativosVenda[] = $inv;
                            }
                        }
                        foreach ($ativosVenda as $inv): 
                        ?>
                        <option value="<?php echo (int)$inv['ativo_id']; ?>" data-qtd="<?php echo number_format($inv['quantidade'], 4, '.', ''); ?>">
                            <?php echo htmlspecialchars($inv['codigo'] . ' - ' . $inv['nome']); ?> 
                            (Quant: <?php echo number_format($inv['quantidade'], 0, ',', '.'); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantidade_venda">Quantidade a vender</label>
                    <input type="text" name="quantidade_venda" id="quantidade_venda" required placeholder="0">
                </div>
                <p class="form-hint" id="vendaQtdMax">Máximo: —</p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="fecharModalLancamento()">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Registrar venda</button>
                </div>
            </form>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Modal Editar (só para editar ativo já na carteira) -->
<div id="modalInvestimento" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="fecharModal()">×</span>
        <h2 id="modalTitulo">Editar Investimento</h2>
        <form method="POST" id="formInvestimentoEditar">
            <input type="hidden" name="acao" value="atualizar">
            <input type="hidden" name="ativo_id" id="formAtivoIdEditar">
            <input type="hidden" name="investimento_id" id="formInvestimentoIdEditar">
            <div class="form-group">
                <label for="quantidade_editar">Quantidade</label>
                <input type="text" name="quantidade" id="quantidade_editar" required>
            </div>
            <div class="form-group">
                <label for="preco_medio_editar">Preço Médio (R$)</label>
                <input type="text" name="preco_medio" id="preco_medio_editar" required>
            </div>
            <div class="form-group">
                <label for="data_compra_editar">Data da compra</label>
                <input type="date" name="data_ultima_atualizacao" id="data_compra_editar" required>
            </div>
            <div class="form-group">
                <label for="notas_editar">Notas</label>
                <textarea name="notas" id="notas_editar" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Salvar</button>
        </form>
    </div>
</div>

<script>
document.getElementById('data_compra').value = '<?php echo date('Y-m-d'); ?>';

<?php if ($ativoParaComprar && !empty($ativosDisponiveis)): ?>
document.addEventListener('DOMContentLoaded', function() {
    abrirModalLancamento();
    document.getElementById('tipoAtivoModal').value = 'variavel';
    trocarTipoAtivo();
    var ativo = <?php echo json_encode($ativosDisponiveis[0]); ?>;
    document.getElementById('ativoSelect').value = ativo.id;
    document.getElementById('formAtivoId').value = ativo.id;
    document.getElementById('preco_medio').value = ativo.preco_atual ? parseFloat(ativo.preco_atual).toFixed(2).replace('.', ',') : '';
});
<?php endif; ?>

function abrirModalLancamento() {
    var modal = document.getElementById('modalLancamento');
    modal.classList.add('active');
    
    // Resetar para aba Compra
    var tabCompra = document.querySelector('.tabs-lancamento .tab[data-tab="compra"]');
    var tabVenda = document.querySelector('.tabs-lancamento .tab[data-tab="venda"]');
    if (tabCompra) tabCompra.classList.add('active');
    if (tabVenda) tabVenda.classList.remove('active');
    
    var painelCompra = document.getElementById('painelCompra');
    var painelVenda = document.getElementById('painelVenda');
    if (painelCompra) painelCompra.style.display = 'block';
    if (painelVenda) painelVenda.style.display = 'none';
    
    var tipoAtivo = document.getElementById('tipoAtivoModal');
    if (tipoAtivo) {
        tipoAtivo.value = 'variavel';
        trocarTipoAtivo();
    }
    
    var dataCompra = document.getElementById('data_compra');
    if (dataCompra) dataCompra.value = '<?php echo date('Y-m-d'); ?>';
}
function fecharModalLancamento() {
    document.getElementById('modalLancamento').classList.remove('active');
}
function trocarTipoAtivo() {
    var v = document.getElementById('tipoAtivoModal').value;
    document.getElementById('painelVariavel').style.display = v === 'variavel' ? 'block' : 'none';
    document.getElementById('painelRendaFixa').style.display = v === 'renda_fixa' ? 'block' : 'none';
}
function trocarTab(tab) {
    // Remover active de todas as tabs
    document.querySelectorAll('.tabs-lancamento .tab').forEach(function(t) { 
        t.classList.remove('active'); 
    });
    
    // Adicionar active na tab selecionada
    var tabSelecionada = document.querySelector('.tabs-lancamento .tab[data-tab="' + tab + '"]');
    if (tabSelecionada) tabSelecionada.classList.add('active');
    
    var painelCompra = document.getElementById('painelCompra');
    var painelVenda = document.getElementById('painelVenda');
    
    if (tab === 'compra') {
        if (painelCompra) painelCompra.style.display = 'block';
        if (painelVenda) painelVenda.style.display = 'none';
        var tipoAtivo = document.getElementById('tipoAtivoModal');
        if (tipoAtivo) {
            tipoAtivo.value = 'variavel';
            trocarTipoAtivo();
        }
    } else if (tab === 'venda') {
        if (painelCompra) painelCompra.style.display = 'none';
        if (painelVenda) painelVenda.style.display = 'block';
        atualizarVendaMax();
    }
}
function atualizarVendaMax() {
    var sel = document.getElementById('vendaAtivoSelect');
    var opt = sel && sel.options[sel.selectedIndex];
    var max = opt ? parseFloat(opt.getAttribute('data-qtd') || 0) : 0;
    document.getElementById('vendaQtdMax').textContent = 'Máximo: ' + (max ? max.toLocaleString('pt-BR') : '—');
}

document.getElementById('tipoAtivoModal').addEventListener('change', trocarTipoAtivo);
document.querySelectorAll('.tabs-lancamento .tab').forEach(function(t) {
    t.addEventListener('click', function() { trocarTab(this.getAttribute('data-tab')); });
});
var vendaSel = document.getElementById('vendaAtivoSelect');
if (vendaSel) {
    vendaSel.addEventListener('change', atualizarVendaMax);
    atualizarVendaMax();
}
var formVenda = document.getElementById('formVenda');
if (formVenda) formVenda.addEventListener('submit', function() {
    var q = document.getElementById('quantidade_venda');
    if (q) q.value = q.value.replace(',', '.');
});

document.getElementById('ativoSelect').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    var preco = opt.getAttribute('data-preco');
    if (preco) document.getElementById('preco_medio').value = parseFloat(preco).toFixed(2).replace('.', ',');
});

document.getElementById('formInvestimento').addEventListener('submit', function() {
    var qtd = document.getElementById('quantidade').value.replace(',', '.');
    var preco = document.getElementById('preco_medio').value.replace(',', '.');
    document.getElementById('quantidade').value = qtd;
    document.getElementById('preco_medio').value = preco;
    if (document.getElementById('ativoSelect').value) document.getElementById('formAtivoId').value = document.getElementById('ativoSelect').value;
});

document.getElementById('valorInvestidoRf').addEventListener('input', function() {
    var v = this.value.replace(',', '.').replace(/[^\d.-]/g, '');
    document.getElementById('valorTotalRf').textContent = isNaN(parseFloat(v)) ? '0,00' : parseFloat(v).toFixed(2).replace('.', ',');
});

document.getElementById('formRendaFixa').addEventListener('submit', function() {
    var v = document.getElementById('valorInvestidoRf').value.replace(',', '.');
    document.getElementById('valorInvestidoRf').value = v;
    var t = document.getElementById('taxaRf').value.replace(',', '.');
    document.getElementById('taxaRf').value = t;
});

function editarInvestimento(inv) {
    document.getElementById('formAtivoIdEditar').value = inv.ativo_id;
    document.getElementById('formInvestimentoIdEditar').value = inv.id;
    document.getElementById('quantidade_editar').value = parseFloat(inv.quantidade).toFixed(4).replace('.', ',');
    document.getElementById('preco_medio_editar').value = parseFloat(inv.preco_medio).toFixed(2).replace('.', ',');
    document.getElementById('data_compra_editar').value = (inv.data_ultima_atualizacao || inv.data_compra || '').toString().substring(0, 10);
    document.getElementById('notas_editar').value = inv.notas || '';
    document.getElementById('modalInvestimento').classList.add('active');
}
function fecharModal() {
    document.getElementById('modalInvestimento').classList.remove('active');
}

document.getElementById('formInvestimentoEditar').addEventListener('submit', function() {
    document.getElementById('quantidade_editar').value = document.getElementById('quantidade_editar').value.replace(',', '.');
    document.getElementById('preco_medio_editar').value = document.getElementById('preco_medio_editar').value.replace(',', '.');
});

window.onclick = function(event) {
    if (event.target.id === 'modalInvestimento') fecharModal();
    if (event.target.id === 'modalLancamento') fecharModalLancamento();
};

document.querySelectorAll('.dropdown-opcoes').forEach(function(el) {
    var btn = el.querySelector('.btn-opcoes');
    var menu = el.querySelector('.dropdown-menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        document.querySelectorAll('.dropdown-menu').forEach(function(m) { m.classList.remove('show'); });
        menu.classList.toggle('show');
    });
});
document.addEventListener('click', function() {
    document.querySelectorAll('.dropdown-menu').forEach(function(m) { m.classList.remove('show'); });
});
</script>

<?php include 'includes/footer.php'; ?>