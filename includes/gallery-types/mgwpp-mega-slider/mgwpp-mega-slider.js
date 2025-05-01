// mega-carousel.js

class MegaCarousel {
    constructor() {
        this.carousel = document.querySelector('.mg-mega-carousel');
        if (!this.carousel) return;
        
        this.slides = Array.from(this.carousel.querySelectorAll('.mg-carousel__slide'));
        this.dotsContainer = this.carousel.querySelector('.mg-mega-carousel-dots-container');
        this.currentIndex = 0;
        this.autoPlayInterval = null;

        // Immediately show the first slide if it exists.
        if (this.slides[0]) {
            this.slides[0].style.opacity = '1';
            this.slides[0].style.transform = 'scale(1)';
        }
        
        // Enable transitions after a short delay.
        setTimeout(() => {
            this.carousel.classList.add('loaded');
        }, 50);
        
        this.initDots();
        this.addEventListeners();
        this.startAutoPlay();
    }

    initDots() {
        if (!this.dotsContainer) return;
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
        const dots = Array.from(this.dotsContainer.children);
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === this.currentIndex);
        });
    }

    goToSlide(index) {
        if (index === this.currentIndex || !this.slides.length) return;
        this.slides[this.currentIndex].classList.remove('mg-active');
        this.currentIndex = (index + this.slides.length) % this.slides.length;
        this.slides[this.currentIndex].classList.add('mg-active');
        this.updateDots();
        this.resetAutoPlay();
    }

    addEventListeners() {
        // Navigation arrows using new class names.
        const prevArrow = this.carousel.querySelector('.mgwpp__prev-mega-slider');
        const nextArrow = this.carousel.querySelector('.mgwpp__next-mega-slider');

        if (prevArrow) {
            prevArrow.addEventListener('click', () => this.prevSlide());
        }
        if (nextArrow) {
            nextArrow.addEventListener('click', () => this.nextSlide());
        }

        // Use pointer events for both mouse and touch interactions.
        let pointerStartX = 0;
        let isDragging = false;
        const swipeThreshold = 30; // Lower threshold for swipe

        this.carousel.addEventListener('pointerdown', e => {
            pointerStartX = e.clientX;
            isDragging = true;
            this.carousel.setPointerCapture(e.pointerId);
        });

        // Optional: add pointermove for live feedback during drag.
        this.carousel.addEventListener('pointermove', e => {
            if (!isDragging) return;
            // Optionally, add visual feedback during dragging.
        });

        const pointerUpHandler = e => {
            if (!isDragging) return;
            isDragging = false;
            const pointerEndX = e.clientX;
            const delta = pointerEndX - pointerStartX;
            if (Math.abs(delta) >= swipeThreshold) {
                delta < 0 ? this.nextSlide() : this.prevSlide();
            }
        };

        this.carousel.addEventListener('pointerup', pointerUpHandler);
        this.carousel.addEventListener('pointercancel', pointerUpHandler);
    }

    nextSlide() {
        this.goToSlide(this.currentIndex + 1);
    }

    prevSlide() {
        this.goToSlide(this.currentIndex - 1);
    }

    startAutoPlay() {
        const autoplayEnabled = this.carousel.getAttribute('data-autoplay') === 'true';
        if (autoplayEnabled) {
            this.autoPlayInterval = setInterval(
                () => this.nextSlide(),
                parseInt(this.carousel.getAttribute('data-autoplay-delay')) || 5000
            );
        }
    }

    resetAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.startAutoPlay();
        }
    }
}

// Initialize the carousel after the DOM is loaded.
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.mg-mega-carousel')) {
        new MegaCarousel();
    }
});

// Lazy-load the first image if necessary.
document.addEventListener('DOMContentLoaded', () => {
    const firstImage = document.querySelector('.lazy-first');
    if (firstImage) {
        firstImage.src = firstImage.getAttribute('data-src');
        firstImage.classList.remove('lazy-first');
    }
});
