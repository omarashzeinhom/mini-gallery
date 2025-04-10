jQuery(document).ready(function($) {
    $('.mgwpp-single-gallery').each(function() {
        const gallery = $(this);
        const slides = gallery.find('.carousel-slide');
        const counter = gallery.find('.image-counter');
        let currentIndex = 0;
        let startX = 0;
        let isDragging = false;
        const swipeThreshold = 30; // Minimum pixels to consider as swipe
        
        if (slides.length === 0) return;

        // Touch events
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

        // Mouse events
        gallery.on('mousedown', function(e) {
            startX = e.clientX;
            isDragging = true;
            gallery.addClass('dragging');
        });

        gallery.on('mousemove', function(e) {
            if (!isDragging) return;
            e.preventDefault();
        });

        gallery.on('mouseup', function(e) {
            if (!isDragging) return;
            isDragging = false;
            gallery.removeClass('dragging');
            const endX = e.clientX;
            handleSwipe(startX, endX);
        });

        function handleSwipe(start, end) {
            const deltaX = start - end;
            
            if (Math.abs(deltaX) > swipeThreshold) {
                if (deltaX > 0) {
                    updateSlide(1); // Swipe left
                } else {
                    updateSlide(-1); // Swipe right
                }
            }
        }

        function updateSlide(direction) {
            slides.removeClass('active');
            currentIndex = (currentIndex + direction + slides.length) % slides.length;
            slides.eq(currentIndex).addClass('active');
            counter.text(`${currentIndex + 1}/${slides.length}`);
        }

        // Original navigation and auto-rotate
        gallery.find('.nav-prev').click(() => updateSlide(-1));
        gallery.find('.nav-next').click(() => updateSlide(1));
        
        let interval = setInterval(() => updateSlide(1), 5000);
        gallery.hover(() => clearInterval(interval), () => {
            interval = setInterval(() => updateSlide(1), 5000);
        });
    });
});

function updateSlide(direction) {
    slides.removeClass('active');
    currentIndex = (currentIndex + direction + slides.length) % slides.length;
    
    // Add z-index management
    slides.css('z-index', 0);
    slides.eq(currentIndex)
        .addClass('active')
        .css('z-index', 1);
    
    counter.text(`${currentIndex + 1}/${slides.length}`);
}