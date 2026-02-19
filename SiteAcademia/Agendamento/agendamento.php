<?php
session_start(); // Inicia a sessão

// 1. VERIFICAÇÃO DE LOGIN
// Se o usuário não estiver logado, redireciona para o login
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['usuario'])) {
  // Ajuste o caminho se necessário, baseado na estrutura de pastas
  header("Location: /Login/login.php");
    exit;
}

// O ID do usuário logado que será usado no agendamento
$id_usuario_logado = $_SESSION['id_usuario'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agendamento | TechFit</title>
  <link rel="stylesheet" href="agendamento.css">
</head>
<body>

  <header>
    <div class="logo">
      <a href="/index.html">
        <img src="/IMG/Logo.png" alt="Logo TechFit">
      </a>
    </div>

    <nav class="nav-buttons" id="nav-buttons">
      <a href="/index.html">Início</a>
      <a href="/Agendamento/agendamento.php">Agendamento</a>
      <a href="/Unidades/Unidades.html">Unidades</a>
      <a href="/Chat/chat.html">Chat</a>
      <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
        <a href="/Admin/painel.php">Painel Admin</a>
      <?php endif; ?>
      <a href="/Nossa História/nos.html">Sobre Nós</a>
      
      <a href="/Alunos/usuario.php" id="perfil-btn">Perfil</a>
      <a href="/Login/login.php?acao=logout" id="logout-btn">Logout</a>
      <span id="user-display" style="margin-left:12px;color:#fff;">Olá, <strong id="user-name"><?php echo htmlspecialchars($_SESSION['usuario'] ?? ''); ?></strong></span>
    </nav>

    <div class="menu-icon" id="menu-icon">☰</div>
  </header>

  <div class="side-menu" id="side-menu">
    <div class="close-btn" id="close-btn">✖</div>
    <a href="/index.html">Início</a>
    <a href="/Agendamento/agendamento.php">Agendamento</a>
    <a href="/Unidades/Unidades.html">Unidades</a>
    <a href="/Chat/chat.html">Chat</a>
    <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
      <a href="/Admin/painel.php">Painel Admin</a>
    <?php endif; ?>
    <a href="/Nossa História/nos.html">Sobre Nós</a>
    
    <a href="/Alunos/usuario.php" id="perfil-side">Perfil</a>
    <a href="/Login/login.php?acao=logout" id="logout-side">Logout</a>
    <span id="user-display-side" style="margin-left:8px;color:#111">Olá, <strong id="user-name-side"><?php echo htmlspecialchars($_SESSION['usuario'] ?? ''); ?></strong></span>
  </div>

  <div class="overlay" id="overlay"></div>

  <div class="calendar">
    <div class="calendar-header">
      <button class="prev-month">&lt;</button>
      <h2></h2>
      <button class="next-month">&gt;</button>
    </div>
    <div class="calendar-days"></div>
  </div>

  <div class="selection-container">
    <div>
      <label for="time-select">Selecione o horário:</label>
      <select id="time-select">
        <option value="">Escolha</option>
        <option value="06:00">06:00</option>
        <option value="08:00">08:00</option>
        <option value="09:00">09:00</option>
        <option value="11:00">11:00</option>
        <option value="12:30">12:30</option>
        <option value="13:30">13:30</option>
        <option value="14:30">14:30</option>
        <option value="15:30">15:30</option>
        <option value="16:30">16:30</option>
        <option value="17:30">17:30</option>
      </select>
    </div>

    <div>
      <label for="goal-select">Selecione o objetivo:</label>
      <select id="goal-select">
        <option value="">Escolha</option>
        <option value="Perda de peso">Perda de peso</option>
        <option value="Ganho de Massa">Ganho de massa</option>
        <option value="Hipertrofia">Hipertrofia</option>
        <option value="Saúde">Saúde</option>
      </select>
    </div>

    <div>
      <label for="modalidade-select">Selecione a modalidade:</label>
      <select id="modalidade-select" name="modalidade">
        <option value="">Escolha</option>
        <option value="Musculação">Musculação</option>
        <option value="Spinning">Spinning</option>
        <option value="Yoga">Yoga</option>
        <option value="Funcional">Funcional</option>
        <option value="Pilates">Pilates</option>
      </select>
    </div>

    <button id="agendar-btn">Agendar</button>
  </div>

  <script src="agendamento.js"></script>
</body>
</html>