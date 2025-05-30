class MGWPPSlideEditor {
    constructor() {
        this.currentSlide = 0;
        this.slides = [this.createNewSlide()];
        this.initEvents();
    }
    
    createNewSlide() {
        return {
            id: Date.now(),
            background: {
                type: 'color', // 'image' or 'color'
                color: '#ffffff',
                image: null
            },
            elements: [],
            animation: {
                in: 'fade',
                out: 'fade',
                duration: 500
            }
        };
    }
    
    initEvents() {
        // Slide navigation
        jQuery('.mgwpp-add-slide').on('click', () => this.addSlide());
        jQuery('.mgwpp-remove-slide').on('click', () => this.removeSlide());
        
        // Background controls
        jQuery('.mgwpp-bg-type-select').on('change', (e) => {
            this.slides[this.currentSlide].background.type = e.target.value;
            this.renderBackgroundControls();
        });
        
        // Element editing
        jQuery(document).on('click', '.mgwpp-add-element', (e) => {
            const type = jQuery(e.target).data('type');
            this.addElement(type);
        });
    }
    
    addSlide() {
        if (this.slides.length >= 30) return;
        this.slides.push(this.createNewSlide());
        this.currentSlide = this.slides.length - 1;
        this.renderSlide();
    }
    
    addElement(type) {
        const newElement = {
            id: 'el_' + Date.now(),
            type: type,
            content: type === 'button' ? 'Click Me' : 'New Text',
            position: { x: 50, y: 50 },
            size: { width: 200, height: type === 'button' ? 50 : 100 },
            animation: { type: 'fade', delay: 0 }
        };
        this.slides[this.currentSlide].elements.push(newElement);
        this.renderElements();
    }
    
    renderSlide() {
        // Update UI to show current slide
        jQuery('.mgwpp-current-slide').text(this.currentSlide + 1);
        jQuery('.mgwpp-total-slides').text(this.slides.length);
        
        // Render background
        this.renderBackground();
        
        // Render elements
        this.renderElements();
    }
    
    renderBackground() {
        const slide = this.slides[this.currentSlide];
        const canvas = jQuery('#mgwpp-canvas');
        
        if (slide.background.type === 'image' && slide.background.image) {
            canvas.css('background-image', `url(${slide.background.image})`);
        } else {
            canvas.css('background-color', slide.background.color);
        }
    }
    
    // ... Additional methods for saving/loading ...
}