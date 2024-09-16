// Carousel for Single Gallery
document.addEventListener("DOMContentLoaded", function() {
    var singleCarousels = document.querySelectorAll(".mg-gallery-single-carousel");

    singleCarousels.forEach(function(carousel) {
        var slides = carousel.querySelectorAll(".carousel-slide");
        var currentIndex = 0;

        function showSlide(index) {
            slides.forEach(function(slide) {
                slide.style.display = "none";
            });
            slides[index].style.display = "block";
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % slides.length;
            showSlide(currentIndex);
        }

        showSlide(currentIndex);
        setInterval(nextSlide, 3000); // Change slide every 3 seconds
    });
});

document.addEventListener("DOMContentLoaded", function() {
    var loading = false; // Flag to prevent multiple requests
    var page = 1; // Start with page 1

    // Function to load more items
    function loadMoreItems() {
        if (loading) return;
        loading = true;

        var xhr = new XMLHttpRequest();
        xhr.open("GET", "/path/to/your/endpoint?page=" + page, true);
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                // Append new items to the grid
                var newItems = xhr.responseText;
                var container = document.getElementById("grid-container");
                container.insertAdjacentHTML('beforeend', newItems);

                // Update loading state
                loading = false;
                page++;
            } else {
                // Handle error
                console.error("Error loading more items.");
                loading = false;
            }
        };
        xhr.send();
    }

    // Load more items when scrolling near the bottom
    window.addEventListener('scroll', function() {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 100) {
            loadMoreItems();
        }
    });

    // Initial load of items if needed
    loadMoreItems();
});

// Carousel for Multi Gallery
document.addEventListener("DOMContentLoaded", function() {
    var multiCarousels = document.querySelectorAll(".mg-gallery.multi-carousel");

    multiCarousels.forEach(function(carousel) {
        var slides = carousel.querySelectorAll(".mg-multi-carousel-slide");
        var currentIndex = 0;
        var imagesPerPage = 6; // Default number of images per page
        var visibleSlides = [];

        // Function to update the number of images per page based on screen width
        function updateImagesPerPage() {
            if (window.innerWidth < 768) {
                imagesPerPage = 2; // 2 images per page on mobile
            } else {
                imagesPerPage = 6; // 6 images per page otherwise
            }
        }

        // Function to show the current page of slides
        function showSlides() {
            var totalSlides = slides.length;
            slides.forEach(function(slide, index) {
                if (index >= currentIndex * imagesPerPage && index < (currentIndex + 1) * imagesPerPage) {
                    slide.style.display = "flex";
                } else {
                    slide.style.display = "none";
                }
            });
        }

        // Function to go to the next page of slides
        function nextSlide() {
            updateImagesPerPage();
            var totalSlides = slides.length;
            currentIndex = (currentIndex + 1) % Math.ceil(totalSlides / imagesPerPage);
            showSlides();
        }

        // Show the initial set of slides
        showSlides();

        // Set up an interval to automatically switch slides
        setInterval(nextSlide, 3000); // Change slide every 3 seconds

        
        // Handle window resize to adjust images per page
        window.addEventListener('resize', function() {
            updateImagesPerPage();
            showSlides();
        });
    });
});
