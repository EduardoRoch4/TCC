<?php
$titulo = 'Contas a pagar - Admin';
ob_start();
?>
<h1 class="admin-page-title">Contas a pagar</h1>
<div class="container">
    <p>
        <a href="<?= BASE_URL ?>?url=admin/contaForm" class="btn btn-primary">Nova conta</a>
        <strong>Total pendente: R$ <?= number_format($totalPendente, 2, ',', '.') ?></strong>
    </p>
    <table class="table">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contas as $c): ?>
                <tr class="<?= $c['pago'] ? 'muted' : '' ?>">
                    <td><?= htmlspecialchars($c['descricao']) ?></td>
                    <td>R$ <?= number_format($c['valor'], 2, ',', '.') ?></td>
                    <td><?= date('d/m/Y', strtotime($c['data_vencimento'])) ?></td>
                    <td><?= $c['pago'] ? 'Pago em ' . date('d/m/Y', strtotime($c['data_pagamento'])) : 'Pendente' ?></td>
                    <td>
                        <?php if (!$c['pago']): ?>
                            <form method="post" action="<?= BASE_URL ?>?url=admin/contaPagar" style="display:inline">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn btn-sm">Marcar pago</button>
                            </form>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>?url=admin/contaForm&id=<?= $c['id'] ?>" class="btn btn-sm">Editar</a>
                        <form method="post" action="<?= BASE_URL ?>?url=admin/contaExcluir" style="display:inline" onsubmit="return confirm('Excluir esta conta?');">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($contas)): ?>
        <p class="muted">Nenhuma conta cadastrada.</p>
    <?php endif; ?>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
