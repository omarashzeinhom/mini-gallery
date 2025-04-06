document.addEventListener('DOMContentLoaded', function() {
    const carousels = document.querySelectorAll('.mg-pro-carousel');
    
    carousels.forEach(carousel => {
        const track = carousel.querySelector('.mg-pro-carousel__track');
        const cards = carousel.querySelectorAll('.mg-pro-carousel__card');
        const prevBtn = carousel.querySelector('.mg-pro-carousel__nav--prev');
        const nextBtn = carousel.querySelector('.mg-pro-carousel__nav--next');
        
        let currentIndex = 0;
        const carouselStyles = getComputedStyle(carousel);
        const cardWidth = parseInt(carouselStyles.getPropertyValue('--card-width'));
        const gap = parseInt(carouselStyles.getPropertyValue('--gap'));

        function updateCarousel() {
            const offset = -(currentIndex * (cardWidth + gap));
            track.style.transform = `translateX(${offset}px)`;
        }

        function nextSlide() {
            currentIndex = Math.min(currentIndex + 1, cards.length - 1);
            updateCarousel();
        }

        function prevSlide() {
            currentIndex = Math.max(currentIndex - 1, 0);
            updateCarousel();
        }

        // Button controls
        nextBtn?.addEventListener('click', nextSlide);
        prevBtn?.addEventListener('click', prevSlide);

        // Touch/swipe handling
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

        // Keyboard navigation
        carousel.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') prevSlide();
            if (e.key === 'ArrowRight') nextSlide();
        });
    });
});