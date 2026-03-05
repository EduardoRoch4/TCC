<?php
$titulo = 'Usuários - Admin';
ob_start();
?>
<h1 class="admin-page-title">Usuários (Login e cadastro)</h1>
<p class="admin-hint">Cadastre usuários (clientes ou administradores). O cliente não faz login no cardápio; o cadastro fica apenas no painel.</p>
<div class="container">
    <p><a href="<?= BASE_URL ?>?url=admin/usuarioForm" class="btn btn-primary">Cadastrar usuário</a></p>
    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Tipo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['nome']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['tipo'] === 'admin' ? 'Administrador' : 'Cliente' ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>?url=admin/usuarioForm&id=<?= $u['id'] ?>" class="btn btn-sm">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php $conteudo = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
