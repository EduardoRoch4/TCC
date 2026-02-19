// --- MENU MOBILE ---
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

// --- ANIMAÇÕES DE ROLAGEM ---
const fadeElements = document.querySelectorAll('.fade-in-up');
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) entry.target.classList.add('visible');
  });
}, { threshold: 0.2 });

fadeElements.forEach(el => observer.observe(el));

// --- ELEMENTOS DO MENU ---
const loginBtn = document.getElementById('login-btn');
const perfilBtn = document.getElementById('perfil-btn');
const loginSide = document.getElementById('login-side');
const perfilSide = document.getElementById('perfil-side');
const logoutSide = document.getElementById("logout-side");
const logoutBtn = document.getElementById("logout-btn");

// --- FUNÇÃO GLOBAL DE LOGOUT ---
function fazerLogout(e) {
  if (e) e.preventDefault();
  window.location.href = "Login/login.php?acao=logout";
}

// BOTÃO FIXO DE LOGOUT (caso exista em páginas internas)
if (logoutBtn) logoutBtn.onclick = fazerLogout;

// --- ATUALIZA INTERFACE COM BASE NA SESSÃO PHP ---
async function refreshSessionUI() {
  try {
    const r = await fetch('Login/session_status.php');
    const s = await r.json();
    const isLogged = !!s.logged;
    const perfil = s.perfil || null;

    const userNameEl = document.getElementById('user-name');
    const userDisplay = document.getElementById('user-display');
    const userNameSide = document.getElementById('user-name-side');
    const userDisplaySide = document.getElementById('user-display-side');

    // --- USUÁRIO LOGADO ---
    if (isLogged) {

      // LOGIN VIRA LOGOUT
      loginBtn.textContent = 'Logout';
      loginSide.textContent = 'Logout';
      loginBtn.onclick = fazerLogout;
      loginSide.onclick = fazerLogout;
      loginBtn.removeAttribute("href");
      loginSide.removeAttribute("href");

      // SE NÃO FOR ADMIN, MOSTRA PERFIL
      if (perfil !== 'admin') {
        perfilBtn.style.display = 'inline-block';
        perfilSide.style.display = 'block';
      } else {
        perfilBtn.style.display = 'none';
        perfilSide.style.display = 'none';
      }

      // MOSTRAR NOME
      userNameEl.textContent = s.usuario;
      userNameSide.textContent = s.usuario;

      userDisplay.style.display = 'inline-block';
      userDisplaySide.style.display = 'inline-block';

      // MOSTRAR BOTÃO DE LOGOUT DO MENU LATERAL
      logoutSide.style.display = "block";
      logoutSide.onclick = fazerLogout;

      // --- LINK ADMIN ---
      setAdminLinks(perfil === 'admin');

    } else {

      // --- USUÁRIO DESLOGADO ---
      loginBtn.textContent = 'Login';
      loginSide.textContent = 'Login';

      loginBtn.href = 'Login/login.php';
      loginSide.href = 'Login/login.php';

      loginBtn.onclick = null;
      loginSide.onclick = null;

      perfilBtn.style.display = 'none';
      perfilSide.style.display = 'none';

      logoutSide.style.display = 'none';

      // NOME = VISITANTE
      userNameEl.textContent = "Visitante";
      userNameSide.textContent = "Visitante";

      userDisplay.style.display = 'inline-block';
      userDisplaySide.style.display = 'inline-block';

      // REMOVER LINK ADMIN
      setAdminLinks(false);
    }

  } catch (e) {
    console.warn('Could not check session status:', e);
  }
}

// --- CRIA / REMOVE LINKS DE ADMIN ---
function setAdminLinks(show) {

  // NAV SUPERIOR
  const nav = document.getElementById('nav-buttons');
  let adminLink = nav.querySelector('a[data-admin-link]');

  if (show) {
    if (!adminLink) {
      adminLink = document.createElement('a');
      adminLink.href = 'Admin/painel.php';
      adminLink.textContent = 'Painel Admin';
      adminLink.dataset.adminLink = "1";
      nav.appendChild(adminLink);
    }
  } else {
    if (adminLink) adminLink.remove();
  }

  // SIDE MENU
  const side = document.getElementById('side-menu');
  let adminLinkSide = side.querySelector('a[data-admin-link-side]');

  if (show) {
    if (!adminLinkSide) {
      adminLinkSide = document.createElement('a');
      adminLinkSide.href = 'Admin/painel.php';
      adminLinkSide.textContent = 'Painel Admin';
      adminLinkSide.dataset.adminLinkSide = "1";
      side.appendChild(adminLinkSide);
    }
  } else {
    if (adminLinkSide) adminLinkSide.remove();
  }
}

// --- INICIAR SISTEMA ---
refreshSessionUI();
