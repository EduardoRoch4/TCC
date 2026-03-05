<?php
$titulo = 'Rendimento - Admin';
ob_start();
?>
<h1 class="admin-page-title">Rendimento do estabelecimento</h1>
<div class="container">
    <form method="get" action="<?= BASE_URL ?>?url=admin/rendimento" class="form form-inline">
        <input type="hidden" name="url" value="admin/rendimento">
        <label>De</label>
        <input type="date" name="inicio" value="<?= htmlspecialchars($inicio) ?>">
        <label>Até</label>
        <input type="date" name="fim" value="<?= htmlspecialchars($fim) ?>">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
    <div class="card card-dash">
        <h3>Total no período (vendas fechadas)</h3>
        <p class="big-number">R$ <?= number_format($rendimento['total'] ?? 0, 2, ',', '.') ?></p>
        <p class="muted"><?= (int)($rendimento['quantidade'] ?? 0) ?> vendas</p>
    </div>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
