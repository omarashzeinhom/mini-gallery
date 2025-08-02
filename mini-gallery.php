<?php
/*
Plugin Name: Mini Gallery
Plugin URI: https://wordpress.org/plugins/mini-gallery/
Description: A Fully Open Source WordPress Gallery, Slider and Carousel Alternative for Premium Plugin Sliders. Choose one of our 10 Default Ones, or create your own.
Version: 1.4
Author: AGWS | And Go Web Solutions
Author URI: https://andgowebsolutions.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: mini-gallery
Domain Path: /languages

Contribute: https://github.com/omarashzeinhom/mini-gallery-dev/
Docs: https://minigallery.andgowebsolutions.com/docs/
*/

// ======================
// SECURITY & CONSTANTS
// ======================
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('MG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MG_PLUGIN_URL', plugins_url('', __FILE__));
define('MGWPP_ASSET_VERSION', filemtime(__FILE__));
define('MGWPP_PLUGIN_FILE', plugin_dir_path(__FILE__) . 'includes/admin/images/');

// ======================
// CORE FUNCTIONALITY
// ======================
function get_plugin_version()
{
    return defined('MGWPP_ASSET_VERSION') ? MGWPP_ASSET_VERSION : '1.4.0';
}

// ======================
// FILE INCLUSIONS
// ======================
// Core functionality
require_once MG_PLUGIN_PATH . 'includes/registration/assets/class-mgwpp-module-manager.php';
require_once MG_PLUGIN_PATH . 'includes/functions/class-mgwpp-shortcode.php';

// Gallery types
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-single-gallery/class-mgwpp-single-gallery.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-multi-gallery/class-mgwpp-multi-gallery.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-grid-gallery/class-mgwpp-grid-gallery.php';

// Slider types
require_once MG_PLUGIN_PATH . 'includes/gallery-types/class-mgwpp-testimonial-carousel.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-threed-carousel/class-mgwpp-threed-carousel.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-mega-slider/class-mgwpp-mega-slider.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-pro-carousel/class-mgwpp-pro-carousel.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-neon-carousel/class-mgwpp-neon-carousel.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-full-page-slider/class-mgwpp-full-page-slider.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-spotlight-carousel/class-mgwpp-spotlight-carousel.php';

// Capabilities and post types
require_once MG_PLUGIN_PATH . 'includes/registration/assets/class-mgwpp-capabilities.php';

// Gallery registration
require_once MG_PLUGIN_PATH . 'includes/registration/gallery/class-mgwpp-gallery-post-type.php';
require_once MG_PLUGIN_PATH . 'includes/registration/gallery/class-mgwpp-gallery-capabilities.php';
require_once MG_PLUGIN_PATH . 'includes/registration/gallery/class-mgwpp-gallery-manager.php';
require_once MG_PLUGIN_PATH . 'includes/functions/class-mgwpp-upload.php';

// Album registration
require_once MG_PLUGIN_PATH . 'includes/registration/album/class-mgwpp-album-post-type.php';
require_once MG_PLUGIN_PATH . 'includes/registration/album/class-mgwpp-album-display.php';
require_once MG_PLUGIN_PATH . 'includes/registration/album/class-mgwpp-album-capabilities.php';
require_once MG_PLUGIN_PATH . 'includes/registration/album/class-mgwpp-album-submit.php';

// Testimonials registration
require_once MG_PLUGIN_PATH . 'includes/registration/testimonials/class-mgwpp-testimonials-post-type.php';
require_once MG_PLUGIN_PATH . 'includes/registration/testimonials/class-mgwpp-testimonials-capabilties.php';
require_once MG_PLUGIN_PATH . 'includes/registration/testimonials/class-mgwpp-testimonials-manager.php';

// Admin core
require_once MG_PLUGIN_PATH . 'includes/admin/class-mgwpp-admin-core.php';
require_once MG_PLUGIN_PATH . 'includes/admin/class-mgwpp-data-handler.php';
require_once MG_PLUGIN_PATH . 'includes/admin/views/edit-gallery/class-mgwpp-edit-gallery.php';

// Uninstall and assets
require_once MG_PLUGIN_PATH . 'includes/registration/class-mgwpp-uninstall.php';
require_once MG_PLUGIN_PATH . 'includes/registration/assets/class-mgwpp-assets.php';
require_once MG_PLUGIN_PATH . 'includes/admin/class-mgwpp_ajax_handler.php';

// Initialize AJAX handler
MGWPP_Ajax_Handler::init();

// ======================
// ACTIVATION/DEACTIVATION
// ======================
register_activation_hook(__FILE__, function () {
    MGWPP_Testimonial_Capabilities::mgwpp_testimonial_capabilities();
    MGWPP_Gallery_Post_Type::mgwpp_register_gallery_post_type();
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
    MGWPP_Gallery_Capabilities::mgwpp_gallery_capabilities();
    MGWPP_Album_Capabilities::mgwpp_album_capabilities();

    if (false === get_option('mgwpp_enabled_modules')) {
        // Module initialization placeholder
    }
    flush_rewrite_rules(false);
});

register_deactivation_hook(__FILE__, function () {
    unregister_post_type('mgwpp_testimonials');
    unregister_post_type('mgwpp_soora');
    unregister_post_type('mgwpp_album');
    flush_rewrite_rules(false);
});

register_uninstall_hook(__FILE__, 'mgwpp_plugin_uninstall');

function mgwpp_plugin_uninstall()
{
    $sowar = get_posts([
        'post_type' => 'mgwpp_soora',
        'numberposts' => -1,
        'post_status' => 'any'
    ]);

    foreach ($sowar as $gallery_image) {
        wp_delete_post(intval($gallery_image->ID), true);
    }
}

// ======================
// PLUGIN INITIALIZATION
// ======================
add_action('init', function () {
    // Register shortcodes
    add_shortcode('mgwpp_gallery', 'mgwpp_gallery_shortcode');

    // Initialize post types
    MGWPP_Gallery_Post_Type::mgwpp_register_gallery_post_type();
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
    MGWPP_Testimonial_Post_Type::mgwpp_register_testimonial_post_type();

    // Initialize capabilities
    MGWPP_Gallery_Capabilities::mgwpp_gallery_capabilities();
    MGWPP_Album_Capabilities::mgwpp_album_capabilities();
    MGWPP_Testimonial_Capabilities::mgwpp_testimonial_capabilities();

    // Register hooks
    MGWPP_Gallery_Manager::mgwpp_register_gallery_delete_action();
    MGWPP_Uninstall::mgwpp_register_uninstall_hook();
});

add_action('after_setup_theme', function () {
    if (!current_theme_supports('post-thumbnails')) {
        add_theme_support('post-thumbnails');
    }
});

// ======================
// ADMIN FUNCTIONALITY
// ======================
add_action('admin_menu', function () {
    if (is_admin()) {
        MGWPP_Admin_Core::init();
    } else {
        esc_html_e('Your Not Allowed To Access Mini Gallery: Access Has Been Reported to Security Plugin', 'mini-gallery');
    }
}, 5);

// ======================
// TEMPLATE HANDLING
// ======================
// In your main plugin file, simplify template handling:
add_filter('template_include', function ($template) {
    if (is_singular('mgwpp_soora')) {
        $custom_template = MG_PLUGIN_PATH . 'templates/single-mgwpp_soora.php';
        return file_exists($custom_template) ? $custom_template : $template;
    }

    if (is_singular('mgwpp_album')) {
        $custom_template = MG_PLUGIN_PATH . 'templates/single-mgwpp_album.php';
        return file_exists($custom_template) ? $custom_template : $template;
    }

    return $template;
});
// ======================
// PLUGIN LINKS
// ======================
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $docs_link = '<a href="https://minigallery.andgowebsolutions.com/docs/" target="_blank">Docs</a>';
    array_unshift($links, $docs_link);
    return $links;
});

add_filter('plugin_row_meta', function ($links, $file) {
    if (plugin_basename(__FILE__) !== $file) {
        return $links;
    }

    $links[] = '<a href="https://github.com/omarashzeinhom/mini-gallery-dev" target="_blank">Contribute</a>';
    $links[] = '<a href="https://wordpress.org/support/plugin/mini-gallery/reviews/#new-post" target="_blank">Rate Plugin ★★★★★</a>';
    return $links;
}, 10, 2);
