// mgwpp-spotlight-carousel.js
document.addEventListener('DOMContentLoaded', () => {
    // Spotlight Effect
    document.addEventListener('mousemove', (e) => {
        document.documentElement.style.setProperty('--mgwpp-x', `${e.clientX}px`);
        document.documentElement.style.setProperty('--mgwpp-y', `${e.clientY}px`);
    });

    // Carousel Functionality
    const carouselSlides = document.querySelectorAll('.mgwpp-carousel-slide');
    const navButtons = document.querySelectorAll('.mgwpp-nav-btn');
    let currentSlideIndex = 0;

function activateSlide(index)
{
    // Remove active classes
    carouselSlides[currentSlideIndex].classList.remove('mgwpp-active');
    navButtons[currentSlideIndex].classList.remove('mgwpp-active');

    //  active classes to new slide
    carouselSlides[index].classList.add('mgwpp-active');
    navButtons[index].classList.add('mgwpp-active');

    // Update current index
    currentSlideIndex = index;
}

    navButtons.forEach((button, index) => {
        button.addEventListener('click', () => activateSlide(index));
    });

    // Auto-advance
    setInterval(() => {
        const nextIndex = (currentSlideIndex + 1) % carouselSlides.length;
        activateSlide(nextIndex);
    }, 8000);



    // Swipe support
    let touchStartX = 0;
    let touchEndX = 0;

function handleGesture()
{
    if (touchEndX < touchStartX - 50) {
        const next = (currentSlideIndex + 1) % carouselSlides.length;
        activateSlide(next);
    }
    if (touchEndX > touchStartX + 50) {
        const prev = (currentSlideIndex - 1 + carouselSlides.length) % carouselSlides.length;
        activateSlide(prev);
    }
}

    const carouselViewport = document.querySelector('.mgwpp-carousel-viewport');
    carouselViewport.addEventListener('touchstart', e => touchStartX = e.changedTouches[0].screenX);
    carouselViewport.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleGesture();
    });

    let startX = 0;
    let isDragging = false;

    // Desktop: Mouse drag
    carouselViewport.addEventListener('mousedown', (e) => {
        isDragging = true;
        startX = e.clientX;
    });
    carouselViewport.addEventListener('mouseup', (e) => {
        if (!isDragging) {
            return;
        }
        isDragging = false;
        const deltaX = e.clientX - startX;
        if (deltaX > 50) {
            const prev = (currentSlideIndex - 1 + carouselSlides.length) % carouselSlides.length;
            activateSlide(prev);
        } else if (deltaX < -50) {
            const next = (currentSlideIndex + 1) % carouselSlides.length;
            activateSlide(next);
        }
    });
    carouselViewport.addEventListener('mouseleave', () => {
        isDragging = false; // cancel drag if user leaves viewport
    });

    // Optional: add visual feedback during drag
    carouselViewport.addEventListener('mousemove', (e) => {
        if (!isDragging) {
            return;
        }
        // You can optionally add a CSS class like `.dragging` here
    });

    carouselViewport.addEventListener('mousedown', () => {
        carouselViewport.classList.add('dragging');
    });
    carouselViewport.addEventListener('mouseup', () => {
        carouselViewport.classList.remove('dragging');
    });
    carouselViewport.addEventListener('mouseleave', () => {
        carouselViewport.classList.remove('dragging');
    });
    

});