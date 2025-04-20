class NeonSlider {
    constructor(container)
    {
        this.slider = container;
        this.isElementor = container.closest('.elementor-widget-mg_neon_carousel');

        // Initialize elements
        this.slides = Array.from(this.slider.querySelectorAll(".neon-slide")) || [];
        this.dotsContainer = this.createDotsContainer();
        this.currentIndex = 0;
        this.touchStartX = 0;
        this.touchEndX = 0;
        this.autoPlayInterval = null;

        // Initial setup
        this.initializeFirstSlide();
        this.initDots();

        // Conditional features
        if (this.shouldShowPreviews()) {
            this.initPreviews();
        }

        this.addEventListeners();
        this.startAutoPlay();
    }

    createDotsContainer()
    {
        const container = this.slider.querySelector('.neon-dots-container') || document.createElement('div');
        if (!container.parentElement) {
            container.className = 'neon-dots-container';
            this.slider.appendChild(container);
        }
        return container;
    }

    initDots()
    {
        // Clear existing dots
        this.dotsContainer.innerHTML = '';

        this.slides?.forEach(
            (_, index) => {
            const dot = document.createElement("div");
            dot.className = "neon-dot";
            if (index === 0) {
                dot.classList.add("active");
            }
            // Click handler using arrow function
                dot.addEventListener("click", () => this.goToSlide(index));
                this.dotsContainer.appendChild(dot);
            }
        );
    }

    initPreviews()
    {
        this.previewImages = this.slider.querySelectorAll(".neon-preview-images img");
        this.previewImages?.forEach(
            (img, index) => {
                img?.addEventListener("click", () => this.changeSlide(index));
                img?.addEventListener(
                "mouseover",
                () => {
                        img.style.transform = "scale(1.1)";
                        img.style.filter = "drop-shadow(0 0 10px var(--neon-primary, #00f3ff))";
                }
            );
            img?.addEventListener(
                "mouseout",
                () => {
                    img.style.transform = "scale(1)";
                    img.style.filter = "none";
                }
            );
            }
        );
    }

    initializeFirstSlide()
    {
        if (this.slides[0]) {
            this.slides[0].classList.add("active", "first-neon-slide");
            void this.slides[0].offsetHeight; // Trigger reflow
            setTimeout(() => this.slides[0].classList.remove("first-neon-slide"), 10);
        }
    }

    shouldShowPreviews()
    {
        try {
            const settings = JSON.parse(this.slider.parentElement.dataset.settings || '{}');
            return settings.show_previews !== false;
        } catch {
            return true; // Default to show previews
        }
    }

    // EVENT HANDLERS
    addEventListeners()
    {
        const events = {
            touchstart: (e) => this.handleTouchStart(e),
            touchmove: (e) => this.handleTouchMove(e),
            touchend: () => this.handleTouchEnd(),
            mousedown: (e) => this.handleMouseStart(e),
            mousemove: (e) => this.handleMouseMove(e),
            mouseup: () => this.handleMouseEnd(),
            mouseleave: () => this.handleMouseEnd()
        };

        Object.entries(events).forEach(
            ([event, handler]) => {
                this.slider?.addEventListener(event, handler);
            }
        );
    }

    handleTouchStart(e)
    {
        this.touchStartX = e?.touches?.[0]?.clientX || 0;
        this.resetAutoPlay();
    }

    handleTouchMove(e)
    {
        this.touchEndX = e?.touches?.[0]?.clientX || 0;
    }

    handleTouchEnd()
    {
        this.handleGesture();
    }

    handleMouseStart(e)
    {
        this.touchStartX = e?.clientX || 0;
        this.resetAutoPlay();
    }

    handleMouseMove(e)
    {
        this.touchEndX = e?.clientX || 0;
    }

    handleMouseEnd()
    {
        this.handleGesture();
    }

    handleGesture()
    {
        if (Math.abs(this.touchEndX - this.touchStartX) < 30) {
            return;
        }
        this.touchEndX < this.touchStartX ? this.nextSlide() : this.prevSlide();
    }

    updateDots()
    {
        const dots = Array.from(this.dotsContainer.children || []);
        dots.forEach(dot => dot.classList.remove("active"));
        dots[this.currentIndex]?.classList.add("active");
    }

    goToSlide(index)
    {
        if (index === this.currentIndex) {
            return;
        }
        this.slides[this.currentIndex]?.classList.remove("active");
        this.currentIndex = (index + this.slides.length) % this.slides.length;
        this.slides[this.currentIndex]?.classList.add("active");
        this.updateDots();
        this.resetAutoPlay();
    }

    nextSlide()
    {
        this.goToSlide(this.currentIndex + 1);
    }

    prevSlide()
    {
        this.goToSlide(this.currentIndex - 1);
    }

    startAutoPlay()
    {
        this.autoPlayInterval = setInterval(() => this.nextSlide(), 5000);
    }

    resetAutoPlay()
    {
        clearInterval(this.autoPlayInterval);
        this.startAutoPlay();
    }

    changeSlide(index)
    {
        this.goToSlide(index);
    }
}

// Initialize for Elementor
jQuery(window).on(
    'elementor/frontend/init',
    () => {
        elementorFrontend.hooks.addAction(
        'frontend/element_ready/mg_neon_carousel.default',
        ($scope) => {
                const slider = $scope.find('.neon-slider')[0];
                slider && new NeonSlider(slider);
        }
    );
    }
);

// Initialize for non-Elementor (shortcode)
document.querySelectorAll('.neon-slider:not(.elementor-element .neon-slider)').forEach(
    slider => {
        new NeonSlider(slider);
    }
);