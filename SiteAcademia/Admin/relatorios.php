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

$usuarioDAO = new UsuarioDAO();
$professorDAO = new ProfessorDAO();
$aulaDAO = new AulaDAO();
$agendamentoDAO = new AgendamentoDAO();

// Estatísticas gerais
$stats = [
    'total_usuarios' => $usuarioDAO->contarTotal(),
    'total_professores' => $professorDAO->contarTotal(),
    'total_aulas' => $aulaDAO->contarTotal(),
    'total_agendamentos' => $agendamentoDAO->contarTotal(),
    'agendamentos_confirmados' => $agendamentoDAO->contarPorStatus('Confirmado'),
    'agendamentos_pendentes' => $agendamentoDAO->contarPorStatus('Pendente'),
    'agendamentos_futuros' => $agendamentoDAO->contarFuturos()
];

// Agendamentos por modalidade
$agendamentos_modalidade = $agendamentoDAO->listarPorModalidade();

// Top 5 horários mais agendados
$top_horarios = $agendamentoDAO->listarTopHorarios(5);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Relatórios | TechFit Admin</title>
  <link rel="stylesheet" href="painel.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <header>
    <div class="logo">
      <img src="../IMG/Logo.png" alt="Logo TechFit">
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
        <a href="professores.php">Professores</a>
        <a href="aulas.php">Aulas</a>
        <a href="agendamentos.php">Agendamentos</a>
        <a class="active" href="relatorios.php">Relatórios</a>
      </nav>

      <div class="sidebar-footer">
        <small>Usuário: <strong><?php echo $adminName; ?></strong></small>
        <a href="/Login/login.php?acao=logout" class="btn-logout">Sair</a>
      </div>
    </aside>

    <main class="dashboard">
      <header class="dashboard-header">
        <div class="dash-title">
          <h1>Relatórios e Estatísticas</h1>
          <p class="muted">Visualize dados e métricas do sistema</p>
        </div>
      </header>

      <!-- Estatísticas gerais -->
      <section class="stats-grid">
        <div class="stat card stat-users">
          <div class="stat-value"><?php echo $stats['total_usuarios']; ?></div>
          <div class="stat-label">Alunos Cadastrados</div>
        </div>
        <div class="stat card stat-teachers">
          <div class="stat-value"><?php echo $stats['total_professores']; ?></div>
          <div class="stat-label">Professores</div>
        </div>
        <div class="stat card stat-bookings">
          <div class="stat-value"><?php echo $stats['total_aulas']; ?></div>
          <div class="stat-label">Aulas</div>
        </div>
        <div class="stat card stat-upcoming">
          <div class="stat-value"><?php echo $stats['total_agendamentos']; ?></div>
          <div class="stat-label">Agendamentos Totais</div>
        </div>
      </section>

      <!-- Status dos agendamentos -->
      <section class="stats-grid" style="margin-bottom: 22px;">
        <div class="stat card" style="background:linear-gradient(180deg,#4cd964,#00b45a);">
          <div class="stat-value"><?php echo $stats['agendamentos_confirmados']; ?></div>
          <div class="stat-label">Confirmados</div>
        </div>
        <div class="stat card" style="background:linear-gradient(180deg,#ff9f43,#ff7a00);">
          <div class="stat-value"><?php echo $stats['agendamentos_pendentes']; ?></div>
          <div class="stat-label">Pendentes</div>
        </div>
        <div class="stat card" style="background:linear-gradient(180deg,#9b59b6,#7a0f9f);">
          <div class="stat-value"><?php echo $stats['agendamentos_futuros']; ?></div>
          <div class="stat-label">Futuros</div>
        </div>
      </section>

      <!-- Gráficos -->
      <section class="charts-section">
        <div class="chart-card">
          <h3>Agendamentos por Modalidade</h3>
          <canvas id="modalidadeChart"></canvas>
        </div>
        <div class="chart-card">
          <h3>Horários Mais Agendados</h3>
          <div style="width: 100%; height: 300px; position: relative; display: flex; align-items: center; justify-content: center;">
            <canvas id="horariosChart"></canvas>
          </div>
        </div>
      </section>

      <!-- Tabelas de detalhes -->
      <section class="recent-section" style="margin-top: 22px;">
        <div class="section-header">
          <h2>Agendamentos por Modalidade</h2>
        </div>
        <div class="table-wrap">
          <table class="recent-table">
            <thead>
              <tr>
                <th>Modalidade</th>
                <th>Quantidade</th>
                <th>Percentual</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $total_mod = array_sum(array_column($agendamentos_modalidade, 'count'));
              foreach ($agendamentos_modalidade as $mod): 
                $percentual = $total_mod > 0 ? round(($mod['count'] / $total_mod) * 100, 2) : 0;
              ?>
                <tr>
                  <td><?php echo htmlspecialchars($mod['modalidade']); ?></td>
                  <td><?php echo $mod['count']; ?></td>
                  <td><?php echo $percentual; ?>%</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>

<footer class="fade-in-up">
  <p>© 2025 TechFit — Todos os direitos reservados</p>
</footer>

  <script>
    // Gráfico de Modalidades
    <?php 
    $modalidades = [];
    $counts_mod = [];
    foreach ($agendamentos_modalidade as $mod) {
        $modalidades[] = $mod['modalidade'];
        $counts_mod[] = $mod['count'];
    }
    ?>
    const ctxMod = document.getElementById('modalidadeChart').getContext('2d');
    new Chart(ctxMod, {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($modalidades); ?>,
        datasets: [{
          data: <?php echo json_encode($counts_mod); ?>,
          backgroundColor: ['#6b8cff', '#ff9f43', '#4cd964', '#ff3b3b', '#9b59b6', '#00b45a'],
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: { responsive: true, maintainAspectRatio: true }
    });

    // Gráfico de Horários
    <?php 
    $horas = [];
    $counts_hor = [];
    foreach ($top_horarios as $hor) {
        $horas[] = $hor['hora'];
        $counts_hor[] = $hor['count'];
    }
    ?>
    const ctxHor = document.getElementById('horariosChart');
    if (ctxHor) {
      new Chart(ctxHor.getContext('2d'), {
        type: 'bar',
        data: {
          labels: <?php echo json_encode($horas); ?>,
          datasets: [{
            label: 'Quantidade de Agendamentos',
            data: <?php echo json_encode($counts_hor); ?>,
            backgroundColor: '#6b8cff',
            borderRadius: 8
          }]
        },
        options: { 
          responsive: true, 
          maintainAspectRatio: false,
          layout: {
            padding: {
              left: 20,
              right: 20,
              top: 20,
              bottom: 20
            }
          },
          plugins: {
            legend: {
              display: false
            }
          },
          scales: { 
            y: { 
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            },
            x: {
              ticks: {
                maxRotation: 0,
                minRotation: 0
              }
            }
          }
        }
      });
    }
  </script>

  <script src="painel.js"></script>
</body>
</html>
