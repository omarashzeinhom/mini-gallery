;(($) => {
    $(document).ready(() => {
      $(".mgwpp-testimonial-carousel").each(function () {
        const $carousel = $(this)
        const $inner = $carousel.find(".mgwpp-carousel-inner")
        const $items = $carousel.find(".mgwpp-carousel-item")
        const itemCount = $items.length
        let currentIndex = 0
  
        // Add indicators
        const $indicators = $('<div class="mgwpp-carousel-indicators"></div>')
        for (let i = 0; i < itemCount; i++) {
          const $indicator = $(
            '<button class="mgwpp-carousel-indicator" aria-label="Go to slide ' + (i + 1) + '"></button>',
          )
          if (i === 0) $indicator.addClass("active")
          $indicator.data("slide-to", i)
          $indicators.append($indicator)
        }
        $carousel.append($indicators)
  
        // Update indicators
        function updateIndicators() {
          $carousel.find(".mgwpp-carousel-indicator").removeClass("active")
          $carousel.find(".mgwpp-carousel-indicator").eq(currentIndex).addClass("active")
        }
  
        // Move to specific slide
        function goToSlide(index) {
          if (index < 0) index = itemCount - 1
          if (index >= itemCount) index = 0
  
          currentIndex = index
          $inner.css("transform", "translateX(" + -currentIndex * 100 + "%)")
          updateIndicators()
        }
  
        // Next slide
        function nextSlide() {
          goToSlide(currentIndex + 1)
        }
  
        // Previous slide
        function prevSlide() {
          goToSlide(currentIndex - 1)
        }
  
        // Click events
        $carousel.find(".mgwpp-carousel-next").on("click", () => {
          nextSlide()
          resetAutoplay()
        })
  
        $carousel.find(".mgwpp-carousel-prev").on("click", () => {
          prevSlide()
          resetAutoplay()
        })
  
        // Indicator clicks
        $carousel.on("click", ".mgwpp-carousel-indicator", function () {
          const index = $(this).data("slide-to")
          goToSlide(index)
          resetAutoplay()
        })
  
        // Autoplay
        let autoplayInterval
        const autoplay = $carousel.data("autoplay") === "yes"
        const interval = $carousel.data("interval") || 5000
  
        function startAutoplay() {
          if (autoplay && itemCount > 1) {
            autoplayInterval = setInterval(nextSlide, interval)
          }
        }
  
        function resetAutoplay() {
          if (autoplayInterval) {
            clearInterval(autoplayInterval)
            startAutoplay()
          }
        }
  
        // Touch support
        let touchStartX = 0
        let touchEndX = 0
  
        $carousel.on("touchstart", (e) => {
          touchStartX = e.originalEvent.touches[0].clientX
        })
  
        $carousel.on("touchend", (e) => {
          touchEndX = e.originalEvent.changedTouches[0].clientX
          handleSwipe()
        })
  
        function handleSwipe() {
          const swipeThreshold = 50
          if (touchEndX < touchStartX - swipeThreshold) {
            nextSlide()
            resetAutoplay()
          } else if (touchEndX > touchStartX + swipeThreshold) {
            prevSlide()
            resetAutoplay()
          }
        }
  
        // Check for dark mode
        function checkTheme() {
          const savedTheme = localStorage.getItem("dashboard-theme")
          if (savedTheme === "dark" || (!savedTheme && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
            $carousel.closest(".dashboard-stats, body").addClass("theme-dark")
          }
        }
  
        // Initialize
        startAutoplay()
        checkTheme()
  
        // Listen for theme changes
        $(document).on("themeChanged", (e, isDark) => {
          if (isDark) {
            $carousel.closest(".dashboard-stats, body").addClass("theme-dark")
          } else {
            $carousel.closest(".dashboard-stats, body").removeClass("theme-dark")
          }
        })
      })
    })
  })(jQuery)
  
  