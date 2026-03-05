<?php $titulo = 'Pedido — Mesa ' . (int)($mesa['numero'] ?? 0) . ' - ' . APP_NAME; require __DIR__ . '/../layout/header.php'; ?>
<div class="container">
    <h1>Pedido — Mesa <?= (int)$mesa['numero'] ?></h1>
    <p class="lead">
        <?php // $clientParam está definido pelo cabeçalho ?>
        <a href="<?= BASE_URL ?>?url=menu/carrinho<?= $clientParam ?>" class="btn btn-secondary">Ver meu pedido (carrinho)</a>
    </p>

    <?php foreach ($categorias as $categoria => $itens): ?>
        <section class="categoria">
            <h2><?= htmlspecialchars($categoria) ?></h2>
            <div class="cardapio-grid pedido-grid">
                <?php foreach ($itens as $p): ?>
                    <div class="card-item card-item-pedido">
                        <div class="card-item-body">
                            <h3><?= htmlspecialchars($p['nome']) ?></h3>
                            <?php if (!empty($p['descricao'])): ?>
                                <p class="descricao"><?= htmlspecialchars($p['descricao']) ?></p>
                            <?php endif; ?>
                            <p class="preco">R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></p>
                            <div class="add-pedido">
                                <input type="number" min="1" value="1" class="qtd-input" data-produto-id="<?= (int)$p['id'] ?>">
                                <button type="button" class="btn btn-primary btn-add-item" data-produto-id="<?= (int)$p['id'] ?>">Adicionar</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
<?php
$baseUrl = BASE_URL . '?url=pedido/adicionar';
$js_extra = <<<JS
<script>
// obtém token de cliente armazenado na aba
function getClientToken() {
    var tok = sessionStorage.getItem('if_client');
    if (!tok) {
        tok = Math.random().toString(36).substr(2, 9);
        sessionStorage.setItem('if_client', tok);
    }
    return tok;
}

document.querySelectorAll('.btn-add-item').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var produtoId = this.dataset.produtoId;
        var card = this.closest('.card-item-pedido');
        var qtd = parseInt(card.querySelector('.qtd-input').value, 10) || 1;
        var fd = new FormData();
        fd.append('produto_id', produtoId);
        fd.append('quantidade', qtd);
        fd.append('client', getClientToken());
        fetch('{$baseUrl}', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok) alert(data.msg || 'Adicionado ao pedido!');
                else alert(data.msg || 'Erro.');
            })
            .catch(function() { alert('Erro de conexão.'); });
    });
});
</script>
JS;
require __DIR__ . '/../layout/footer.php';
?>
