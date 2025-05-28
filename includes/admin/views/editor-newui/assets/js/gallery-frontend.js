;(($) => {
  class MGGalleryFrontend {
    constructor() {
      this.init()
    }

    init() {
      this.initCarousels()
      this.initSliders()
      this.initMasonry()
      this.initLightbox()
    }

    initCarousels() {
      $(".mg-gallery-carousel").each(function () {
        const carousel = $(this)
        const container = carousel.find(".carousel-container")
        const slides = container.find(".carousel-slide")
        const prevBtn = carousel.find(".carousel-prev")
        const nextBtn = carousel.find(".carousel-next")

        let currentSlide = 0
        const totalSlides = slides.length

        function updateCarousel() {
          const translateX = -currentSlide * 100
          container.css("transform", `translateX(${translateX}%)`)
        }

        prevBtn.on("click", () => {
          currentSlide = currentSlide > 0 ? currentSlide - 1 : totalSlides - 1
          updateCarousel()
        })

        nextBtn.on("click", () => {
          currentSlide = currentSlide < totalSlides - 1 ? currentSlide + 1 : 0
          updateCarousel()
        })

        // Auto-play
        setInterval(() => {
          currentSlide = currentSlide < totalSlides - 1 ? currentSlide + 1 : 0
          updateCarousel()
        }, 5000)
      })
    }

    initSliders() {
      $(".mg-gallery-slider").each(function () {
        const slider = $(this)
        const slides = slider.find(".slider-slide")
        const dots = slider.find(".slider-dot")

        let currentSlide = 0

        function updateSlider() {
          slides.removeClass("active")
          dots.removeClass("active")

          slides.eq(currentSlide).addClass("active")
          dots.eq(currentSlide).addClass("active")
        }

        dots.on("click", function () {
          currentSlide = $(this).data("slide")
          updateSlider()
        })

        // Auto-play
        setInterval(() => {
          currentSlide = currentSlide < slides.length - 1 ? currentSlide + 1 : 0
          updateSlider()
        }, 4000)
      })
    }

    initMasonry() {
      if (typeof Masonry !== "undefined") {
        $(".mg-gallery-masonry").each(function () {
          new Masonry(this, {
            itemSelector: ".masonry-item",
            columnWidth: ".masonry-item",
            percentPosition: true,
          })
        })
      }
    }

    initLightbox() {
      $(".mg-gallery-item img").on("click", function () {
        const src = $(this).attr("src")
        const alt = $(this).attr("alt")

        const lightbox = $(`
                    <div class="mg-lightbox">
                        <div class="lightbox-overlay">
                            <div class="lightbox-content">
                                <img src="${src}" alt="${alt}">
                                <button class="lightbox-close">×</button>
                            </div>
                        </div>
                    </div>
                `)

        $("body").append(lightbox)

        lightbox.find(".lightbox-close, .lightbox-overlay").on("click", function (e) {
          if (e.target === this) {
            lightbox.remove()
          }
        })
      })
    }
  }

  // Initialize when document is ready
  $(document).ready(() => {
    new MGGalleryFrontend()
  })
})(jQuery)
