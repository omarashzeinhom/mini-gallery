(function($){
    'use strict';
    $(document).ready(function(){
        $('.mgwpp-testimonial-carousel').each(function(){
            var $carousel = $(this);
            var $inner = $carousel.find('.mgwpp-carousel-inner');
            var $items = $inner.find('.mgwpp-carousel-item');
            var totalItems = $items.length;
            var currentIndex = 0;
            var autoplay = $carousel.data('autoplay') === 'yes';
            var intervalTime = parseInt($carousel.data('interval'), 10) || 5000;
            var autoPlayInterval;

            function updateCarousel(){
                var translateX = -currentIndex * 100;
                $inner.css('transform', 'translateX(' + translateX + '%)');
            }

            $carousel.find('.mgwpp-carousel-prev').on('click', function(){
                currentIndex = (currentIndex > 0) ? currentIndex - 1 : totalItems - 1;
                updateCarousel();
                resetAutoplay();
            });

            $carousel.find('.mgwpp-carousel-next').on('click', function(){
                currentIndex = (currentIndex < totalItems - 1) ? currentIndex + 1 : 0;
                updateCarousel();
                resetAutoplay();
            });

            function startAutoplay(){
                if(autoplay){
                    autoPlayInterval = setInterval(function(){
                        currentIndex = (currentIndex < totalItems - 1) ? currentIndex + 1 : 0;
                        updateCarousel();
                    }, intervalTime);
                }
            }

            function resetAutoplay(){
                if(autoplay){
                    clearInterval(autoPlayInterval);
                    startAutoplay();
                }
            }

            startAutoplay();
        });
    });
})(jQuery);
