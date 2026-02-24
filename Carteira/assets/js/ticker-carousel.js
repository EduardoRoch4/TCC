// Carrossel Automático do Ticker
let tickerCurrentIndex = 0;
let tickerAutoPlayInterval;
let itemsPerView = 8; // Quantos itens mostrar por vez

function getTickerItems() {
    return document.querySelectorAll('.ticker-item');
}

function getTickerTrack() {
    return document.getElementById('tickerCarouselTrack');
}

function initTickerCarousel() {
    const tickerTrack = getTickerTrack();
    const tickerItems = getTickerItems();
    
    if (!tickerTrack || tickerItems.length === 0) return;
    
    // Ajustar quantidade de itens visíveis baseado na largura
    updateTickerItemsPerView();
    
    // Iniciar auto-play
    startTickerAutoPlay();
    
    // Pausar ao passar o mouse
    const tickerWrapper = document.querySelector('.quotes-ticker');
    if (tickerWrapper) {
        tickerWrapper.addEventListener('mouseenter', stopTickerAutoPlay);
        tickerWrapper.addEventListener('mouseleave', startTickerAutoPlay);
    }
}

function updateTickerItemsPerView() {
    const width = window.innerWidth;
    if (width < 768) {
        itemsPerView = 2;
    } else if (width < 1024) {
        itemsPerView = 4;
    } else if (width < 1400) {
        itemsPerView = 6;
    } else {
        itemsPerView = 8;
    }
}

function moveTickerCarousel() {
    const tickerTrack = getTickerTrack();
    const tickerItems = getTickerItems();
    
    if (!tickerTrack || tickerItems.length === 0) return;
    
    const totalItems = tickerItems.length;
    const maxIndex = Math.max(0, totalItems - itemsPerView);
    
    tickerCurrentIndex++;
    
    if (tickerCurrentIndex > maxIndex) {
        tickerCurrentIndex = 0;
    }
    
    // Calcular offset
    if (tickerItems[0]) {
        const itemWidth = tickerItems[0].offsetWidth + 24; // width + gap
        const offset = -tickerCurrentIndex * itemWidth;
        tickerTrack.style.transform = `translateX(${offset}px)`;
    }
}

function startTickerAutoPlay() {
    stopTickerAutoPlay();
    tickerAutoPlayInterval = setInterval(moveTickerCarousel, 3000); // Move a cada 3 segundos
}

function stopTickerAutoPlay() {
    if (tickerAutoPlayInterval) {
        clearInterval(tickerAutoPlayInterval);
        tickerAutoPlayInterval = null;
    }
}

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTickerCarousel);
} else {
    initTickerCarousel();
}

// Ajustar ao redimensionar
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        updateTickerItemsPerView();
        tickerCurrentIndex = 0;
        moveTickerCarousel();
    }, 250);
});
