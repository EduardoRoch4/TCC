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

$agendamentoController = new AgendamentoController();
$usuarioController = new UsuarioController();

// Buscar todos os agendamentos
$agendamentos_objetos = $agendamentoController->listarTodos();
$agendamentos = [];
foreach ($agendamentos_objetos as $ag) {
    $usuario = $usuarioController->buscarPorId($ag->getIdUsuario());
    $agendamentos[] = [
        'id_agendamento' => $ag->getIdAgendamento(),
        'id_usuario' => $ag->getIdUsuario(),
        'data_hora' => $ag->getDataHora(),
        'objetivo' => $ag->getObjetivo(),
        'modalidade' => $ag->getModalidade(),
        'status_' => $ag->getStatus(),
        'usuario_nome' => $usuario ? $usuario->getNome() : 'N/A'
    ];
}

// Buscar usuários para seleção (apenas alunos, sem duplicatas)
$usuarios_objetos = $usuarioController->listarTodos();
$usuarios = [];
$usuarios_ids_adicionados = [];
foreach ($usuarios_objetos as $u) {
    // Filtrar apenas alunos e evitar duplicatas
    if ($u->getPerfil() === 'aluno' && !in_array($u->getIdUsuario(), $usuarios_ids_adicionados)) {
        $usuarios[] = [
            'id_usuario' => $u->getIdUsuario(),
            'nome' => $u->getNome()
        ];
        $usuarios_ids_adicionados[] = $u->getIdUsuario();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerenciamento de Agendamentos | TechFit Admin</title>
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
        <a href="professores.php">Professores</a>
        <a href="aulas.php">Aulas</a>
        <a class="active" href="agendamentos.php">Agendamentos</a>
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
          <h1>Gerenciamento de Agendamentos</h1>
          <p class="muted">Visualize e edite os agendamentos cadastrados</p>
        </div>
        <div class="dash-actions">
          <input id="search-input" placeholder="Pesquisar agendamento..." />
          <button class="btn small" id="novo-agendamento">+ Novo Agendamento</button>
        </div>
      </header>

      <section class="recent-section">
        <div class="section-header">
          <h2>Agendamentos Cadastrados</h2>
          <small id="count-agendamentos" class="muted"><?php echo count($agendamentos); ?> agendamentos</small>
        </div>
        <div class="table-wrap">
          <table class="recent-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Data/Hora</th>
                <th>Usuário</th>
                <th>Objetivo</th>
                <th>Modalidade</th>
                <th>Status</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody id="agendamentos-table-body">
              <?php if (empty($agendamentos)): ?>
                <tr><td colspan="7" class="muted">Nenhum agendamento encontrado</td></tr>
              <?php else: ?>
                <?php foreach ($agendamentos as $ag): 
                  // Converter data para formato datetime-local (YYYY-MM-DDTHH:mm)
                  $datetime_obj = new DateTime($ag['data_hora']);
                  $data_datetime_local = $datetime_obj->format('Y-m-d\TH:i');
                ?>
                  <tr data-id="<?php echo htmlspecialchars($ag['id_agendamento']); ?>" 
                      data-usuario-id="<?php echo htmlspecialchars($ag['id_usuario'] ?? ''); ?>"
                      data-data-hora="<?php echo htmlspecialchars($data_datetime_local); ?>"
                      data-objetivo="<?php echo htmlspecialchars($ag['objetivo'] ?? ''); ?>"
                      data-modalidade="<?php echo htmlspecialchars($ag['modalidade'] ?? ''); ?>"
                      data-status="<?php echo htmlspecialchars($ag['status_'] ?? ''); ?>">
                    <td>#<?php echo htmlspecialchars($ag['id_agendamento']); ?></td>
                    <td><?php echo (new DateTime($ag['data_hora']))->format('d/m/Y H:i'); ?></td>
                    <td><?php echo htmlspecialchars($ag['usuario_nome'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($ag['objetivo'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($ag['modalidade'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($ag['status_'] ?? '—'); ?></td>
                    <td>
                      <button class="btn-action editar" data-id="<?php echo htmlspecialchars($ag['id_agendamento']); ?>">Editar</button>
                      <button class="btn-action deletar" data-id="<?php echo htmlspecialchars($ag['id_agendamento']); ?>">Deletar</button>
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
      <h2 id="modal-titulo">Editar Agendamento</h2>
      <form id="form-agendamento">
        <input type="hidden" id="agendamento-id">
        <select id="agendamento-usuario" required>
          <option value="">Selecione um usuário</option>
          <?php foreach ($usuarios as $user): ?>
            <option value="<?php echo htmlspecialchars($user['id_usuario']); ?>">
              <?php echo htmlspecialchars($user['nome']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="datetime-local" id="agendamento-data" required>
        <select id="agendamento-objetivo" required>
          <option value="">Selecione um objetivo</option>
          <option value="Perda de peso">Perda de peso</option>
          <option value="Ganho de Massa">Ganho de Massa</option>
          <option value="Hipertrofia">Hipertrofia</option>
          <option value="Saúde">Saúde</option>
        </select>
        <input type="text" id="agendamento-modalidade" placeholder="Modalidade">
        <select id="agendamento-status" required>
          <option value="">Selecione um status</option>
          <option value="Confirmado">Confirmado</option>
        </select>
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
