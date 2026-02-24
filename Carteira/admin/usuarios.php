<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Gerenciar Usuários';

$pdo = getConnection();
$mensagem = '';
$erro = '';

// Processar ações CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'criar') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
        
        if (strlen($nome) >= 3 && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($senha) >= 6) {
            try {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                // Tenta senha_hash primeiro, se não existir usa senha
                try {
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, is_admin) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nome, $email, $senhaHash, $isAdmin]);
                } catch (PDOException $e) {
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, is_admin) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nome, $email, $senhaHash, $isAdmin]);
                }
                $mensagem = 'Usuário criado com sucesso!';
            } catch (PDOException $e) {
                $erro = 'Erro ao criar usuário: ' . ($e->getCode() == 23000 ? 'Email já existe' : 'Erro no banco');
            }
        } else {
            $erro = 'Dados inválidos. Nome (min 3), email válido e senha (min 6) são obrigatórios.';
        }
    }
    
    if ($acao === 'editar') {
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
        $novaSenha = $_POST['nova_senha'] ?? '';
        
        if ($id > 0 && strlen($nome) >= 3 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                if (!empty($novaSenha) && strlen($novaSenha) >= 6) {
                    $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                    // Tenta senha_hash primeiro, se não existir usa senha
                    try {
                        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, senha_hash = ?, is_admin = ? WHERE id = ?");
                        $stmt->execute([$nome, $email, $senhaHash, $isAdmin, $id]);
                    } catch (PDOException $e) {
                        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ?, is_admin = ? WHERE id = ?");
                        $stmt->execute([$nome, $email, $senhaHash, $isAdmin, $id]);
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, is_admin = ? WHERE id = ?");
                    $stmt->execute([$nome, $email, $isAdmin, $id]);
                }
                $mensagem = 'Usuário atualizado com sucesso!';
            } catch (PDOException $e) {
                $erro = 'Erro ao atualizar: ' . ($e->getCode() == 23000 ? 'Email já existe' : 'Erro no banco');
            }
        }
    }
    
    if ($acao === 'excluir') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && $id != $_SESSION['usuario_id']) {
            try {
                $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
                $mensagem = 'Usuário excluído com sucesso!';
            } catch (PDOException $e) {
                $erro = 'Erro ao excluir usuário.';
            }
        } else {
            $erro = 'Não é possível excluir seu próprio usuário.';
        }
    }
}

// Buscar usuário para edição
$usuarioEditar = null;
if (isset($_GET['editar'])) {
    $idEditar = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT id, nome, email, is_admin FROM usuarios WHERE id = ?");
    $stmt->execute([$idEditar]);
    $usuarioEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Filtros
$filtroBusca = trim($_GET['busca'] ?? '');
$filtroTipo = $_GET['tipo'] ?? '';
$filtroOrdenacao = $_GET['ordenar'] ?? 'data_desc';

// Construir query com filtros
$sqlUsuarios = "
    SELECT u.id, u.nome, u.email, u.is_admin, 
           COALESCE(u.created_at, u.updated_at, NOW()) as created_at,
           COUNT(DISTINCT ci.id) as qtd_investimentos,
           COUNT(DISTINCT crf.id) as qtd_renda_fixa
    FROM usuarios u
    LEFT JOIN carteira_investimentos ci ON u.id = ci.usuario_id
    LEFT JOIN carteira_renda_fixa crf ON u.id = crf.usuario_id
    WHERE 1=1
";

$paramsUsuarios = [];

if ($filtroBusca) {
    $sqlUsuarios .= " AND (u.nome LIKE ? OR u.email LIKE ?)";
    $termo = "%{$filtroBusca}%";
    $paramsUsuarios[] = $termo;
    $paramsUsuarios[] = $termo;
}

if ($filtroTipo === 'admin') {
    $sqlUsuarios .= " AND u.is_admin = 1";
} elseif ($filtroTipo === 'usuario') {
    $sqlUsuarios .= " AND (u.is_admin = 0 OR u.is_admin IS NULL)";
}

$sqlUsuarios .= " GROUP BY u.id, u.nome, u.email, u.is_admin, u.created_at, u.updated_at";

// Ordenação
switch ($filtroOrdenacao) {
    case 'nome_asc':
        $sqlUsuarios .= " ORDER BY u.nome ASC";
        break;
    case 'nome_desc':
        $sqlUsuarios .= " ORDER BY u.nome DESC";
        break;
    case 'data_asc':
        $sqlUsuarios .= " ORDER BY COALESCE(u.created_at, u.updated_at, NOW()) ASC";
        break;
    default:
        $sqlUsuarios .= " ORDER BY COALESCE(u.created_at, u.updated_at, NOW()) DESC";
}

try {
    $stmt = $pdo->prepare($sqlUsuarios);
    $stmt->execute($paramsUsuarios);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback
    $stmt = $pdo->query("SELECT id, nome, email, is_admin FROM usuarios ORDER BY id DESC");
    $usuarios = [];
    while ($u = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $u['created_at'] = date('Y-m-d H:i:s');
        $u['qtd_investimentos'] = 0;
        $u['qtd_renda_fixa'] = 0;
        $usuarios[] = $u;
    }
}

$totalUsuarios = count($usuarios);
$totalAdmins = count(array_filter($usuarios, function($u) { return !empty($u['is_admin']); }));

include __DIR__ . '/includes/header.php';
?>

<main class="main admin-page">
    <section class="page-header">
        <div class="container">
            <h1>Gerenciar Usuários</h1>
            <p>CRUD completo de usuários do sistema</p>
        </div>
    </section>

    <?php if ($mensagem): ?>
    <div class="container"><div class="alert alert-success"><?php echo htmlspecialchars($mensagem); ?></div></div>
    <?php endif; ?>
    <?php if ($erro): ?>
    <div class="container"><div class="alert alert-error"><?php echo htmlspecialchars($erro); ?></div></div>
    <?php endif; ?>

    <section class="admin-stats">
        <div class="container">
            <div class="summary-cards">
                <div class="summary-card">
                    <span class="summary-label">Total de usuários</span>
                    <span class="summary-value"><?php echo number_format($totalUsuarios, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Administradores</span>
                    <span class="summary-value"><?php echo number_format($totalAdmins, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Usuários comuns</span>
                    <span class="summary-value"><?php echo number_format($totalUsuarios - $totalAdmins, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-filters">
        <div class="container">
            <div class="filters-card">
                <h3>Filtros e Busca</h3>
                <form method="GET" class="filters-form">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>Buscar</label>
                            <input type="text" name="busca" value="<?php echo htmlspecialchars($filtroBusca); ?>" placeholder="Nome ou email...">
                        </div>
                        <div class="filter-group">
                            <label>Tipo</label>
                            <select name="tipo">
                                <option value="">Todos</option>
                                <option value="admin" <?php echo $filtroTipo == 'admin' ? 'selected' : ''; ?>>Administradores</option>
                                <option value="usuario" <?php echo $filtroTipo == 'usuario' ? 'selected' : ''; ?>>Usuários</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Ordenar por</label>
                            <select name="ordenar">
                                <option value="data_desc" <?php echo $filtroOrdenacao == 'data_desc' ? 'selected' : ''; ?>>Data (mais recente)</option>
                                <option value="data_asc" <?php echo $filtroOrdenacao == 'data_asc' ? 'selected' : ''; ?>>Data (mais antigo)</option>
                                <option value="nome_asc" <?php echo $filtroOrdenacao == 'nome_asc' ? 'selected' : ''; ?>>Nome (A-Z)</option>
                                <option value="nome_desc" <?php echo $filtroOrdenacao == 'nome_desc' ? 'selected' : ''; ?>>Nome (Z-A)</option>
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">Aplicar</button>
                            <a href="usuarios.php" class="btn btn-outline">Limpar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="admin-content">
        <div class="container">
            <div class="admin-card">
                <div class="section-header">
                    <h3><?php echo $usuarioEditar ? 'Editar Usuário' : 'Criar Novo Usuário'; ?></h3>
                    <?php if ($usuarioEditar): ?>
                    <a href="usuarios.php" class="btn btn-sm btn-outline">Cancelar</a>
                    <?php endif; ?>
                </div>
                
                <form method="POST" class="admin-form">
                    <input type="hidden" name="acao" value="<?php echo $usuarioEditar ? 'editar' : 'criar'; ?>">
                    <?php if ($usuarioEditar): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$usuarioEditar['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nome completo</label>
                            <input type="text" name="nome" required minlength="3" 
                                   value="<?php echo htmlspecialchars($usuarioEditar['nome'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" required 
                                   value="<?php echo htmlspecialchars($usuarioEditar['email'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><?php echo $usuarioEditar ? 'Nova senha (deixe em branco para manter)' : 'Senha'; ?></label>
                            <input type="password" name="<?php echo $usuarioEditar ? 'nova_senha' : 'senha'; ?>" 
                                   <?php echo $usuarioEditar ? '' : 'required'; ?> minlength="6" placeholder="••••••">
                        </div>
                        <div class="form-group">
                            <label>Administrador</label>
                            <label class="toggle-label">
                                <input type="checkbox" name="is_admin" value="1" 
                                       <?php echo (!empty($usuarioEditar['is_admin'])) ? 'checked' : ''; ?>>
                                Tornar administrador
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?php echo $usuarioEditar ? 'Atualizar' : 'Criar'; ?> Usuário</button>
                </form>
            </div>

            <div class="admin-card">
                <div class="card-header-flex">
                    <div>
                        <h3>Lista de Usuários</h3>
                        <p class="card-desc">Total: <?php echo count($usuarios); ?> usuário(s)</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Investimentos</th>
                                <th>Cadastrado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($u['nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php if (!empty($u['is_admin'])): ?>
                                    <span class="badge badge-admin">Admin</span>
                                    <?php else: ?>
                                    <span class="badge">Usuário</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo (int)$u['qtd_investimentos']; ?> ativos, 
                                    <?php echo (int)$u['qtd_renda_fixa']; ?> renda fixa
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="usuarios.php?editar=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline">Editar</a>
                                        <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Excluir este usuário? Todas as carteiras serão removidas.');">
                                            <input type="hidden" name="acao" value="excluir">
                                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
