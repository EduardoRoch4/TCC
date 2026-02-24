<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>CarteiraInvest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <a href="index.php" class="logo">
                <span class="logo-icon">📊</span>
                <span>CarteiraInvest</span>
            </a>
            <nav class="nav">
                <a href="index.php">Início</a>
                <a href="ativos.php">Ativos</a>
                <?php if (isLoggedIn()): ?>
                    <a href="carteira.php">Minha Carteira</a>
                    <a href="dashboard.php">Dashboard</a>
                    <?php if (function_exists('isAdmin') && isAdmin()): ?>
                    <a href="admin/index.php" class="nav-admin">Painel Admin</a>
                    <?php endif; ?>
                    <div class="user-menu">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                        <a href="logout.php" class="btn btn-outline btn-sm">Sair</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Entrar</a>
                    <a href="register.php" class="btn btn-primary">Cadastrar</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
