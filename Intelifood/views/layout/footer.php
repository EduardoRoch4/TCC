    </main>

    <?php if ($carrinho_qtd > 0): ?>
    <div class="cart-bar">
        <div class="cart-bar-inner">
            <span class="cart-bar-icon">🛒 <strong><?= $carrinho_qtd ?></strong></span>
            <a href="<?= BASE_URL ?>?url=menu/carrinho<?= $clientParam ?>" class="cart-bar-btn">Revisar pedido</a>
            <span class="cart-bar-total">R$ <?= number_format($carrinho_total, 2, ',', '.') ?></span>
        </div>
    </div>
    <?php endif; ?>

    <nav class="bottom-nav">
        <a href="<?= BASE_URL ?>?url=menu/index<?= $clientParam ?>" class="bottom-nav-item <?= ($pagina_ativa ?? '') === 'cardapio' ? 'active' : '' ?>">
            <span class="bottom-nav-icon">📋</span>
            <span>Cardápio</span>
        </a>
        <a href="<?= BASE_URL ?>?url=menu/carrinho<?= $clientParam ?>" class="bottom-nav-item <?= ($pagina_ativa ?? '') === 'pedido' ? 'active' : '' ?>">
            <span class="bottom-nav-icon">📝</span>
            <span>Pedido</span>
        </a>
    </nav>

    <script src="<?= BASE_URL ?>asset.php?f=js/app.js"></script>
    <?php if (!empty($js_extra)): ?><?= $js_extra ?><?php endif; ?>
</body>
</html>
