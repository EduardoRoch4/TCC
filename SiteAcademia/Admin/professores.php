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

$professorController = new ProfessorController();

// Buscar todos os professores
$professores_objetos = $professorController->listarTodos();
$professores = [];
foreach ($professores_objetos as $p) {
    $professores[] = [
        'id_professor' => $p->getIdProfessor(),
        'nome_professor' => $p->getNomeProfessor(),
        'especializacao' => $p->getEspecializacao()
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerenciamento de Professores | TechFit Admin</title>
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
        <a href="alunos.php">Alunos</a>
        <a class="active" href="professores.php">Professores</a>
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
          <h1>Gerenciamento de Professores</h1>
          <p class="muted">Visualize e edite informações dos professores cadastrados</p>
        </div>
        <div class="dash-actions">
          <input id="search-input" placeholder="Pesquisar professor..." />
          <button class="btn small" id="novo-professor">+ Novo Professor</button>
        </div>
      </header>

      <section class="recent-section">
        <div class="section-header">
          <h2>Professores Cadastrados</h2>
          <small id="count-professores" class="muted"><?php echo count($professores); ?> professores</small>
        </div>
        <div class="table-wrap">
          <table class="recent-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Especialidade</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody id="professores-table-body">
              <?php if (empty($professores)): ?>
                <tr><td colspan="4" class="muted">Nenhum professor encontrado</td></tr>
              <?php else: ?>
                <?php foreach ($professores as $prof): ?>
                  <tr>
                    <td>#<?php echo htmlspecialchars($prof['id_professor']); ?></td>
                    <td><?php echo htmlspecialchars($prof['nome_professor']); ?></td>
                    <td><?php echo htmlspecialchars($prof['especializacao'] ?? '—'); ?></td>
                    <td>
                      <button class="btn-action editar" data-id="<?php echo htmlspecialchars($prof['id_professor']); ?>">Editar</button>
                      <button class="btn-action deletar" data-id="<?php echo htmlspecialchars($prof['id_professor']); ?>">Deletar</button>
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
      <h2 id="modal-titulo">Editar Professor</h2>
      <form id="form-professor">
        <input type="hidden" id="professor-id">
        <input type="text" id="professor-nome" placeholder="Nome" required>
        <input type="email" id="professor-email" placeholder="Email" required>
        <input type="text" id="professor-especialidade" placeholder="Especialidade">
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
