jQuery(document).ready(function($) {
    $('.mgwpp-single-gallery').each(function() {
        const gallery = $(this);
        const slides = gallery.find('.carousel-slide');
        const counter = gallery.find('.image-counter');
        let currentIndex = 0;
        let startX = 0;
        let isDragging = false;
        const swipeThreshold = parseInt(gallery.data('swipe-threshold')) || 30;
        
        if (slides.length === 0) return;

        // Touch events for mobile.
        gallery.on('touchstart', function(e) {
            startX = e.originalEvent.touches[0].clientX;
            isDragging = true;
        });
        gallery.on('touchmove', function(e) {
            if (!isDragging) return;
            e.preventDefault();
        });
        gallery.on('touchend', function(e) {
            if (!isDragging) return;
            isDragging = false;
            const endX = e.originalEvent.changedTouches[0].clientX;
            handleSwipe(startX, endX);
        });

        // Mouse events for desktop drag.
        gallery.on('mousedown', function(e) {
            startX = e.clientX;
            isDragging = true;
            gallery.addClass('dragging');
        });
        gallery.on('mousemove', function(e) {
            if (!isDragging) return;
            e.preventDefault();
        });
        gallery.on('mouseup mouseleave', function(e) {
            if (!isDragging) return;
            isDragging = false;
            gallery.removeClass('dragging');
            const endX = e.clientX;
            handleSwipe(startX, endX);
        });
        
        function handleSwipe(start, end) {
            const deltaX = start - end;
            if (Math.abs(deltaX) > swipeThreshold) {
                updateSlide(deltaX > 0 ? 1 : -1);
            }
        }

        function updateSlide(direction) {
            slides.removeClass('active');
            currentIndex = (currentIndex + direction + slides.length) % slides.length;
            slides.eq(currentIndex).addClass('active');
            if(counter.length){
                counter.text(`${currentIndex + 1}/${slides.length}`);
            }
        }

        // Navigation buttons click events.
        gallery.find('.nav-prev').click(() => updateSlide(-1));
        gallery.find('.nav-next').click(() => updateSlide(1));

        // Auto-rotate
        let autoRotateSpeed = parseInt(gallery.data('auto-rotate')) || 5000;
        let interval = setInterval(() => updateSlide(1), autoRotateSpeed);
        gallery.hover(() => clearInterval(interval), () => {
            interval = setInterval(() => updateSlide(1), autoRotateSpeed);
        });
    });
});
