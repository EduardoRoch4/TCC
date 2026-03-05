<?php
$titulo = 'Pedidos - Admin';
ob_start();
?>
<h1 class="admin-page-title">Pedidos</h1>
<div class="container">
    <p>
        <a href="<?= BASE_URL ?>?url=admin/pedidos" class="btn btn-secondary">Todos</a>
        <a href="<?= BASE_URL ?>?url=admin/pedidos&status=aberto" class="btn btn-secondary">Abertos</a>
        <a href="<?= BASE_URL ?>?url=admin/pedidos&status=fechado" class="btn btn-secondary">Fechados</a>
        <a href="<?= BASE_URL ?>?url=admin/pedidos&status=cancelado" class="btn btn-secondary">Cancelados</a>
    </p>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Mesa</th>
                <th>Total</th>
                <th>Status</th>
                <th>Data</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $v): ?>
                <tr>
                    <td><?= $v['id'] ?></td>
                    <td>Mesa <?= $v['mesa_numero'] ?></td>
                    <td>R$ <?= number_format($v['total'], 2, ',', '.') ?></td>
                    <td><span class="badge badge-<?= $v['status'] ?>"><?= $v['status'] ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($v['criado_em'])) ?></td>
                    <td><a href="<?= BASE_URL ?>?url=admin/pedidoDetalhe&id=<?= $v['id'] ?>" class="btn btn-sm">Ver</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($pedidos)): ?>
        <p class="muted">Nenhum pedido encontrado.</p>
    <?php endif; ?>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
