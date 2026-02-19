<?php
session_start();
// Só admins podem visualizar o painel
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
  header('Location: /Login/login.php');
    exit;
}
$adminName = isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Admin';

// Carregar autoloader e classes necessárias
require_once __DIR__ . '/../app/config/autoload.php';

/** @var UsuarioController $usuarioController */
$usuarioController = new UsuarioController();

// Buscar todos os alunos
/** @var Usuario[] $usuarios_objetos */
$usuarios_objetos = $usuarioController->listarTodos();
$alunos = [];
foreach ($usuarios_objetos as $u) {
    // Filtrar apenas alunos
    if ($u->getPerfil() === 'aluno') {
        $alunos[] = [
            'id_usuario' => $u->getIdUsuario(),
            'nome' => $u->getNome(),
            'email' => $u->getEmail()
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="pragma" content="no-cache">
  <meta http-equiv="expires" content="0">
  <title>Gerenciamento de Alunos | TechFit Admin</title>
  <link rel="stylesheet" href="painel.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <header>
    <div class="logo">
      <a href="../index.html">
        <img src="../IMG/Logo.png" alt="Logo TechFit">
      </a>
    </div>

    <nav class="nav-buttons" id="nav-buttons">
      <a href="../index.html">Início</a>
      <a href="../Agendamento/Agendamento.html">Agendamento</a>
      <a href="../Unidades/Unidades.html">Unidades</a>
      <a href="../Chat/chat.html">Chat</a>
      <a href="../Admin/painel.php">Painel Admin</a>
      <a href="../Nossa História/nos.html">Sobre Nós</a>
      <a href="../Alunos/usuario.php" id="perfil-btn" style="display:none;">Perfil</a>
      <a href="../Login/login.php" id="login-btn">Login</a>
      <span id="user-display" style="display:none;margin-left:12px;color:#fff;">Olá, <strong id="user-name"><?php echo htmlspecialchars($adminName); ?></strong></span>
    </nav>

    <div class="menu-icon" id="menu-icon">☰</div>
  </header>

  <div class="side-menu" id="side-menu">
    <div class="close-btn" id="close-btn">✖</div>
      <a href="../index.html">Início</a>
      <a href="../Agendamento/Agendamento.html">Agendamento</a>
      <a href="../Unidades/Unidades.html">Unidades</a>
      <a href="../Chat/chat.html">Chat</a>
      <a href="../Admin/painel.php">Painel Admin</a>
      <a href="../Nossa História/nos.html">Sobre Nós</a>
      <a href="../Alunos/usuario.php" id="perfil-btn" style="display:none">Perfil</a>
      <a href="../Login/login.php" id="login-btn">Login</a>
  </div>

  <div class="overlay" id="overlay"></div>

  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-top">
        <a href="../index.html">
          <img src="../IMG/Logo.png" alt="TechFit" class="sidebar-logo">
        </a>
        <h3>TechFit Admin</h3>
      </div>

      <nav class="sidebar-nav">
        <a href="painel.php">Visão Geral</a>
        <a class="active" href="alunos.php">Alunos</a>
        <a href="professores.php">Professores</a>
        <a href="aulas.php">Aulas</a>
        <a href="agendamentos.php">Agendamentos</a>
        <a href="relatorios.php">Relatórios</a>
      </nav>

      <div class="sidebar-footer">
        <small>Usuário: <strong><?php echo $adminName; ?></strong></small>
        <a href="/Login/login.php?acao=logout" class="btn-logout">Sair</a>
      </div>
    </aside>

    <main class="dashboard">
      <header class="dashboard-header">
        <div class="dash-title">
          <h1>Gerenciamento de Alunos</h1>
          <p class="muted">Visualize e edite informações dos alunos cadastrados</p>
        </div>
        <div class="dash-actions">
          <input id="search-input" placeholder="Pesquisar aluno..." />
          <button class="btn small" id="novo-aluno">+ Novo Aluno</button>
        </div>
      </header>

      <section class="recent-section">
        <div class="section-header">
          <h2>Alunos Cadastrados</h2>
          <small id="count-alunos" class="muted"><?php echo count($alunos); ?> alunos</small>
        </div>
        <div class="table-wrap">
          <table class="recent-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody id="alunos-table-body">
              <?php if (empty($alunos)): ?>
                <tr><td colspan="4" class="muted">Nenhum aluno encontrado</td></tr>
              <?php else: ?>
                <?php foreach ($alunos as $aluno): ?>
                  <tr>
                    <td>#<?php echo htmlspecialchars($aluno['id_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['email']); ?></td>
                    <td>
                      <button class="btn-action editar" data-id="<?php echo htmlspecialchars($aluno['id_usuario']); ?>">Editar</button>
                      <button class="btn-action deletar" data-id="<?php echo htmlspecialchars($aluno['id_usuario']); ?>">Deletar</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <div id="modal" class="modal">
    <div class="modal-content">
      <span class="close" id="close">&times;</span>
      <h2 id="modal-titulo">Editar Aluno</h2>
      <form id="form-aluno">
        <input type="hidden" id="aluno-id">
        <input type="text" id="aluno-nome" placeholder="Nome" required>
        <input type="email" id="aluno-email" placeholder="Email" required>
        <button type="submit" class="btn">Salvar</button>
      </form>
    </div>
  </div>

<footer class="fade-in-up">
  <p>© 2025 TechFit — Todos os direitos reservados</p>
</footer>

  <script src="admin.js"></script>
</body>
</html>
