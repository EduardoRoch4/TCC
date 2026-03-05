<?php
$titulo = 'Mesas - Admin';
ob_start();
?>
<h1 class="admin-page-title">Mesas</h1>
<div class="container">
    <p><a href="<?= BASE_URL ?>?url=admin/mesaForm" class="btn btn-primary">Nova mesa</a></p>
    <table class="table">
        <thead>
            <tr>
                <th>Número</th>
                <th>Capacidade</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mesas as $m): ?>
                <tr>
                    <td>Mesa <?= $m['numero'] ?></td>
                    <td><?= $m['capacidade'] ?> lugares</td>
                    <td><?= $m['ocupada'] ? 'Ocupada' : 'Livre' ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>?url=admin/mesaForm&id=<?= $m['id'] ?>" class="btn btn-sm">Editar</a>
                        <?php if (!$m['ocupada']): ?>
                        <form method="post" action="<?= BASE_URL ?>?url=admin/mesaExcluir" style="display:inline" onsubmit="return confirm('Remover esta mesa?');">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
