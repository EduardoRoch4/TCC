// ===================== SISTEMA DE NOTIFICAÇÕES =====================
function showNotification(message, type = 'info', duration = 5000) {
  let container = document.getElementById('notification-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'notification-container';
    container.className = 'notification-container';
    document.body.appendChild(container);
  }

  const notification = document.createElement('div');
  notification.className = `notification ${type}`;

  const icons = {
    success: '✅',
    error: '❌',
    warning: '⚠️',
    info: 'ℹ️'
  };

  notification.innerHTML = `
    <span class="notification-icon">${icons[type]}</span>
    <span class="notification-content">${message}</span>
    <button class="notification-close" onclick="this.parentElement.remove()">×</button>
  `;

  container.appendChild(notification);

  if (duration > 0) {
    setTimeout(() => {
      notification.classList.add('hiding');
      setTimeout(() => notification.remove(), 300);
    }, duration);
  }
}

// ===================== VARIÁVEIS DO CALENDÁRIO =====================
const calendarDays = document.querySelector(".calendar-days");
const monthName = document.querySelector(".calendar-header h2");
const prevBtn = document.querySelector(".prev-month");
const nextBtn = document.querySelector(".next-month");
const agendarBtn = document.getElementById("agendar-btn");
const timeSelect = document.getElementById("time-select");
const goalSelect = document.getElementById("goal-select");
const modalidadeSelect = document.getElementById("modalidade-select");

let currentDate = new Date();
let selectedDay = null;

// ===================== CALENDÁRIO (COM BLOQUEIO DE DATAS PASSADAS) =====================
function renderCalendar(date) {
  const year = date.getFullYear();
  const month = date.getMonth();

  const monthNames = [
    "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
    "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
  ];

  monthName.textContent = `${monthNames[month]} ${year}`;
  calendarDays.innerHTML = "";

  const daysInMonth = new Date(year, month + 1, 0).getDate();

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  for (let i = 1; i <= daysInMonth; i++) {
    const day = document.createElement("div");
    const dateObj = new Date(year, month, i);

    day.classList.add("day");
    day.innerHTML = `<span>${i}</span>`;

    // 🔥 DATA PASSADA = BLOQUEADA
    if (dateObj < today) {
      day.classList.add("disabled");
      day.addEventListener("click", () => {
        showNotification("Você não pode selecionar uma data anterior ao dia de hoje!", "error");
      });

    } else {
      // 🔥 DATA VÁLIDA
      day.addEventListener("click", () => {
        document.querySelectorAll(".day.selected").forEach(d => d.classList.remove("selected"));
        day.classList.add("selected");
        selectedDay = i;
      });
    }

    calendarDays.appendChild(day);
  }
}

// ===================== NAVEGAÇÃO DE MESES =====================
prevBtn.addEventListener("click", () => {
  currentDate.setMonth(currentDate.getMonth() - 1);
  renderCalendar(currentDate);
});

nextBtn.addEventListener("click", () => {
  currentDate.setMonth(currentDate.getMonth() + 1);
  renderCalendar(currentDate);
});

renderCalendar(currentDate);

// ===================== BOTÃO AGENDAR =====================
agendarBtn.addEventListener("click", async () => {
  const horario = timeSelect.value;
  const objetivo = goalSelect.value;
  const modalidade = modalidadeSelect.value;

  if (!selectedDay || !horario || !objetivo || !modalidade) {
    showNotification("Por favor, selecione o dia, o horário, o objetivo e a modalidade antes de agendar!", 'warning');
    return;
  }

  const dadosAgendamento = {
    dia: selectedDay,
    mes: currentDate.getMonth() + 1,
    ano: currentDate.getFullYear(),
    horario,
    objetivo,
    modalidade
  };

  try {
    const response = await fetch('processar_agendamento.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dadosAgendamento)
    });

    const text = await response.text();
    let resultado;

    try {
      resultado = JSON.parse(text);
    } catch {
      console.error('Resposta não JSON:', text);
      showNotification('Erro inesperado do servidor.', 'error');
      return;
    }

    showNotification(resultado.message, resultado.status);

    if (resultado.status === 'success') {
      selectedDay = null;
      timeSelect.value = "";
      goalSelect.value = "";
      modalidadeSelect.value = "";
      renderCalendar(currentDate);
    }

  } catch (e) {
    console.error("Erro:", e);
    showNotification("Falha ao enviar agendamento.", 'error');
  }
});

// ===================== MENU HAMBÚRGUER =====================
const menuIcon = document.getElementById('menu-icon');
const sideMenu = document.getElementById('side-menu');
const closeBtn = document.getElementById('close-btn');
const overlay = document.getElementById('overlay');

menuIcon.addEventListener('click', () => {
  sideMenu.classList.add('active');
  overlay.style.display = 'block';
});

closeBtn.addEventListener('click', () => {
  sideMenu.classList.remove('active');
  overlay.style.display = 'none';
});

overlay.addEventListener('click', () => {
  sideMenu.classList.remove('active');
  overlay.style.display = 'none';
});

// ===================== ANIMAÇÃO =====================
const fadeElements = document.querySelectorAll('.fade-in-up');
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) entry.target.classList.add('visible');
  });
}, { threshold: 0.2 });
fadeElements.forEach(el => observer.observe(el));

// ===================== ATUALIZAÇÃO DE SESSÃO =====================
async function refreshSessionUI() {
  try {
    const r = await fetch('../Login/session_status.php');
    const s = await r.json();

    const isLogged = !!s.logged;
    const perfil = s.perfil || null;

    const loginBtn = document.getElementById('login-btn');
    const perfilBtn = document.getElementById('perfil-btn');
    const loginSide = document.getElementById('login-side');
    const perfilSide = document.getElementById('perfil-side');

    if (loginBtn) {
      loginBtn.href = isLogged ? '/Login/login.php?acao=logout' : '/Login/login.php';
      loginBtn.textContent = isLogged ? 'Logout' : 'Login';
    }
    if (loginSide) {
      loginSide.href = isLogged ? '/Login/login.php?acao=logout' : '/Login/login.php';
      loginSide.textContent = isLogged ? 'Logout' : 'Login';
    }

    if (perfilBtn) perfilBtn.style.display = isLogged ? '' : 'none';
    if (perfilSide) perfilSide.style.display = isLogged ? '' : 'none';

    const userNameEl = document.getElementById('user-name');
    const userDisplay = document.getElementById('user-display');
    const userNameSide = document.getElementById('user-name-side');
    const userDisplaySide = document.getElementById('user-display-side');

    if (userNameEl) userNameEl.textContent = s.usuario || '';
    if (userDisplay) userDisplay.style.display = isLogged ? '' : 'none';
    if (userNameSide) userNameSide.textContent = s.usuario || '';
    if (userDisplaySide) userDisplaySide.style.display = isLogged ? '' : 'none';

    function setAdminLinks(show) {
      const nav = document.querySelector('.nav-buttons');
      if (nav) {
        let a = nav.querySelector('a[data-admin-link]');
        if (show && isLogged && !a) {
          a = document.createElement('a');
          a.href = '/Admin/painel.php';
          a.textContent = 'Painel Admin';
          a.dataset.adminLink = "1";
          nav.appendChild(a);
        }
        if ((!show || !isLogged) && a) a.remove();
      }

      const side = document.getElementById('side-menu');
      if (side) {
        let s = side.querySelector('a[data-admin-link-side]');
        if (show && isLogged && !s) {
          s = document.createElement('a');
          s.href = '/Admin/painel.php';
          s.textContent = 'Painel Admin';
          s.dataset.adminLinkSide = "1";
          side.appendChild(s);
        }
        if ((!show || !isLogged) && s) s.remove();
      }
    }

    setAdminLinks(perfil === 'admin' && isLogged);

  } catch (err) {
    console.warn('session check failed:', err);
  }
}

refreshSessionUI();
