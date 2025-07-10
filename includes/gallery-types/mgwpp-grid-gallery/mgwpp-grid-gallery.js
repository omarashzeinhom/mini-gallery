
document.addEventListener("DOMContentLoaded", function () {
    const layoutBtns = document.querySelectorAll(".mgwpp-layout-btn");
    const gridContainer = document.querySelector(".mgwpp-grid-container");
    const carouselContainer = gridContainer.closest(".mgwpp-gallery-container");

    let currentLayout = "grid";
    let intervalRef;

  // Carousel functionality
    function initCarousel()
    {
        const wrapper = gridContainer;
        let slides = Array.from(wrapper.querySelectorAll(".mgwpp-grid-item"));
        let currentIndex = 0;
        let isDragging = false;
        let startX = 0, currentTranslate = 0, prevTranslate = 0, isAnimating = false;
        const slideWidth = wrapper.clientWidth / 3;

      // Remove old clones
        wrapper.querySelectorAll(".clone").forEach(clone => clone.remove());

      // Clone start/end
        slides.slice(-3).forEach(slide => {
            const clone = slide.cloneNode(true);
            clone.classList.add("clone");
            wrapper.insertBefore(clone, wrapper.firstChild);
        });
        slides.slice(0, 3).forEach(slide => {
            const clone = slide.cloneNode(true);
            clone.classList.add("clone");
            wrapper.appendChild(clone);
        });

        slides = Array.from(wrapper.querySelectorAll(".mgwpp-grid-item"));
        wrapper.style.transition = "none";
        wrapper.style.transform = `translateX(-${slideWidth * 3}px)`;
        currentIndex = 3;

        function moveToIndex(index)
        {
            wrapper.style.transition = "transform 0.5s ease";
            wrapper.style.transform = `translateX(-${index * slideWidth}px)`;
        }

        function nextSlide()
        {
            if (isAnimating) {
                return;
            }
            isAnimating = true;
            currentIndex++;
            moveToIndex(currentIndex);
        }

        function prevSlide()
        {
            if (isAnimating) {
                return;
            }
            isAnimating = true;
            currentIndex--;
            moveToIndex(currentIndex);
        }

        wrapper.addEventListener("transitionend", () => {
            isAnimating = false;
            if (currentIndex >= slides.length - 3) {
                currentIndex = 3;
                wrapper.style.transition = "none";
                wrapper.style.transform = `translateX(-${slideWidth * currentIndex}px)`;
            }
            if (currentIndex < 3) {
                currentIndex = slides.length - 6;
                wrapper.style.transition = "none";
                wrapper.style.transform = `translateX(-${slideWidth * currentIndex}px)`;
            }
        });

        function dragStart(e)
        {
            if (isAnimating) {
                return;
            }
            isDragging = true;
            startX = e.type.includes("mouse") ? e.clientX : e.touches[0].clientX;
            prevTranslate = currentTranslate;
            wrapper.style.transition = "none";
        }

        function dragMove(e)
        {
            if (!isDragging) {
                return;
            }
            const x = e.type.includes("mouse") ? e.clientX : e.touches[0].clientX;
            const diff = x - startX;
            currentTranslate = prevTranslate + diff;
            wrapper.style.transform = `translateX(${currentTranslate}px)`;
        }

        function dragEnd()
        {
            if (!isDragging) {
                return;
            }
            isDragging = false;
            const movedBy = currentTranslate - prevTranslate;
            if (movedBy < -slideWidth / 4) {
                nextSlide();
            } else if (movedBy > slideWidth / 4) {
                prevSlide();
            } else {
                moveToIndex(currentIndex);
            }
        }

        wrapper.addEventListener("mousedown", dragStart);
        wrapper.addEventListener("touchstart", dragStart, { passive: true });
        window.addEventListener("mousemove", dragMove);
        window.addEventListener("touchmove", dragMove, { passive: true });
        window.addEventListener("mouseup", dragEnd);
        window.addEventListener("touchend", dragEnd);

      // Autoplay
        intervalRef = setInterval(() => {
            if (!isDragging && currentLayout !== "masonry") {
                nextSlide();
            }
        }, 3000);

        carouselContainer.addEventListener("mouseenter", () => clearInterval(intervalRef));
        carouselContainer.addEventListener("mouseleave", () => {
            if (currentLayout !== "masonry") {
                intervalRef = setInterval(() => nextSlide(), 3000);
            }
        });
    }

    function destroyCarousel()
    {
        clearInterval(intervalRef);
        const wrapper = gridContainer;
        wrapper.style.transition = "none";
        wrapper.style.transform = "none";
        wrapper.querySelectorAll(".clone").forEach(clone => clone.remove());
        wrapper.replaceWith(wrapper.cloneNode(true)); // reset event listeners
    }

    layoutBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            layoutBtns.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            currentLayout = btn.getAttribute("data-layout");
            gridContainer.setAttribute("data-layout", currentLayout);

            destroyCarousel();
            if (currentLayout !== "masonry") {
                setTimeout(() => initCarousel(), 100); // allow DOM update
            }
        });
    });

  // Init default layout
    if (currentLayout !== "masonry") {
        initCarousel();
    }
});

