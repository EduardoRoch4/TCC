<?php
require_once 'config/config.php';
requireLogin();

$pageTitle = 'Dashboard';

$pdo = getConnection();
$usuarioId = $_SESSION['usuario_id'];

// Função para atualizar histórico (mesma lógica da carteira.php)
function atualizarHistoricoCarteiraDashboard($pdo, $usuarioId, $valorTotalVar, $valorInvestVar, $valorRendaFixa) {
    try {
        $hoje = date('Y-m-d');
        $valorTotalAtual = $valorTotalVar + $valorRendaFixa;
        $valorAplicadoAtual = $valorInvestVar + $valorRendaFixa;

        $stHist = $pdo->prepare("
            INSERT INTO historico_valor_carteira (usuario_id, data_ref, valor_total, valor_aplicado)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                valor_total = VALUES(valor_total),
                valor_aplicado = VALUES(valor_aplicado)
        ");
        $stHist->execute([$usuarioId, $hoje, round($valorTotalAtual, 2), round($valorAplicadoAtual, 2)]);
    } catch (PDOException $e) {
        error_log('Erro ao atualizar histórico da carteira: ' . $e->getMessage());
    }
}

// Buscar resumo da carteira (agregado por ativo)
$stmt = $pdo->prepare("
    SELECT agg.quantidade, agg.preco_medio, a.codigo, a.nome, a.preco_atual, a.variacao_dia, t.nome as tipo
    FROM (
        SELECT ci.ativo_id, SUM(ci.quantidade) as quantidade,
               SUM(ci.quantidade * ci.preco_medio) / NULLIF(SUM(ci.quantidade), 0) as preco_medio
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

$valorTotal = 0;
$valorInvestido = 0;
$porTipo = [];
$porAtivo = []; // para gráfico de pizza por ativo

foreach ($investimentos as $inv) {
    $valorAtual = $inv['quantidade'] * $inv['preco_atual'];
    $valorInv = $inv['quantidade'] * $inv['preco_medio'];
    $valorTotal += $valorAtual;
    $valorInvestido += $valorInv;

    $tipo = $inv['tipo'] ?? 'Outros';
    if (!isset($porTipo[$tipo])) $porTipo[$tipo] = 0;
    $porTipo[$tipo] += $valorAtual;

    $porAtivo[$inv['codigo']] = ($porAtivo[$inv['codigo']] ?? 0) + $valorAtual;
}

// Renda fixa: usa valor investido como valor atual/aplicado (sem projeção de juros)
$valorRendaFixa = 0;
try {
    $stmtRf = $pdo->prepare("SELECT SUM(valor_investido) AS total_rf FROM carteira_renda_fixa WHERE usuario_id = ?");
    $stmtRf->execute([$usuarioId]);
    $rowRf = $stmtRf->fetch(PDO::FETCH_ASSOC);
    $valorRendaFixa = (float)($rowRf['total_rf'] ?? 0);
} catch (PDOException $e) {
    $valorRendaFixa = 0;
}

// Totais gerais (variável + renda fixa)
$valorTotalGeral = $valorTotal + $valorRendaFixa;
$valorInvestidoGeral = $valorInvestido + $valorRendaFixa;

$resultadoTotal = $valorTotalGeral - $valorInvestidoGeral;
$resultadoPct = $valorInvestidoGeral > 0 ? (($valorTotalGeral / $valorInvestidoGeral) - 1) * 100 : 0;

// Salvar snapshot hoje (valor_total e valor_aplicado para gráfico Evolução do Patrimônio)
atualizarHistoricoCarteiraDashboard($pdo, $usuarioId, $valorTotal, $valorInvestido, $valorRendaFixa);
$hoje = date('Y-m-d');

try {
    // Busca histórico dos últimos 12 meses, agregando por mês (pega o último valor de cada mês)
    $stmtHistList = $pdo->prepare("
        SELECT 
            DATE_FORMAT(data_ref, '%Y-%m') as mes_ano,
            MAX(data_ref) as data_ref,
            MAX(valor_total) as valor_total,
            MAX(valor_aplicado) as valor_aplicado
        FROM historico_valor_carteira
        WHERE usuario_id = ? 
            AND data_ref >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(data_ref, '%Y-%m')
        ORDER BY mes_ano ASC
    ");
    $stmtHistList->execute([$usuarioId]);
    $historicoRaw = $stmtHistList->fetchAll(PDO::FETCH_ASSOC);
    
    // Se não tem dados mensais, busca todos os dados diários e agrega por mês
    if (empty($historicoRaw)) {
        $stmtHistList2 = $pdo->prepare("
            SELECT data_ref, valor_total, valor_aplicado
            FROM historico_valor_carteira
            WHERE usuario_id = ?
            ORDER BY data_ref ASC
        ");
        $stmtHistList2->execute([$usuarioId]);
        $historicoDia = $stmtHistList2->fetchAll(PDO::FETCH_ASSOC);
        
        // Agrega por mês manualmente
        $historicoPorMes = [];
        foreach ($historicoDia as $row) {
            $mesAno = date('Y-m', strtotime($row['data_ref']));
            if (!isset($historicoPorMes[$mesAno])) {
                $historicoPorMes[$mesAno] = $row;
            } else {
                // Mantém o último valor do mês
                if (strtotime($row['data_ref']) > strtotime($historicoPorMes[$mesAno]['data_ref'])) {
                    $historicoPorMes[$mesAno] = $row;
                }
            }
        }
        $historico = array_values($historicoPorMes);
    } else {
        $historico = $historicoRaw;
    }
} catch (PDOException $e) {
    try {
        $stmtHistList = $pdo->prepare("SELECT data_ref, valor_total FROM historico_valor_carteira WHERE usuario_id = ? ORDER BY data_ref ASC");
        $stmtHistList->execute([$usuarioId]);
        $historicoDia = $stmtHistList->fetchAll(PDO::FETCH_ASSOC);
        
        // Agrega por mês manualmente
        $historicoPorMes = [];
        foreach ($historicoDia as $row) {
            $mesAno = date('Y-m', strtotime($row['data_ref']));
            if (!isset($historicoPorMes[$mesAno])) {
                $historicoPorMes[$mesAno] = ['data_ref' => $row['data_ref'], 'valor_total' => $row['valor_total'], 'valor_aplicado' => $row['valor_total']];
            } else {
                if (strtotime($row['data_ref']) > strtotime($historicoPorMes[$mesAno]['data_ref'])) {
                    $historicoPorMes[$mesAno] = ['data_ref' => $row['data_ref'], 'valor_total' => $row['valor_total'], 'valor_aplicado' => $row['valor_total']];
                }
            }
        }
        $historico = array_values($historicoPorMes);
    } catch (PDOException $e2) {
        $historico = [];
    }
}

if (empty($historico) && $valorTotalGeral > 0) {
    $historico = [['data_ref' => $hoje, 'valor_total' => $valorTotalGeral, 'valor_aplicado' => $valorInvestidoGeral]];
}

$topAtivos = array_slice($investimentos, 0, 5);

// Dados para os gráficos (JSON)
$chartEvolucaoLabels = array_map(function ($h) { return $h['data_ref']; }, $historico);
$chartEvolucaoValores = array_map(function ($h) { return (float) $h['valor_total']; }, $historico);
$chartEvolucaoAplicado = array_map(function ($h) { return (float) ($h['valor_aplicado'] ?? $h['valor_total']); }, $historico);
$chartPizzaLabels = array_keys($porAtivo);
$chartPizzaValores = array_values($porAtivo);
$chartPizzaCores = [
    '#00c853', '#1a365d', '#2196f3', '#ff9800', '#9c27b0',
    '#009688', '#795548', '#607d8b', '#e91e63', '#3f51b5'
];

include 'includes/header.php';
?>

<main class="main dashboard-page">
    <section class="page-header">
        <div class="container">
            <h1>Dashboard</h1>
            <p>Visão geral e evolução da sua carteira de investimentos</p>
        </div>
    </section>

    <section class="dashboard-summary">
        <div class="container">
            <div class="summary-cards">
                <div class="summary-card highlight">
                    <span class="summary-label">Valor Total da Carteira</span>
                    <span class="summary-value">R$ <?php echo number_format($valorTotalGeral, 2, ',', '.'); ?></span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Total Investido</span>
                    <span class="summary-value">R$ <?php echo number_format($valorInvestidoGeral, 2, ',', '.'); ?></span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Lucro/Prejuízo</span>
                    <span class="summary-value <?php echo $resultadoTotal >= 0 ? 'positive' : 'negative'; ?>">
                        R$ <?php echo number_format($resultadoTotal, 2, ',', '.'); ?>
                        (<?php echo ($resultadoPct >= 0 ? '+' : '') . number_format($resultadoPct, 2, ',', '.'); ?>%)
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-charts">
        <div class="container">
            <div class="charts-grid">
                <div class="chart-card chart-full">
                    <div class="chart-header-evolucao">
                        <h3>Evolução do Patrimônio</h3>
                        <span class="chart-period">12 Meses</span>
                    </div>
                    <div class="chart-container chart-evolucao">
                        <canvas id="chartEvolucao" height="120"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>Distribuição por ativo</h3>
                    <div class="chart-container chart-pie">
                        <canvas id="chartPizza"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>Distribuição por tipo</h3>
                    <div class="chart-container">
                        <canvas id="chartBarras"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-content">
        <div class="container">
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>Top Ativos</h3>
                    <?php if (empty($topAtivos)): ?>
                    <p class="empty-text">Nenhum investimento na carteira.</p>
                    <a href="ativos.php" class="btn btn-sm btn-primary">Comprar Ativos</a>
                    <?php else: ?>
                    <ul class="top-ativos-list">
                        <?php foreach ($topAtivos as $inv):
                            $val = $inv['quantidade'] * $inv['preco_atual'];
                            $pct = $valorTotal > 0 ? ($val / $valorTotal) * 100 : 0;
                        ?>
                        <li>
                            <div class="ativo-info">
                                <span class="codigo"><?php echo htmlspecialchars($inv['codigo']); ?></span>
                                <span class="nome"><?php echo htmlspecialchars($inv['nome']); ?></span>
                            </div>
                            <div class="ativo-valor">
                                R$ <?php echo number_format($val, 2, ',', '.'); ?>
                                <span class="pct"><?php echo number_format($pct, 1, ',', '.'); ?>%</span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card">
                    <h3>Distribuição por tipo (lista)</h3>
                    <?php if (empty($porTipo)): ?>
                    <p class="empty-text">Sem dados para exibir.</p>
                    <?php else: ?>
                    <ul class="distribuicao-list">
                        <?php foreach ($porTipo as $tipo => $valor):
                            $pct = $valorTotal > 0 ? ($valor / $valorTotal) * 100 : 0;
                        ?>
                        <li>
                            <div class="bar-label">
                                <span><?php echo htmlspecialchars($tipo); ?></span>
                                <span><?php echo number_format($pct, 1); ?>%</span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: <?php echo $pct; ?>%"></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-actions">
                <a href="carteira.php" class="btn btn-primary">Ver Carteira Completa</a>
                <a href="ativos.php" class="btn btn-outline">Explorar Ativos</a>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    var evolucaoLabels = <?php echo json_encode($chartEvolucaoLabels); ?>;
    var evolucaoValores = <?php echo json_encode($chartEvolucaoValores); ?>;
    var evolucaoAplicado = <?php echo json_encode($chartEvolucaoAplicado); ?>;
    var pizzaLabels = <?php echo json_encode($chartPizzaLabels); ?>;
    var pizzaValores = <?php echo json_encode($chartPizzaValores); ?>;
    var pizzaCores = <?php echo json_encode(array_slice($chartPizzaCores, 0, count($chartPizzaLabels))); ?>;
    var porTipoLabels = <?php echo json_encode(array_keys($porTipo)); ?>;
    var porTipoValores = <?php echo json_encode(array_values($porTipo)); ?>;

    evolucaoLabels = evolucaoLabels.map(function(d) {
        // Se já está no formato YYYY-MM (agregado por mês), formata como "MM/YYYY"
        if (d.match(/^\d{4}-\d{2}$/)) {
            var parts = d.split('-');
            return parts[1] + '/' + parts[0];
        }
        // Se está no formato YYYY-MM-DD, formata como "MM/YYYY"
        var parts = d.split('-');
        if (parts.length === 3) {
            return parts[1] + '/' + parts[0];
        }
        return d;
    });

    var ganhoData = evolucaoValores.map(function(v, i) {
        var apl = evolucaoAplicado[i] || 0;
        // permite mostrar lucro (positivo) e prejuízo (negativo)
        return v - apl;
    });

    if (evolucaoValores.length > 0) {
        new Chart(document.getElementById('chartEvolucao'), {
            type: 'bar',
            data: {
                labels: evolucaoLabels,
                datasets: [
                    {
                        label: 'Valor aplicado',
                        data: evolucaoAplicado,
                        backgroundColor: 'rgba(0, 120, 80, 0.9)',
                        stack: 'stack0'
                    },
                    {
                        label: 'Ganho capital',
                        data: ganhoData,
                        backgroundColor: 'rgba(0, 200, 83, 0.6)',
                        stack: 'stack0'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            afterBody: function(items) {
                                var i = items[0] && items[0].dataIndex;
                                if (i === undefined) return '';
                                var total = evolucaoValores[i];
                                var apl = evolucaoAplicado[i] || 0;
                                var ganho = total - apl;
                                return 'Patrimônio: R$ ' + (total || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}) + '\nValor aplicado: R$ ' + apl.toLocaleString('pt-BR', {minimumFractionDigits: 2}) + '\nGanho capital: R$ ' + ganho.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    x: { stacked: true },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            callback: function(v) { return 'R$ ' + v.toLocaleString('pt-BR'); }
                        }
                    }
                }
            }
        });
    }

    if (pizzaLabels.length > 0) {
        new Chart(document.getElementById('chartPizza'), {
            type: 'doughnut',
            data: {
                labels: pizzaLabels,
                datasets: [{
                    data: pizzaValores,
                    backgroundColor: pizzaCores,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    if (porTipoLabels.length > 0) {
        new Chart(document.getElementById('chartBarras'), {
            type: 'bar',
            data: {
                labels: porTipoLabels,
                datasets: [{
                    label: 'Valor (R$)',
                    data: porTipoValores,
                    backgroundColor: 'rgba(0, 200, 83, 0.7)',
                    borderColor: '#00c853',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(v) { return 'R$ ' + v.toLocaleString('pt-BR'); }
                        }
                    }
                }
            }
        });
    }
})();
</script>

<?php include 'includes/footer.php'; ?>
