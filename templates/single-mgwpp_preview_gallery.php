<?php
/**
 * Mini Gallery Preview Template
 * Displays gallery preview in isolation
 */

// Get and validate parameters
$gallery_id = isset($_GET['gallery_id']) ? absint($_GET['gallery_id']) : 0;

if (!$gallery_id || 
    !isset($_GET['_wpnonce']) || 
    !wp_verify_nonce($_GET['_wpnonce'], 'mgwpp_preview') || 
    !isset($_GET['mgwpp_preview'])) {
    status_header(403);
    wp_die(__('Invalid gallery preview request', 'mini-gallery'));
}

// Set preview mode constant
define('MGWPP_PREVIEW_MODE', true);

// Setup minimal environment
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php printf(__('Preview: Gallery #%d', 'mini-gallery'), $gallery_id); ?></title>
    
    <?php 
    // Load only plugin assets, not theme styles
    wp_head();
    
    // Manually load necessary assets
    if (class_exists('MGWPP_Assets')) {
        MGWPP_Assets::enqueue_preview_assets($gallery_id);
    }
    ?>
    
    <style>
        /* Reset all styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f0f0f1 !important;
            padding: 20px !important;
            margin: 0 !important;
            min-height: 100vh !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .mgwpp-preview-container {
            width: 100%;
            max-width: 100%;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            position: relative !important;
            z-index: 10000 !important;
        }
        
        /* Hide admin bar in preview */
        #wpadminbar {
            display: none !important;
        }
        
        /* Prevent theme interference */
        body *:not(.mgwpp-preview-container):not(.mgwpp-preview-container *) {
            display: none !important;
        }
    </style>
</head>
<body class="mgwpp-preview-mode">
    <div class="mgwpp-preview-container">
        <?php
        // Render the gallery using the shortcode
        echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]');
        ?>
    </div>
    
    <?php 
    // Output footer scripts
    wp_footer();
    ?>
</body>
</html>