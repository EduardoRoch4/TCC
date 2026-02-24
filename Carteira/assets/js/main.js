/**
 * CarteiraInvest - JavaScript principal
 */

document.addEventListener('DOMContentLoaded', function() {
    initSearchForm();
    initMasks();
    initConfirmations();
});

// Máscaras de input
function initMasks() {
    const inputsNumero = document.querySelectorAll('input[name="quantidade"], input[name="preco_medio"]');
    inputsNumero.forEach(function(input) {
        input.addEventListener('blur', function() {
            let valor = this.value.replace(/[^\d,.-]/g, '').replace(',', '.');
            if (valor && !isNaN(parseFloat(valor))) {
                const num = parseFloat(valor);
                if (this.name === 'quantidade') {
                    this.value = num.toFixed(4).replace('.', ',');
                } else {
                    this.value = num.toFixed(2).replace('.', ',');
                }
            }
        });
    });
}

// Busca com sugestão (para expansão futura)
function initSearchForm() {
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.closest('form')?.submit();
            }
        });
    }
}

// Confirmações
function initConfirmations() {
    const formsDelete = document.querySelectorAll('form[onsubmit*="confirm"]');
    formsDelete.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja remover?')) {
                e.preventDefault();
            }
        });
    });
}

// Toast/Notificação simples (para feedback)
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + (type || 'info');
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;bottom:20px;right:20px;padding:1rem 1.5rem;background:#1a202c;color:white;border-radius:8px;z-index:9999;animation:slideIn 0.3s;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.style.animation = 'slideOut 0.3s';
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3000);
}

// Animação para números (opcional)
function animateValue(element, start, end, duration) {
    const startTime = performance.now();
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const value = start + (end - start) * progress;
        element.textContent = value.toFixed(2);
        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}
