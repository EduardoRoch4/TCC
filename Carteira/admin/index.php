<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Painel Administrativo';

$pdo = getConnection();

// Filtros
$filtroPeriodo = $_GET['periodo'] ?? '30'; // dias
$filtroTipo = $_GET['tipo_ativo'] ?? '';
$filtroBusca = trim($_GET['busca'] ?? '');

// Calcular datas
$dataInicio = date('Y-m-d', strtotime("-{$filtroPeriodo} days"));

// Total de usuários cadastrados
$totalUsuariosGeral = (int) $pdo->query("SELECT COUNT(*) AS total FROM usuarios")->fetch()['total'];
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM usuarios WHERE is_admin = 0");
    $totalUsuarios = (int) $stmt->fetch()['total'];
} catch (PDOException $e) {
    $totalUsuarios = $totalUsuariosGeral;
}

// Usuários novos no período
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM usuarios WHERE created_at >= ?");
    $stmt->execute([$dataInicio]);
    $usuariosNovos = (int) $stmt->fetch()['total'];
} catch (PDOException $e) {
    $usuariosNovos = 0;
}

// Ativos mais comprados (com filtros)
$sqlAtivos = "
    SELECT a.codigo, a.nome, 
           COUNT(DISTINCT ci.usuario_id) AS qtd_usuarios,
           SUM(ci.quantidade) AS quantidade_total,
           SUM(ci.quantidade * a.preco_atual) AS valor_total
    FROM carteira_investimentos ci
    JOIN ativos a ON ci.ativo_id = a.id
    LEFT JOIN tipos_ativo t ON a.tipo_id = t.id
    WHERE 1=1
";

$paramsAtivos = [];
if ($filtroTipo) {
    $sqlAtivos .= " AND t.nome = ?";
    $paramsAtivos[] = $filtroTipo;
}
if ($filtroBusca) {
    $sqlAtivos .= " AND (a.codigo LIKE ? OR a.nome LIKE ?)";
    $termo = "%{$filtroBusca}%";
    $paramsAtivos[] = $termo;
    $paramsAtivos[] = $termo;
}

$sqlAtivos .= " GROUP BY ci.ativo_id, a.id, a.codigo, a.nome
    ORDER BY qtd_usuarios DESC, valor_total DESC
    LIMIT 20";

$stmt = $pdo->prepare($sqlAtivos);
$stmt->execute($paramsAtivos);
$ativosMaisComprados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total de investimentos (posições) na plataforma
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM carteira_investimentos");
$totalPosicoes = (int) $stmt->fetch()['total'];

// Valor total investido na plataforma
try {
    $stmt = $pdo->query("
        SELECT SUM(ci.quantidade * ci.preco_medio) as valor_investido,
               SUM(ci.quantidade * a.preco_atual) as valor_atual
        FROM carteira_investimentos ci
        JOIN ativos a ON ci.ativo_id = a.id
    ");
    $valores = $stmt->fetch(PDO::FETCH_ASSOC);
    $valorTotalInvestido = (float)($valores['valor_investido'] ?? 0);
    $valorTotalAtual = (float)($valores['valor_atual'] ?? 0);
    $lucroTotal = $valorTotalAtual - $valorTotalInvestido;
} catch (PDOException $e) {
    $valorTotalInvestido = 0;
    $valorTotalAtual = 0;
    $lucroTotal = 0;
}

// Últimos usuários cadastrados
$stmt = $pdo->query("
    SELECT id, nome, email, created_at 
    FROM usuarios 
    ORDER BY created_at DESC 
    LIMIT 10
");
$ultimosUsuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Resumo por tipo de ativo (quantidade de posições)
$stmt = $pdo->query("
    SELECT t.nome AS tipo, COUNT(*) AS qtd
    FROM carteira_investimentos ci
    JOIN ativos a ON ci.ativo_id = a.id
    LEFT JOIN tipos_ativo t ON a.tipo_id = t.id
    GROUP BY t.id, t.nome
    ORDER BY qtd DESC
");
$porTipo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar tipos de ativo para filtro
$stmtTipos = $pdo->query("SELECT DISTINCT nome FROM tipos_ativo ORDER BY nome");
$tiposAtivo = $stmtTipos->fetchAll(PDO::FETCH_COLUMN);

// Preparar dados para o gráfico
$chartLabels = [];
$chartUsuarios = [];
$chartValores = [];
foreach (array_slice($ativosMaisComprados, 0, 10) as $row) {
    $chartLabels[] = $row['codigo'];
    $chartUsuarios[] = (int)$row['qtd_usuarios'];
    $chartValores[] = (float)$row['valor_total'];
}

include __DIR__ . '/includes/header.php';
?>

<main class="main admin-page">
    <section class="page-header">
        <div class="container">
            <h1>Painel Administrativo</h1>
            <p>Visão geral da plataforma CarteiraInvest</p>
        </div>
    </section>

    <section class="admin-stats">
        <div class="container">
            <div class="summary-cards">
                <div class="summary-card highlight">
                    <span class="summary-label">Usuários cadastrados</span>
                    <span class="summary-value"><?php echo number_format($totalUsuarios, 0, ',', '.'); ?></span>
                    <span class="summary-hint"><?php echo $usuariosNovos; ?> novos nos últimos <?php echo $filtroPeriodo; ?> dias</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Total de contas</span>
                    <span class="summary-value"><?php echo number_format($totalUsuariosGeral, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Posições na carteira</span>
                    <span class="summary-value"><?php echo number_format($totalPosicoes, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Valor total investido</span>
                    <span class="summary-value">R$ <?php echo number_format($valorTotalInvestido, 2, ',', '.'); ?></span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Valor atual total</span>
                    <span class="summary-value">R$ <?php echo number_format($valorTotalAtual, 2, ',', '.'); ?></span>
                </div>
                <div class="summary-card <?php echo $lucroTotal >= 0 ? 'positive' : 'negative'; ?>">
                    <span class="summary-label">Lucro/Prejuízo total</span>
                    <span class="summary-value">R$ <?php echo number_format($lucroTotal, 2, ',', '.'); ?></span>
                    <span class="summary-hint"><?php echo $valorTotalInvestido > 0 ? number_format(($lucroTotal / $valorTotalInvestido) * 100, 2, ',', '.') : '0,00'; ?>%</span>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-filters">
        <div class="container">
            <div class="filters-card">
                <h3>Filtros</h3>
                <form method="GET" class="filters-form">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>Período</label>
                            <select name="periodo">
                                <option value="7" <?php echo $filtroPeriodo == '7' ? 'selected' : ''; ?>>Últimos 7 dias</option>
                                <option value="30" <?php echo $filtroPeriodo == '30' ? 'selected' : ''; ?>>Últimos 30 dias</option>
                                <option value="90" <?php echo $filtroPeriodo == '90' ? 'selected' : ''; ?>>Últimos 90 dias</option>
                                <option value="365" <?php echo $filtroPeriodo == '365' ? 'selected' : ''; ?>>Último ano</option>
                                <option value="9999" <?php echo $filtroPeriodo == '9999' ? 'selected' : ''; ?>>Todos</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Tipo de ativo</label>
                            <select name="tipo_ativo">
                                <option value="">Todos os tipos</option>
                                <?php foreach ($tiposAtivo as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo $filtroTipo == $tipo ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Buscar ativo</label>
                            <input type="text" name="busca" value="<?php echo htmlspecialchars($filtroBusca); ?>" placeholder="Código ou nome...">
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                            <a href="index.php" class="btn btn-outline">Limpar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="admin-content">
        <div class="container">
            <div class="admin-grid">
                <div class="admin-card">
                    <div class="card-header-flex">
                        <div>
                            <h3>Ativos mais comprados</h3>
                            <p class="card-desc">Por quantidade de usuários que possuem o ativo</p>
                        </div>
                        <span class="card-badge"><?php echo count($ativosMaisComprados); ?> ativos</span>
                    </div>
                    <?php if (empty($ativosMaisComprados)): ?>
                    <p class="empty-text">Nenhuma posição registrada ainda.</p>
                    <?php else: ?>
                    <!-- Gráfico -->
                    <div class="chart-container-admin" style="margin-bottom: 1.5rem;">
                        <canvas id="chartAtivosMaisComprados"></canvas>
                    </div>
                    <!-- Tabela responsiva -->
                    <div class="table-responsive-admin">
                        <table class="table table-admin-responsive">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>Usuários</th>
                                    <th>Qtd. total</th>
                                    <th>Valor total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ativosMaisComprados as $row): ?>
                                <tr>
                                    <td data-label="Código"><strong><?php echo htmlspecialchars($row['codigo']); ?></strong></td>
                                    <td data-label="Nome"><?php echo htmlspecialchars($row['nome']); ?></td>
                                    <td data-label="Usuários"><?php echo $row['qtd_usuarios']; ?></td>
                                    <td data-label="Qtd. total"><?php echo number_format($row['quantidade_total'], 2, ',', '.'); ?></td>
                                    <td data-label="Valor total">R$ <?php echo number_format($row['valor_total'], 2, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="admin-card">
                    <h3>Últimos usuários cadastrados</h3>
                    <?php if (empty($ultimosUsuarios)): ?>
                    <p class="empty-text">Nenhum usuário.</p>
                    <?php else: ?>
                    <ul class="admin-list">
                        <?php foreach ($ultimosUsuarios as $u): ?>
                        <li>
                            <span class="list-nome"><?php echo htmlspecialchars($u['nome']); ?></span>
                            <span class="list-email"><?php echo htmlspecialchars($u['email']); ?></span>
                            <span class="list-date"><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <div style="margin-top: 1rem;">
                        <a href="usuarios.php" class="btn btn-sm btn-primary">Gerenciar todos os usuários</a>
                    </div>
                </div>

                <div class="admin-card">
                    <h3>Posições por tipo de ativo</h3>
                    <?php if (empty($porTipo)): ?>
                    <p class="empty-text">Sem dados.</p>
                    <?php else: ?>
                    <ul class="distribuicao-list">
                        <?php foreach ($porTipo as $row): ?>
                        <li>
                            <div class="bar-label">
                                <span><?php echo htmlspecialchars($row['tipo'] ?? 'N/A'); ?></span>
                                <span><?php echo $row['qtd']; ?> posições</span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: <?php echo $totalPosicoes > 0 ? ($row['qtd'] / $totalPosicoes) * 100 : 0; ?>%"></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <div class="admin-card">
                    <div class="card-header-flex">
                        <div>
                            <h3>Notícias financeiras</h3>
                            <p class="card-desc">Principais notícias do mercado (NewsAPI / RSS)</p>
                        </div>
                    </div>
                    <div id="newsContainerAdmin" class="news-grid news-grid-admin">
                        <div class="news-loading">Carregando notícias...</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
