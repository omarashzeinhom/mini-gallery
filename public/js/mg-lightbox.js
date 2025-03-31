document.addEventListener('DOMContentLoaded', function () {
    const lightbox = document.getElementById('mgwpp-lightbox');
    if (!lightbox) return; // Exit if lightbox is missing

    const body = document.body;
    const items = Array.from(document.querySelectorAll('.mgwpp-gallery-item'));
    let currentIndex = 0;

    function updateBodyScroll(state) {
        body.classList[state ? 'add' : 'remove']('lightbox-open');
    }

    function openLightbox(index, albumTitle) {
        if (!items[index]) return;
        currentIndex = index;
        const item = items[index];
        const imgContainer = lightbox.querySelector('.mgwpp-lightbox-image-container');
        const caption = item.dataset.caption || "";
        const overlay = lightbox.querySelector('.mgwpp-lightbox-overlay');

        // Clear the previous image and add the new one dynamically
        imgContainer.innerHTML = '';  // Clear any existing content
        const img = document.createElement('img');
        img.src = item.href;  // Use the full image URL from the gallery item
        img.alt = item.querySelector('img')?.alt || '';  // Add alt text from the thumbnail if available
        img.classList.add('mgwpp-lightbox-image');
        imgContainer.appendChild(img);

        // Set the caption and album title
        lightbox.querySelector('.mgwpp-lightbox-caption').textContent = caption;
        overlay.textContent = albumTitle;

        lightbox.classList.add('active');
        updateBodyScroll(true);
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        updateBodyScroll(false);
    }

    function navigate(direction) {
        currentIndex = (currentIndex + direction + items.length) % items.length;
        openLightbox(currentIndex); // Open next or previous image
    }

    // Event delegation for gallery item click
    document.addEventListener('click', function (e) {
        const item = e.target.closest('.mgwpp-gallery-item');
        if (item) {
            e.preventDefault(); // Prevent the default anchor link behavior
            const albumTitle = item.closest('.mgwpp-gallery-container').querySelector('.mgwpp-gallery-title').textContent;
            openLightbox(items.indexOf(item), albumTitle); // Open the lightbox with the clicked image
        }

        // Close lightbox when clicking close button or outside the lightbox
        if (e.target.matches('.mgwpp-close, #mgwpp-lightbox')) {
            closeLightbox();
        }
    });

    // Navigation buttons for previous and next
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('mgwpp-prev')) navigate(-1);
        if (e.target.classList.contains('mgwpp-next')) navigate(1);
    });

    // Keyboard navigation (Esc to close, arrows to navigate)
    document.addEventListener('keydown', function (e) {
        if (lightbox.classList.contains('active')) {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') navigate(-1);
            if (e.key === 'ArrowRight') navigate(1);
        }
    });
});
