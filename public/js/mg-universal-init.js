document.addEventListener('DOMContentLoaded', initMGGalleries);

if (typeof window.vc !== 'undefined') {
    window.vc.events.on('shortcodes:add', initMGGalleries);
    window.vc.events.on('shortcodes:update', initMGGalleries);
}

function initMGGalleries() {
    // Initialize Spotlight Carousel
    document.querySelectorAll('.mgwpp-spotlight-carousel').forEach(carousel => {
        // Your existing spotlight initialization code
        const carouselSlides = carousel.querySelectorAll('.mgwpp-carousel-slide');
        const navButtons = carousel.querySelectorAll('.mgwpp-nav-btn');
        let currentSlideIndex = 0;

        function activateSlide(index) {
            carouselSlides[currentSlideIndex].classList.remove('mgwpp-active');
            navButtons[currentSlideIndex].classList.remove('mgwpp-active');
            carouselSlides[index].classList.add('mgwpp-active');
            navButtons[index].classList.add('mgwpp-active');
            currentSlideIndex = index;
        }

        navButtons.forEach((button, index) => {
            button.addEventListener('click', () => activateSlide(index));
        });

        setInterval(() => {
            const nextIndex = (currentSlideIndex + 1) % carouselSlides.length;
            activateSlide(nextIndex);
        }, 8000);
    });

    // Add initialization for other gallery types here
}