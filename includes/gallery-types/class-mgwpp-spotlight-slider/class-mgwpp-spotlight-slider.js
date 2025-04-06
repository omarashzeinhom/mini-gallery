// mgwpp-spotlight-slider.js
document.addEventListener('DOMContentLoaded', () => {
    // Spotlight Effect
    document.addEventListener('mousemove', (e) => {
        document.documentElement.style.setProperty('--mgwpp-x', `${e.clientX}px`);
        document.documentElement.style.setProperty('--mgwpp-y', `${e.clientY}px`);
    });

    // slider Functionality
    const sliderSlides = document.querySelectorAll('.mgwpp-slider-slide');
    const navButtons = document.querySelectorAll('.mgwpp-nav-btn');
    let currentSlideIndex = 0;

    function activateSlide(index) {
        // Remove active classes
        sliderSlides[currentSlideIndex].classList.remove('mgwpp-active');
        navButtons[currentSlideIndex].classList.remove('mgwpp-active');

        // Add active classes to new slide
        sliderSlides[index].classList.add('mgwpp-active');
        navButtons[index].classList.add('mgwpp-active');

        // Update current index
        currentSlideIndex = index;
    }

    navButtons.forEach((button, index) => {
        button.addEventListener('click', () => activateSlide(index));
    });

    // Auto-advance
    setInterval(() => {
        const nextIndex = (currentSlideIndex + 1) % sliderSlides.length;
        activateSlide(nextIndex);
    }, 8000);



    // Swipe support
    let touchStartX = 0;
    let touchEndX = 0;

    function handleGesture() {
        if (touchEndX < touchStartX - 50) {
            const next = (currentSlideIndex + 1) % sliderSlides.length;
            activateSlide(next);
        }
        if (touchEndX > touchStartX + 50) {
            const prev = (currentSlideIndex - 1 + sliderSlides.length) % sliderSlides.length;
            activateSlide(prev);
        }
    }

    const sliderViewport = document.querySelector('.mgwpp-slider-viewport');
    sliderViewport.addEventListener('touchstart', e => touchStartX = e.changedTouches[0].screenX);
    sliderViewport.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleGesture();
    });

    let startX = 0;
    let isDragging = false;

    // Desktop: Mouse drag
    sliderViewport.addEventListener('mousedown', (e) => {
        isDragging = true;
        startX = e.clientX;
    });
    sliderViewport.addEventListener('mouseup', (e) => {
        if (!isDragging) return;
        isDragging = false;
        const deltaX = e.clientX - startX;
        if (deltaX > 50) {
            const prev = (currentSlideIndex - 1 + sliderSlides.length) % sliderSlides.length;
            activateSlide(prev);
        } else if (deltaX < -50) {
            const next = (currentSlideIndex + 1) % sliderSlides.length;
            activateSlide(next);
        }
    });
    sliderViewport.addEventListener('mouseleave', () => {
        isDragging = false; // cancel drag if user leaves viewport
    });

    // Optional: add visual feedback during drag
    sliderViewport.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        // You can optionally add a CSS class like `.dragging` here
    });

    sliderViewport.addEventListener('mousedown', () => {
        sliderViewport.classList.add('dragging');
    });
    sliderViewport.addEventListener('mouseup', () => {
        sliderViewport.classList.remove('dragging');
    });
    sliderViewport.addEventListener('mouseleave', () => {
        sliderViewport.classList.remove('dragging');
    });
    

});