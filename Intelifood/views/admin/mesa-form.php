<?php
$titulo = ($mesa ? 'Editar' : 'Nova') . ' mesa - Admin';
$isEdit = !empty($mesa);
ob_start();
?>
<div class="container">
    <h1><?= $isEdit ? 'Editar mesa' : 'Nova mesa' ?></h1>
    <form method="post" action="<?= BASE_URL ?>?url=admin/mesaSalvar" class="form form-inline">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $mesa['id'] ?>">
        <?php endif; ?>
        <label>Número da mesa</label>
        <input type="number" name="numero" required min="1" value="<?= $mesa['numero'] ?? '' ?>">
        <label>Capacidade (lugares)</label>
        <input type="number" name="capacidade" required min="1" value="<?= $mesa['capacidade'] ?? 4 ?>">
        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
    <a href="<?= BASE_URL ?>?url=admin/mesas" class="btn btn-secondary">Voltar</a>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
