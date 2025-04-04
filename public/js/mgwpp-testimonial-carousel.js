(function($) {
  $(document).ready(() => {
      $(".mgwpp-testimonial-carousel").each(function() {
          const $carousel = $(this);
          const $inner = $carousel.find(".mgwpp-carousel-inner");
          const $items = $carousel.find(".mgwpp-carousel-item");
          let currentIndex = 0;
          let itemsPerView = getItemsPerView();

          function getItemsPerView() {
              const width = window.innerWidth;
              if (width >= 1024) return 3;
              if (width >= 768) return 2;
              return 1;
          }

          function updateCarousel() {
              const itemWidth = 100 / itemsPerView;
              const translateX = -currentIndex * itemWidth;
              $inner.css("transform", `translateX(${translateX}%)`);
              updateIndicators();
          }


          function updateIndicators() {
              const totalSlides = Math.ceil($items.length / itemsPerView);
              $carousel.find(".mgwpp-carousel-indicator").removeClass("active");
              $carousel.find(".mgwpp-carousel-indicator").eq(Math.floor(currentIndex / itemsPerView)).addClass("active");
          }

          function handleResize() {
              itemsPerView = getItemsPerView();
              updateCarousel();
          }

          // Initialize indicators
          const totalSlides = Math.ceil($items.length / itemsPerView);
          const $indicators = $('<div class="mgwpp-carousel-indicators"></div>');
          for (let i = 0; i < totalSlides; i++) {
              $indicators.append(`<button class="mgwpp-carousel-indicator" data-slide-to="${i}"></button>`);
          }
          $carousel.append($indicators);

          // Navigation handlers
          $carousel.on("click", ".mgwpp-carousel-next", () => {
              currentIndex = Math.min(currentIndex + itemsPerView, $items.length - itemsPerView);
              updateCarousel();
          });

          $carousel.on("click", ".mgwpp-carousel-prev", () => {
              currentIndex = Math.max(currentIndex - itemsPerView, 0);
              updateCarousel();
          });

          // Handle window resize
          $(window).on("resize", handleResize);
          handleResize(); // Initial calculation
      });
  });
})(jQuery);

