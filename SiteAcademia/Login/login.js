const formLogin = document.getElementById('form-login');
    const formCadastro = document.getElementById('form-cadastro');
    const mostrarCadastro = document.getElementById('mostrar-cadastro');
    const mostrarLogin = document.getElementById('mostrar-login');
    const msg = document.getElementById('mensagem');

    mostrarCadastro.addEventListener('click', (e) => {
      e.preventDefault();
      formLogin.style.display = 'none';
      formCadastro.style.display = 'block';
      msg.textContent = '';
    });

    mostrarLogin.addEventListener('click', (e) => {
      e.preventDefault();
      formLogin.style.display = 'block';
      formCadastro.style.display = 'none';
      msg.textContent = '';
    });

    // Registration and login are handled by the server-side PHP in login.php.
    // This script is only responsible for toggling the login / register forms.

    document.getElementById('btn-voltar').addEventListener('click', () => window.location.href = '../index.html');
    document.getElementById('btn-voltar2').addEventListener('click', () => window.location.href = '../index.html');