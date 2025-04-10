class MGGridGallery {
    constructor() {
        this.gallery = document.querySelector('.mg-gallery-container');
        this.grid = this.gallery?.querySelector('.mg-grid-container');
        this.filters = this.gallery?.querySelectorAll('.mg-filter-btn');
        this.images = [];
        this.lightbox = null;
        this.currentIndex = 0;
        
        if (this.grid) {
            this.init();
        }
    }

    init() {
        // Store initial images
        this.images = Array.from(this.grid.querySelectorAll('.mg-grid-item'));
        
        // Set up event listeners
        this.filters?.forEach(btn => {
            btn.addEventListener('click', () => this.handleFilter(btn));
        });
        
        this.grid?.addEventListener('click', e => {
            const item = e.target.closest('.mg-grid-item');
            if (item) this.handleItemClick(item);
        });
        
        this.setupLightbox();
    }

    handleFilter(btn) {
        // Update active state
        this.filters?.forEach(f => f.classList.remove('active'));
        btn.classList.add('active');
        
        // Get filter category
        const category = btn.dataset.category;
        this.grid.classList.add('loading');
        
        // Simulate filtering delay
        setTimeout(() => {
            this.images?.forEach(item => {
                const show = category === 'all' || 
                           item.dataset.categories.includes(category);
                item.style.display = show ? 'block' : 'none';
            });
            this.grid.classList.remove('loading');
        }, 300);
    }

    handleItemClick(item) {
        this.currentIndex = Array.from(this.grid.children)
                               .filter(el => el.style.display !== 'none')
                               .indexOf(item);
        
        this.images = Array.from(this.grid.children)
                         .filter(el => el.style.display !== 'none')
                         .map(el => ({
                            src: el.querySelector('img').dataset.full,
                            caption: el.querySelector('.mg-image-caption')?.innerText || ''
                         }));
        
        this.openLightbox();
    }

    setupLightbox() {
        this.lightbox = document.createElement('div');
        this.lightbox.className = 'mg-lightbox';
        this.lightbox.innerHTML = `
            <div class="mg-lightbox-content">
                <span class="mg-close">&times;</span>
                <img class="mg-lightbox-image" src="" alt="">
                <div class="mg-lightbox-caption"></div>
                <button class="mg-prev">‹</button>
                <button class="mg-next">›</button>
            </div>
        `;

        document.body.appendChild(this.lightbox);

        // Event listeners
        this.lightbox.querySelector('.mg-close').addEventListener('click', () => this.closeLightbox());
        this.lightbox.querySelector('.mg-prev').addEventListener('click', () => this.navigate(-1));
        this.lightbox.querySelector('.mg-next').addEventListener('click', () => this.navigate(1));
        document.addEventListener('keydown', (e) => this.handleKeyboard(e));
    }

    openLightbox() {
        this.lightbox.style.display = 'flex';
        this.updateLightboxContent();
    }

    closeLightbox() {
        this.lightbox.style.display = 'none';
    }

    navigate(direction) {
        this.currentIndex = (this.currentIndex + direction + this.images.length) % this.images.length;
        this.updateLightboxContent();
    }

    updateLightboxContent() {
        const currentImage = this.images[this.currentIndex];
        const imgElement = this.lightbox.querySelector('.mg-lightbox-image');
        const captionElement = this.lightbox.querySelector('.mg-lightbox-caption');

        imgElement.src = currentImage.src;
        captionElement.textContent = currentImage.caption;
    }

    handleKeyboard(e) {
        if (this.lightbox.style.display === 'flex') {
            switch(e.key) {
                case 'ArrowLeft':
                    this.navigate(-1);
                    break;
                case 'ArrowRight':
                    this.navigate(1);
                    break;
                case 'Escape':
                    this.closeLightbox();
                    break;
            }
        }
    }
}

// Initialize the gallery
document.addEventListener('DOMContentLoaded', () => {
    new MGGridGallery();
});