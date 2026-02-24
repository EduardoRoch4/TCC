<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    header('Location: carteira.php');
    exit;
}

$pageTitle = 'Cadastrar';
$erros = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    
    $resultado = registrar($nome, $email, $senha, $confirmarSenha);
    
    if ($resultado['sucesso']) {
        $sucesso = true;
        if (login($email, $senha)) {
            header('Location: carteira.php?novo=1');
            exit;
        }
    } else {
        $erros = $resultado['erros'];
    }
}

include 'includes/header.php';
?>

<main class="main auth-page">
    <div class="container">
        <div class="auth-card">
            <h1>Criar conta</h1>
            <p class="auth-subtitle">Comece a gerenciar seus investimentos</p>
            
            <?php if ($sucesso): ?>
            <div class="alert alert-success">Conta criada com sucesso! Redirecionando...</div>
            <?php elseif (!empty($erros)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($erros as $erro): ?>
                    <li><?php echo htmlspecialchars($erro); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="nome">Nome completo</label>
                    <input type="text" id="nome" name="nome" required minlength="3"
                           value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>"
                           placeholder="Seu nome">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="seu@email.com">
                </div>
                <div class="form-group">
                    <label for="senha">Senha (mínimo 6 caracteres)</label>
                    <input type="password" id="senha" name="senha" required minlength="6" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Criar conta</button>
            </form>
            
            <p class="auth-footer">
                Já tem conta? <a href="login.php">Faça login</a>
            </p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
