<?php
// definimos a variável currentClient no PHP para permitir que as views
// mostrem o token sem ter que reconstruí-lo. getClient() já garante que
// a sessão conterá sempre um valor (novo ou existente).
$currentClient = $this->getClient();
$clientParam = $currentClient ? '&client=' . urlencode($currentClient) : '';

$carrinho_total = 0;
$carrinho_qtd = 0;
if ($currentClient && !empty($_SESSION['clientes'][$currentClient]['venda_id'])) {
    $vendaId = (int) $_SESSION['clientes'][$currentClient]['venda_id'];
    $vendaModel = new Venda();
    $v = $vendaModel->porId($vendaId);
    if ($v && $v['status'] === 'aberto') {
        $carrinho_total = (float) $v['total'];
        $itensCarrinho = $vendaModel->itens($vendaId);
        foreach ($itensCarrinho as $i) { $carrinho_qtd += (int) $i['quantidade']; }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>asset.php?f=css/style.css">
    <script>
    // Token management for per-tab clients:
    // - If there is no "client" parameter but a token already exists in
    //   sessionStorage, we redirect to add it so that server requests stay
    //   consistent (also survives reloads).
    // - If the tab was opened from another window (window.opener available),
    //   we treat it as a fresh client and generate a new token, reloading once.
    // - Otherwise we use the token from URL or the SERVER-PROVIDED CURRENT_CLIENT
    //   and keep it in sessionStorage.
    (function(){
        var params = new URLSearchParams(window.location.search);
        var tok = params.get('client');
        var stored = sessionStorage.getItem('if_client');

        // persist storage token in URL when missing (e.g. reload)
        if (!tok && stored) {
            params.set('client', stored);
            window.location.replace(window.location.pathname + '?' + params.toString());
            return; // reload will execute new script with tok defined
        }

        // new tab detection (opened from another window/tab)
        if (window.opener && !window.opener.closed && !sessionStorage.getItem('if_new_tab')) {
            sessionStorage.setItem('if_new_tab', '1');
            var newTok = Math.random().toString(36).substr(2,9);
            params.set('client', newTok);
            window.location = window.location.pathname + '?' + params.toString();
            return;
        }

        // now synchronize sessionStorage with the active token
        if (tok) {
            sessionStorage.setItem('if_client', tok);
        } else {
            var current = "<?= htmlspecialchars($currentClient) ?>";
            if (current) {
                sessionStorage.setItem('if_client', current);
            } else if (!sessionStorage.getItem('if_client')) {
                sessionStorage.setItem('if_client', Math.random().toString(36).substr(2,9));
            }
        }
    })();
    </script>
</head>
<body class="cliente">
    <header class="header header-cardapio">
        <div class="container header-inner header-center">
            <a href="<?= BASE_URL ?>?url=menu/index" class="logo"><?= APP_NAME ?></a>
            <button id="btn-new-client" class="btn btn-sm btn-secondary" style="margin-left:auto;">Novo pedido</button>
        </div>
    </header>
    <script>
    // botão manual para iniciar novo cliente
    var btnNew = document.getElementById('btn-new-client');
    if (btnNew) {
        btnNew.addEventListener('click', function(e){
            e.preventDefault();
            sessionStorage.removeItem('if_client');
            sessionStorage.removeItem('if_new_tab');
            window.location = '<?= BASE_URL ?>?url=menu/mesa';
        });
    }
    </script>
    <main class="main main-cardapio">
        <?php if (!empty($_SESSION['sucesso'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
            <?php unset($_SESSION['sucesso']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>
