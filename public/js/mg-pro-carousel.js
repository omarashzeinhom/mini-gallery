// public/js/pro-carousel.js
document.addEventListener('DOMContentLoaded', function() {
    const carousels = document.querySelectorAll('.mg-pro-carousel');
    
    carousels.forEach(carousel => {
        const track = carousel.querySelector('.mg-pro-carousel__track');
        const cards = carousel.querySelectorAll('.mg-pro-carousel__card');
        const prevBtn = carousel.querySelector('.mg-pro-carousel__nav--prev');
        const nextBtn = carousel.querySelector('.mg-pro-carousel__nav--next');
        const thumbs = carousel.querySelectorAll('.mg-pro-carousel__thumb');
        
        let currentIndex = 0;
        const cardWidth = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--card-width'));
        const gap = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--gap'));
        
        function updateCarousel() {
            const offset = -(currentIndex * (cardWidth + gap));
            track.style.transform = `translateX(${offset}px)`;
            thumbs.forEach(thumb => thumb.classList.remove('active'));
            thumbs[currentIndex]?.classList.add('active');
        }
        
        function nextSlide() {
            currentIndex = (currentIndex + 1) % cards.length;
            updateCarousel();
        }
        
        function prevSlide() {
            currentIndex = (currentIndex - 1 + cards.length) % cards.length;
            updateCarousel();
        }
        
        // Navigation
        nextBtn?.addEventListener('click', nextSlide);
        prevBtn?.addEventListener('click', prevSlide);
        
        // Thumbnail click
        thumbs.forEach((thumb, index) => {
            thumb.addEventListener('click', () => {
                currentIndex = index;
                updateCarousel();
            });
        });
        
        // Auto-play
        let autoplay = setInterval(nextSlide, 5000);
        
        carousel.addEventListener('mouseenter', () => clearInterval(autoplay));
        carousel.addEventListener('mouseleave', () => {
            autoplay = setInterval(nextSlide, 5000);
        });
        
        // Touch/swipe
        let touchStartX = 0;
        
        carousel.addEventListener('touchstart', e => {
            touchStartX = e.touches[0].clientX;
        });
        
        carousel.addEventListener('touchend', e => {
            const touchEndX = e.changedTouches[0].clientX;
            if (Math.abs(touchEndX - touchStartX) > 50) {
                touchEndX < touchStartX ? nextSlide() : prevSlide();
            }
        });
    });
});