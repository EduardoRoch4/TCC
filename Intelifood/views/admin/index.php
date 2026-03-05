<?php
$titulo = 'Painel - Admin';
ob_start();
?>
<h1 class="admin-page-title">Painel administrativo</h1>
<div class="container">
    <div class="dashboard-cards">
        <div class="card card-dash">
            <h3>Pedidos em aberto</h3>
            <p class="big-number"><?= count($pedidosAbertos) ?></p>
            <a href="<?= BASE_URL ?>?url=admin/pedidos&status=aberto" class="btn btn-secondary">Ver pedidos</a>
        </div>
        <div class="card card-dash">
            <h3>Rendimento total (fechados)</h3>
            <p class="big-number">R$ <?= number_format($rendimento['total'] ?? 0, 2, ',', '.') ?></p>
            <p class="muted"><?= (int)($rendimento['quantidade'] ?? 0) ?> vendas</p>
            <a href="<?= BASE_URL ?>?url=admin/rendimento" class="btn btn-secondary">Ver rendimento</a>
        </div>
        <div class="card card-dash">
            <h3>Contas a pagar</h3>
            <p class="big-number">R$ <?= number_format($totalPendente, 2, ',', '.') ?></p>
            <a href="<?= BASE_URL ?>?url=admin/contas" class="btn btn-secondary">Ver contas</a>
        </div>
    </div>
    <?php if (!empty($pedidosAbertos)): ?>
        <h2>Pedidos abertos</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Mesa</th>
                    <th>Total</th>
                    <th>Data</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidosAbertos as $v): ?>
                    <tr>
                        <td><?= $v['id'] ?></td>
                        <td>Mesa <?= $v['mesa_numero'] ?></td>
                        <td>R$ <?= number_format($v['total'], 2, ',', '.') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($v['criado_em'])) ?></td>
                        <td><a href="<?= BASE_URL ?>?url=admin/pedidoDetalhe&id=<?= $v['id'] ?>" class="btn btn-sm">Ver</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
