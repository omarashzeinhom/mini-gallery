jQuery(document).ready(function ($) {
    $('.mgwpp-single-carousel').each(function () {
        const $carousel = $(this);
        const $slides = $carousel.find('.mgwpp-single-carousel__slide');
        const $counter = $carousel.find('.mgwpp-single-carousel__counter');
        let currentIndex = 0;
        let startX = 0;
        let isDragging = false;
        const swipeThreshold = $carousel.data('swipe-threshold') || 30;

        if (!$slides.length) {
            return;
        }
        if ($slides.length < 2) {
            $carousel.find('.mgwpp-single-carousel__controls').hide();
            return;
        }

        // bind pointer events (unified mouse+touch)
        $carousel.on('pointerdown', e => {
            e.preventDefault();
            startX = e.clientX;
            isDragging = true;
        });
        $carousel.on('pointerup pointerleave', e => {
            if (!isDragging) {
                return;
            }
            isDragging = false;
            handleSwipe(startX, e.clientX);
        });

        // nav buttons
        $carousel.find('.mgwpp-single-carousel__nav--prev').click(() => updateSlide(-1));
        $carousel.find('.mgwpp-single-carousel__nav--next').click(() => updateSlide(1));

        function handleSwipe(start, end)
        {
            const delta = start - end;
            if (Math.abs(delta) > swipeThreshold) {
                updateSlide(delta > 0 ? 1 : -1);
            }
        }

        function updateSlide(direction)
        {
            $slides.removeClass('mgwpp-single-carousel__slide--active');
            currentIndex = (currentIndex + direction + $slides.length) % $slides.length;
            $slides.eq(currentIndex).addClass('mgwpp-single-carousel__slide--active');
            $counter.text(`${currentIndex + 1}/${$slides.length}`);
        }

        // Auto‑rotation
        let rotationInterval;
        const autoRotateSpeed = $carousel.data('auto-rotate') || 3000;
        function startRotation()
        {
            clearInterval(rotationInterval);
            console.log('▶️ starting rotation, speed =', autoRotateSpeed);
            if (autoRotateSpeed > 0) {
                rotationInterval = setInterval(() => {
                    console.log('↪️ rotate');
                    updateSlide(1);
                }, autoRotateSpeed);
            }
        }


        // set initial state & kick things off
        updateSlide(0);
        $carousel.hover(
            () => clearInterval(rotationInterval),
            startRotation
        );
        startRotation();
    });
});
