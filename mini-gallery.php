<?php

/**
 * Plugin Name: Mini Gallery
 * Description: A WordPress plugin to display a simple custom gallery.
 * Version: 1.1
 * Author: Omar Ashraf Zeinhom AbdElRahman | ANDGOEDU
 * License: GPLv2
 */

if (!defined('ABSPATH')) {
    exit;
}

/* Include necessary files */
require_once plugin_dir_path(__FILE__) . 'includes/functions/class-mgwpp-shortcode.php';

//Gallery Types 
// Add this with your other requires at the top
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-mega-slider.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-pro-carousel.php';

// Galleries
require_once plugin_dir_path(__FILE__) . 'includes/registration/gallery/class-mgwpp-gallery-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/gallery/class-mgwpp-gallery-capabilities.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions/class-mgwpp-upload.php';
// Albums
require_once plugin_dir_path(__FILE__) . 'includes/registration/album/class-mgwpp-album-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/album/class-mgwpp-album-display.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/album/class-mgwpp-album-capabilities.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/album/class-mgwpp-album-submit.php';
// Functions
require_once plugin_dir_path(__FILE__) . 'includes/functions/class-mgwpp-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/class-mgwpp-uninstall.php'; // Include the uninstall class
require_once plugin_dir_path(__FILE__) . 'includes/registration/gallery/class-mgwpp-gallery-manager.php'; // Include the gallery manager class
require_once plugin_dir_path(__FILE__) . 'public/mgwpp-gallery-shortcode.php'; // Include the gallery shortcode class

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
}
add_action('init', 'mgwpp_initialize_plugin');

// Enqueue front-end scripts and styles
function mgwpp_enqueue_assets()
{
    // Register scripts and styles
    wp_register_script('mg-carousel', plugin_dir_url(__FILE__) . 'public/js/carousel.js', array(), '1.0', true);
    wp_register_style('mg-styles', plugin_dir_url(__FILE__) . 'public/css/styles.css', array(), '1.0');
    wp_register_style('mg-album-styles', plugin_dir_url(__FILE__) . 'public/css/mg-album-styles.css', array(), '1.0');
    wp_register_style('mg-mega-carousel-styles', plugin_dir_url(__FILE__) . 'public/css/mg-mega-carousel-styles.css', array(), '1.0');
    wp_register_style('mgwpp-pro-carousel-styles', plugin_dir_url(__FILE__) . 'public/css/mg-pro-carousel.css', array(), '1.0');
    wp_register_script('mgwpp-pro-carousel-js', plugin_dir_url(__FILE__) . 'public/js/mg-pro-carousel.js',array(), '1.0', true);
    // Enqueue for front-end only

    wp_enqueue_script('mg-carousel');
    wp_enqueue_style('mg-styles');
    wp_enqueue_style('mg-mega-carousel-styles');  // Add this line
    wp_enqueue_style('mgwpp-pro-carousel-styles'); // Add this line
}

add_action('wp_enqueue_scripts', 'mgwpp_enqueue_assets');



// Enqueue admin assets
function mgwpp_enqueue_admin_assets()
{
    // Register scripts and styles
    wp_register_script('mg-admin-carousel', plugin_dir_url(__FILE__) . 'admin/js/mg-admin-scripts.js', array('jquery'), '1.0', true);
    wp_register_style('mg-admin-styles', plugin_dir_url(__FILE__) . 'admin/css/mg-admin-styles.css', array(), '1.0');

    // Register additional styles for admin area
    wp_register_style('mg-styles', plugin_dir_url(__FILE__) . 'public/css/styles.css', array(), '1.0');
    wp_register_style('mg-album-styles', plugin_dir_url(__FILE__) . 'public/css/mg-album-styles.css', array(), '1.0');
    wp_register_style('mg-mega-carousel-styles', plugin_dir_url(__FILE__) . 'public/css/mg-mega-carousel-styles.css', array(), '1.0');
    wp_register_style('mgwpp-pro-carousel-styles', plugin_dir_url(__FILE__) . 'public/css/mg-pro-carousel.css', array(), '1.0');

    // Enqueue for admin pages
    wp_enqueue_script('mg-admin-carousel');
    wp_enqueue_style('mg-admin-styles');
    wp_enqueue_style('mg-styles');
    wp_enqueue_style('mg-album-styles');
    wp_enqueue_style('mg-mega-carousel-styles');
    wp_enqueue_style('mgwpp-pro-carousel-styles');
}
add_action('admin_enqueue_scripts', 'mgwpp_enqueue_admin_assets');




// Register the shortcode
function mgwpp_register_shortcodes() {
    add_shortcode('mgwpp_gallery', 'mgwpp_gallery_shortcode');
}
add_action('init', 'mgwpp_register_shortcodes');


// Activation & Deactivation Hooks
function mgwpp_plugin_activate()
{
    MGWPP_Gallery_Post_Type::mgwpp_register_gallery_post_type();
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
    MGWPP_Capabilities::mgwpp_add_marketing_team_role();
    MGWPP_Capabilities::mgwpp_gallery_capabilities();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'mgwpp_plugin_activate');

function mgwpp_plugin_deactivate()
{
    unregister_post_type('mgwpp_soora');
    unregister_post_type('mgwpp_album');
    remove_role('marketing_team');
    flush_rewrite_rules();
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
add_action(
    'admin_init',
    function () {
        error_log('POST Data: ' . print_r($_POST, true));
        error_log('REQUEST Data: ' . print_r($_REQUEST, true));
    }
);


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
