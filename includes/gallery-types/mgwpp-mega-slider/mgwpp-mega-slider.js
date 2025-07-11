class MegaCarousel {
    constructor(carouselElement) {
        this.carousel = carouselElement;
        this.slides = Array.from(this.carousel.querySelectorAll('.mg-carousel__slide'));
        this.dotsContainer = this.carousel.querySelector('.mg-mega-carousel-dots-container');
        this.currentIndex = 0;
        this.autoPlayInterval = null;

        // Initialize immediately
        this.initDots();
        this.addEventListeners();
        this.startAutoPlay();
        
        // Show first slide immediately
        if (this.slides.length > 0) {
            this.slides[0].classList.add('mg-active');
        }
    }

    initDots() {
        if (!this.dotsContainer) return;
        
        // Clear existing dots
        this.dotsContainer.innerHTML = '';
        
        // Create new dots
        this.slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('mg-mega-carousel-dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => this.goToSlide(index));
            this.dotsContainer.appendChild(dot);
        });
    }

    updateDots() {
        if (!this.dotsContainer) return;
        
        const dots = this.dotsContainer.querySelectorAll('.mg-mega-carousel-dot');
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === this.currentIndex);
        });
    }

    goToSlide(index) {
        // Validate index
        if (index < 0) index = this.slides.length - 1;
        if (index >= this.slides.length) index = 0;
        if (index === this.currentIndex) return;
        
        // Update classes
        this.slides[this.currentIndex].classList.remove('mg-active');
        this.slides[index].classList.add('mg-active');
        
        // Update state
        this.currentIndex = index;
        this.updateDots();
        this.resetAutoPlay();
    }

    addEventListeners() {
        // Previous button
        const prevArrow = this.carousel.querySelector('.mgwpp__prev-mega-slider');
        if (prevArrow) {
            prevArrow.addEventListener('click', (e) => {
                e.preventDefault();
                this.prevSlide();
            });
        }
        
        // Next button
        const nextArrow = this.carousel.querySelector('.mgwpp__next-mega-slider');
        if (nextArrow) {
            nextArrow.addEventListener('click', (e) => {
                e.preventDefault();
                this.nextSlide();
            });
        }
        
        // Touch/swipe support
        let touchStartX = 0;
        
        this.carousel.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
        }, { passive: true });
        
        this.carousel.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > 50) { // Minimum swipe distance
                diff > 0 ? this.nextSlide() : this.prevSlide();
            }
        }, { passive: true });
    }

    nextSlide() {
        this.goToSlide(this.currentIndex + 1);
    }

    prevSlide() {
        this.goToSlide(this.currentIndex - 1);
    }

    startAutoPlay() {
        const autoplay = this.carousel.dataset.autoplay === 'true';
        if (!autoplay) return;
        
        const delay = parseInt(this.carousel.dataset.autoplayDelay) || 3000;
        this.autoPlayInterval = setInterval(() => this.nextSlide(), delay);
    }

    resetAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.startAutoPlay();
        }
    }
}

// Initialize all mega carousels on the page
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.mg-mega-carousel').forEach(carousel => {
        new MegaCarousel(carousel);
    });
});

// Lazy loading for first image
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.lazy-first').forEach(img => {
        img.src = img.dataset.src;
        img.classList.remove('lazy-first');
    });
});