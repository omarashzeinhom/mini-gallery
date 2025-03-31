class MegaCarousel {
    constructor() {
        this.carousel = document.querySelector('.mg-mega-carousel');
        this.slides = Array.from(document.querySelectorAll('.mg-carousel__slide'));
        this.dotsContainer = document.querySelector('.mg-dots-container');
        this.currentIndex = 0;
        this.autoPlayInterval = null;

        // Immediate first slide visibility
        this.slides[0].style.opacity = '1';
        this.slides[0].style.transform = 'scale(1)';
        
        // Enable transitions after 50ms
        setTimeout(() => {
            this.carousel.classList.add('loaded');
        }, 50);

        this.initDots();
        this.addEventListeners();
        this.startAutoPlay();

    }

    initDots() {
        this.slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('mg-dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => this.goToSlide(index));
            this.dotsContainer.appendChild(dot);
        });
    }

    updateDots() {
        const dots = Array.from(this.dotsContainer.children);
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === this.currentIndex);
        });
    }

    goToSlide(index) {
        if (index === this.currentIndex) return;

        this.slides[this.currentIndex].classList.remove('mg-active');
        this.currentIndex = (index + this.slides.length) % this.slides.length;
        this.slides[this.currentIndex].classList.add('mg-active');
        this.updateDots();
        this.resetAutoPlay();
    }

    addEventListeners() {
        this.carousel.querySelector('.mg-prev').addEventListener('click', () => this.prevSlide());
        this.carousel.querySelector('.mg-next').addEventListener('click', () => this.nextSlide());
        
        // Touch handling
        let touchStartX = 0;
        this.carousel.addEventListener('touchstart', e => {
            touchStartX = e.touches[0].clientX;
        });
        
        this.carousel.addEventListener('touchend', e => {
            const touchEndX = e.changedTouches[0].clientX;
            if (Math.abs(touchEndX - touchStartX) > 50) {
                touchEndX < touchStartX ? this.nextSlide() : this.prevSlide();
            }
        });
    }

    nextSlide() { this.goToSlide(this.currentIndex + 1); }
    prevSlide() { this.goToSlide(this.currentIndex - 1); }

    startAutoPlay() {
        this.autoPlayInterval = setInterval(() => this.nextSlide(), 5000);
    }

    resetAutoPlay() {
        clearInterval(this.autoPlayInterval);
        this.startAutoPlay();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.mg-mega-carousel')) {
        new MegaCarousel();
    }
});