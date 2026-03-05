<?php $titulo = 'Meu pedido - ' . APP_NAME; $pagina_ativa = 'pedido'; require __DIR__ . '/../layout/header.php'; ?>
<div class="container">
    <h1>Meu pedido — Mesa <?= (int)($venda['mesa_numero'] ?? 0) ?></h1>

    <?php if (empty($itens)): ?>
        <p class="lead">Carrinho vazio. <a href="<?= BASE_URL ?>?url=menu/pedido&mesa_id=<?= (int)$venda['mesa_id'] ?><?= $clientParam ?>">Adicionar itens</a></p>
    <?php else: ?>
        <table class="table table-carrinho">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qtd</th>
                    <th>Preço un.</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr data-item-id="<?= (int)$item['id'] ?>">
                        <td><?= htmlspecialchars($item['produto_nome']) ?></td>
                        <td><?= (int)$item['quantidade'] ?></td>
                        <td>R$ <?= number_format((float)$item['preco_unitario'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format((float)$item['subtotal'], 2, ',', '.') ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger btn-remover-item" data-item-id="<?= (int)$item['id'] ?>">Remover</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong>Total</strong></td>
                    <td colspan="2"><strong>R$ <?= number_format((float)$venda['total'], 2, ',', '.') ?></strong></td>
                </tr>
            </tfoot>
        </table>
        <p>
            <a href="<?= BASE_URL ?>?url=menu/pedido&mesa_id=<?= (int)$venda['mesa_id'] ?><?= $clientParam ?>" class="btn btn-secondary">Continuar adicionando</a>
            <form method="post" action="<?= BASE_URL ?>?url=pedido/finalizar<?= $clientParam ?>" style="display:inline" onsubmit="return confirm('Enviar pedido e finalizar?');">
                <input type="hidden" name="client" value="<?= htmlspecialchars($currentClient) ?>">
                <button type="submit" class="btn btn-primary">Enviar pedido</button>
            </form>
        </p>
    <?php endif; ?>
</div>
<?php
$baseUrl = BASE_URL . '?url=pedido/removerItem';
$js_extra = <<<JS
<script>
function getClientToken() {
    var tok = sessionStorage.getItem('if_client');
    if (!tok) {
        tok = Math.random().toString(36).substr(2, 9);
        sessionStorage.setItem('if_client', tok);
    }
    return tok;
}
document.querySelectorAll('.btn-remover-item').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var itemId = this.dataset.itemId;
        var fd = new FormData();
        fd.append('item_id', itemId);
        fd.append('client', getClientToken());
        fetch('{$baseUrl}', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok) location.reload();
                else alert(data.msg || 'Erro.');
            });
    });
});
</script>
JS;
require __DIR__ . '/../layout/footer.php';
?>
