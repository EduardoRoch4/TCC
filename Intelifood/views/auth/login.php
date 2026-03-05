<?php $titulo = 'Login - ' . APP_NAME; require __DIR__ . '/../layout/header.php'; ?>
<div class="container form-page">
    <h1>Entrar</h1>
    <?php if ($erro): ?>
        <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['sucesso'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>?url=auth/logar" class="form">
        <label>E-mail</label>
        <input type="email" name="email" required placeholder="seu@email.com">
        <label>Senha</label>
        <input type="password" name="senha" required>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
    <p class="form-footer">Não tem conta? <a href="<?= BASE_URL ?>?url=auth/registrar">Cadastre-se</a></p>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
