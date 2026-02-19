// 🧩 Modal de conteúdo
const modal = document.getElementById('modal');
const close = document.getElementById('close');
const titulo = document.getElementById('modal-titulo');
const texto = document.getElementById('modal-texto');

function abrirModal(tipo) {
  if (modal) {
    modal.style.display = 'flex';
    if (tipo === 'alunos') {
      if (titulo) titulo.textContent = 'Gerenciamento de Alunos';
      if (texto) texto.textContent = 'Aqui você poderá visualizar, cadastrar e editar informações dos alunos (em desenvolvimento).';
    } else if (tipo === 'professores') {
      if (titulo) titulo.textContent = 'Gerenciamento de Professores';
      if (texto) texto.textContent = 'Controle de dados de instrutores e turmas (em breve).';
    } else if (tipo === 'relatorios') {
      if (titulo) titulo.textContent = 'Relatórios Gerenciais';
      if (texto) texto.textContent = 'Visualize relatórios de presença, ocupação e desempenho (em breve).';
    }
  }
}

if (close) {
  close.addEventListener('click', () => {
    if (modal) modal.style.display = 'none';
  });
}
window.addEventListener('click', (e) => {
  if (e.target === modal && modal) modal.style.display = 'none';
});

// 🟢 Menu lateral e overlay (igual ao index)
const menuIcon = document.getElementById('menu-icon');
const sideMenu = document.getElementById('side-menu');
const closeBtn = document.getElementById('close-btn');
const overlay = document.getElementById('overlay');

menuIcon.addEventListener('click', () => {
  sideMenu.classList.add('active');
  overlay.classList.add('show');
});
closeBtn.addEventListener('click', () => {
  sideMenu.classList.remove('active');
  overlay.classList.remove('show');
});
overlay.addEventListener('click', () => {
  sideMenu.classList.remove('active');
  overlay.classList.remove('show');
});

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

// --- CARREGAR DADOS DO DASHBOARD ---
async function loadDashboard() {
  try {
    const res = await fetch('get_dashboard_data.php');
    const json = await res.json();
    if (!json || json.status !== 'ok') {
      console.error('Erro ao carregar dados do dashboard:', json);
      document.getElementById('recent-note').textContent = 'Não foi possível carregar os dados.';
      return;
    }

    const d = json.data || {};
    document.getElementById('count-users').textContent = d.total_usuarios ?? '—';
    document.getElementById('count-teachers').textContent = d.total_professores ?? '—';
    document.getElementById('count-bookings').textContent = d.total_agendamentos ?? '—';
    document.getElementById('count-upcoming').textContent = d.agendamentos_futuros ?? '—';

    const tbody = document.getElementById('recent-table-body');
    tbody.innerHTML = '';
    const recent = d.recent_agendamentos || [];
    if (recent.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="muted">Nenhum registro encontrado</td></tr>';
      document.getElementById('recent-note').textContent = 'Sem registros recentes.';
    } else {
      document.getElementById('recent-note').textContent = recent.length + ' registros mostrados';
      recent.forEach(r => {
        const tr = document.createElement('tr');
        const date = new Date(r.data_hora);
        const dt = isNaN(date) ? r.data_hora : date.toLocaleString('pt-BR', {dateStyle: 'short', timeStyle: 'short'});
        tr.innerHTML = `
          <td>${dt}</td>
          <td>${r.usuario ?? '—'}</td>
          <td>${r.objetivo ?? '—'}</td>
          <td>${r.modalidade ?? '—'}</td>
          <td>${r.status_ ?? '—'}</td>
        `;
        tr.addEventListener('click', () => {
          abrirModalDetalhes(r);
        });
        tbody.appendChild(tr);
      });
    }

  } catch (err) {
    console.error('Erro fetch dashboard:', err);
    document.getElementById('recent-note').textContent = 'Erro ao carregar dados (ver console).';
  }
}

// abrir modal com detalhes de agendamento
function abrirModalDetalhes(row) {
  titulo.textContent = 'Detalhes do Agendamento';
  texto.innerHTML = `
    <p><strong>Data / Hora:</strong> ${new Date(row.data_hora).toLocaleString('pt-BR')}</p>
    <p><strong>Usuário:</strong> ${row.usuario ?? '—'}</p>
    <p><strong>Objetivo:</strong> ${row.objetivo ?? '—'}</p>
    <p><strong>Modalidade:</strong> ${row.modalidade ?? '—'}</p>
    <p><strong>Status:</strong> ${row.status_ ?? '—'}</p>
  `;
  modal.style.display = 'flex';
}

 // Carrega dados ao abrir a página
loadDashboard();