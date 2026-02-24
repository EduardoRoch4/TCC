<?php
require_once 'config/config.php';
requireLogin();

$pageTitle = 'Dashboard';

$pdo = getConnection();
$usuarioId = $_SESSION['usuario_id'];

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

$resultadoTotal = $valorTotal - $valorInvestido;
$resultadoPct = $valorInvestido > 0 ? (($valorTotal / $valorInvestido) - 1) * 100 : 0;

// Salvar snapshot hoje (valor_total e valor_aplicado para gráfico Evolução do Patrimônio)
$historico = [];
$hoje = date('Y-m-d');
try {
    $stmtHist = $pdo->prepare("
        INSERT INTO historico_valor_carteira (usuario_id, data_ref, valor_total, valor_aplicado)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE valor_total = VALUES(valor_total), valor_aplicado = COALESCE(VALUES(valor_aplicado), valor_aplicado)
    ");
    $stmtHist->execute([$usuarioId, $hoje, round($valorTotal, 2), round($valorInvestido, 2)]);
} catch (PDOException $e) {
    try {
        $stmtHist = $pdo->prepare("
            INSERT INTO historico_valor_carteira (usuario_id, data_ref, valor_total)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE valor_total = VALUES(valor_total)
        ");
        $stmtHist->execute([$usuarioId, $hoje, round($valorTotal, 2)]);
    } catch (PDOException $e2) { }
}

try {
    $stmtHistList = $pdo->prepare("
        SELECT data_ref, valor_total, valor_aplicado
        FROM historico_valor_carteira
        WHERE usuario_id = ?
        ORDER BY data_ref ASC
    ");
    $stmtHistList->execute([$usuarioId]);
    $historico = $stmtHistList->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stmtHistList = $pdo->prepare("SELECT data_ref, valor_total FROM historico_valor_carteira WHERE usuario_id = ? ORDER BY data_ref ASC");
    $stmtHistList->execute([$usuarioId]);
    $historico = $stmtHistList->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($historico) && $valorTotal > 0) {
    $historico = [['data_ref' => $hoje, 'valor_total' => $valorTotal, 'valor_aplicado' => $valorInvestido]];
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
                    <span class="summary-value">R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?></span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Total Investido</span>
                    <span class="summary-value">R$ <?php echo number_format($valorInvestido, 2, ',', '.'); ?></span>
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
        var parts = d.split('-');
        return parts[2] + '/' + parts[1];
    });

    var ganhoData = evolucaoValores.map(function(v, i) {
        var apl = evolucaoAplicado[i] || 0;
        return Math.max(0, v - apl);
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
