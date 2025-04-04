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



// Carousel for Multi Gallery
document.addEventListener("DOMContentLoaded", function () {
    var multiCarousels = document.querySelectorAll(".mg-gallery.multi-carousel");

    multiCarousels.forEach(function (carousel) {
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
            slides.forEach(function (slide, index) {
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
        window.addEventListener('resize', function () {
            updateImagesPerPage();
            showSlides();
        });
    });
});



document.addEventListener("DOMContentLoaded", function () {
    const toggleCheckbox = document.getElementById("mode-toggle-checkbox");
    const body = document.body;

    // Check for saved theme in localStorage
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark") {
        body.setAttribute("data-theme", "dark");
        toggleCheckbox.checked = true;
    }

    // Toggle theme on checkbox change
    toggleCheckbox?.addEventListener("change", function () {
        if (this.checked) {
            body.setAttribute("data-theme", "dark");
            localStorage.setItem("theme", "dark");
        } else {
            body.removeAttribute("data-theme");
            localStorage.setItem("theme", "light");
        }
    });
});


function toggleDashboardTheme() {
    const dashboardEl = document.getElementById('dashboard-stats');
    const moonIcon = document.getElementById('theme-icon-moon');
    const sunIcon = document.getElementById('theme-icon-sun');
    
    if (dashboardEl.classList.contains('theme-light')) {
      dashboardEl.classList.remove('theme-light');
      dashboardEl.classList.add('theme-dark');
      moonIcon.classList.add('hidden');
      sunIcon.classList.remove('hidden');
      localStorage.setItem('dashboard-theme', 'dark');
    } else {
      dashboardEl.classList.remove('theme-dark');
      dashboardEl.classList.add('theme-light');
      moonIcon.classList.remove('hidden');
      sunIcon.classList.add('hidden');
      localStorage.setItem('dashboard-theme', 'light');
    }
  }
  
  // Check for saved theme preference
  document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('dashboard-theme');
    const dashboardEl = document.getElementById('dashboard-stats');
    const moonIcon = document.getElementById('theme-icon-moon');
    const sunIcon = document.getElementById('theme-icon-sun');
    
    if (savedTheme === 'dark' || 
        (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      dashboardEl.classList.remove('theme-light');
      dashboardEl.classList.add('theme-dark');
      moonIcon.classList.add('hidden');
      sunIcon.classList.remove('hidden');
    }
  });




  
// JavaScript (admin/js/testimonial-admin.js)
jQuery(document).ready(function($) {
    $('#mgwpp_upload_image').click(function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Author Photo',
            multiple: false
        }).open().on('select', function() {
            var uploaded_image = image.state().get('selection').first();
            $('#mgwpp_image_id').val(uploaded_image.id);
            $('#mgwpp_image_preview').html('<img src="'+uploaded_image.attributes.url+'" style="max-width:200px;">');
            $('#mgwpp_remove_image').show();
        });
    });

    $('#mgwpp_remove_image').click(function(e) {
        e.preventDefault();
        $('#mgwpp_image_id').val('');
        $('#mgwpp_image_preview').html('');
        $(this).hide();
    });
});
