<?php
$titulo = 'Cardápio - ' . APP_NAME;
$pagina_ativa = 'cardapio';
require __DIR__ . '/../layout/header.php';
?>
<div class="info-card">
    <div class="info-card-header">
        <span class="info-card-logo"><?= APP_NAME ?></span>
        <h2 class="info-card-name"><?= APP_NAME ?></h2>
    </div>
    <p class="info-card-cuisine">Cardápio digital</p>
    <div class="info-card-status">
        <span class="status-badge status-aberta">● Aberta</span>
    </div>
    <p class="info-card-min">Pedido mínimo: consulte na mesa.</p>
    <a href="<?= BASE_URL ?>?url=menu/mesa<?= $clientParam ?>" class="btn btn-primary btn-escolher-mesa">Escolher minha mesa e pedir</a>
</div>

<div class="container container-cardapio">
    <?php foreach ($categorias as $categoria => $itens): ?>
        <section class="categoria-cardapio">
            <h2 class="categoria-titulo"><?= htmlspecialchars($categoria) ?></h2>
            <div class="menu-list">
                <?php foreach ($itens as $p): ?>
                    <div class="menu-item-row">
                        <div class="menu-item-info">
                            <h3 class="menu-item-nome"><?= htmlspecialchars($p['nome']) ?></h3>
                            <p class="menu-item-desc"><?= htmlspecialchars(mb_strimwidth($p['descricao'] ?? '', 0, 80, '...')) ?></p>
                            <span class="menu-item-vermais" data-nome="<?= htmlspecialchars($p['nome']) ?>" data-desc="<?= htmlspecialchars($p['descricao'] ?? '') ?>">Ver mais</span>
                            <p class="menu-item-preco">R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></p>
                        </div>
                        <div class="menu-item-thumb">
                            <span class="menu-item-img-placeholder">🍽</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<div id="modal-vermais" class="modal" aria-hidden="true">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <h3 id="modal-nome"></h3>
        <p id="modal-desc"></p>
        <button type="button" class="btn btn-secondary btn-fechar-modal">Fechar</button>
    </div>
</div>
<script>
document.querySelectorAll('.menu-item-vermais').forEach(function(el) {
    el.addEventListener('click', function() {
        document.getElementById('modal-nome').textContent = this.dataset.nome;
        document.getElementById('modal-desc').textContent = this.dataset.desc || '—';
        document.getElementById('modal-vermais').setAttribute('aria-hidden', 'false');
    });
});
document.querySelector('.btn-fechar-modal')?.addEventListener('click', function() {
    document.getElementById('modal-vermais').setAttribute('aria-hidden', 'true');
});
document.querySelector('.modal-backdrop')?.addEventListener('click', function() {
    document.getElementById('modal-vermais').setAttribute('aria-hidden', 'true');
});
</script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
