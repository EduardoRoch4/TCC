<?php
require_once 'config/config.php';

$pageTitle = 'Ativos';
$busca = trim($_GET['q'] ?? '');
$filtroTipoAtivo = $_GET['tipo'] ?? '';
$filtroOrdenacaoAtivo = $_GET['ordenar'] ?? 'codigo_asc';

$pdo = getConnection();

// Construir query com filtros
$sqlAtivos = "SELECT a.*, t.nome as tipo FROM ativos a 
    LEFT JOIN tipos_ativo t ON a.tipo_id = t.id 
    WHERE 1=1";

$paramsAtivos = [];

if ($busca) {
    $sqlAtivos .= " AND (a.codigo LIKE ? OR a.nome LIKE ?)";
    $termo = "%{$busca}%";
    $paramsAtivos[] = $termo;
    $paramsAtivos[] = $termo;
}

if ($filtroTipoAtivo) {
    $sqlAtivos .= " AND t.nome = ?";
    $paramsAtivos[] = $filtroTipoAtivo;
}

// Ordenação
switch ($filtroOrdenacaoAtivo) {
    case 'preco_desc':
        $sqlAtivos .= " ORDER BY a.preco_atual DESC";
        break;
    case 'preco_asc':
        $sqlAtivos .= " ORDER BY a.preco_atual ASC";
        break;
    case 'variacao_desc':
        $sqlAtivos .= " ORDER BY a.variacao_dia DESC";
        break;
    case 'variacao_asc':
        $sqlAtivos .= " ORDER BY a.variacao_dia ASC";
        break;
    case 'codigo_desc':
        $sqlAtivos .= " ORDER BY a.codigo DESC";
        break;
    default:
        $sqlAtivos .= " ORDER BY a.codigo ASC";
}

$stmt = $pdo->prepare($sqlAtivos);
$stmt->execute($paramsAtivos);
$ativos = $stmt->fetchAll();

// Buscar tipos para filtro
$stmtTipos = $pdo->query("SELECT DISTINCT nome FROM tipos_ativo ORDER BY nome");
$tiposAtivoDisponiveis = $stmtTipos->fetchAll(PDO::FETCH_COLUMN);

include 'includes/header.php';
?>

<main class="main">
    <section class="page-header">
        <div class="container">
            <h1>Catálogo de Ativos</h1>
            <p>Pesquise e visualize os ativos disponíveis para sua carteira</p>
        </div>
    </section>

    <section class="ativos-filters">
        <div class="container">
            <div class="filters-card">
                <h3>Filtros e Busca</h3>
                <form method="GET" class="filters-form">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>Buscar</label>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($busca); ?>" 
                                   placeholder="Código ou nome...">
                        </div>
                        <div class="filter-group">
                            <label>Tipo</label>
                            <select name="tipo">
                                <option value="">Todos</option>
                                <?php foreach ($tiposAtivoDisponiveis as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo $filtroTipoAtivo == $tipo ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Ordenar por</label>
                            <select name="ordenar">
                                <option value="codigo_asc" <?php echo $filtroOrdenacaoAtivo == 'codigo_asc' ? 'selected' : ''; ?>>Código (A-Z)</option>
                                <option value="codigo_desc" <?php echo $filtroOrdenacaoAtivo == 'codigo_desc' ? 'selected' : ''; ?>>Código (Z-A)</option>
                                <option value="preco_desc" <?php echo $filtroOrdenacaoAtivo == 'preco_desc' ? 'selected' : ''; ?>>Preço (maior)</option>
                                <option value="preco_asc" <?php echo $filtroOrdenacaoAtivo == 'preco_asc' ? 'selected' : ''; ?>>Preço (menor)</option>
                                <option value="variacao_desc" <?php echo $filtroOrdenacaoAtivo == 'variacao_desc' ? 'selected' : ''; ?>>Variação (maior)</option>
                                <option value="variacao_asc" <?php echo $filtroOrdenacaoAtivo == 'variacao_asc' ? 'selected' : ''; ?>>Variação (menor)</option>
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">Aplicar</button>
                            <a href="ativos.php" class="btn btn-outline">Limpar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="ativos-list">
        <div class="container">
            <?php if (empty($ativos)): ?>
            <div class="empty-state">
                <p>Nenhum ativo encontrado para "<?php echo htmlspecialchars($busca); ?>".</p>
                <a href="ativos.php" class="btn btn-outline">Ver todos os ativos</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table ativos-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Preço</th>
                            <th>Variação</th>
                            <?php if (isLoggedIn()): ?>
                            <th>Ação</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ativos as $ativo): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($ativo['codigo']); ?></strong></td>
                            <td><?php echo htmlspecialchars($ativo['nome']); ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($ativo['tipo'] ?? 'N/A'); ?></span></td>
                            <td>R$ <?php echo number_format($ativo['preco_atual'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="variacao <?php echo $ativo['variacao_dia'] >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo ($ativo['variacao_dia'] >= 0 ? '+' : '') . number_format($ativo['variacao_dia'], 2, ',', '.') . '%'; ?>
                                </span>
                            </td>
                            <?php if (isLoggedIn()): ?>
                            <td>
                                <a href="carteira.php?Comprar=<?php echo $ativo['id']; ?>" class="btn btn-sm btn-primary">Comprar</a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
