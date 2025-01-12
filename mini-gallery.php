<?php

/**
 * Plugin Name: Mini Gallery
 * Description: A WordPress plugin to display a simple custom gallery.
 * Version: 1.1
 * Author: Omar Ashraf Zeinhom AbdElRahman | ANDGOEDU
 * License: GPLv2
 */

if (!defined('ABSPATH')) { exit;
}

/* Include necessary files */
 
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

    // Enqueue for front-end only
    if (!is_admin()) {
        wp_enqueue_script('mg-carousel');
        wp_enqueue_style('mg-styles');
    }
}
add_action('wp_enqueue_scripts', 'mgwpp_enqueue_assets');

// Enqueue admin assets
function mgwpp_enqueue_admin_assets()
{
    // Register scripts and styles
    wp_register_script('mg-admin-carousel', plugin_dir_url(__FILE__) . 'admin/js/mg-admin-scripts.js', array('jquery'), '1.0', true);
    wp_register_style('mg-admin-styles', plugin_dir_url(__FILE__) . 'admin/css/mg-admin-styles.css', array(), '1.0');

    // Enqueue for admin pages
    wp_enqueue_script('mg-admin-carousel');
    wp_enqueue_style('mg-admin-styles');
}
add_action('admin_enqueue_scripts', 'mgwpp_enqueue_admin_assets');






 // Define the shortcode function
function mgwpp_gallery_shortcode($atts)
{
    $atts = shortcode_atts(['id' => '', 'paged' => 1], $atts);
    $post_id = max(0, intval($atts['id']));
    $paged = max(1, intval($atts['paged']));
    $output = '';
 
    if ($post_id) {
        // Retrieve the gallery type from post meta
        $gallery_type = get_post_meta($post_id, 'gallery_type', true);
        if (!$gallery_type) {
            $gallery_type = 'single_carousel'; // Fallback to default if not set
        }
 
        $images_per_page = 6; // Number of images per page for multi-carousel
        $offset = ($paged - 1) * $images_per_page;
 
        // Retrieve all images for the gallery
        $all_images = get_attached_media('image', $post_id);
 
        if ($all_images) {
            if ($gallery_type === 'single_carousel') {
                $output .= '<div id="mg-carousel" class="mg-gallery-single-carousel">';
                foreach ($all_images as $image) {
                    $output .= '<div class="carousel-slide">' . 
                        wp_get_attachment_image($image->ID, 'medium', false, ['loading' => 'lazy']) . 
                        '</div>';
                }
                $output .= '</div>';
            } elseif ($gallery_type === 'multi_carousel') {
                $output .= '<div id="mg-multi-carousel" class="mg-gallery multi-carousel" data-page="' . esc_attr($paged) . '">';
                
                // Slice images for current page
                $images = array_slice($all_images, $offset, $images_per_page);
                foreach ($images as $image) {
                    $output .= '<div class="mg-multi-carousel-slide">' . 
                        wp_get_attachment_image($image->ID, 'medium', false, ['class' => 'mg-multi-carousel-slide', 'loading' => 'lazy']) . 
                        '</div>';
                }
                $output .= '</div>';
            } elseif ($gallery_type === 'grid') {
                $output .= '<div class="grid-layout">';
                foreach ($all_images as $image) {
                    $output .= '<div class="grid-item">' . 
                        wp_get_attachment_image($image->ID, 'medium', false, ['loading' => 'lazy']) . 
                        '</div>';
                }
                $output .= '</div>';
            }
        }
        else {
            $output .= '<p>No images found for this gallery.</p>';
        }
 
        // Handle pagination if necessary for multi-carousel
        if ($gallery_type === 'multi_carousel' && count($all_images) > $images_per_page) {
            $total_pages = ceil(count($all_images) / $images_per_page);
            $output .= '<div class="gallery-pagination">';
            if ($paged > 1) {
                $output .= '<a href="' . esc_url(add_query_arg(['paged' => $paged - 1], get_permalink($post_id))) . '">Previous</a>';
            }
            if ($paged < $total_pages) {
                $output .= '<a href="' . esc_url(add_query_arg(['paged' => $paged + 1], get_permalink($post_id))) . '">Next</a>';
            }
            $output .= '</div>';
        }
    } else {
        $output .= '<p>Invalid gallery ID.</p>';
    }
 
    return $output;
}
 

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
    'admin_init', function () {
        error_log('POST Data: ' . print_r($_POST, true));
        error_log('REQUEST Data: ' . print_r($_REQUEST, true));
    }
);
