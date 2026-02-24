<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    header('Location: carteira.php');
    exit;
}

$pageTitle = 'Entrar';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } elseif (login($email, $senha)) {
        $redirect = $_GET['redirect'] ?? 'carteira.php';
        header('Location: ' . $redirect);
        exit;
    } else {
        $erro = 'Email ou senha incorretos.';
    }
}

include 'includes/header.php';
?>

<main class="main auth-page">
    <div class="container">
        <div class="auth-card">
            <h1>Entrar na sua conta</h1>
            <p class="auth-subtitle">Acesse sua carteira de investimentos</p>
            
            <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="seu@email.com">
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </form>
            
            <p class="auth-footer">
                Não tem conta? <a href="register.php">Cadastre-se gratuitamente</a>
            </p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
