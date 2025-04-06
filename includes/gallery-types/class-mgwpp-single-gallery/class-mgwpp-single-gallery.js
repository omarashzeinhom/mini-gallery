// Carousel for Single Gallery
document.addEventListener("DOMContentLoaded", function () {
    var singleCarousels = document.querySelectorAll(".mg-gallery-single-carousel");

    singleCarousels.forEach(function (carousel) {
        var slides = carousel.querySelectorAll(".carousel-slide");
        var currentIndex = 0;

        function showSlide(index) {
            slides.forEach(function (slide) {
                slide.style.display = "none";
            });
            if (slides[index]) {
                slides[index].style.display = "block";
            }
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % (slides.length || 1);
            showSlide(currentIndex);
        }

        showSlide(currentIndex);
        setInterval(nextSlide, 3000); // Change slide every 3 seconds
    });
});
