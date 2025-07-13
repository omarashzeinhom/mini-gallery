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
    // Load theme styles
    if ($stylesheet = get_stylesheet_uri()) {
        echo '<link rel="stylesheet" href="' . esc_url($stylesheet) . '">';
    }
    
    // Load plugin frontend styles
    wp_enqueue_style('mgwpp-frontend');
    
    // Output styles
    wp_head();
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
    </style>
</head>
<body>
    <?php
    // Setup WP Query for the gallery
    $query = new WP_Query([
        'p' => $gallery_id,
        'post_type' => 'mgwpp_soora',
        'post_status' => 'publish'
    ]);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            include MG_PLUGIN_PATH . 'templates/single-mgwpp_soora.php';
        endwhile;
    } else {
        echo '<div class="mgwpp-preview-container">';
        echo '<p>' . esc_html__('Gallery not found', 'mini-gallery') . '</p>';
        echo '</div>';
    }
    
    wp_reset_postdata();
    ?>
    
    <?php wp_footer(); ?>
</body>
</html>