
document.addEventListener('DOMContentLoaded', () => {
    // Spotlight Effect
    document.addEventListener('mousemove', (e) => {
        document.documentElement.style.setProperty('--x', `${e.clientX}px`);
        document.documentElement.style.setProperty('--y', `${e.clientY}px`);
    });

    // Carousel Functionality
    const slides = document.querySelectorAll('.carousel-slide');
    const navBtns = document.querySelectorAll('.nav-btn');
    let currentSlide = 0;

    function showSlide(index) {
        slides[currentSlide].classList.remove('active');
        navBtns[currentSlide].classList.remove('active');

        slides[index].classList.add('active');
        navBtns[index].classList.add('active');
        currentSlide = index;
    }

    navBtns.forEach((btn, index) => {
        btn.addEventListener('click', () => showSlide(index));
    });

    // Auto-advance
    setInterval(() => {
        const nextSlide = (currentSlide + 1) % slides.length;
        showSlide(nextSlide);
    }, 8000);
});
