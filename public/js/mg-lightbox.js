document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('mgwpp-lightbox');
    if (!lightbox) return;

    const items = Array.from(document.querySelectorAll('.mgwpp-gallery-item'));
    let currentIndex = 0;

    function openLightbox(index) {
        if (!items[index]) return;
        
        const item = items[index];
        const imgContainer = lightbox.querySelector('.mgwpp-lightbox-image-container');
        const caption = item.dataset.caption || '';
        const albumTitle = item.closest('.mgwpp-gallery-container')?.querySelector('.mgwpp-gallery-title')?.textContent || '';

        imgContainer.innerHTML = '';
        const img = document.createElement('img');
        img.src = item.href;
        img.alt = item.querySelector('img')?.alt || '';
        img.classList.add('mgwpp-lightbox-image');
        imgContainer.appendChild(img);

        lightbox.querySelector('.mgwpp-lightbox-caption').textContent = caption;
        lightbox.querySelector('.mgwpp-lightbox-overlay').textContent = albumTitle;
        
        lightbox.classList.add('active');
        document.body.classList.add('lightbox-open');
        currentIndex = index;
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.classList.remove('lightbox-open');
    }

    function navigate(direction) {
        const newIndex = (currentIndex + direction + items.length) % items.length;
        openLightbox(newIndex);
    }

    // Event listeners
    document.addEventListener('click', function(e) {
        const item = e.target.closest('.mgwpp-gallery-item');
        if (item) {
            e.preventDefault();
            openLightbox(items.indexOf(item));
        }

        if (e.target.classList.contains('mgwpp-close') || e.target === lightbox) {
            closeLightbox();
        }
        
        if (e.target.classList.contains('mgwpp-prev')) navigate(-1);
        if (e.target.classList.contains('mgwpp-next')) navigate(1);
    });

    document.addEventListener('keydown', function(e) {
        if (!lightbox.classList.contains('active')) return;
        
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') navigate(-1);
        if (e.key === 'ArrowRight') navigate(1);
    });
});