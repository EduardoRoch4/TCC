<?php
$titulo = 'Pedido #' . $venda['id'] . ' - Admin';
ob_start();
?>
<div class="container">
    <h1>Pedido #<?= $venda['id'] ?> — Mesa <?= $venda['mesa_numero'] ?></h1>
    <p>Status: <span class="badge badge-<?= $venda['status'] ?>"><?= $venda['status'] ?></span> |
       Data: <?= date('d/m/Y H:i', strtotime($venda['criado_em'])) ?></p>
    <table class="table">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Qtd</th>
                <th>Preço un.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $i): ?>
                <tr>
                    <td><?= htmlspecialchars($i['produto_nome']) ?></td>
                    <td><?= $i['quantidade'] ?></td>
                    <td>R$ <?= number_format($i['preco_unitario'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($i['subtotal'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>R$ <?= number_format($venda['total'], 2, ',', '.') ?></strong></td>
            </tr>
        </tfoot>
    </table>
    <?php if ($venda['status'] === 'aberto'): ?>
        <form method="post" action="<?= BASE_URL ?>?url=admin/fecharPedido" style="display:inline" onsubmit="return confirm('Fechar pedido e liberar a mesa?');">
            <input type="hidden" name="venda_id" value="<?= $venda['id'] ?>">
            <button type="submit" class="btn btn-primary">Fechar pedido e liberar mesa</button>
        </form>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>?url=admin/pedidos" class="btn btn-secondary">Voltar</a>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
