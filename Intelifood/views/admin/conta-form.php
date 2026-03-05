<?php
$titulo = ($conta ? 'Editar' : 'Nova') . ' conta - Admin';
$isEdit = !empty($conta);
ob_start();
?>
<div class="container">
    <h1><?= $isEdit ? 'Editar conta' : 'Nova conta a pagar' ?></h1>
    <form method="post" action="<?= BASE_URL ?>?url=admin/contaSalvar" class="form">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $conta['id'] ?>">
        <?php endif; ?>
        <label>Descrição</label>
        <input type="text" name="descricao" required value="<?= htmlspecialchars($conta['descricao'] ?? '') ?>">
        <label>Valor (R$)</label>
        <input type="text" name="valor" required placeholder="0,00" value="<?= $conta ? number_format($conta['valor'], 2, ',', '') : '' ?>">
        <label>Data de vencimento</label>
        <input type="date" name="data_vencimento" required value="<?= $conta['data_vencimento'] ?? '' ?>">
        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
    <a href="<?= BASE_URL ?>?url=admin/contas" class="btn btn-secondary">Voltar</a>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
