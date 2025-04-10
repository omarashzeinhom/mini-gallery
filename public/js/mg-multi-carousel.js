document.addEventListener("DOMContentLoaded", function () {
    // For each multi-carousel element.
    var carousels = document.querySelectorAll(".mg-gallery.multi-carousel");

    carousels.forEach(function (carousel) {
        var autoRotateSpeed = parseInt(carousel.dataset.autoRotate, 10) || 3000;
        var defaultImagesPerPage = parseInt(carousel.dataset.imagesPerPage, 10) || 6;
        // If the carousel has the 'cards' class, we do not adjust width via JS.
        var isCardsMode = carousel.classList.contains('cards');
        var slidesWrapper = carousel.querySelector(".slides-wrapper");
        var slides = Array.from(slidesWrapper.querySelectorAll(".mg-multi-carousel-slide"));
        var imagesPerPage = defaultImagesPerPage;
        var slideWidth = 0;
        var currentIndex = 0;
        var isAnimating = false;
        var startX = 0;
        var currentTranslate = 0;
        var prevTranslate = 0;
        var dragging = false;
        
        // Setup the carousel: clone slides for infinite effect and adjust sizes.
        function setupCarousel() {
            updateImagesPerPage();
            if (!isCardsMode) {
                slideWidth = carousel.clientWidth / imagesPerPage;
                slides.forEach(function (slide) {
                    slide.style.width = slideWidth + "px";
                });
            }
            
            // Remove old clones.
            var clones = slidesWrapper.querySelectorAll(".clone");
            clones.forEach(function(clone) {
                clone.remove();
            });
            // Get a fresh list of slides.
            slides = Array.from(slidesWrapper.querySelectorAll(".mg-multi-carousel-slide"));
            
            // For an infinite loop, clone last imagesPerPage slides at beginning...
            var prependSlides = slides.slice(-imagesPerPage).map(function(slide) {
                var clone = slide.cloneNode(true);
                clone.classList.add("clone");
                return clone;
            });
            prependSlides.forEach(function(clone) {
                slidesWrapper.insertBefore(clone, slidesWrapper.firstChild);
            });
            
            // ...and clone first imagesPerPage slides at end.
            var appendSlides = slides.slice(0, imagesPerPage).map(function(slide) {
                var clone = slide.cloneNode(true);
                clone.classList.add("clone");
                return clone;
            });
            appendSlides.forEach(function(clone) {
                slidesWrapper.appendChild(clone);
            });
            
            // Update slide list and set wrapper width.
            slides = Array.from(slidesWrapper.querySelectorAll(".mg-multi-carousel-slide"));
            if (!isCardsMode) {
                slidesWrapper.style.width = slides.length * slideWidth + "px";
            }
            // Set initial index to first real slide.
            currentIndex = imagesPerPage;
            setTransition(false);
            setTranslate(-currentIndex * (isCardsMode ? 350 : slideWidth));
        }

        function updateImagesPerPage() {
            if (!isCardsMode) {
                if (window.innerWidth < 768) {
                    imagesPerPage = 2;
                } else {
                    imagesPerPage = defaultImagesPerPage;
                }
            }
        }
        
        function setTransition(enable) {
            slidesWrapper.style.transition = enable ? "transform 0.5s ease" : "none";
        }
        
        function setTranslate(translateValue) {
            currentTranslate = translateValue;
            slidesWrapper.style.transform = "translateX(" + translateValue + "px)";
        }
        
        // Auto-rotate function.
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
        
        // Drag and touch support.
        slidesWrapper.addEventListener("mousedown", dragStart);
        slidesWrapper.addEventListener("touchstart", dragStart, { passive: true });
        window.addEventListener("mouseup", dragEnd);
        window.addEventListener("touchend", dragEnd);
        window.addEventListener("mousemove", dragAction);
        window.addEventListener("touchmove", dragAction, { passive: true });
        
        function dragStart(event) {
            if (isAnimating) return;
            dragging = true;
            startX = getPositionX(event);
            prevTranslate = currentTranslate;
            setTransition(false);
        }
        
        function dragAction(event) {
            if (!dragging) return;
            var currentPosition = getPositionX(event);
            var diff = currentPosition - startX;
            setTranslate(prevTranslate + diff);
        }
        
        function dragEnd() {
            if (!dragging) return;
            dragging = false;
            var movedBy = currentTranslate - prevTranslate;
            if (movedBy < -((isCardsMode ? 350 : slideWidth) / 4)) {
                nextSlide();
            } else if (movedBy > ((isCardsMode ? 350 : slideWidth) / 4)) {
                prevSlide();
            } else {
                setTransition(true);
                setTranslate(-currentIndex * (isCardsMode ? 350 : slideWidth));
            }
        }
        
        function getPositionX(event) {
            return event.type.includes("mouse") 
                ? event.clientX 
                : event.touches[0].clientX;
        }
        
        var autoRotateInterval = setInterval(autoRotate, autoRotateSpeed);
        
        carousel.addEventListener("mouseenter", function () {
            clearInterval(autoRotateInterval);
        });
        carousel.addEventListener("mouseleave", function () {
            autoRotateInterval = setInterval(autoRotate, autoRotateSpeed);
        });
        
        window.addEventListener("resize", function () {
            setupCarousel();
        });
        
        setupCarousel();
    });
});
