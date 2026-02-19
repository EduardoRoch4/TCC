<?php
session_start();

// Carregar autoloader e classes necessárias
require_once __DIR__ . '/../app/config/autoload.php';

// Logout via GET param
if (isset($_GET['acao']) && $_GET['acao'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: /Login/login.php');
    exit;
}

// Inicializar controller
$usuarioController = new UsuarioController();

$mensagem = "";
$mostrar_redefinir_senha = false;
$usuario_sem_senha = null;
$usuario = null;

// ====== CADASTRO DE USUÁRIO ======
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['tipo']) && $_POST['tipo'] === 'cadastro') {
    $usuario_nome = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';
    $perfil = 'aluno'; // Sempre aluno no cadastro público

    if (!empty($usuario_nome) && !empty($email) && !empty($senha)) {
        
        $resultado = $usuarioController->cadastrar($usuario_nome, $email, $senha, $perfil);
        
        if ($resultado['sucesso']) {
            // Login automático após cadastro
            $usuario_cadastrado = $resultado['usuario'];
            $_SESSION['usuario'] = $usuario_cadastrado->getNome();
            $_SESSION['id_usuario'] = $usuario_cadastrado->getIdUsuario();
            $_SESSION['perfil'] = $usuario_cadastrado->getPerfil();
            
            // Redirecionar baseado no perfil
            if ($perfil === 'admin') {
                header("Location: /Admin/painel.php");
            } else {
                header("Location: /Alunos/usuario.php");
            }
            exit;
        } else {
            $mensagem = $resultado['mensagem'];
        }
    } else {
        $mensagem = "⚠️ Preencha todos os campos!";
    }
}

// ====== LOGIN DE USUÁRIO ======
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['tipo']) && $_POST['tipo'] === 'login') {
    $usuario_nome = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';

    // Se está tentando definir nova senha, processar isso primeiro
    if (!empty($usuario_nome) && isset($_POST['nova_senha']) && !empty($_POST['nova_senha'])) {
        $usuario_obj = $usuarioController->buscarPorNome($usuario_nome);
        
        if ($usuario_obj) {
            $nova_senha = trim($_POST['nova_senha']);
            $confirmar_senha = isset($_POST['confirmar_senha']) ? trim($_POST['confirmar_senha']) : '';
            
            if ($nova_senha !== $confirmar_senha) {
                $mensagem = "❌ As senhas não coincidem!";
                $mostrar_redefinir_senha = true;
                $usuario_sem_senha = $usuario_obj->getIdUsuario();
                $usuario = $usuario_nome;
            } elseif (strlen($nova_senha) < 4) {
                $mensagem = "❌ A senha deve ter pelo menos 4 caracteres!";
                $mostrar_redefinir_senha = true;
                $usuario_sem_senha = $usuario_obj->getIdUsuario();
                $usuario = $usuario_nome;
            } else {
                // Definir nova senha
                $resultado = $usuarioController->redefinirSenha($usuario_obj->getIdUsuario(), $nova_senha);
                
                if ($resultado['sucesso']) {
                    // Senha definida com sucesso, fazer login
                    $_SESSION['usuario'] = $usuario_obj->getNome();
                    $_SESSION['id_usuario'] = $usuario_obj->getIdUsuario();
                    $_SESSION['perfil'] = $usuario_obj->getPerfil();
                    
                    if ($usuario_obj->getPerfil() === 'admin') {
                        header("Location: /Admin/painel.php");
                    } else {
                        header("Location: /Alunos/usuario.php");
                    }
                    exit;
                } else {
                    $mensagem = "❌ Erro ao definir senha. Tente novamente.";
                    $mostrar_redefinir_senha = true;
                    $usuario_sem_senha = $usuario_obj->getIdUsuario();
                    $usuario = $usuario_nome;
                }
            }
        } else {
            $mensagem = "⚠️ Usuário não encontrado!";
        }
    } elseif (!empty($usuario_nome) && !empty($senha)) {
        $resultado = $usuarioController->login($usuario_nome, $senha);
        
        if ($resultado['sucesso']) {
            $usuario_obj = $resultado['usuario'];
            $_SESSION['usuario'] = $usuario_obj->getNome();
            $_SESSION['id_usuario'] = $usuario_obj->getIdUsuario();
            $_SESSION['perfil'] = $usuario_obj->getPerfil();
            
            // Redirecionar baseado no perfil
            if ($usuario_obj->getPerfil() === 'admin') {
                header("Location: /Admin/painel.php");
            } else {
                header("Location: /Alunos/usuario.php");
            }
            exit;
        } else {
            $mensagem = $resultado['mensagem'];
            
            // Se precisa redefinir senha
            if (isset($resultado['redefinir_senha']) && $resultado['redefinir_senha']) {
                $mostrar_redefinir_senha = true;
                $usuario_sem_senha = $resultado['usuario']->getIdUsuario();
                $usuario = $usuario_nome;
            }
        }
    } else {
        $mensagem = "⚠️ Preencha todos os campos!";
    }
}

?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | TechFit</title>
  <link rel="stylesheet" type="text/css" href="login.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  
  <div class="login-container">
    <div class="login-box">
      <h1>TechFit</h1>

      <?php if(!empty($mensagem)) { echo "<p id='mensagem'>$mensagem</p>"; } ?>

      <div id="form-login">
        <?php if (isset($mostrar_redefinir_senha) && $mostrar_redefinir_senha): ?>
          <form method="POST" action="">
            <input type="hidden" name="tipo" value="login">
            <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usuario ?? ''); ?>">
            <input type="password" name="nova_senha" id="nova-senha" placeholder="Defina sua senha" required minlength="4">
            <input type="password" name="confirmar_senha" id="confirmar-senha" placeholder="Confirme sua senha" required minlength="4">
            <button type="submit" id="btn-definir-senha">Definir Senha</button>
          </form>
        <?php else: ?>
          <form method="POST" action="">
            <input type="hidden" name="tipo" value="login">
            <input type="text" name="usuario" id="login-usuario" placeholder="Usuário" required>
            <input type="email" name="email" id="login-email" placeholder="Email" required>
            <input type="password" name="senha" id="login-senha" placeholder="Senha" required>
            <button type="submit" id="btn-login">Entrar</button>
          </form>
        <?php endif; ?>
        <button type="button" id="btn-voltar" class="btn-link" onclick="history.back()">
          &larr; Voltar
        </button>
        <?php if (!isset($mostrar_redefinir_senha) || !$mostrar_redefinir_senha): ?>
          <p>Não tem conta? <a href="#" id="mostrar-cadastro">Cadastre-se</a></p>
        <?php endif; ?>
      </div>

      <div id="form-cadastro" style="display:none;">
        <form method="POST" action="">
          <input type="hidden" name="tipo" value="cadastro">
          <input type="text" name="usuario" id="cadastro-usuario" placeholder="Crie um usuário" required>
          <input type="email" name="email" id="cadastro-email" placeholder="Digite seu email" required>
          <input type="password" name="senha" id="cadastro-senha" placeholder="Crie uma senha" required>
          <button type="submit" id="btn-cadastrar">Cadastrar</button>
        </form>
        <p>Já tem conta? <a href="#" id="mostrar-login">Voltar ao login</a></p>
      </div>
    </div>
  </div>

  <script>
    // Alternar entre Login e Cadastro
    const formLogin = document.getElementById('form-login');
    const formCadastro = document.getElementById('form-cadastro');

    document.getElementById('mostrar-cadastro').onclick = () => {
      formLogin.style.display = 'none';
      formCadastro.style.display = 'block';
    };

    document.getElementById('mostrar-login').onclick = () => {
      formCadastro.style.display = 'none';
      formLogin.style.display = 'block';
    };
  </script>
</body>
</html>