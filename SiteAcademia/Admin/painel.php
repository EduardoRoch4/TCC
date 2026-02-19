<?php
session_start();
// Só admins podem visualizar o painel
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
  header('Location: /Login/login.php');
    exit;
}
$adminName = isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Admin';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel Administrativo | TechFit</title>
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
        <a class="active" href="painel.php">Visão Geral</a>
        <a href="alunos.php">Alunos</a>
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
          <h1>Visão Geral</h1>
          <p class="muted">Resumo rápido do sistema e últimos agendamentos</p>
        </div>
        <div class="dash-actions">
          <input id="search-input" placeholder="Pesquisar..." />
          <button class="btn small" id="novo-reg">+ Novo</button>
        </div>
      </header>

      <section class="stats-grid">
        <div class="stat card stat-users">
          <div class="stat-value" id="count-users">—</div>
          <div class="stat-label">Alunos</div>
        </div>
        <div class="stat card stat-teachers">
          <div class="stat-value" id="count-teachers">—</div>
          <div class="stat-label">Professores</div>
        </div>
        <div class="stat card stat-bookings">
          <div class="stat-value" id="count-bookings">—</div>
          <div class="stat-label">Agendamentos</div>
        </div>
        <div class="stat card stat-upcoming">
          <div class="stat-value" id="count-upcoming">—</div>
          <div class="stat-label">Agendamentos futuros</div>
        </div>
      </section>

      <section class="recent-section">
        <div class="section-header">
          <h2>Agendamentos recentes</h2>
          <small id="recent-note" class="muted">Carregando...</small>
        </div>
        <div class="table-wrap">
          <table class="recent-table">
            <thead>
              <tr><th>Data / Hora</th><th>Usuário</th><th>Objetivo</th><th>Modalidade</th><th>Status</th></tr>
            </thead>
            <tbody id="recent-table-body">
              <tr><td colspan="5" class="muted">Nenhum registro encontrado</td></tr>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>

  <div id="modal" class="modal">
    <div class="modal-content">
      <span class="close" id="close">&times;</span>
      <h2 id="modal-titulo"></h2>
      <p id="modal-texto"></p>
    </div>
  </div>

<footer class="fade-in-up">
  <p>© 2025 TechFit — Todos os direitos reservados</p>
</footer>

  <script src="painel.js"></script>
</body>
</html>