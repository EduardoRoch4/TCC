// Carrossel de Ativos na Página Inicial
let currentSlide = 0;
let slidesPerView = 3; // Quantos slides mostrar por vez

function getSlides() {
    return document.querySelectorAll('.carousel-slide');
}

function initCarousel() {
    const track = document.getElementById('carouselTrack');
    const dots = document.getElementById('carouselDots');
    const slides = getSlides();
    const totalSlides = slides.length;
    
    if (!track || !slides.length) return;
    
    // Ajustar slidesPerView baseado na largura
    handleResize();
    
    // Criar dots
    if (dots) {
        dots.innerHTML = '';
        const totalDots = Math.ceil(totalSlides / slidesPerView);
        for (let i = 0; i < totalDots; i++) {
            const dot = document.createElement('span');
            dot.className = 'carousel-dot' + (i === 0 ? ' active' : '');
            dot.onclick = () => goToSlide(i);
            dots.appendChild(dot);
        }
    }
    
    updateCarousel();
}

function moveCarousel(direction) {
    const slides = getSlides();
    const totalSlides = slides.length;
    const maxSlide = Math.ceil(totalSlides / slidesPerView) - 1;
    currentSlide += direction;
    
    if (currentSlide < 0) {
        currentSlide = maxSlide;
    } else if (currentSlide > maxSlide) {
        currentSlide = 0;
    }
    
    updateCarousel();
}

function goToSlide(index) {
    currentSlide = index;
    updateCarousel();
}

function updateCarousel() {
    const track = document.getElementById('carouselTrack');
    if (!track) return;
    
    const offset = -currentSlide * (100 / slidesPerView);
    track.style.transform = `translateX(${offset}%)`;
    
    // Atualizar dots
    const dots = document.querySelectorAll('.carousel-dot');
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
    });
}

// Auto-play (opcional)
let autoPlayInterval;
function startAutoPlay() {
    autoPlayInterval = setInterval(() => {
        moveCarousel(1);
    }, 5000); // Muda a cada 5 segundos
}

function stopAutoPlay() {
    if (autoPlayInterval) {
        clearInterval(autoPlayInterval);
    }
}

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCarousel);
} else {
    initCarousel();
}

// Pausar auto-play ao passar o mouse
const carouselWrapper = document.querySelector('.carousel-wrapper');
if (carouselWrapper) {
    carouselWrapper.addEventListener('mouseenter', stopAutoPlay);
    carouselWrapper.addEventListener('mouseleave', startAutoPlay);
}

// Iniciar auto-play após 3 segundos
setTimeout(startAutoPlay, 3000);

// Responsividade
function handleResize() {
    const width = window.innerWidth;
    if (width < 768) {
        // Mobile: 1 slide por vez
        slidesPerView = 1;
    } else if (width < 1024) {
        // Tablet: 2 slides por vez
        slidesPerView = 2;
    } else {
        // Desktop: 3 slides por vez
        slidesPerView = 3;
    }
    
    // Atualizar CSS das slides
    const slides = getSlides();
    slides.forEach(slide => {
        if (width < 768) {
            slide.style.minWidth = '100%';
        } else if (width < 1024) {
            slide.style.minWidth = 'calc(50% - 0.75rem)';
        } else {
            slide.style.minWidth = 'calc(33.333% - 1rem)';
        }
    });
    
    updateCarousel();
}

window.addEventListener('resize', handleResize);
handleResize();
