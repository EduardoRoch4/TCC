<?php
session_start(); // Inicia a sessão PHP

// 1. AÇÃO DE LOGOUT
// Verifica se o usuário clicou no link de logout
if (isset($_GET['acao']) && $_GET['acao'] === 'logout') {
    session_unset();     // Limpa todas as variáveis da sessão
    session_destroy();   // Destrói a sessão
    
    // Redireciona para o login (caminho baseado no seu usuario.js)
    header("Location: /Login/login.php");
    exit;
}

// 2. VERIFICAÇÃO DE LOGIN
// Se não houver uma sessão ativa, manda o usuário para a página de login
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['usuario'])) {
  header("Location: /Login/login.php");
    exit;
}

// 3. Carregar autoloader e classes necessárias
require_once __DIR__ . '/../app/config/autoload.php';

// 4. Inicializar controllers
$usuarioController = new UsuarioController();
$pagamentoController = new PagamentoController();
$agendamentoController = new AgendamentoController();

// 5. BUSCAR DADOS DO USUÁRIO PARA A PÁGINA
$id_usuario = $_SESSION['id_usuario'];
$nome_usuario = $_SESSION['usuario']; // Nome já veio da sessão

// Busca o usuário completo
$usuario = $usuarioController->buscarPorId($id_usuario);
$dados_usuario = ['email' => $usuario ? $usuario->getEmail() : 'Email não cadastrado'];
$unidade_usuario = $usuario ? ($usuario->getUnidade() ?: 'Não definida') : 'Não definida';

// Busca o último pagamento
$pagamento = $pagamentoController->buscarUltimoPagamento($id_usuario);
$dados_pagamento = ['plano' => 'Nenhum', 'data_pagamento' => null];
$proximo_pag = 'N/A';

if ($pagamento) {
    $dados_pagamento = [
        'plano' => $pagamento->getPlano(),
        'data_pagamento' => $pagamento->getDataPagamento()
    ];
    
    // Calcula a data do próximo pagamento (data do último + 1 mês)
    if ($pagamento->getDataPagamento()) {
        try {
            $data_pag = new DateTime($pagamento->getDataPagamento());
            $data_pag->modify('+1 month');
            $proximo_pag = $data_pag->format('d/m/Y');
        } catch (Exception $e) {
            $proximo_pag = 'Erro ao calcular';
        }
    }
}

// Busca agendamentos do usuário
$agendamentos_objetos = $agendamentoController->listarPorUsuario($id_usuario);
$agendamentos = [];
foreach ($agendamentos_objetos as $ag) {
    $agendamentos[] = [
        'id_agendamento' => $ag->getIdAgendamento(),
        'data_hora' => $ag->getDataHora(),
        'objetivo' => $ag->getObjetivo(),
        'modalidade' => $ag->getModalidade(),
        'status_' => $ag->getStatus()
    ];
}

// Formata o ID de usuário como matrícula
$matricula = '#' . str_pad($id_usuario, 6, '0', STR_PAD_LEFT);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meu Perfil | TechFit</title>
  <link rel="stylesheet" href="./usuario.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <header>
    <div class="logo">
      <a href="../index.html">
        <img src="../IMG/Logo.png" alt="Logo TechFit">
      </a>
    </div>

    <nav class="nav-buttons">
      <a href="../index.html">Início</a>
      <a href="../Agendamento/agendamento.html">Agendamento</a>
      <a href="../Unidades/Unidades.html">Unidades</a>
      <a href="../Nossa História/nos.html">Sobre Nós</a>
      
      <a href="../Login/login.php?acao=logout" id="logout">Logout</a>
      <span id="user-display" style="display:inline-block;margin-left:12px;color:#111">Olá, <strong id="user-name"><?php echo htmlspecialchars($_SESSION['usuario'] ?? ''); ?></strong></span>
    </nav>
  </header>

  <main class="profile-container fade-in-up">
    <div class="profile-card">
      <img src="../IMG/avatar-placeholder.png" alt="Foto do Usuário" class="profile-pic">
      
      <h2 id="nome-usuario"><?php echo htmlspecialchars($nome_usuario); ?></h2>
      <?php if ($pagamento && $pagamento->getStatus() === 'Cancelado'): ?>
        <h3 class="plano-cancelado">Plano <?php echo htmlspecialchars(strtoupper($dados_pagamento['plano'])); ?> - Cancelado</h3>
      <?php else: ?>
        <h3 class="plano-ativo">Plano <?php echo htmlspecialchars(strtoupper($dados_pagamento['plano'])); ?></h3>
      <?php endif; ?>

      <div class="profile-info">
        <div class="info-row"><strong>Matrícula:</strong> <span><?php echo $matricula; ?></span></div>
        <div class="info-row"><strong>Email:</strong> <span id="email"><?php echo htmlspecialchars($dados_usuario['email']); ?></span></div>
        
        <div class="info-row"><strong>Telefone:</strong> <span>(11) 99999-9999</span></div>
        <div class="info-row"><strong>Unidade:</strong> <span><?php echo htmlspecialchars($unidade_usuario); ?></span></div>
        
        <div class="info-row"><strong>Próximo Pagamento:</strong> <span><?php echo $proximo_pag; ?></span></div>
      </div>
      <div class="profile-actions">
        <button onclick="abrirModalEditar()" class="btn">Editar Perfil</button>
        <?php 
          $status_pagamento = $pagamento ? $pagamento->getStatus() : null;
          if ($pagamento && $status_pagamento !== 'Cancelado' && $dados_pagamento['plano'] !== 'Nenhum'): 
        ?>
          <button onclick="cancelarPlano()" class="btn btn-cancelar">Cancelar Plano</button>
        <?php endif; ?>
        <a href="../Agendamento/Agendamento.html" class="btn">Agendamentos</a>
        <a href="../index.html" class="btn voltar">Voltar ao Início</a>
      </div>
    </div>
  </main>

  <!-- Seção: agendamentos do usuário -->
  <section class="user-agendamentos fade-in-up">
    <div class="container">
      <h2>Meus Agendamentos</h2>
      <?php if (empty($agendamentos)): ?>
        <p>Você não possui agendamentos cadastrados.</p>
      <?php else: ?>
        <ul class="agendamentos-list">
          <?php foreach ($agendamentos as $ag):
            // Formata a data/hora para exibição
            $dt = new DateTime($ag['data_hora']);
            $str_dt = $dt->format('d/m/Y H:i');
            $objetivo_ex = htmlspecialchars($ag['objetivo']);
            $modalidade_ex = isset($ag['modalidade']) && $ag['modalidade'] !== null ? htmlspecialchars($ag['modalidade']) : '';
            $status_ex = htmlspecialchars($ag['status_']);
          ?>
            <li class="agendamento-item">
              <div class="ag-info">
                <div class="ag-date"><?php echo $str_dt; ?></div>
                <div class="ag-objetivo">Objetivo: <?php echo $objetivo_ex; ?></div>
                <?php if ($modalidade_ex): ?>
                  <div class="ag-modalidade">Modalidade: <?php echo $modalidade_ex; ?></div>
                <?php endif; ?>
                <div class="ag-status">Status: <?php echo $status_ex; ?></div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </section>

  <!-- Modal Editar Perfil -->
  <div id="modalEditar" class="modal">
    <div class="modal-content">
      <span class="close" onclick="fecharModalEditar()">&times;</span>
      <h2>Editar Perfil</h2>
      <form id="formEditarPerfil" onsubmit="atualizarPerfil(event)">
        <div class="form-group">
          <label for="editNome">Nome:</label>
          <input type="text" id="editNome" name="nome" value="<?php echo htmlspecialchars($nome_usuario); ?>" required>
        </div>
        <div class="form-group">
          <label for="editEmail">Email:</label>
          <input type="email" id="editEmail" name="email" value="<?php echo htmlspecialchars($dados_usuario['email']); ?>" required>
        </div>
        <div class="form-group">
          <label for="editSenhaAtual">Senha Atual (para alterar senha):</label>
          <input type="password" id="editSenhaAtual" name="senha_atual" placeholder="Deixe em branco se não quiser alterar">
        </div>
        <div class="form-group">
          <label for="editNovaSenha">Nova Senha:</label>
          <input type="password" id="editNovaSenha" name="nova_senha" placeholder="Mínimo 6 caracteres">
        </div>
        <div class="form-actions">
          <button type="submit" class="btn">Salvar Alterações</button>
          <button type="button" class="btn voltar" onclick="fecharModalEditar()">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Container de Notificações -->
  <div id="notification-container" class="notification-container"></div>

  <footer>
    <p>© 2025 TechFit — Todos os direitos reservados</p>
  </footer>

  <script>
    // Função para mostrar notificações
    function showNotification(message, type = 'success') {
      const container = document.getElementById('notification-container');
      const notification = document.createElement('div');
      notification.className = `notification notification-${type}`;
      
      const icon = type === 'success' ? '✓' : '✗';
      notification.innerHTML = `
        <span class="notification-icon">${icon}</span>
        <span class="notification-content">${message}</span>
        <span class="notification-close" onclick="this.parentElement.remove()">&times;</span>
      `;
      
      container.appendChild(notification);
      
      // Remover automaticamente após 5 segundos
      setTimeout(() => {
        if (notification.parentElement) {
          notification.remove();
        }
      }, 5000);
    }

    // Modal Editar Perfil
    function abrirModalEditar() {
      document.getElementById('modalEditar').style.display = 'block';
    }

    function fecharModalEditar() {
      document.getElementById('modalEditar').style.display = 'none';
      // Limpar campos de senha
      document.getElementById('editSenhaAtual').value = '';
      document.getElementById('editNovaSenha').value = '';
    }

    // Fechar modal ao clicar fora
    window.onclick = function(event) {
      const modal = document.getElementById('modalEditar');
      if (event.target === modal) {
        fecharModalEditar();
      }
    }

    // Atualizar Perfil
    function atualizarPerfil(event) {
      event.preventDefault();
      
      const nome = document.getElementById('editNome').value.trim();
      const email = document.getElementById('editEmail').value.trim();
      const senhaAtual = document.getElementById('editSenhaAtual').value;
      const novaSenha = document.getElementById('editNovaSenha').value;
      
      if (!nome || !email) {
        showNotification('Preencha todos os campos obrigatórios!', 'error');
        return;
      }
      
      if (novaSenha && novaSenha.length < 6) {
        showNotification('A nova senha deve ter pelo menos 6 caracteres!', 'error');
        return;
      }
      
      if (novaSenha && !senhaAtual) {
        showNotification('É necessário informar a senha atual para alterar a senha!', 'error');
        return;
      }
      
      fetch('api_usuario.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          action: 'atualizar_perfil',
          nome: nome,
          email: email,
          senha_atual: senhaAtual || null,
          nova_senha: novaSenha || null
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'ok') {
          showNotification(data.message, 'success');
          fecharModalEditar();
          // Atualizar dados na página
          document.getElementById('nome-usuario').textContent = nome;
          document.getElementById('email').textContent = email;
          // Recarregar página após 1 segundo para atualizar sessão
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          showNotification(data.message, 'error');
        }
      })
      .catch(error => {
        showNotification('Erro ao atualizar perfil. Tente novamente.', 'error');
        console.error('Erro:', error);
      });
    }

    // Cancelar Plano
    function cancelarPlano() {
      if (!confirm('Tem certeza que deseja cancelar seu plano? Esta ação não pode ser desfeita.')) {
        return;
      }
      
      fetch('api_pagamento.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          action: 'cancelar_plano'
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'ok') {
          showNotification(data.message, 'success');
          // Recarregar página após 1 segundo
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          showNotification(data.message, 'error');
        }
      })
      .catch(error => {
        showNotification('Erro ao cancelar plano. Tente novamente.', 'error');
        console.error('Erro:', error);
      });
    }
  </script>

  </body>
</html>