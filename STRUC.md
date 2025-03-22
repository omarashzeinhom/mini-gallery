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