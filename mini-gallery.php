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

require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-single-gallery/class-mgwpp-single-gallery.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-multi-gallery/class-mgwpp-multi-gallery.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-grid-gallery/class-mgwpp-grid-gallery.php';

// Slider Types
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-testimonial-carousel.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-threed-carousel/class-mgwpp-threed-carousel.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-mega-slider/class-mgwpp-mega-slider.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-pro-carousel/class-mgwpp-pro-carousel.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-neon-carousel/class-mgwpp-neon-carousel.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-full-page-slider/class-mgwpp-full-page-slider.php';
require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-spotlight-carousel/class-mgwpp-spotlight-carousel.php';


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
//require_once plugin_dir_path(__FILE__) . 'includes/functions/class-mgwpp-admin.php';
// New Admin Core
require_once plugin_dir_path(__FILE__) . 'includes/admin/class-mgwpp-admin-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/class-mgwpp-data-handler.php';



require_once plugin_dir_path(__FILE__) . 'includes/registration/class-mgwpp-uninstall.php';

// Elementor Integration
require_once plugin_dir_path(__FILE__) . 'includes/elementor/class-mg-elementor-integration.php';
// WPBakery Page Builder Integration
require_once plugin_dir_path(__FILE__) . 'includes/vc/class-mgwpp-vc-integration.php';

//Assets
require_once plugin_dir_path(__FILE__) . 'includes/registration/assets/class-mgwpp-assets.php';
//require_once plugin_dir_path(__FILE__) . 'includes/registration/assets/class-mgwpp-admin-assets.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-mgwpp_ajax_handler.php';
MGWPP_Ajax_Handler::init();


// When enqueueing:

// Activation & Deactivation Hooks
function mgwpp_plugin_activate()
{
    MGWPP_Testimonial_Capabilities::mgwpp_testimonial_capabilities();
    MGWPP_Gallery_Post_Type::mgwpp_register_gallery_post_type();
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
    MGWPP_Capabilities::mgwpp_add_marketing_team_role();
    MGWPP_Capabilities::mgwpp_gallery_capabilities();
    MGWPP_Album_Capabilities::mgwpp_album_capabilities();
    flush_rewrite_rules(false); // causes error on true
}
register_activation_hook(__FILE__, 'mgwpp_plugin_activate');

function mgwpp_register_shortcodes() {
    // Register the gallery shortcode
    add_shortcode('mgwpp_gallery', 'mgwpp_gallery_shortcode');
}
add_action('admin_init', 'mgwpp_register_shortcodes'); // Ensure it's registered in the admin area


// Initialize plugin
function mgwpp_initialize_plugin()
{
    // Register gallery shortcode
    add_shortcode('mgwpp_gallery', 'mgwpp_gallery_shortcode');
    add_action( 'admin_init', 'mgwpp_register_shortcodes' ); 
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

add_action('plugins_loaded', function () {
    if (is_admin()) {
        MGWPP_Admin_Core::init();
    }
});

// Link Elementor templates to galleries
add_action('elementor/editor/after_save', function($post_id) {
     // 1. Verify nonce exists first
    if (!isset($_POST['mgwpp_parent_gallery_nonce'])) {
        return;
    }

    // 2. Sanitize nonce value
    $nonce = sanitize_key(wp_unslash($_POST['mgwpp_parent_gallery_nonce']));

    // 3. Verify nonce validity
    if (!wp_verify_nonce($nonce, 'mgwpp_save_parent_gallery')) {
        return;
    }

    // 4. Sanitize and validate parent gallery ID
    $parent_gallery = isset($_POST['mgwpp_parent_gallery']) 
        ? absint(wp_unslash($_POST['mgwpp_parent_gallery'])) 
        : 0;

    // 5. Save validated data
    update_post_meta($post_id, '_mgwpp_parent_gallery', $parent_gallery);
});

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


// Add this to your plugin's main file or a relevant include file

add_action('template_redirect', 'mgwpp_handle_preview_request');

function mgwpp_handle_preview_request() {
    // 1. First check if this is a preview request
    if (!isset($_GET['mgwpp_preview']) || $_GET['mgwpp_preview'] !== '1') {
        return;
    }

    // 2. Verify nonce with proper action
    if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'mgwpp_preview')) {
        wp_die(
            '<h1>' . esc_html__('Preview Authorization Failed', 'mini-gallery') . '</h1>' .
            '<p>' . esc_html__('Please return to the admin and click the preview button again.', 'mini-gallery') . '</p>' .
            '<p><a href="' . esc_url(admin_url('edit.php?post_type=mgwpp_soora')) . '">' . 
            esc_html__('Return to Galleries', 'mini-gallery') . '</a></p>'
        );
    }

    // 3. Validate gallery ID
    $gallery_id = isset($_GET['gallery_id']) ? absint($_GET['gallery_id']) : 0;
    if (!$gallery_id) {
        wp_die(esc_html__('Invalid gallery ID format.', 'mini-gallery'));
    }

    // 4. Verify gallery exists
    $gallery = get_post($gallery_id);
    if (!$gallery || 'mgwpp_soora' !== $gallery->post_type) {
        wp_die(esc_html__('The requested gallery no longer exists.', 'mini-gallery'));
    }

    // 5. Show preview template
    get_header();
    echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]');
    get_footer();
    exit;
}
add_action('template_redirect', 'mgwpp_handle_preview_request');