document.addEventListener("DOMContentLoaded", function () {
    var carousels = document.querySelectorAll(".mgwpp-gallery-container.multi-carousel");
  
    carousels.forEach(function (carousel) {
      var autoRotateSpeed = parseInt(carousel.dataset.autoRotate, 10) || 3000;
      var defaultImagesPerPage = parseInt(carousel.dataset.imagesPerPage, 10) || 3;
      var isCardsMode = carousel.classList.contains('cards');
  
      var slidesWrapper = carousel.querySelector(".mgwpp-slides-wrapper");
      var slides = Array.from(slidesWrapper.querySelectorAll(".mgwpp-grid-item"));
  
      var imagesPerPage = defaultImagesPerPage;
      var slideWidth = 0;
      var currentIndex = 0;
      var isAnimating = false;
      var startX = 0;
      var currentTranslate = 0;
      var prevTranslate = 0;
      var dragging = false;
  
      function setupCarousel() {
        updateImagesPerPage();
        if (!isCardsMode) {
          slideWidth = carousel.clientWidth / imagesPerPage;
          slides.forEach(slide => slide.style.width = slideWidth + "px");
        }
  
        // Remove old clones
        slidesWrapper.querySelectorAll(".clone").forEach(clone => clone.remove());
  
        // Clone last and first imagesPerPage slides
        slides = Array.from(slidesWrapper.querySelectorAll(".mgwpp-grid-item"));
        slides.slice(-imagesPerPage).forEach(slide => {
          let clone = slide.cloneNode(true);
          clone.classList.add("clone");
          slidesWrapper.insertBefore(clone, slidesWrapper.firstChild);
        });
        slides.slice(0, imagesPerPage).forEach(slide => {
          let clone = slide.cloneNode(true);
          clone.classList.add("clone");
          slidesWrapper.appendChild(clone);
        });
  
        slides = Array.from(slidesWrapper.querySelectorAll(".mgwpp-grid-item"));
        if (!isCardsMode) {
          slidesWrapper.style.width = slides.length * slideWidth + "px";
        }
  
        currentIndex = imagesPerPage;
        setTransition(false);
        setTranslate(-currentIndex * (isCardsMode ? 350 : slideWidth));
      }
  
      function updateImagesPerPage() {
        imagesPerPage = (window.innerWidth < 768 && !isCardsMode) ? 2 : defaultImagesPerPage;
      }
  
      function setTransition(enable) {
        slidesWrapper.style.transition = enable ? "transform 0.5s ease" : "none";
      }
  
      function setTranslate(value) {
        currentTranslate = value;
        slidesWrapper.style.transform = `translateX(${value}px)`;
      }
  
      function autoRotate() {
        if (dragging) return;
        nextSlide();
      }
  
      function nextSlide() {
        if (isAnimating) return;
        isAnimating = true;
        currentIndex++;
        setTransition(true);
        setTranslate(-currentIndex * (isCardsMode ? 350 : slideWidth));
      }
  
      function prevSlide() {
        if (isAnimating) return;
        isAnimating = true;
        currentIndex--;
        setTransition(true);
        setTranslate(-currentIndex * (isCardsMode ? 350 : slideWidth));
      }
  
      slidesWrapper.addEventListener("transitionend", function () {
        isAnimating = false;
        if (currentIndex >= slides.length - imagesPerPage) {
          currentIndex = imagesPerPage;
          setTransition(false);
          setTranslate(-currentIndex * (isCardsMode ? 350 : slideWidth));
        }
        if (currentIndex < imagesPerPage) {
          currentIndex = slides.length - imagesPerPage * 2;
          setTransition(false);
          setTranslate(-currentIndex * (isCardsMode ? 350 : slideWidth));
        }
      });
  
      // Drag handlers
      slidesWrapper.addEventListener("mousedown", dragStart);
      slidesWrapper.addEventListener("touchstart", dragStart, { passive: true });
      window.addEventListener("mouseup", dragEnd);
      window.addEventListener("touchend", dragEnd);
      window.addEventListener("mousemove", dragAction);
      window.addEventListener("touchmove", dragAction, { passive: true });
  
      function dragStart(e) {
        if (isAnimating) return;
        dragging = true;
        startX = getPositionX(e);
        prevTranslate = currentTranslate;
        setTransition(false);
      }
  
      function dragAction(e) {
        if (!dragging) return;
        let currentPosition = getPositionX(e);
        let diff = currentPosition - startX;
        setTranslate(prevTranslate + diff);
      }
  
      function dragEnd() {
        if (!dragging) return;
        dragging = false;
        let movedBy = currentTranslate - prevTranslate;
        let threshold = (isCardsMode ? 350 : slideWidth) / 4;
        if (movedBy < -threshold) nextSlide();
        else if (movedBy > threshold) prevSlide();
        else {
          setTransition(true);
          setTranslate(-currentIndex * (isCardsMode ? 350 : slideWidth));
        }
      }
  
      function getPositionX(e) {
        return e.type.includes("mouse") ? e.clientX : e.touches[0].clientX;
      }
  
      // Autoplay + pause on hover
      let autoRotateInterval = setInterval(autoRotate, autoRotateSpeed);
      carousel.addEventListener("mouseenter", () => clearInterval(autoRotateInterval));
      carousel.addEventListener("mouseleave", () => autoRotateInterval = setInterval(autoRotate, autoRotateSpeed));
  
      window.addEventListener("resize", setupCarousel);
      setupCarousel();
    });
  });
  