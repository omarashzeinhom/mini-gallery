includes/
└── admin/
    └── editor/
        ├── class-mgwpp-editor-core.php       # Main controller
        ├── class-mgwpp-slide-manager.php     # Slide CRUD operations
        ├── class-mgwpp-layer-manager.php     # Layer management  
        ├── class-mgwpp-editor-assets.php     # Asset management
        ├── views/
        │   ├── editor-main.php               # Main editor UI
        │   ├── editor-slide.php              # Single slide UI
        │   └── editor-layer.php              # Layer controls
        └── assets/
            ├── css/
            │   ├── editor-core.css           # Base styles
            │   ├── editor-slides.css         # Slide-specific
            │   └── editor-layers.css         # Layer styles
            └── js/
                ├── editor-core.js            # Main logic
                ├── editor-interactions.js    # Drag/drop
                └── editor-animations.js      # Timeline controls