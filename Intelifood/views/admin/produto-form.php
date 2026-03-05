<?php
$titulo = ($produto ? 'Editar' : 'Novo') . ' produto - Admin';
$isEdit = !empty($produto);
ob_start();
?>
<div class="container">
    <h1><?= $isEdit ? 'Editar produto' : 'Novo produto' ?></h1>
    <form method="post" action="<?= BASE_URL ?>?url=admin/produtoSalvar" class="form">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $produto['id'] ?>">
        <?php endif; ?>
        <label>Nome</label>
        <input type="text" name="nome" required value="<?= htmlspecialchars($produto['nome'] ?? '') ?>">
        <label>Descrição</label>
        <textarea name="descricao" rows="3"><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>
        <label>Preço (R$)</label>
        <input type="text" name="preco" required placeholder="0,00" value="<?= $produto ? number_format($produto['preco'], 2, ',', '') : '' ?>">
        <label>Categoria</label>
        <input type="text" name="categoria" required placeholder="Ex: Lanches, Bebidas" value="<?= htmlspecialchars($produto['categoria'] ?? '') ?>">
        <?php if ($isEdit): ?>
        <label>
            <input type="checkbox" name="ativo" value="1" <?= $produto['ativo'] ? 'checked' : '' ?>>
            Ativo (aparece no cardápio)
        </label>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
    <a href="<?= BASE_URL ?>?url=admin/produtos" class="btn btn-secondary">Voltar</a>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
