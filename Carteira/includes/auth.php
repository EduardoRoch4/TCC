<?php
/**
 * Funções de autenticação
 * - Usa senha_hash com password_hash()
 * - Prepared statements em todas as queries
 * - Retorno estruturado para erros/sucesso
 */

function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $redirect = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
        header("Location: login.php?redirect=$redirect");
        exit;
    }
}

function getUsuarioAtual() {
    if (!isLoggedIn()) {
        return null;
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function login($email, $senha) {
    $email = trim($email);
    if (empty($email) || empty($senha)) {
        return false;
    }

    $pdo = getConnection();
    
    try {
        // Primeiro tenta buscar com todos os campos possíveis
        $stmt = $pdo->prepare("
            SELECT id, nome, email, 
                   COALESCE(senha_hash, senha) as senha_campo,
                   is_admin 
            FROM usuarios 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            return false;
        }
        
        // Verifica a senha
        $senhaCampo = $usuario['senha_campo'];
        
        // Se o campo começa com $2y$ ou $2a$ ou $2b$, é um hash bcrypt
        if (preg_match('/^\$2[ayb]\$/', $senhaCampo)) {
            // É hash bcrypt, usa password_verify
            if (password_verify($senha, $senhaCampo)) {
                $_SESSION['usuario_id']    = $usuario['id'];
                $_SESSION['usuario_nome']  = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['is_admin']      = !empty($usuario['is_admin']);
                return true;
            }
        } else {
            // Senha em texto plano (apenas para migração, não recomendado)
            if ($senha === $senhaCampo) {
                // Migra para hash ao fazer login
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                try {
                    $pdo->prepare("UPDATE usuarios SET senha_hash = ? WHERE id = ?")->execute([$senhaHash, $usuario['id']]);
                } catch (PDOException $e) {
                    // Se não tiver coluna senha_hash, ignora
                }
                
                $_SESSION['usuario_id']    = $usuario['id'];
                $_SESSION['usuario_nome']  = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['is_admin']      = !empty($usuario['is_admin']);
                return true;
            }
        }
    } catch (PDOException $e) {
        error_log("Erro no login: " . $e->getMessage());
        // Tenta método alternativo se o COALESCE não funcionar
        try {
            $stmt = $pdo->prepare("SELECT id, nome, email, senha_hash, is_admin FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && isset($usuario['senha_hash']) && password_verify($senha, $usuario['senha_hash'])) {
                $_SESSION['usuario_id']    = $usuario['id'];
                $_SESSION['usuario_nome']  = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['is_admin']      = !empty($usuario['is_admin']);
                return true;
            }
        } catch (PDOException $e2) {
            error_log("Erro no login alternativo: " . $e2->getMessage());
        }
    }

    return false;
}

function registrar($nome, $email, $senha, $confirmarSenha) {
    $nome   = trim($nome);
    $email  = trim($email);
    $erros  = [];

    // Validações
    if (strlen($nome) < 3) {
        $erros[] = "Nome deve ter pelo menos 3 caracteres.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email inválido.";
    }

    if (strlen($senha) < 6) {
        $erros[] = "Senha deve ter pelo menos 6 caracteres.";
    }

    if ($senha !== $confirmarSenha) {
        $erros[] = "As senhas não coincidem.";
    }

    if (!empty($erros)) {
        return ['sucesso' => false, 'erros' => $erros];
    }

    $pdo = getConnection();

    // Verifica se email já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['sucesso' => false, 'erros' => ['Este email já está cadastrado.']];
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO usuarios 
            (nome, email, senha_hash, created_at) 
        VALUES (?, ?, ?, NOW())
    ");

    try {
        $stmt->execute([$nome, $email, $senhaHash]);
        return ['sucesso' => true];
    } catch (PDOException $e) {
        error_log("Erro ao registrar usuário: " . $e->getMessage());
        return ['sucesso' => false, 'erros' => ['Erro interno ao cadastrar. Tente novamente mais tarde.']];
    }
}

function isAdmin() {
    return isLoggedIn() && !empty($_SESSION['is_admin']);
}

function requireAdmin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit;
    }
}

function logout() {
    // Limpa todas as variáveis de sessão
    $_SESSION = array();

    // Destroi a sessão completamente
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    // Redireciona (opcional, pode ser feito no logout.php também)
    header('Location: index.php');
    exit;
}