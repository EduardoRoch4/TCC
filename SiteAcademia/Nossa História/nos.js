const menuIcon = document.getElementById('menu-icon');
    const sideMenu = document.getElementById('side-menu');

    menuIcon.addEventListener('click', () => {
      sideMenu.classList.toggle('active');
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

        if (loginBtn) loginBtn.href = isLogged ? '/Login/login.php?acao=logout' : '/Login/login.php';
        if (loginSide) loginSide.href = isLogged ? '/Login/login.php?acao=logout' : '/Login/login.php';
        if (loginBtn) loginBtn.textContent = isLogged ? 'Logout' : 'Login';
        if (loginSide) loginSide.textContent = isLogged ? 'Logout' : 'Login';
        if (perfilBtn) perfilBtn.style.display = isLogged ? '' : 'none';
        if (perfilSide) perfilSide.style.display = isLogged ? '' : 'none';

        const userNameEl = document.getElementById('user-name');
        const userDisplay = document.getElementById('user-display');
        if (userNameEl) userNameEl.textContent = s.usuario || '';
        if (userDisplay) userDisplay.style.display = isLogged ? '' : 'none';

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

      } catch (err) { console.warn('session check', err); }
    }

    refreshSessionUI();

const fadeElements = document.querySelectorAll('.fade-in-up');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('visible');
      });
    }, { threshold: 0.2 });
    fadeElements.forEach(el => observer.observe(el));