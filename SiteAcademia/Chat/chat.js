// 🟢 Função de chat
const input = document.getElementById('mensagem');
const enviar = document.getElementById('enviar');
const chatBox = document.getElementById('chat-box');
// 🟢 ADICIONADO: Seleciona o container dos botões
const quickRepliesContainer = document.getElementById('quick-replies');

function adicionarMensagem(texto, tipo = 'user') {
  const msg = document.createElement('div');
  msg.classList.add('message', tipo);
  msg.textContent = texto;
  chatBox.appendChild(msg);
  chatBox.scrollTop = chatBox.scrollHeight;
}

// 🟢 ADICIONADO: Banco de dados de perguntas e respostas
const respostasProntas = {
  "Quais são os horários de funcionamento?": "Nossa academia funciona de Seg. a Sex. das 6h às 23h, e Sáb. das 8h às 14h.",
  "Quais planos vocês oferecem?": "Temos planos Mensal, Trimestral e Anual. O plano anual é o nosso melhor custo-benefício! Para mais detalhes, acesse nossa página de planos.",
  "Como agendar uma aula?": "Você pode agendar aulas de funcional, spinning ou yoga diretamente pela nossa página de 'Agendamento' no menu."
};

// 🟢 ADICIONADO: Função para buscar resposta
function obterResposta(pergunta) {
  // Verifica se a pergunta existe no nosso banco de respostas
  if (respostasProntas[pergunta]) {
    return respostasProntas[pergunta];
  }
  // Resposta padrão para perguntas personalizadas
  return 'Entendido! Um instrutor entrará em contato em breve.';
}

// 🟢 MODIFICADO: Evento de clique do botão Enviar
enviar.addEventListener('click', () => {
  const texto = input.value.trim();
  if (texto !== '') {
    adicionarMensagem(texto, 'user');
    input.value = '';

    // Busca a resposta correta (pronta ou padrão)
    const resposta = obterResposta(texto);

    setTimeout(() => {
      adicionarMensagem(resposta, 'system');
    }, 800);
  }
});

input.addEventListener('keypress', (e) => {
  if (e.key === 'Enter') enviar.click();
});

// 🟢 ADICIONADO: Evento de clique para os botões de perguntas rápidas
quickRepliesContainer.addEventListener('click', (e) => {
  // Verifica se o clique foi em um botão com a classe 'quick-reply'
  if (e.target.classList.contains('quick-reply')) {
    // Pega a pergunta completa do atributo 'data-question'
    const pergunta = e.target.dataset.question;
    
    // 1. Adiciona a pergunta do usuário ao chat
    adicionarMensagem(pergunta, 'user');
    
    // 2. Obtém a resposta correspondente
    const resposta = obterResposta(pergunta); // Com certeza vai achar a resposta
    
    // 3. Adiciona a resposta do sistema ao chat
    setTimeout(() => {
      adicionarMensagem(resposta, 'system');
    }, 800);
  }
});


// 🟢 Menu lateral e overlay (SEU CÓDIGO ORIGINAL - SEM MUDANÇAS)
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

// 🟢 Controle de login/logout (SEU CÓDIGO ORIGINAL - SEM MUDANÇAS)
const loginBtn = document.getElementById('login-btn');
const perfilBtn = document.getElementById('perfil-btn');
const loginSide = document.getElementById('login-side');
const perfilSide = document.getElementById('perfil-side');

async function atualizarInterface() {
  try {
    const r = await fetch('../Login/session_status.php');
    const s = await r.json();
    const isLogged = !!s.logged;
    const perfil = s.perfil || null;
    const userNameEl = document.getElementById('user-name');
    const userDisplay = document.getElementById('user-display');
    const userNameSide = document.getElementById('user-name-side');
    const userDisplaySide = document.getElementById('user-display-side');

    if (isLogged) {
      loginBtn.textContent = 'Logout';
      loginSide.textContent = 'Logout';
      loginBtn.href = '/Login/login.php?acao=logout';
      loginSide.href = '/Login/login.php?acao=logout';
      perfilBtn.style.display = 'inline-block';
      perfilSide.style.display = 'inline-block';
    } else {
      loginBtn.textContent = 'Login';
      loginSide.textContent = 'Login';
      loginBtn.href = '/Login/login.php';
      loginSide.href = '/Login/login.php';
      perfilBtn.style.display = 'none';
      perfilSide.style.display = 'none';
    }

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
    if (userNameEl) userNameEl.textContent = s.usuario || '';
    if (userDisplay) userDisplay.style.display = isLogged ? '' : 'none';
    if (userNameSide) userNameSide.textContent = s.usuario || '';
    if (userDisplaySide) userDisplaySide.style.display = isLogged ? '' : 'none';

  } catch (e) {
    console.warn('session check error', e);
  }
}

atualizarInterface();

// 🟢 Animação (SEU CÓDIGO ORIGINAL - SEM MUDANÇAS)
const fadeElements = document.querySelectorAll('.fade-in-up');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('visible');
      });
    }, { threshold: 0.2 });
    fadeElements.forEach(el => observer.observe(el));

// 🟢 CORREÇÃO: A chave '}' extra que estava aqui no seu arquivo original foi removida.