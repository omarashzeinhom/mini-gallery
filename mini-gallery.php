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

if (!defined('ABSPATH')) {
    exit;
}

define('MG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MG_PLUGIN_URL', plugins_url('', __FILE__));
define('MGWPP_ASSET_VERSION', filemtime(__FILE__));
define('MGWPP_PLUGIN_FILE',  plugin_dir_path(__FILE__) . 'includes/admin/images/');


function get_plugin_version()
{
    // Use actual version constant if available
    return defined('MGWPP_ASSET_VERSION') ? MGWPP_ASSET_VERSION : '1.4.0';
}

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
require_once plugin_dir_path(__FILE__) . 'includes/registration/assets/class-mgwpp-capabilities.php';

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


// New Admin Core
require_once plugin_dir_path(__FILE__) . 'includes/admin/class-mgwpp-admin-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/class-mgwpp-data-handler.php';



require_once plugin_dir_path(__FILE__) . 'includes/registration/class-mgwpp-uninstall.php';


//Add Block Integration if Possible In Editor.

//Assets
require_once plugin_dir_path(__FILE__) . 'includes/registration/assets/class-mgwpp-assets.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/assets/class-mgwpp-admin-assets.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/class-mgwpp_ajax_handler.php';
MGWPP_Ajax_Handler::init();
// Editor Assets
require_once __DIR__ . '/includes/admin/class-mgwpp-admin-editors.php';


require_once plugin_dir_path(__FILE__) . 'includes/registration/assets/class-mgwpp-module-manager.php';



// When enqueueing:

// Activation & Deactivation Hooks
function mgwpp_plugin_activate()
{
    MGWPP_Testimonial_Capabilities::mgwpp_testimonial_capabilities();
    MGWPP_Gallery_Post_Type::mgwpp_register_gallery_post_type();
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
    MGWPP_Capabilities::mgwpp_add_marketing_team_role();
    MGWPP_Gallery_Capabilities::mgwpp_gallery_capabilities();
    MGWPP_Album_Capabilities::mgwpp_album_capabilities();
    if (false === get_option('mgwpp_enabled_modules')) {
        //update_option('mgwpp_enabled_modules', MGWPP_Settings::get_enabled_modules());
    }
    flush_rewrite_rules(false); // causes error on true
}
register_activation_hook(__FILE__, 'mgwpp_plugin_activate');

function mgwpp_register_shortcodes()
{
    // Register the gallery shortcode
    add_shortcode('mgwpp_gallery', 'mgwpp_gallery_shortcode');
}
add_action('admin_init', 'mgwpp_register_shortcodes'); // Ensure it's registered in the admin area


// Initialize plugin
function mgwpp_initialize_plugin()
{
    // Register gallery shortcode
    add_shortcode('mgwpp_gallery', 'mgwpp_gallery_shortcode');
    add_action('admin_init', 'mgwpp_register_shortcodes');
    // Call the static methods to initialize classes
    MGWPP_Gallery_Post_Type::mgwpp_register_gallery_post_type();
    MGWPP_Capabilities::mgwpp_gallery_capabilities();

    // Galleries
    MGWPP_Gallery_Manager::mgwpp_register_gallery_delete_action(); // Register gallery deletion
    MGWPP_Uninstall::mgwpp_register_uninstall_hook(); // Register the uninstall hook
    MGWPP_Capabilities::mgwpp_add_marketing_team_role();

    //Albums
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
    MGWPP_Album_Capabilities::mgwpp_album_capabilities();


    //Testimonials
    MGWPP_Testimonial_Post_Type::mgwpp_register_testimonial_post_type();
    MGWPP_Testimonial_Capabilities::mgwpp_testimonial_capabilities();

    // Portfolio Items *Coming Soon
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
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    if (is_singular('mgwpp_album')) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/single-mgwpp_album.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    return $template;
}

add_action('admin_menu', function () {
    if (is_admin()) {
        MGWPP_Admin_Core::init();
    } else {
        esc_html_e('Your Not Allowed To Access Mini Gallery: Access Has Been Reported to Security Plugin', 'mini-gallery');
    }
}, 5);


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

add_action('template_redirect', 'mgwpp_handle_preview_request');

function mgwpp_handle_preview_request()
{
    if (!isset($_GET['mgwpp_preview']) || $_GET['mgwpp_preview'] !== '1') {
        return;
    }

    if (isset($_GET['_wpnonce'])) {
        $nonce = sanitize_key(wp_unslash($_GET['_wpnonce']));
    }

    if (!wp_verify_nonce($nonce ?? '', 'mgwpp_preview')) {
        wp_die(
            '<h1>' . esc_html__('Preview Authorization Failed', 'mini-gallery') . '</h1>' .
                '<p>' . esc_html__('Please return to the admin and click the preview button again.', 'mini-gallery') . '</p>' .
                '<p><a href="' . esc_url(admin_url('edit.php?post_type=mgwpp_soora')) . '">' .
                esc_html__('Return to Galleries', 'mini-gallery') . '</a></p>'
        );
    }

    $gallery_id = isset($_GET['gallery_id']) ? absint($_GET['gallery_id']) : 0;
    if (!$gallery_id) {
        wp_die(esc_html__('Invalid gallery ID format.', 'mini-gallery'));
    }

    $gallery = get_post($gallery_id);
    if (!$gallery || 'mgwpp_soora' !== $gallery->post_type) {
        wp_die(esc_html__('The requested gallery no longer exists.', 'mini-gallery'));
    }
    get_header();
    echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]');
    get_footer();
    exit;
}
add_action('template_redirect', 'mgwpp_handle_preview_request');


add_action('template_redirect', function () {
    if (is_singular('mgwpp_soora')) {
        // Track gallery views for analytics (optional)
        do_action('mgwpp_gallery_viewed', get_queried_object_id());
    }
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'mgwpp_album_id';
    return $vars;
});


// Action links (below plugin name)
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mgwpp_add_plugin_action_links');
function mgwpp_add_plugin_action_links($links)
{
    $docs_link = '<a href="https://minigallery.andgowebsolutions.com/docs/" target="_blank">Docs</a>';
    array_unshift($links, $docs_link);
    return $links;
}

// Row meta (beneath plugin description)
add_filter('plugin_row_meta', 'mgwpp_add_plugin_row_meta', 10, 2);
function mgwpp_add_plugin_row_meta($links, $file)
{
    if (plugin_basename(__FILE__) !== $file) {
        return $links;
    }

    $links[] = '<a href="https://github.com/omarashzeinhom/mini-gallery-dev" target="_blank">Contribute</a>';
    $links[] = '<a href="https://wordpress.org/support/plugin/mini-gallery/reviews/#new-post" target="_blank">Rate Plugin ★★★★★</a>';

    return $links;
}
