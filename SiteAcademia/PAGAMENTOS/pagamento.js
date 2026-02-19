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

function getParametro(nome) {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get(nome);
}

// Atualiza o valor do plano automaticamente
function atualizarPreco(plano) {
  const precoSpan = document.getElementById("preco");
  let preco = "R$ 0,00";

  switch (plano.toLowerCase()) {
    case "black":
      preco = "R$ 149,90";
      break;
    case "fit":
      preco = "R$ 99,90";
      break;
    case "tech":
      preco = "R$ 119,90";
      break;
  }

  precoSpan.textContent = preco;
}

// Quando a página carregar...
document.addEventListener("DOMContentLoaded", () => {
  const planoSelecionado = getParametro("plano");
  const selectPlano = document.getElementById("plano");

  // Se veio o plano da página anterior, já seleciona
  if (planoSelecionado && selectPlano) {
    selectPlano.value = planoSelecionado.toLowerCase();
    atualizarPreco(planoSelecionado);
  }

  // Atualiza o preço ao mudar o plano no select
  selectPlano?.addEventListener("change", (e) => {
    atualizarPreco(e.target.value);
  });
});

// Validação e simulação do pagamento
document.querySelector(".pagamento-form")?.addEventListener("submit", (e) => {
  e.preventDefault();

  const nome = document.getElementById("nome").value.trim();
  const email = document.getElementById("email").value.trim();
  const cartao = document.getElementById("cartao").value.trim();
  const plano = document.getElementById("plano").value;
  const preco = document.getElementById("preco").textContent;

  if (!nome || !email || !cartao || !plano) {
    showNotification("Por favor, preencha todos os campos obrigatórios.", 'warning');
    return;
  }

  // Simula o processamento
  showNotification(`Pagamento aprovado!\n\nPlano: ${plano.toUpperCase()}\nValor: ${preco}\nCliente: ${nome}\n\nBem-vindo(a) à TechFit! 💪`, 'success', 4000);

  // Redireciona após confirmar
  setTimeout(() => {
    window.location.href = "/Alunos/usuario.php";
  }, 4000);
});