<?php
$titulo = ($usuario ? 'Editar' : 'Cadastrar') . ' usuário - Admin';
$isEdit = !empty($usuario);
ob_start();
?>
<h1 class="admin-page-title"><?= $isEdit ? 'Editar usuário' : 'Cadastrar usuário' ?></h1>
<div class="container">
    <form method="post" action="<?= BASE_URL ?>?url=admin/usuarioSalvar" class="form">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
        <?php endif; ?>
        <label>Nome</label>
        <input type="text" name="nome" required value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>">
        <label>E-mail</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
        <label>Senha <?= $isEdit ? '(deixe em branco para não alterar)' : '(mín. 6 caracteres)' ?></label>
        <input type="password" name="senha" <?= $isEdit ? '' : 'required minlength="6"' ?> placeholder="<?= $isEdit ? '••••••••' : '' ?>">
        <label>Tipo</label>
        <select name="tipo">
            <option value="cliente" <?= ($usuario['tipo'] ?? '') === 'cliente' ? 'selected' : '' ?>>Cliente</option>
            <option value="admin" <?= ($usuario['tipo'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
        </select>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
    <a href="<?= BASE_URL ?>?url=admin/usuarios" class="btn btn-secondary">Voltar</a>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
