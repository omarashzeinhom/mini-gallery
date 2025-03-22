// Mega Carousel
class NeonSlider {
    constructor(container) {
        this.slider = container;
        this.slides = Array.from(this.slider.querySelectorAll(".neon-slide")) || [];
        this.dotsContainer = this.slider.querySelector(".dots-container");
        this.currentIndex = 0;
        this.touchStartX = 0;
        this.touchEndX = 0;
        this.autoPlayInterval = null;

        // Add 'active' class to the first slide immediately on page load
        this.slides[0]?.classList?.add("active");
        this.slides[0]?.classList?.add("first-neon-slide"); // Prevent transition delay for first load

        // Force a reflow to apply the styles immediately
        this.slides[0]?.offsetHeight; // Accessing height triggers a reflow

        // Remove the 'first-slide' class after a tiny delay to avoid animation delay
        setTimeout(() => {
            this.slides[0]?.classList?.remove("first-neon-slide");
        }, 10); // Short delay after applying 'active'

        this.initDots();
        this.addEventListeners();
        this.startAutoPlay();

        // Add event listener to the preview images
        this.previewImages = this.slider.querySelectorAll(".neon-preview-images img");
        this.previewImages?.forEach((img, index) => {
            img?.addEventListener("click", () => {
                this.changeSlide(index);
            });
        });
    }
    // Function to initialize the dots for navigation
    initDots() {
        this.slides?.forEach((_, index) => {
            const dot = document.createElement("div");
            dot.classList.add("dot");
            if (index === 0) dot.classList.add("active");
            dot?.addEventListener("click", () => this.goToSlide(index));
            this.dotsContainer?.appendChild(dot);
        });
    }

    // Adding event listeners for touch and mouse events
    addEventListeners() {
        this.slider?.addEventListener("touchstart", (e) => this.handleTouchStart(e));
        this.slider?.addEventListener("touchmove", (e) => this.handleTouchMove(e));
        this.slider?.addEventListener("touchend", () => this.handleTouchEnd());

        this.slider?.addEventListener("mousedown", (e) => this.handleMouseStart(e));
        this.slider?.addEventListener("mousemove", (e) => this.handleMouseMove(e));
        this.slider?.addEventListener("mouseup", () => this.handleMouseEnd());
        this.slider?.addEventListener("mouseleave", () => this.handleMouseEnd());
    }

    // Function to handle touch start
    handleTouchStart(e) {
        this.touchStartX = e?.touches?.[0]?.clientX || 0;
        this.resetAutoPlay();
    }

    // Function to handle touch move
    handleTouchMove(e) {
        this.touchEndX = e?.touches?.[0]?.clientX || 0;
    }

    // Function to handle touch end and determine if swipe occurred
    handleTouchEnd() {
        this.handleGesture();
    }

    // Function to handle mouse start
    handleMouseStart(e) {
        this.touchStartX = e?.clientX || 0;
        this.resetAutoPlay();
    }

    // Function to handle mouse move
    handleMouseMove(e) {
        this.touchEndX = e?.clientX || 0;
    }

    // Function to handle mouse end and determine if swipe occurred
    handleMouseEnd() {
        this.handleGesture();
    }

    // Function to handle gestures (left/right swipe)
    handleGesture() {
        if (Math.abs(this.touchEndX - this.touchStartX) < 30) return;

        if (this.touchEndX < this.touchStartX) {
            this.nextSlide();
        } else {
            this.prevSlide();
        }
    }

    // Function to update the navigation dots
    updateDots() {
        const dots = Array.from(this.dotsContainer?.children || []);
        dots?.forEach((dot) => dot?.classList?.remove("active"));
        dots?.[this.currentIndex]?.classList?.add("active");
    }

    // Function to go to a specific slide
    goToSlide(index) {
        if (index === this.currentIndex) return;

        this.slides?.[this.currentIndex]?.classList?.remove("active");
        this.currentIndex = (index + this.slides?.length) % this.slides?.length;
        this.slides?.[this.currentIndex]?.classList?.add("active");
        this.updateDots();
        this.resetAutoPlay();
    }

    // Function to go to the next slide
    nextSlide() {
        this.goToSlide(this.currentIndex + 1);
    }

    // Function to go to the previous slide
    prevSlide() {
        this.goToSlide(this.currentIndex - 1);
    }

    // Start auto-playing the slides
    startAutoPlay() {
        this.autoPlayInterval = setInterval(() => this.nextSlide(), 5000);
    }

    // Reset auto-play after interaction
    resetAutoPlay() {
        clearInterval(this.autoPlayInterval);
        this.startAutoPlay();
    }

    // Change slide based on the preview image clicked
    changeSlide(index) {
        this.goToSlide(index);
    }





    
}
document.querySelectorAll('.neon-slider').forEach(slider => {
    new NeonSlider(slider);
});
// Add neon interaction to preview images
document.querySelectorAll(".neon-preview-images img")?.forEach((img) => {
    img?.addEventListener("mouseover", () => {
        img.style.transform = "scale(1.1)";
        img.style.filter = "drop-shadow(0 0 10px #00f3ff)";
    });

    img?.addEventListener("mouseout", () => {
        img.style.transform = "scale(1)";
        img.style.filter = "none";
    });
});

new NeonSlider();