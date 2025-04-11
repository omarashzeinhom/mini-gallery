<?php

/**
 * Plugin Name: Mini Gallery
 * Description: A Fully Open Source WordPress Gallery , Slider and Carousel Alternative for Premium Plugin Sliders , Choose one of our 10 Default Ones , or create your own  
 * Version: 2.0
 * Author: Omar Ashraf Zeinhom AbdElRahman | ANDGOEDU
 * License: GPLv2
 */
if (!defined('ABSPATH')) {
    exit;
}

define('MG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MG_PLUGIN_URL', plugins_url('', __FILE__));
define('MGWPP_ASSET_VERSION', filemtime(__FILE__));



/* Include necessary files */
require_once plugin_dir_path(__FILE__) . 'includes/functions/class-mgwpp-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions/class-mgwpp-security-uploads-scanner.php';


//Gallery Types 

require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-single-gallery.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-multi-gallery.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-grid-gallery.php';

// Slider Types
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-testimonial-carousel.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-threed-carousel.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-mega-slider.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-pro-carousel.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-neon-carousel.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-full-page-slider.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-spotlight-carousel.php';


// Capabilities and Post Types
// Galleries Registration for Capabilties
require_once plugin_dir_path(__FILE__) . 'includes/registration/gallery/class-mgwpp-gallery-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/gallery/class-mgwpp-gallery-capabilities.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/gallery/class-mgwpp-gallery-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions/class-mgwpp-upload.php';
// Albums Registration for Capabilties
require_once plugin_dir_path(__FILE__) . 'includes/registration/album/class-mgwpp-album-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/album/class-mgwpp-album-display.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/album/class-mgwpp-album-capabilities.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/album/class-mgwpp-album-submit.php';
// Testimonials Registrations for Capabilties and Post Type 
require_once plugin_dir_path(__FILE__) . 'includes/registration/testimonials/class-mgwpp-testimonials-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/testimonials/class-mgwpp-testimonials-capabilties.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/testimonials/class-mgwpp-testimonials-manager.php';


// Functions Admin Uninstall 
require_once plugin_dir_path(__FILE__) . 'includes/functions/class-mgwpp-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/class-mgwpp-uninstall.php';

// Elementor Integration
require_once plugin_dir_path(__FILE__) . 'includes/elementor/class-mg-elementor-integration.php';
// WPBakery Page Builder Integration
require_once plugin_dir_path(__FILE__) . 'includes/vc/class-mgwpp-vc-integration.php';

//Assets
require_once plugin_dir_path(__FILE__) . 'includes/registration/assets/class-mgwpp-assets.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/assets/class-mgwpp-admin-assets.php';



// When enqueueing:

// Activation & Deactivation Hooks
function mgwpp_plugin_activate()
{
    MGWPP_Testimonial_Capabilities::mgwpp_testimonial_capabilities();
    MGWPP_Gallery_Post_Type::mgwpp_register_gallery_post_type();
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
    MGWPP_Capabilities::mgwpp_add_marketing_team_role();
    MGWPP_Capabilities::mgwpp_gallery_capabilities();
    flush_rewrite_rules(false); // causes error on true
}
register_activation_hook(__FILE__, 'mgwpp_plugin_activate');


// Initialize plugin
function mgwpp_initialize_plugin()
{
    // Register gallery shortcode
    add_shortcode('mgwpp_gallery', 'mgwpp_gallery_shortcode');

    // Call the static methods to initialize classes
    MGWPP_Gallery_Post_Type::mgwpp_register_gallery_post_type();
    MGWPP_Capabilities::mgwpp_gallery_capabilities();

    MGWPP_Gallery_Manager::mgwpp_register_gallery_delete_action(); // Register gallery deletion
    MGWPP_Uninstall::mgwpp_register_uninstall_hook(); // Register the uninstall hook
    MGWPP_Capabilities::mgwpp_add_marketing_team_role();

    //MGWPP_Admin::mgwpp_register_admin_menu(); // Register the admin menu
    //Albums
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
    MGWPP_Album_Capabilities::mgwpp_album_capabilities();


    //MGWPP_Admin::mgwpp_register_admin_menu(); // Register the admin menu
    //Testimonials
    MGWPP_Testimonial_Post_Type::mgwpp_register_testimonial_post_type();
    MGWPP_Testimonial_Capabilities::mgwpp_testimonial_capabilities();
}
add_action('init', 'mgwpp_initialize_plugin');


function mgwpp_add_theme_support()
{
    if (!current_theme_supports('post-thumbnails')) {
        add_theme_support('post-thumbnails');
    }
}
add_action('after_setup_theme', 'mgwpp_add_theme_support');




add_filter('template_include', 'mgwpp_custom_templates');
function mgwpp_custom_templates($template)
{
    if (is_singular('mgwpp_soora')) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/single-mgwpp_soora.php';
        if (file_exists($custom_template)) return $custom_template;
    }

    if (is_singular('mgwpp_album')) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/single-mgwpp_album.php';
        if (file_exists($custom_template)) return $custom_template;
    }

    return $template;
}





function mgwpp_plugin_deactivate()
{
    unregister_post_type('mgwpp_testimonials');
    unregister_post_type('mgwpp_soora');
    unregister_post_type('mgwpp_album');
    remove_role('marketing_team');
    flush_rewrite_rules(false); // causes error on true
}
register_deactivation_hook(__FILE__, 'mgwpp_plugin_deactivate');

// Uninstall Hook
function mgwpp_plugin_uninstall()
{
    $sowar = get_posts(
        array(
            'post_type' => 'mgwpp_soora',
            'numberposts' => -1,
            'post_status' => 'any'
        )
    );
    foreach ($sowar as $gallery_image) {
        wp_delete_post(intval($gallery_image->ID), true);
    }
    remove_role('marketing_team');
}
/**
 *
 * Debugging
 */
//add_action(
//    'admin_init',
//    function () {
//error_log('POST Data: ' . print_r($_POST, true));
//error_log('REQUEST Data: ' . print_r($_REQUEST, true));
//    }
//);


/**
 * Proper ob_end_flush() for all levels
 *
 * This replaces the WordPress `wp_ob_end_flush_all()` function
 * with a replacement that doesn't cause PHP notices.
 */
//remove_action('shutdown', 'wp_ob_end_flush_all', 1);
//add_action('shutdown', function () {
//    while (@ob_end_flush());
//});