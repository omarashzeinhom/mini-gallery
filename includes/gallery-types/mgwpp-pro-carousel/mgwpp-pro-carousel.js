document.addEventListener('DOMContentLoaded', () => {
    class MGWPPCarousel {
        constructor(element) {
            this.element = element;
            this.track = element.querySelector('.mgwpp-pro-carousel__track');
            this.cards = Array.from(element.querySelectorAll('.mgwpp-pro-carousel__card'));
            this.prevBtn = element.querySelector('.mgwpp-pro-carousel__nav--prev');
            this.nextBtn = element.querySelector('.mgwpp-pro-carousel__nav--next');
            
            this.currentIndex = 0;
            this.isDragging = false;
            this.startPos = 0;
            this.currentTranslate = 0;
            this.prevTranslate = 0;
            this.animationID = 0;

            this.init();
        }

        init() {
            this.calculateDimensions();
            this.setupEventListeners();
            this.updateNavigation();
        }

        calculateDimensions() {
            const firstCard = this.cards[0];
            if (!firstCard) return;
            
            const styles = getComputedStyle(this.element);
            this.cardWidth = firstCard.offsetWidth;
            this.gap = parseInt(styles.getPropertyValue('--mgwpp-pro-carousel-gap'));
        }

        updateNavigation() {
            this.prevBtn.disabled = this.currentIndex === 0;
            this.nextBtn.disabled = this.currentIndex >= this.cards.length - this.visibleCards();
        }

        visibleCards() {
            return Math.floor(this.element.offsetWidth / (this.cardWidth + this.gap));
        }

        getPositionX(event) {
            return event.type.includes('touch') ? event.touches[0].clientX : event.clientX;
        }

        setTransform(position) {
            this.track.style.transform = `translateX(${position}px)`;
        }

        animation() {
            this.setTransform(this.currentTranslate);
            if (this.isDragging) requestAnimationFrame(() => this.animation());
        }

        handleTouchStart(e) {
            this.isDragging = true;
            this.startPos = this.getPositionX(e);
            this.prevTranslate = this.currentTranslate;
            this.track.style.transition = 'none';
            this.animation();
        }

        handleTouchMove(e) {
            if (!this.isDragging) return;
            const currentPos = this.getPositionX(e);
            this.currentTranslate = this.prevTranslate + currentPos - this.startPos;
        }

        handleTouchEnd() {
            this.isDragging = false;
            this.track.style.transition = '';
            
            const movedBy = this.currentTranslate - this.prevTranslate;
            const cardWithGap = this.cardWidth + this.gap;
            
            if (Math.abs(movedBy) > cardWithGap * 0.25) {
                this.currentIndex += movedBy > 0 ? -1 : 1;
            }
            
            this.currentTranslate = -this.currentIndex * cardWithGap;
            this.setTransform(this.currentTranslate);
            this.updateNavigation();
        }

        setupEventListeners() {
            // Touch events
            this.track.addEventListener('touchstart', (e) => this.handleTouchStart(e));
            this.track.addEventListener('touchmove', (e) => this.handleTouchMove(e));
            this.track.addEventListener('touchend', () => this.handleTouchEnd());
            
            // Mouse events
            this.track.addEventListener('mousedown', (e) => this.handleTouchStart(e));
            this.track.addEventListener('mousemove', (e) => this.handleTouchMove(e));
            this.track.addEventListener('mouseup', () => this.handleTouchEnd());
            this.track.addEventListener('mouseleave', () => this.handleTouchEnd());

            // Navigation buttons
            this.prevBtn?.addEventListener('click', () => {
                this.currentIndex = Math.max(this.currentIndex - 1, 0);
                this.currentTranslate = -this.currentIndex * (this.cardWidth + this.gap);
                this.setTransform(this.currentTranslate);
                this.updateNavigation();
            });

            this.nextBtn?.addEventListener('click', () => {
                this.currentIndex = Math.min(this.currentIndex + 1, this.cards.length - 1);
                this.currentTranslate = -this.currentIndex * (this.cardWidth + this.gap);
                this.setTransform(this.currentTranslate);
                this.updateNavigation();
            });

            // Resize handler
            window.addEventListener('resize', () => {
                this.calculateDimensions();
                this.currentTranslate = -this.currentIndex * (this.cardWidth + this.gap);
                this.setTransform(this.currentTranslate);
            });
        }
    }

    // Initialize all carousels
    document.querySelectorAll('.mgwpp-pro-carousel').forEach(carousel => {
        new MGWPPCarousel(carousel);
    });
});