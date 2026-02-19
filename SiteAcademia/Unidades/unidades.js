// Pesquisa de unidades
document.getElementById("searchInput").addEventListener("keyup", function () {
  let filter = this.value.toLowerCase();
  let cards = document.getElementsByClassName("unidade-card");

  for (let i = 0; i < cards.length; i++) {
    let title = cards[i].querySelector("h3").innerText.toLowerCase();
    let endereco = cards[i].querySelector(".unidade-endereco").innerText.toLowerCase();

    if (title.includes(filter) || endereco.includes(filter)) {
      cards[i].style.display = "";
    } else {
      cards[i].style.display = "none";
    }
  }
});

// Atualiza interface de sessão (login/logout/admin links)
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
      const nav = document.querySelector('.nav-buttons') || document.getElementById('nav-buttons');
      if (nav) {
        let a = nav.querySelector('a[data-admin-link]');
        if (show && isLogged && !a) {
          a = document.createElement('a');
          a.href = '/Admin/painel.php';
          a.textContent = 'Painel Admin';
          a.setAttribute('data-admin-link', '1');
          nav.appendChild(a);
        }
        if ((!show || !isLogged) && a) a.remove();
      }

      const side = document.getElementById('side-menu') || document.querySelector('.side-menu');
      if (side) {
        let s = side.querySelector('a[data-admin-link-side]');
        if (show && isLogged && !s) {
          s = document.createElement('a');
          s.href = '/Admin/painel.php';
          s.textContent = 'Painel Admin';
          s.setAttribute('data-admin-link-side', '1');
          side.appendChild(s);
        }
        if ((!show || !isLogged) && s) s.remove();
      }
    }

    // Mostrar apenas se estiver logado E for admin
    setAdminLinks(perfil === 'admin' && isLogged);
  } catch (e) { console.warn('session check failed', e); }
}

refreshSessionUI();

// Adicionar evento de clique nos botões de escolher unidade
document.addEventListener('DOMContentLoaded', function() {
  const botoesUnidade = document.querySelectorAll('.btn-agendar[data-unidade]');
  
  botoesUnidade.forEach(btn => {
    btn.addEventListener('click', function() {
      const unidade = this.getAttribute('data-unidade');
      if (unidade) {
        // Redireciona para a página de pagamento com o parâmetro da unidade
        window.location.href = `../PAGAMENTOS/pagamentos.php?unidade=${encodeURIComponent(unidade)}`;
      }
    });
  });
});
