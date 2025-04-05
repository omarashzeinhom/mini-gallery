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

    function activateSlide(index) {
        // Remove active classes
        carouselSlides[currentSlideIndex].classList.remove('mgwpp-active');
        navButtons[currentSlideIndex].classList.remove('mgwpp-active');

        // Add active classes to new slide
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
});