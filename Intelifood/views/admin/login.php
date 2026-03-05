<?php
$titulo = 'Acesso administrativo';
require __DIR__ . '/../layout/header.php';
?>
<div class="form-page">
    <h1>Acesso administrativo</h1>
    <p class="muted">Login apenas para administradores. O cardápio do cliente não possui login.</p>
    <?php if (!empty($erro)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>?url=admin/logar" class="form">
        <label>E-mail</label>
        <input type="email" name="email" required placeholder="admin@exemplo.com">
        <label>Senha</label>
        <input type="password" name="senha" required>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
    <p class="form-footer"><a href="<?= BASE_URL ?>?url=menu/index">← Voltar ao cardápio</a></p>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
