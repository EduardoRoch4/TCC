<?php $titulo = 'Cadastro - ' . APP_NAME; require __DIR__ . '/../layout/header.php'; ?>
<div class="container form-page">
    <h1>Cadastrar</h1>
    <?php if ($erro): ?>
        <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>?url=auth/registrarPost" class="form">
        <label>Nome</label>
        <input type="text" name="nome" required placeholder="Seu nome">
        <label>E-mail</label>
        <input type="email" name="email" required placeholder="seu@email.com">
        <label>Senha (mín. 6 caracteres)</label>
        <input type="password" name="senha" required minlength="6">
        <label>Confirmar senha</label>
        <input type="password" name="confirma_senha" required>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
    </form>
    <p class="form-footer">Já tem conta? <a href="<?= BASE_URL ?>?url=auth/login">Entrar</a></p>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
