# Mini Gallery Plugin Strucutre

```md
includes/
├── gallery-types/                  // For adding new gallery types
│   ├── class-mgwpp-mega-slider.php
│   ├── class-mgwpp-pro-carousel.php
├── registration/                   // For registering post types, capabilities, etc.
│   ├── gallery/                    // ✅ Gallery-related registration
│   │   ├── class-mgwpp-gallery-post-type.php
│   │   ├── class-mgwpp-gallery-capabilities.php
│   ├── album/                      // ✅ Album-related registration
│   │   ├── class-mgwpp-album-post-type.php
│   │   ├── class-mgwpp-album-capabilities.php
│   │   ├── class-mgwpp-album-display.php
│   │   ├── class-mgwpp-album-submit.php
├── functions/                      // Core functionality
│   ├── class-mgwpp-shortcode.php
│   ├── class-mgwpp-upload.php
│   ├── class-mgwpp-admin.php
├── registration/                   // Uninstall and manager classes
│   ├── class-mgwpp-uninstall.php
│   ├── class-mgwpp-gallery-manager.php
public/                             // Front-end assets
├── js/
│   ├── carousel.js
├── css/
│   ├── styles.css
│   ├── mg-album-styles.css
│   ├── mg-mega-carousel-styles.css
│   ├── mg-pro-carousel.css
admin/                              // Admin assets
├── js/
│   ├── mg-admin-scripts.js
├── css/
│   ├── mg-admin-styles.css
```


## Elementor Notices

1- To Disable Elementor errors correctly for intelphisense when developing for elementor 

```json
{
    "editor.defaultFormatter": "zobo.php-intellisense",
    "intelephense.environment.includePaths": [
        "C:/xampp/htdocs/wordpress/wp-content/plugins/elementor"
      ]
}
```

## WPBakery Notices

Styles:

- mg-single-carousel-styles

- mg-multi-carousel-styles

- mg-grid-styles

- mg-mega-carousel-styles

- mgwpp-pro-carousel-styles

- mgwpp-neon-carousel-styles

- mgwpp-threed-carousel-styles

- mg-fullpage-slider-styles

- mg-spotlight-slider-styles

- mgwpp-testimonial-carousel-styles

Scripts:

- mg-single-carousel-js

- mg-multi-carousel-js

- mg-mega-carousel-js

- mgwpp-pro-carousel-js

- mgwpp-neon-carousel-js

- mgwpp-threed-carousel-js

- mg-fullpage-slider-js

- mg-spotlight-slider-js

- mgwpp-testimonial-carousel-js

- mg-universal-init