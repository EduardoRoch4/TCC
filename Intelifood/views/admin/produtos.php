<?php
$titulo = 'Produtos - Admin';
ob_start();
?>
<h1 class="admin-page-title">Cardápio (Produtos)</h1>
<p class="admin-hint">Os itens cadastrados aqui aparecem no cardápio do cliente. Edite ou desative para alterar o que o cliente vê.</p>
<div class="container">
    <p><a href="<?= BASE_URL ?>?url=admin/produtoForm" class="btn btn-primary">Novo produto</a></p>
    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Ativo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= htmlspecialchars($p['categoria']) ?></td>
                    <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                    <td><?= $p['ativo'] ? 'Sim' : 'Não' ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>?url=admin/produtoForm&id=<?= $p['id'] ?>" class="btn btn-sm">Editar</a>
                        <form method="post" action="<?= BASE_URL ?>?url=admin/produtoExcluir" style="display:inline" onsubmit="return confirm('Remover este produto?');">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
