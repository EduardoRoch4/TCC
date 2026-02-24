// Carregar Notícias Financeiras
async function carregarNoticias() {
    const container = document.getElementById('newsContainer');
    if (!container) return;
    
    try {
        const response = await fetch('api/noticias.php');
        const data = await response.json();
        
        if (data.noticias && data.noticias.length > 0) {
            container.innerHTML = '';
            data.noticias.forEach(noticia => {
            const card = document.createElement('div');
            card.className = 'news-card';
            
            // Se não tiver imagem, usar uma imagem placeholder relacionada a finanças
            let imagemUrl = noticia.imagem || '';
            if (!imagemUrl) {
                // Imagem placeholder genérica de finanças
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
        } else {
            container.innerHTML = '<p class="empty-text">Nenhuma notícia disponível no momento.</p>';
        }
    } catch (error) {
        console.error('Erro ao carregar notícias:', error);
        container.innerHTML = '<p class="empty-text">Erro ao carregar notícias. Tente novamente mais tarde.</p>';
    }
}

function formatarData(dataStr) {
    const data = new Date(dataStr);
    const agora = new Date();
    const diffMs = agora - data;
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
