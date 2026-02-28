// Descobrir URL correta da API (raiz ou /admin)
function getNoticiasApiUrl() {
    var path = window.location.pathname || '';
    if (path.indexOf('/admin/') !== -1) {
        return '../api/noticias.php';
    }
    return 'api/noticias.php';
}

// Renderizar notícias em um container específico
function renderizarNoticiasEmContainer(container, noticias) {
    if (!container) return;

    if (!noticias || noticias.length === 0) {
        container.innerHTML = '<p class="empty-text">Nenhuma notícia disponível no momento.</p>';
        return;
    }

    container.innerHTML = '';
    noticias.forEach(function(noticia) {
        var card = document.createElement('div');
        card.className = 'news-card';

        // Se não tiver imagem, usar uma imagem placeholder relacionada a finanças
        var imagemUrl = noticia.imagem || '';
        if (!imagemUrl) {
            imagemUrl = 'https://via.placeholder.com/400x200/0a2540/ffffff?text=Finanças';
        }

        card.innerHTML = `
            <div class="news-image" style="background-image: url('${imagemUrl}')">
                ${!noticia.imagem ? '<div class="news-image-placeholder">📊</div>' : ''}
            </div>
            <div class="news-content">
                <h3><a href="${noticia.link}" target="_blank" rel="noopener">${noticia.titulo}</a></h3>
                <p>${noticia.descricao}</p>
                <div class="news-meta">
                    <span class="news-source">${noticia.fonte}</span>
                    <span class="news-date">${formatarData(noticia.data)}</span>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

// Carregar Notícias Financeiras (página inicial e painel admin)
async function carregarNoticias() {
    const containers = document.querySelectorAll('#newsContainer, #newsContainerAdmin');
    if (!containers.length) return;

    containers.forEach(function(container) {
        container.innerHTML = '<div class="news-loading">Carregando notícias...</div>';
    });

    try {
        const response = await fetch(getNoticiasApiUrl());
        const data = await response.json();

        containers.forEach(function(container) {
            renderizarNoticiasEmContainer(container, data.noticias || []);
        });
    } catch (error) {
        console.error('Erro ao carregar notícias:', error);
        containers.forEach(function(container) {
            container.innerHTML = '<p class="empty-text">Erro ao carregar notícias. Tente novamente mais tarde.</p>';
        });
    }
}

function formatarData(dataStr) {
    const data = new Date(dataStr);
    if (isNaN(data.getTime())) {
        return '';
    }
    const agora = new Date();
    let diffMs = agora - data;
    if (diffMs < 0) diffMs = 0; // evita valores negativos "há -X minutos"
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 60) {
        return `há ${diffMins} minuto${diffMins !== 1 ? 's' : ''}`;
    } else if (diffHours < 24) {
        return `há ${diffHours} hora${diffHours !== 1 ? 's' : ''}`;
    } else if (diffDays < 7) {
        return `há ${diffDays} dia${diffDays !== 1 ? 's' : ''}`;
    } else {
        return data.toLocaleDateString('pt-BR');
    }
}

// Carregar notícias quando a página estiver pronta
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', carregarNoticias);
} else {
    carregarNoticias();
}
