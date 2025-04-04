(function($) {
    $(document).ready(function() {
      const $carousel = $('.mgwpp-carousel-testimonials');
      const $track = $carousel.find('.mgwpp-carousel-testimonials-track');
      const $slides = $track.find('.mgwpp-carousel-testimonials-slide');
      const slideCount = $slides.length;
      let currentIndex = 0;
  
      function updateCarousel() {
        const slideWidth = $slides.first().outerWidth(true);
        $track.css('transform', 'translateX(' + (-currentIndex * slideWidth) + 'px)');
      }
  
      $carousel.find('.mgwpp-carousel-testimonials-next').on('click', function() {
        if (currentIndex < slideCount - 1) {
          currentIndex++;
          updateCarousel();
        }
      });
  
      $carousel.find('.mgwpp-carousel-testimonials-prev').on('click', function() {
        if (currentIndex > 0) {
          currentIndex--;
          updateCarousel();
        }
      });
  
      $(window).on('resize', updateCarousel);
    });
  })(jQuery);
  

  document.addEventListener("DOMContentLoaded", function () {
    // Dark Mode Toggle for the Testimonials Carousel
    const carousel = document.querySelector('.mgwpp-carousel-testimonials');
    // Check for saved theme in localStorage or system preference
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark" || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      carousel.classList.add("dark-mode");
    }
    
    // Basic Carousel Functionality
    const track = carousel.querySelector('.mgwpp-carousel-testimonials-track');
    const slides = track.querySelectorAll('.mgwpp-carousel-testimonials-slide');
    let currentIndex = 0;
    
    function updateCarousel() {
      const slideWidth = slides[0].offsetWidth;
      track.style.transform = `translateX(${-currentIndex * slideWidth}px)`;
    }
    
    // Next/Previous Buttons
    carousel.querySelector('.mgwpp-carousel-testimonials-next').addEventListener('click', function() {
      if (currentIndex < slides.length - 1) {
        currentIndex++;
        updateCarousel();
      }
    });
    
    carousel.querySelector('.mgwpp-carousel-testimonials-prev').addEventListener('click', function() {
      if (currentIndex > 0) {
        currentIndex--;
        updateCarousel();
      }
    });
    
    // Optional: Autoplay functionality
    setInterval(function() {
      currentIndex = (currentIndex + 1) % slides.length;
      updateCarousel();
    }, parseInt(carousel.getAttribute('data-interval'), 10) || 3000);
    
    // Update carousel on window resize
    window.addEventListener('resize', updateCarousel);
  });
  