<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin - CarteiraInvest</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <header class="header header-admin">
        <div class="container header-content">
            <a href="../index.php" class="logo">💼 CarteiraInvest</a>
            <nav class="nav">
                <a href="../index.php">Site</a>
                <a href="index.php">Dashboard</a>
                <a href="usuarios.php">Usuários</a>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?> (Admin)</span>
                <a href="../logout.php" class="btn btn-outline btn-sm">Sair</a>
            </nav>
        </div>
    </header>
