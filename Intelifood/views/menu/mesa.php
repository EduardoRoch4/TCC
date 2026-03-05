<?php
$mesaSessao = $_SESSION['mesa_id'] ?? null;
$numeroSessao = $_SESSION['mesa_numero'] ?? null;
$titulo = 'Escolher mesa - ' . APP_NAME;
require __DIR__ . '/../layout/header.php'; ?>
<div class="container">
    <h1>Escolha sua mesa</h1>
    <?php if ($mesaSessao): ?>
        <p class="alert alert-error">
            Você já está com a mesa <strong><?= htmlspecialchars($numeroSessao) ?></strong> selecionada.<br>
            Abra outra aba/janela para iniciar um novo pedido ou <a href="<?= BASE_URL ?>?url=menu/pedido&mesa_id=<?= $mesaSessao ?><?= $clientParam ?>">retorne ao seu pedido</a>.
        </p>
    <?php else: ?>
        <p class="lead">Selecione a mesa em que você está para iniciar o pedido.</p>
        <?php if (empty($mesas)): ?>
            <p class="alert alert-error">Nenhuma mesa disponível no momento. Tente mais tarde.</p>
        <?php else: ?>
            <div class="mesas-grid">
                <?php foreach ($mesas as $m): ?>
                    <div class="mesa-card" data-mesa-id="<?= (int)$m['id'] ?>">
                        <span class="mesa-numero">Mesa <?= (int)$m['numero'] ?></span>
                        <span class="mesa-capacidade"><?= (int)$m['capacidade'] ?> lugares</span>
                        <button type="button" class="btn btn-primary btn-mesa">Usar esta mesa</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <form id="form-mesa" method="post" action="<?= BASE_URL ?>?url=pedido/iniciar" style="display:none">
                <input type="hidden" name="mesa_id" id="input-mesa-id">
                <input type="hidden" name="client" id="input-client">
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php
$base = BASE_URL;
$js_extra = <<<JS
<script>
// garante que cada aba possua seu token independente
function getClientToken() {
    var tok = sessionStorage.getItem('if_client');
    if (!tok) {
        tok = Math.random().toString(36).substr(2, 9);
        sessionStorage.setItem('if_client', tok);
    }
    return tok;
}

// realça e bloqueia outros cartões durante seleção
var cards = document.querySelectorAll('.mesa-card');
document.querySelectorAll('.btn-mesa').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var card = this.closest('.mesa-card');
        // visual
        cards.forEach(function(c){ c.classList.add('disabled'); });
        card.classList.add('selected');
        // preparar dados
        var id = card.dataset.mesaId;
        document.getElementById('input-mesa-id').value = id;
        document.getElementById('input-client').value = getClientToken();
        var form = document.getElementById('form-mesa');
        var fd = new FormData(form);
        fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok && data.redirect) {
                    // redireciona com o token caso não esteja no redirect
                    var url = new URL(data.redirect, window.location.href);
                    if (!url.searchParams.get('client')) {
                        url.searchParams.set('client', getClientToken());
                    }
                    window.location = url.toString();
                } else {
                    alert(data.msg || 'Erro ao iniciar pedido.');
                    cards.forEach(function(c){ c.classList.remove('disabled'); });
                    card.classList.remove('selected');
                }
            })
            .catch(function() {
                alert('Erro de conexão.');
                cards.forEach(function(c){ c.classList.remove('disabled'); });
                card.classList.remove('selected');
            });
    });
});
</script>
JS;
require __DIR__ . '/../layout/footer.php';
?>
