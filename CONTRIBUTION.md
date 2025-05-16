# Mini Gallery Plugin Contribution Guide

## Core Directory Structure

```text
mini-gallery/
├── core/
│   ├── admin/                  # Admin panel functionality
│   │   ├── controllers/        # Admin controllers
│   │   │   ├── class-mgwpp-admin-core.php
│   │   │   ├── class-mgwpp-admin-menu.php
│   │   ├── models/             # Data handling
│   │   │   ├── class-mgwpp-data-handler.php
│   │   │   ├── class-mgwpp-data-manager.php
│   │   ├── views/              # Admin UI templates
│   │   │   ├── dashboard/      # Dashboard views
│   │   │   ├── galleries/      # Gallery management
│   │   │   ├── albums/         # Album management
│   │   │   │   └── class-mgwpp-albums-view.php
│   │   │   └── testimonials/   # Testimonial views
│   ├── registration/           # CPT & capability registration
│   │   ├── gallery/            # Gallery post type
│   │   │   ├── class-mgwpp-gallery-post-type.php
│   │   │   ├── class-mgwpp-gallery-capabilities.php
│   │   ├── album/              # Album post type
│   │   │   ├── class-mgwpp-album-post-type.php
│   │   │   ├── class-mgwpp-album-capabilities.php
│   │   └── testimonials/       # Testimonial post type
│   │       ├── class-mgwpp-testimonials-post-type.php
│   │       └── class-mgwpp-testimonials-capabilities.php
│   ├── integrations/           # Third-party integrations
│   │   ├── elementor/          # Elementor integration
│   │   └── vc/                 # WPBakery integration
│   ├── shortcodes/             # Shortcode handlers
│   ├── templates/              # Frontend templates
│   │   ├── single-mgwpp_soora.php  # Single gallery
│   │   └── single-mgwpp_album.php  # Single album
├── assets/
│   ├── css/                    # Compiled CSS
│   ├── js/                     # JavaScript files
│   └── images/                 # Plugin images
├── tests/                      # PHPUnit tests
├── vendor/                     # Composer dependencies
├── mini-gallery.php            # Main plugin bootstrap
└── uninstall.php               # Cleanup operations
```

### Key Architectural Components

#### 1. Admin Management (`core/admin`)

- **Controllers**: Main admin functionality entry points
- **Models**: Data handling and business logic
- **Views**: Admin UI templates organized by feature

#### 2. Post Type Registration (`core/registration`)

- Gallery/Album/Testimonial registration with:
  - Post type definitions
  - Custom capability management
  - Role permissions

#### 3. Integration Layer (`core/integrations`)

- Page builder integrations:
  - Elementor widget implementations
  - WPBakery modules
- Follows WordPress integration patterns

#### 4. Frontend System (`core/templates`)

- Custom template files for:
  - Single gallery display
  - Album views
  - Testimonial layouts
- Uses WordPress template hierarchy

##### Development Conventions

##### File Naming Standards

| Component Type       | Pattern                      | Example                      |
|----------------------|-----------------------------|------------------------------|
| Main Classes         | `class-mgwpp-[feature].php` | `class-mgwpp-gallery-manager.php` |
| Views                | `view-[feature].php`        | `view-album-editor.php`       |
| Assets               | `mgwpp-[feature].[ext]`     | `mgwpp-gallery-carousel.css`  |

### Code Structure

```php
// Typical class structure
class MGWPP_Feature_Manager {
    
    // Class constants at top
    const VERSION = '1.0';
    
    // Properties next
    protected $settings;
    
    // Constructor with dependency injection
    public function __construct(Settings_Interface $settings) {
        $this->settings = $settings;
    }
    
    // Public methods next
    public function init() {
        add_action('init', [$this, 'register_components']);
    }
    
    // Protected/private methods last
    protected function register_components() {
        // Implementation
    }
}
```

## Contribution Workflow

1. **Feature Branches**

   ```bash
   git checkout -b feature/gallery-zoom
   git push -u origin feature/gallery-zoom
   ```

2. **Code Validation**

   ```bash
   # PHPCS checks
   phpcs --standard=WordPress ./core

   # PHPStan static analysis
   phpstan analyse -l 6 ./core
   ```

3. **Testing Matrix**

   | Component         | PHP Versions | WordPress Versions |
   |-------------------|--------------|--------------------|
   | Gallery System    | 7.4+         | 5.9+               |
   | Album Management  | 7.4+         | 6.0+               |
   | Testimonial Module| 8.0+         | 6.2+               |

4. **Documentation Updates**
   - Update relevant `README` sections
   - Add/update PHPDoc blocks
   - Create architecture diagrams for complex features

## Security Practices

1. **Input Handling**

   ```php
   // Sanitization example
   $input = isset($_POST['title']) 
       ? sanitize_text_field(wp_unslash($_POST['title']))
       : '';

   // Validation example
   if (!wp_verify_nonce($_POST['nonce'], 'mgwpp_action')) {
       wp_die('Invalid nonce');
   }
   ```

2. **Capability Checks**

   ```php
   // Always check before privileged operations
   if (!current_user_can('edit_mgwpp_gallery', $gallery_id)) {
       return new WP_Error('unauthorized', 'Access denied');
   }
   ```

3. **Data Escaping**

   ```php
   // Frontend output
   <h2><?php echo esc_html($gallery_title); ?></h2>
   <div><?php echo wp_kses_post($gallery_content); ?></div>
   ```

This structure maintains your existing implementation while adding clear architectural guidelines and development patterns.

```

The reorganization focuses on:
1. Clear layer separation (Core/Assets/Integrations)
2. Standardized file naming conventions
3. Component lifecycle documentation
4. Integrated security practices
5. Version compatibility matrices


### Plugin Directory Structure

```text
Folder PATH listing
Volume serial number is FE09-EB2B
C:.
|   .gitignore
|   composer.json
|   composer.lock
|   CONTRIBUTION.md
|   directory-tree.txt
|   gulpfile.js
|   LICENSE.md
|   livereload.js
|   mini-gallery-new.php
|   mini-gallery.php
|   package.json
|   phpcs.xml
|   phpunit.xml
|   README.md
|   readme.txt
|   STRUC.md
|   style.css
|   uninstall.php
|   yarn.lock
|   
+---.vscode
|       settings.json
|       
+---includes
|   +---admin
|   |   |   ADMINDirectory.md
|   |   |   class-mgwpp-admin-assets.php
|   |   |   class-mgwpp-admin-core.php
|   |   |   class-mgwpp-admin-edit-gallery.php
|   |   |   class-mgwpp-admin-editors.php
|   |   |   class-mgwpp-admin-menu.php
|   |   |   class-mgwpp-admin-metaboxes.php
|   |   |   class-mgwpp-data-handler.php
|   |   |   class-mgwpp-data-manager.php
|   |   |   class-mgwpp-module-loader.php
|   |   |   class-mgwpp-table-builder.php
|   |   |   class-mgwpp_ajax_handler.php
|   |   |   
|   |   +---css
|   |   |       mg-admin-edit-dashboard-styles.css
|   |   |       mg-admin-styles.css
|   |   |       variables.css
|   |   |       
|   |   +---images
|   |   |   +---galleries-preview
|   |   |   |       3d-carousel.webp
|   |   |   |       full-page-slider.webp
|   |   |   |       grid.webp
|   |   |   |       mega-slider.webp
|   |   |   |       mgwpp-logo-panel.png
|   |   |   |       multi-carousel.webp
|   |   |   |       neon-carousel.webp
|   |   |   |       pro-carousel.webp
|   |   |   |       single-carousel.webp
|   |   |   |       spotlight-carousel.webp
|   |   |   |       testimonials.webp
|   |   |   |       
|   |   |   +---icons
|   |   |   |       add-new.png
|   |   |   |       album.png
|   |   |   |       build.png
|   |   |   |       crown.png
|   |   |   |       fastest.png
|   |   |   |       gallery.png
|   |   |   |       logo.png
|   |   |   |       moon-icon-dark.png
|   |   |   |       moon-icon.png
|   |   |   |       next-page.png
|   |   |   |       storage-usage.png
|   |   |   |       sun-icon-alt.png
|   |   |   |       sun-icon.png
|   |   |   |       testimonial-alt.png
|   |   |   |       testimonial.png
|   |   |   |       
|   |   |   +---logo
|   |   |   |       mgwpp-logo-panel.png
|   |   |   |       mgwpp-logo.png
|   |   |   |       
|   |   |   \---modules-icons
|   |   |       +---galleries
|   |   |       |       3d-carousel.png
|   |   |       |       albums.png
|   |   |       |       fullpage-slider.png
|   |   |       |       grid.png
|   |   |       |       lightbox.png
|   |   |       |       mega-carousel.png
|   |   |       |       multi-gallery.png
|   |   |       |       neon-carousel.png
|   |   |       |       pro-carousel.png
|   |   |       |       single-gallery.png
|   |   |       |       spotlight-carousel.png
|   |   |       |       testimonial.png
|   |   |       |       
|   |   |       \---sub-modules
|   |   |               albums.png
|   |   |               carbon--review.png
|   |   |               custom-gallery.png
|   |   |               elementor.png
|   |   |               galleries.png
|   |   |               reviews-alt.png
|   |   |               
|   |   +---js
|   |   |       admin-edit.js
|   |   |       mg-admin-scripts.js
|   |   |       mg-scripts.js
|   |   |       
|   |   +---tables
|   |   |       class-mgwpp-albums-table.php
|   |   |       class-mgwpp-galleries-table.php
|   |   |       class-mgwpp-storage-table.php
|   |   |       
|   |   \---views
|   |       +---albums
|   |       |       class-mgwpp-albums-view.php
|   |       |       
|   |       +---dashboard
|   |       |       class-mgwpp-dashboard-view.php
|   |       |       mgwpp-dashboard-view.css
|   |       |       mgwpp-dashboard-view.js
|   |       |       
|   |       +---galleries
|   |       |       class-mgwpp-galleries-view.php
|   |       |       mgwpp-galleries-view.css
|   |       |       mgwpp-galleries-view.js
|   |       |       
|   |       +---inner-header
|   |       |       class-mgwpp-inner-header.php
|   |       |       mgwpp-inner-header.css
|   |       |       mgwpp-inner-header.js
|   |       |       
|   |       +---modules
|   |       |       class-mgwpp-modules-view-fix.php
|   |       |       class-mgwpp-modules-view-old.php
|   |       |       class-mgwpp-modules-view.php
|   |       |       mgwpp-modules-view.css
|   |       |       mgwpp-modules-view.js
|   |       |       
|   |       +---security
|   |       |       class-mgwpp-security-view.php
|   |       |       
|   |       \---testimonials
|   |               class-mgwpp-testimonials-view.php
|   |               
|   +---elementor
|   |   |   class-mg-elementor-integration.php
|   |   |   
|   |   \---elementor-widgets
|   |           class-mg-elementor-custom-slider-widget.php
|   |           class-mg-elementor-gallery-grid.php
|   |           class-mg-elementor-gallery-multi.php
|   |           class-mg-elementor-gallery-single.php
|   |           class-mg-elementor-mega-carousel-widget.php
|   |           class-mg-elementor-neon-carousel-widget.php
|   |           class-mg-elementor-pro-carousel-widget.php
|   |           class-mg-elementor-testimonial-carousel.php
|   |           class-mg-elementor-threed-carousel.php
|   |           
|   +---functions
|   |       class-mgwpp-security-uploads-scanner.php
|   |       class-mgwpp-shortcode.php
|   |       class-mgwpp-upload.php
|   |       
|   +---gallery-types
|   |   |   class-mgwpp-testimonial-carousel.php
|   |   |   
|   |   +---mgwpp-full-page-slider
|   |   |       class-mgwpp-full-page-slider.php
|   |   |       mgwpp-full-page-slider.css
|   |   |       mgwpp-full-page-slider.js
|   |   |       
|   |   +---mgwpp-grid-gallery
|   |   |       class-mgwpp-grid-gallery.php
|   |   |       mgwpp-grid-gallery.css
|   |   |       mgwpp-grid-gallery.js
|   |   |       
|   |   +---mgwpp-mega-slider
|   |   |       class-mgwpp-mega-slider.php
|   |   |       mgwpp-mega-slider.css
|   |   |       mgwpp-mega-slider.js
|   |   |       
|   |   +---mgwpp-multi-gallery
|   |   |       class-mgwpp-multi-gallery.php
|   |   |       mgwpp-multi-gallery.css
|   |   |       mgwpp-multi-gallery.js
|   |   |       
|   |   +---mgwpp-neon-carousel
|   |   |       class-mgwpp-neon-carousel.php
|   |   |       mgwpp-neon-carousel.css
|   |   |       mgwpp-neon-carousel.js
|   |   |       
|   |   +---mgwpp-pro-carousel
|   |   |       class-mgwpp-pro-carousel.php
|   |   |       mgwpp-pro-carousel.css
|   |   |       mgwpp-pro-carousel.js
|   |   |       
|   |   +---mgwpp-single-gallery
|   |   |       class-mgwpp-single-gallery-editor-options.php
|   |   |       class-mgwpp-single-gallery.php
|   |   |       mgwpp-single-gallery.css
|   |   |       mgwpp-single-gallery.js
|   |   |       
|   |   +---mgwpp-spotlight-carousel
|   |   |       class-mgwpp-spotlight-carousel.php
|   |   |       mgwpp-spotlight-carousel.css
|   |   |       mgwpp-spotlight-carousel.js
|   |   |       
|   |   \---mgwpp-threed-carousel
|   |           class-mgwpp-threed-carousel.php
|   |           mgwpp-threed-carousel.css
|   |           mgwpp-threed-carousel.js
|   |           
|   +---main
|   +---registration
|   |   |   class-mgwpp-uninstall.php
|   |   |   
|   |   +---album
|   |   |       class-mgwpp-album-capabilities.php
|   |   |       class-mgwpp-album-display.php
|   |   |       class-mgwpp-album-post-type.php
|   |   |       class-mgwpp-album-submit.php
|   |   |       
|   |   +---assets
|   |   |       class-mgwpp-admin-assets.php
|   |   |       class-mgwpp-assets.php
|   |   |       class-mgwpp-elementor-assets.php
|   |   |       class-mgwpp-settings-OLD.php
|   |   |       class-mgwpp-settings.php
|   |   |       
|   |   +---custom-gallery
|   |   |       class-mgwpp-custom-gallery-registration.php
|   |   |       
|   |   +---gallery
|   |   |       class-mgwpp-gallery-capabilities.php
|   |   |       class-mgwpp-gallery-manager.php
|   |   |       class-mgwpp-gallery-post-type.php
|   |   |       
|   |   \---testimonials
|   |           class-mgwpp-testimonials-capabilties.php
|   |           class-mgwpp-testimonials-manager.php
|   |           class-mgwpp-testimonials-post-type.php
|   |           
|   \---vc
|           class-mgwpp-vc-integration.php
|           
+---public
|   +---css
|   |       mg-album-styles.css
|   |       mg-elementor-editor.css
|   |       mgwpp-custom-slider.css
|   |       mgwpp-testimonial-carousel.css
|   |       styles.css
|   |       
|   +---front-end
|   |   \---icons
|   |           layout-grid.webp
|   |           layout-masonry.webp
|   |           layout-minimal.webp
|   |           
|   \---js
|           mg-albums-styles.js
|           mg-elementor-editor.js
|           mg-lightbox.js
|           mg-universal-init.js
|           mgwpp-testimonial-carousel.js
|           
+---src
|   +---js
|   |       app.js
|   |       
|   \---scss
|           app.scss
|           
+---templates
|       archive-mgwpp_testimonial.php
|       single-mgwpp_album.php
|       single-mgwpp_soora.php
|       single-mgwpp_testimonial.php
|       
\---tests
        test.php  
```
