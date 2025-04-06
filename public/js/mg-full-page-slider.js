class MGWPP_FullPageSlider {
  constructor(container) {
    this.container = container;
    this.slides = Array.from(container.querySelectorAll('.mg-fullpage-slide'));
    this.currentIndex = 0;
    this.isAnimating = false;
    this.autoplayInterval = null;
    this.touchStartX = 0;
    this.touchEndX = 0;


    // Initialize after images load
    this.waitForImages().then(() => this.init());
  }
  waitForImages() {
    return Promise.all(
      this.slides.map(slide => {
        const img = slide.querySelector('img');
        if (img.complete) return Promise.resolve();
        return new Promise(resolve => {
          img.addEventListener('load', resolve);
          img.addEventListener('error', resolve);
        });
      })
    );
  }
  init() {
    this.createDots();
    this.activateSlide(this.currentIndex);
    this.addEventListeners();
    this.startAutoplay(5000);
    this.addKeyboardNavigation();
    this.addResizeListener();
  }

  activateSlide(index) {
    if (this.isAnimating) return;
    this.isAnimating = true;

    this.slides.forEach(slide => {
      slide.classList.remove('mg-active');
      slide.style.transform = '';
    });

    const direction = index > this.currentIndex ? 1 : -1;
    this.slides[index].style.transform = `translateX(${direction * 100}%)`;

    requestAnimationFrame(() => {
      this.slides[this.currentIndex].style.transform = `translateX(${-direction * 100}%)`;
      this.slides[index].style.transform = 'translateX(0)';
      this.slides[index].classList.add('mg-active');

      setTimeout(() => {
        this.slides[this.currentIndex].style.transform = '';
        this.currentIndex = index;
        this.isAnimating = false;
        this.updateDots(index);
      }, 1000);
    });
  }

  createDots() {
    this.dotsContainer = document.createElement('div');
    this.dotsContainer.className = 'mg-fullpage-slider-dots';

    this.slides.forEach((_, i) => {
      const dot = document.createElement('button');
      dot.className = `mg-full-page-slider-dot ${i === 0 ? 'active' : ''}`;
      dot.addEventListener('click', () => this.goToSlide(i));
      dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
      this.dotsContainer.appendChild(dot);
    });

    document.body.appendChild(this.dotsContainer);
  }

  updateDots(index) {
    if (!this.dotsContainer) return;
    this.dotsContainer.querySelectorAll('.mg-full-page-slider-dot').forEach((dot, i) => {
      dot.classList.toggle('active', i === index);
    });
    
  }

  goToSlide(index) {
    if (index < 0) index = this.slides.length - 1;
    if (index >= this.slides.length) index = 0;
    this.activateSlide(index);
  }

  startAutoplay(delay) {
    this.autoplayInterval = setInterval(() => {
      if (!document.hidden) this.goToSlide(this.currentIndex + 1);
    }, delay);
  }

  handleSwipe() {
    const threshold = 50;
    const swipeDistance = this.touchStartX - this.touchEndX;

    if (Math.abs(swipeDistance) > threshold) {
      swipeDistance > 0
        ? this.goToSlide(this.currentIndex + 1)
        : this.goToSlide(this.currentIndex - 1);
    }
  }

  addEventListeners() {
    // Mouse events
    document.querySelector('.mg-prev').addEventListener('click', () => this.goToSlide(this.currentIndex - 1));
    document.querySelector('.mg-next').addEventListener('click', () => this.goToSlide(this.currentIndex + 1));

    // Touch events
    this.container.addEventListener('touchstart', e => {
      this.touchStartX = e.changedTouches[0].screenX;
    });

    this.container.addEventListener('touchend', e => {
      this.touchEndX = e.changedTouches[0].screenX;
      this.handleSwipe();
    });

    // Pause on interaction
    this.container.addEventListener('mouseenter', () => clearInterval(this.autoplayInterval));
    this.container.addEventListener('mouseleave', () => this.startAutoplay(5000));
  }

  addKeyboardNavigation() {
    document.addEventListener('keydown', e => {
      switch (e.key) {
        case 'ArrowLeft':
          this.goToSlide(this.currentIndex - 1);
          break;
        case 'ArrowRight':
          this.goToSlide(this.currentIndex + 1);
          break;
      }
    });
  }

  addResizeListener() {
    window.addEventListener('resize', () => {
      this.slides.forEach(slide => {
        slide.style.transform = '';
      });
    });
  }

  destroy() {
    clearInterval(this.autoplayInterval);
    this.dotsContainer.remove();
    // Remove event listeners as needed
  }
}

// Initialize sliders
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.mg-fullpage-viewport').forEach(container => {
    new MGWPP_FullPageSlider(container);
  });
});