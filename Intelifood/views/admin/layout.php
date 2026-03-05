<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Admin - ' . APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>asset.php?f=css/style.css">
</head>
<body class="admin">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="<?= BASE_URL ?>?url=admin/index" class="logo"><?= APP_NAME ?> — Admin</a>
            <a href="<?= BASE_URL ?>?url=admin/index">Início</a>
            <a href="<?= BASE_URL ?>?url=admin/pedidos">Pedidos</a>
            <a href="<?= BASE_URL ?>?url=admin/mesas">Mesas</a>
            <a href="<?= BASE_URL ?>?url=admin/produtos">Cardápio (Produtos)</a>
            <a href="<?= BASE_URL ?>?url=admin/rendimento">Rendimento</a>
            <a href="<?= BASE_URL ?>?url=admin/contas">Contas a pagar</a>
            <a href="<?= BASE_URL ?>?url=admin/usuarios">Usuários (login/cadastro)</a>
            <a href="<?= BASE_URL ?>?url=menu/index">Ver cardápio do cliente</a>
            <a href="<?= BASE_URL ?>?url=admin/logout">Sair</a>
        </aside>
        <div class="admin-content">
            <?php if (!empty($_SESSION['sucesso'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
                <?php unset($_SESSION['sucesso']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['erro'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_SESSION['erro']) ?></div>
                <?php unset($_SESSION['erro']); ?>
            <?php endif; ?>
            <?= $conteudo ?? '' ?>
        </div>
    </div>
    <script src="<?= BASE_URL ?>asset.php?f=js/app.js"></script>
</body>
</html>
