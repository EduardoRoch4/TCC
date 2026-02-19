// ===================== SISTEMA DE NOTIFICAÇÕES =====================
function showNotification(message, type = 'info', duration = 5000) {
  // Criar container se não existir
  let container = document.getElementById('notification-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'notification-container';
    container.className = 'notification-container';
    document.body.appendChild(container);
  }

  // Criar notificação
  const notification = document.createElement('div');
  notification.className = `notification ${type}`;
  
  // Ícones por tipo
  const icons = {
    success: '✅',
    error: '❌',
    warning: '⚠️',
    info: 'ℹ️'
  };
  
  notification.innerHTML = `
    <span class="notification-icon">${icons[type] || icons.info}</span>
    <span class="notification-content">${message}</span>
    <button class="notification-close" onclick="this.parentElement.remove()">×</button>
  `;
  
  container.appendChild(notification);
  
  // Remover automaticamente após a duração
  if (duration > 0) {
    setTimeout(() => {
      notification.classList.add('hiding');
      setTimeout(() => {
        if (notification.parentElement) {
          notification.remove();
        }
      }, 300);
    }, duration);
  }
  
  return notification;
}

// 🧩 Modal de conteúdo
const modal = document.getElementById('modal');
const close = document.getElementById('close');
const titulo = document.getElementById('modal-titulo');

function abrirModal() {
  if (modal) modal.style.display = 'flex';
}

if (close) {
  close.addEventListener('click', () => {
    if (modal) modal.style.display = 'none';
  });
}

window.addEventListener('click', (e) => {
  if (e.target === modal) {
    if (modal) modal.style.display = 'none';
  }
});

// 🟢 Menu lateral e overlay
const menuIcon = document.getElementById('menu-icon');
const sideMenu = document.getElementById('side-menu');
const closeBtn = document.getElementById('close-btn');
const overlay = document.getElementById('overlay');

if (menuIcon) {
  menuIcon.addEventListener('click', () => {
    if (sideMenu) sideMenu.classList.add('active');
    if (overlay) overlay.classList.add('show');
  });
}

if (closeBtn) {
  closeBtn.addEventListener('click', () => {
    if (sideMenu) sideMenu.classList.remove('active');
    if (overlay) overlay.classList.remove('show');
  });
}

if (overlay) {
  overlay.addEventListener('click', () => {
    if (sideMenu) sideMenu.classList.remove('active');
    if (overlay) overlay.classList.remove('show');
  });
}

// 🟢 Controle de login/logout
const loginBtn = document.getElementById('login-btn');
const perfilBtn = document.getElementById('perfil-btn');

async function atualizarInterface() {
  try {
    const r = await fetch('../Login/session_status.php');
    const s = await r.json();
    const isLogged = !!s.logged;
    const perfil = s.perfil || null;

    if (isLogged) {
      if (loginBtn) {
        loginBtn.textContent = 'Logout';
        loginBtn.href = '/Login/login.php?acao=logout';
      }
      if (perfilBtn) {
        perfilBtn.style.display = 'inline-block';
      }
      const userNameEl = document.getElementById('user-name');
      const userDisplay = document.getElementById('user-display');
      if (userNameEl) userNameEl.textContent = s.usuario || '';
      if (userDisplay) userDisplay.style.display = isLogged ? '' : 'none';
    } else {
      if (loginBtn) {
        loginBtn.textContent = 'Login';
        loginBtn.href = '/Login/login.php';
      }
      if (perfilBtn) {
        perfilBtn.style.display = 'none';
      }
    }

    // hide non-admin features for non-admins
    const adminLinks = document.querySelectorAll('a[href*="/Admin/painel.php"], a[href*="Admin/admin.html"], a[href*="/Admin/"]');
    adminLinks.forEach(a => a.style.display = (perfil === 'admin') ? '' : 'none');

  } catch (err) {
    console.warn('session check error', err);
  }
}

atualizarInterface();

const fadeElements = document.querySelectorAll('.fade-in-up');
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) entry.target.classList.add('visible');
  });
}, { threshold: 0.2 });
fadeElements.forEach(el => observer.observe(el));

// ===== FUNCIONALIDADES POR PÁGINA =====

// 🎓 PÁGINA DE ALUNOS
const formAluno = document.getElementById('form-aluno');
const novoAlunoBtn = document.getElementById('novo-aluno');
const searchInput = document.getElementById('search-input');

if (novoAlunoBtn) {
  novoAlunoBtn.addEventListener('click', () => {
    document.getElementById('aluno-id').value = '';
    document.getElementById('aluno-nome').value = '';
    document.getElementById('aluno-email').value = '';
    abrirModal();
  });
}

if (formAluno) {
  formAluno.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('aluno-id').value;
    const nome = document.getElementById('aluno-nome').value;
    const email = document.getElementById('aluno-email').value;

    const dados = { nome, email };
    if (id) dados.id = id;

    try {
      const res = await fetch('api_alunos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
      });
      const result = await res.json();
      if (result.status === 'ok') {
        showNotification('Aluno salvo com sucesso!', 'success');
        setTimeout(() => location.reload(), 1000);
      } else {
        showNotification('Erro: ' + (result.message || 'Erro desconhecido'), 'error');
      }
    } catch (err) {
      console.error('Erro:', err);
      showNotification('Erro ao salvar aluno', 'error');
    }
  });
}

// Editar aluno
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('editar') && window.location.pathname.includes('alunos.php')) {
    const id = e.target.getAttribute('data-id');
    const tr = e.target.closest('tr');
    const nome = tr.cells[1].textContent;
    const email = tr.cells[2].textContent;

    document.getElementById('aluno-id').value = id;
    document.getElementById('aluno-nome').value = nome;
    document.getElementById('aluno-email').value = email;
    abrirModal();
  }

  // Deletar aluno
  if (e.target.classList.contains('deletar') && window.location.pathname.includes('alunos.php')) {
    const id = e.target.getAttribute('data-id');
    if (confirm('Tem certeza que deseja deletar este aluno?')) {
      fetch('api_alunos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, action: 'delete' })
      })
      .then(async res => {
        // Verificar status HTTP
        if (!res.ok) {
          console.error('Status HTTP:', res.status, res.statusText);
          const text = await res.text();
          console.error('Resposta do servidor:', text);
          throw new Error('Erro HTTP ' + res.status + ': ' + res.statusText);
        }
        
        // Verificar Content-Type
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          console.warn('Content-Type inesperado:', contentType);
        }
        
        const responseText = await res.text();
        console.log('Resposta da API (raw):', responseText);
        
        // Verificar se a resposta é JSON válido
        try {
          return JSON.parse(responseText);
        } catch (parseError) {
          console.error('Erro ao fazer parse do JSON:', parseError);
          console.error('Resposta completa recebida:', responseText);
          throw new Error('Resposta inválida do servidor');
        }
      })
      .then(result => {
        if (result.status === 'ok') {
          showNotification('Aluno deletado com sucesso!', 'success');
          setTimeout(() => location.reload(), 1000);
        } else {
          showNotification('Erro ao deletar: ' + (result.message || 'Erro desconhecido'), 'error');
          console.error('Erro na API:', result);
        }
      })
      .catch(err => {
        console.error('Erro ao conectar com API:', err);
        showNotification('Erro ao deletar aluno: ' + err.message, 'error');
      });
    }
  }
});

// 👨‍🏫 PÁGINA DE PROFESSORES
const formProfessor = document.getElementById('form-professor');
const novoProfessorBtn = document.getElementById('novo-professor');

if (novoProfessorBtn) {
  novoProfessorBtn.addEventListener('click', () => {
    document.getElementById('professor-id').value = '';
    document.getElementById('professor-nome').value = '';
    document.getElementById('professor-email').value = '';
    document.getElementById('professor-especialidade').value = '';
    abrirModal();
  });
}

if (formProfessor) {
  formProfessor.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('professor-id').value;
    const nome = document.getElementById('professor-nome').value;
    const email = document.getElementById('professor-email').value;
    const especialidade = document.getElementById('professor-especialidade').value;

    const dados = { nome, email, especialidade };
    if (id) dados.id = id;

    try {
      const res = await fetch('api_professores.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
      });
      
      // Verificar status HTTP
      if (!res.ok) {
        console.error('Status HTTP:', res.status, res.statusText);
        const text = await res.text();
        console.error('Resposta do servidor:', text);
        showNotification('Erro HTTP ' + res.status + ': ' + res.statusText, 'error');
        return;
      }
      
      // Verificar Content-Type
      const contentType = res.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        console.warn('Content-Type inesperado:', contentType);
      }
      
      const responseText = await res.text();
      console.log('Resposta da API (raw):', responseText);
      
      // Verificar se a resposta é JSON válido
      let result;
      try {
        result = JSON.parse(responseText);
      } catch (parseError) {
        console.error('Erro ao fazer parse do JSON:', parseError);
        console.error('Resposta completa recebida:', responseText);
        if (responseText.trim().startsWith('<')) {
          showNotification('Erro: O servidor retornou HTML em vez de JSON. Isso geralmente indica um erro PHP. Verifique o console para mais detalhes.', 'error');
        } else {
          showNotification('Erro: O servidor retornou uma resposta inválida. Verifique o console para mais detalhes.', 'error');
        }
        return;
      }
      
      if (result.status === 'ok') {
        showNotification('Professor salvo com sucesso!', 'success');
        setTimeout(() => location.reload(), 1000);
      } else {
        showNotification('Erro: ' + (result.message || 'Erro desconhecido'), 'error');
        console.error('Erro completo:', result);
      }
    } catch (err) {
      console.error('Erro:', err);
      showNotification('Erro ao salvar professor: ' + err.message, 'error');
    }
  });
}

// Editar/Deletar professor
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('editar') && window.location.pathname.includes('professores.php')) {
    const id = e.target.getAttribute('data-id');
    const tr = e.target.closest('tr');
    const nome = tr.cells[1].textContent;
    const especialidade = tr.cells[2].textContent;

    document.getElementById('professor-id').value = id;
    document.getElementById('professor-nome').value = nome;
    document.getElementById('professor-email').value = '';
    document.getElementById('professor-especialidade').value = especialidade === '—' ? '' : especialidade;
    abrirModal();
  }

  if (e.target.classList.contains('deletar') && window.location.pathname.includes('professores.php')) {
    const id = e.target.getAttribute('data-id');
    if (confirm('Tem certeza que deseja deletar este professor?')) {
      fetch('api_professores.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, action: 'delete' })
      })
      .then(res => res.json())
      .then(result => {
        if (result.status === 'ok') {
          showNotification('Professor deletado!', 'success');
          setTimeout(() => location.reload(), 1000);
        } else {
          showNotification('Erro ao deletar: ' + result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Erro:', err);
        showNotification('Erro ao deletar professor', 'error');
      });
    }
  }
});

// 📚 PÁGINA DE AULAS
const formAula = document.getElementById('form-aula');
const novaAulaBtn = document.getElementById('nova-aula');

if (novaAulaBtn) {
  novaAulaBtn.addEventListener('click', () => {
    document.getElementById('aula-id').value = '';
    document.getElementById('aula-local').value = '';
    document.getElementById('aula-modalidade').value = '';
    document.getElementById('aula-lotacao').value = '';
    document.getElementById('aula-professor').value = '';
    abrirModal();
  });
}

if (formAula) {
  formAula.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('aula-id').value;
    const local = document.getElementById('aula-local').value;
    const modalidade = document.getElementById('aula-modalidade').value;
    const lotacao = document.getElementById('aula-lotacao').value;
    const professor = document.getElementById('aula-professor').value;

    // Validar campos obrigatórios
    if (!local || !modalidade) {
      showNotification('Por favor, preencha Local e Modalidade', 'warning');
      return;
    }

    const dados = { local, modalidade, lotacao, professor };
    if (id) dados.id = id;

    try {
      const res = await fetch('api_aulas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
      });
      
      // Verificar status HTTP
      if (!res.ok) {
        console.error('Status HTTP:', res.status, res.statusText);
        const text = await res.text();
        console.error('Resposta do servidor:', text);
        showNotification('Erro HTTP ' + res.status + ': ' + res.statusText, 'error');
        return;
      }
      
      const responseText = await res.text();
      console.log('Resposta da API (raw):', responseText);
      
      // Verificar se a resposta é JSON válido
      let result;
      try {
        result = JSON.parse(responseText);
      } catch (parseError) {
        console.error('Erro ao fazer parse do JSON:', parseError);
        console.error('Resposta completa recebida:', responseText);
        if (responseText.trim().startsWith('<')) {
          showNotification('Erro: O servidor retornou HTML em vez de JSON. Isso geralmente indica um erro PHP. Verifique o console para mais detalhes.', 'error');
        } else {
          showNotification('Erro: O servidor retornou uma resposta inválida. Verifique o console para mais detalhes.', 'error');
        }
        return;
      }
      
      if (result.status === 'ok') {
        showNotification('Aula salva com sucesso!', 'success');
        setTimeout(() => location.reload(), 1000);
      } else {
        showNotification('Erro: ' + (result.message || 'Erro desconhecido'), 'error');
        console.error('Erro completo:', result);
      }
    } catch (err) {
      console.error('Erro:', err);
      showNotification('Erro ao salvar aula: ' + err.message, 'error');
    }
  });
}

// Editar/Deletar aula
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('editar') && window.location.pathname.includes('aulas.php')) {
    const id = e.target.getAttribute('data-id');
    const tr = e.target.closest('tr');
    const local = tr.cells[1].textContent;
    const modalidade = tr.cells[2].textContent;
    const lotacao = tr.cells[3].textContent;

    document.getElementById('aula-id').value = id;
    document.getElementById('aula-local').value = local === '—' ? '' : local;
    document.getElementById('aula-modalidade').value = modalidade === '—' ? '' : modalidade;
    document.getElementById('aula-lotacao').value = lotacao === '—' ? '' : lotacao;
    abrirModal();
  }

  if (e.target.classList.contains('deletar') && window.location.pathname.includes('aulas.php')) {
    const id = e.target.getAttribute('data-id');
    if (confirm('Tem certeza que deseja deletar esta aula?')) {
      fetch('api_aulas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, action: 'delete' })
      })
      .then(res => res.json())
      .then(result => {
        if (result.status === 'ok') {
          showNotification('Aula deletada!', 'success');
          setTimeout(() => location.reload(), 1000);
        } else {
          showNotification('Erro ao deletar: ' + result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Erro:', err);
        showNotification('Erro ao deletar aula', 'error');
      });
    }
  }
});

// 📅 PÁGINA DE AGENDAMENTOS
const formAgendamento = document.getElementById('form-agendamento');
const novoAgendamentoBtn = document.getElementById('novo-agendamento');

if (novoAgendamentoBtn) {
  novoAgendamentoBtn.addEventListener('click', () => {
    document.getElementById('agendamento-id').value = '';
    document.getElementById('agendamento-usuario').value = '';
    document.getElementById('agendamento-data').value = '';
    document.getElementById('agendamento-objetivo').value = '';
    document.getElementById('agendamento-modalidade').value = '';
    document.getElementById('agendamento-status').value = '';
    abrirModal();
  });
}

if (formAgendamento) {
  formAgendamento.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('agendamento-id').value;
    const usuario = document.getElementById('agendamento-usuario').value;
    const data = document.getElementById('agendamento-data').value;
    const objetivo = document.getElementById('agendamento-objetivo').value;
    const modalidade = document.getElementById('agendamento-modalidade').value;
    const status = document.getElementById('agendamento-status').value;

    // Validar campos obrigatórios
    if (!usuario) {
      showNotification('Por favor, selecione um usuário', 'warning');
      return;
    }
    if (!data) {
      showNotification('Por favor, selecione uma data e hora', 'warning');
      return;
    }
    if (!objetivo) {
      showNotification('Por favor, informe o objetivo', 'warning');
      return;
    }
    if (!status) {
      showNotification('Por favor, selecione um status', 'warning');
      return;
    }

    console.log('Enviando dados:', { usuario, data, objetivo, modalidade, status });

    const dados = { usuario, data, objetivo, modalidade, status };
    if (id) dados.id = id;

    try {
      const res = await fetch('api_agendamentos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
      });
      
      // Verificar status HTTP
      if (!res.ok) {
        console.error('Status HTTP:', res.status, res.statusText);
        const text = await res.text();
        console.error('Resposta do servidor:', text);
        showNotification('Erro HTTP ' + res.status + ': ' + res.statusText, 'error');
        return;
      }
      
      // Verificar Content-Type
      const contentType = res.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        console.warn('Content-Type inesperado:', contentType);
      }
      
      const responseText = await res.text();
      console.log('Resposta da API (raw):', responseText);
      console.log('Primeiros 200 caracteres:', responseText.substring(0, 200));
      
      // Verificar se a resposta é JSON válido
      let result;
      try {
        result = JSON.parse(responseText);
      } catch (parseError) {
        console.error('Erro ao fazer parse do JSON:', parseError);
        console.error('Resposta completa recebida:', responseText);
        console.error('Tipo da resposta:', typeof responseText);
        console.error('Tamanho da resposta:', responseText.length);
        
        // Tentar identificar o problema
        if (responseText.trim().startsWith('<')) {
          showNotification('Erro: O servidor retornou HTML em vez de JSON. Isso geralmente indica um erro PHP. Verifique o console para mais detalhes.', 'error');
        } else {
          showNotification('Erro: O servidor retornou uma resposta inválida. Verifique o console para mais detalhes.', 'error');
        }
        return;
      }
      
      if (result.status === 'ok') {
        showNotification('Agendamento salvo com sucesso!', 'success');
        setTimeout(() => location.reload(), 1000);
      } else {
        showNotification('Erro: ' + (result.message || 'Erro desconhecido'), 'error');
        console.error('Erro completo:', result);
      }
    } catch (err) {
      console.error('Erro ao processar:', err);
      showNotification('Erro ao salvar agendamento: ' + err.message, 'error');
    }
  });
}

// Editar/Deletar agendamento
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('editar') && window.location.pathname.includes('agendamentos.php')) {
    const id = e.target.getAttribute('data-id');
    const tr = e.target.closest('tr');
    
    // Obter dados dos atributos data da linha
    const usuarioId = tr.getAttribute('data-usuario-id');
    const dataHora = tr.getAttribute('data-data-hora');
    const objetivo = tr.getAttribute('data-objetivo');
    const modalidade = tr.getAttribute('data-modalidade');
    const status = tr.getAttribute('data-status');

    document.getElementById('agendamento-id').value = id;
    document.getElementById('agendamento-usuario').value = usuarioId || '';
    document.getElementById('agendamento-data').value = dataHora || '';
    document.getElementById('agendamento-objetivo').value = objetivo || '';
    document.getElementById('agendamento-modalidade').value = modalidade || '';
    document.getElementById('agendamento-status').value = status || '';
    abrirModal();
  }

  if (e.target.classList.contains('deletar') && window.location.pathname.includes('agendamentos.php')) {
    const id = e.target.getAttribute('data-id');
    if (confirm('Tem certeza que deseja deletar este agendamento?')) {
      fetch('api_agendamentos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, action: 'delete' })
      })
      .then(res => res.json())
      .then(result => {
        if (result.status === 'ok') {
          showNotification('Agendamento deletado!', 'success');
          setTimeout(() => location.reload(), 1000);
        } else {
          showNotification('Erro ao deletar: ' + result.message, 'error');
        }
      })
      .catch(err => {
        console.error('Erro:', err);
        showNotification('Erro ao deletar agendamento', 'error');
      });
    }
  }
});

// 🔍 Busca
if (searchInput) {
  searchInput.addEventListener('input', (e) => {
    const termo = e.target.value.toLowerCase();
    // Selecionar apenas linhas da tabela atual (evitar filtrar múltiplas tabelas)
    const tabela = searchInput.closest('.dashboard')?.querySelector('table tbody');
    if (tabela) {
      const linhas = tabela.querySelectorAll('tr');
      linhas.forEach(tr => {
        const texto = tr.textContent.toLowerCase();
        tr.style.display = texto.includes(termo) ? '' : 'none';
      });
    }
  });
}
