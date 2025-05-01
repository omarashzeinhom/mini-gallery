document.addEventListener('DOMContentLoaded', () => {
    class MGWPPCarousel {
        constructor(element) {
            this.element = element;
            this.container = element.querySelector('.mgwpp-pro-carousel__container');
            this.track = element.querySelector('.mgwpp-pro-carousel__track');
            this.cards = Array.from(element.querySelectorAll('.mgwpp-pro-carousel__card'));
            this.prevBtn = element.querySelector('.mgwpp-pro-carousel__nav--prev');
            this.nextBtn = element.querySelector('.mgwpp-pro-carousel__nav--next');

            this.currentIndex = 0;
            this.isDragging = false;
            this.startPos = 0;
            this.currentTranslate = 0;
            this.prevTranslate = 0;
            this.cardWidth = 0;
            this.gap = 0;
            this.visibleCardCount = 0;

            // Clone slides for infinite effect
            this.originalCards = [...this.cards];
            this.cloneSlides();
            this.init();
        }

        cloneSlides() {
            // Clone first and last few slides
            const clonesStart = this.originalCards.slice(-this.visibleCardCount).map(card => {
                const clone = card.cloneNode(true);
                clone.classList.add('clone');
                return clone;
            });

            const clonesEnd = this.originalCards.slice(0, this.visibleCardCount).map(card => {
                const clone = card.cloneNode(true);
                clone.classList.add('clone');
                return clone;
            });

            this.track.prepend(...clonesStart);
            this.track.append(...clonesEnd);
            this.cards = Array.from(this.track.querySelectorAll('.mgwpp-pro-carousel__card'));
        }

        init() {
            this.calculateDimensions();
            this.setupEventListeners();
            this.jumpToStart();
        }

        calculateDimensions() {
            const firstCard = this.originalCards[0];
            if (!firstCard) return;

            const styles = getComputedStyle(this.element);
            this.cardWidth = firstCard.offsetWidth;
            this.gap = parseInt(styles.getPropertyValue('--mgwpp-pro-carousel-gap')) || 0;
            this.visibleCardCount = Math.floor(this.element.offsetWidth / (this.cardWidth + this.gap));
        }

        jumpToStart() {
            this.currentIndex = this.visibleCardCount;
            this.setTransform(-this.currentIndex * (this.cardWidth + this.gap), false);
        }

        setTransform(position, smooth = true) {
            this.track.style.transition = smooth ? 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)' : 'none';
            this.track.style.transform = `translateX(${position}px)`;
            this.currentTranslate = position;
        }

        handleTouchStart(e) {
            this.isDragging = true;
            this.startPos = this.getPositionX(e);
            this.prevTranslate = this.currentTranslate;
            this.setTransform(this.currentTranslate, false);
        }

        handleTouchMove(e) {
            if (!this.isDragging) return;
            const currentPos = this.getPositionX(e);
            this.currentTranslate = this.prevTranslate + currentPos - this.startPos;
            this.track.style.transform = `translateX(${this.currentTranslate}px)`;
        }

        handleTouchEnd() {
            this.isDragging = false;
            const movedBy = this.currentTranslate - this.prevTranslate;
            const threshold = (this.cardWidth + this.gap) * 0.25;

            if (Math.abs(movedBy) > threshold) {
                this.currentIndex += movedBy > 0 ? -1 : 1;
            }

            this.navigateTo(this.currentIndex);
        }

        navigateTo(index) {
            this.currentIndex = index;
            const targetPosition = -this.currentIndex * (this.cardWidth + this.gap);
            this.setTransform(targetPosition);
        }

        checkBoundary() {
            const totalSlides = this.originalCards.length;
            
            if (this.currentIndex <= 0) {
                this.currentIndex = totalSlides;
                this.setTransform(-this.currentIndex * (this.cardWidth + this.gap), false);
            }
            else if (this.currentIndex >= totalSlides + this.visibleCardCount) {
                this.currentIndex = this.visibleCardCount;
                this.setTransform(-this.currentIndex * (this.cardWidth + this.gap), false);
            }
        }

        setupEventListeners() {
            // Touch/Mouse events
            const handleStart = e => this.handleTouchStart(e);
            const handleMove = e => this.handleTouchMove(e);
            const handleEnd = () => this.handleTouchEnd();

            this.track.addEventListener('touchstart', handleStart, { passive: true });
            this.track.addEventListener('touchmove', handleMove, { passive: false });
            this.track.addEventListener('touchend', handleEnd);
            this.track.addEventListener('mousedown', handleStart);
            this.track.addEventListener('mousemove', handleMove);
            this.track.addEventListener('mouseup', handleEnd);
            this.track.addEventListener('mouseleave', handleEnd);

            // Navigation buttons
            this.prevBtn?.addEventListener('click', () => {
                this.currentIndex--;
                this.navigateTo(this.currentIndex);
            });

            this.nextBtn?.addEventListener('click', () => {
                this.currentIndex++;
                this.navigateTo(this.currentIndex);
            });

            // Handle infinite loop
            this.track.addEventListener('transitionend', () => this.checkBoundary());

            // Resize handler
            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    this.calculateDimensions();
                    this.navigateTo(this.currentIndex);
                }, 200);
            });
        }

        getPositionX(event) {
            return event.type.includes('touch') ? event.touches[0].clientX : event.clientX;
        }
    }

    // Initialize all carousels
    document.querySelectorAll('.mgwpp-pro-carousel').forEach(carousel => {
        new MGWPPCarousel(carousel);
    });
});