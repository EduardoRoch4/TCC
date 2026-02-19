// Use server-side session (PHP) for auth; provide logout link to server endpoint.
const logoutBtn = document.getElementById('logout');
if (logoutBtn) {
  logoutBtn.addEventListener('click', (e) => {
    e.preventDefault();
    window.location.href = '/Login/login.php?acao=logout';
  });
}
