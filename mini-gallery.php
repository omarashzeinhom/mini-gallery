<?php
/**
 * Plugin Name: Mini Gallery
 * Description: A WordPress plugin to display a simple custom gallery with album support.
 * Version: 1.2
 * Author: Omar Ashraf Zeinhom AbdElRahman | ANDGOEDU
 * License: GPLv2
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin version constant
define('MGWPP_VERSION', '1.2');

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/registration/class-mgwpp-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/class-mgwpp-capabilities.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions/class-mgwpp-upload.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions/class-mgwpp-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/class-mgwpp-gallery-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/class-mgwpp-uninstall.php';
require_once plugin_dir_path(__FILE__) . 'public/mgwpp-gallery-shortcode.php';

// Album-related includes
require_once plugin_dir_path(__FILE__) . 'includes/registration/class-mgwpp-album-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/class-mgwpp-album-display.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration/class-mgwpp-album-capabilities.php';

// Initialize plugin
function mgwpp_initialize_plugin()
{
    // Register shortcodes
    add_shortcode('mgwpp_gallery', 'mgwpp_gallery_shortcode');
    add_shortcode('mgwpp_album', array('MGWPP_Album_Display', 'album_shortcode'));
 
    // Call the static methods to initialize classes
    MGWPP_Post_Type::mgwpp_register_post_type();
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
    MGWPP_Capabilities::mgwpp_add_marketing_team_role();
    MGWPP_Capabilities::mgwpp_capabilities();
    MGWPP_Album_Capabilities::mgwpp_album_capabilities();
    MGWPP_Gallery_Manager::mgwpp_register_gallery_delete_action();
    MGWPP_Uninstall::mgwpp_register_uninstall_hook();
    MGWPP_Admin::mgwpp_register_menu();
}
add_action('init', 'mgwpp_initialize_plugin');

// Enqueue front-end scripts and styles
function mgwpp_enqueue_assets()
{
    // Register and enqueue existing assets
    wp_register_script('mg-carousel', plugin_dir_url(__FILE__) . 'public/js/carousel.js', array(), MGWPP_VERSION, true);
    wp_register_style('mg-styles', plugin_dir_url(__FILE__) . 'public/css/styles.css', array(), MGWPP_VERSION);

    // Register and enqueue album-specific assets
    wp_register_style('mg-album-styles', plugin_dir_url(__FILE__) . 'public/css/album-styles.css', array(), MGWPP_VERSION);

    if (!is_admin()) {
        wp_enqueue_script('mg-carousel');
        wp_enqueue_style('mg-styles');
        wp_enqueue_style('mg-album-styles');
    }
}
add_action('wp_enqueue_scripts', 'mgwpp_enqueue_assets');

// Enqueue admin assets
function mgwpp_enqueue_admin_assets($hook)
{
    wp_register_script('mg-admin-carousel', plugin_dir_url(__FILE__) . 'admin/js/mg-scripts.js', array('jquery'), MGWPP_VERSION, true);
    wp_register_style('mg-admin-styles', plugin_dir_url(__FILE__) . 'admin/css/mg-styles.css', array(), MGWPP_VERSION);
    
    // Register album-specific admin assets
    wp_register_style('mg-admin-album-styles', plugin_dir_url(__FILE__) . 'admin/css/album-styles.css', array(), MGWPP_VERSION);

    wp_enqueue_script('mg-admin-carousel');
    wp_enqueue_style('mg-admin-styles');
    wp_enqueue_style('mg-admin-album-styles');
}
add_action('admin_enqueue_scripts', 'mgwpp_enqueue_admin_assets');

// Gallery shortcode function remains unchanged
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
// Activation Hook
function mgwpp_plugin_activate()
{
    MGWPP_Post_Type::mgwpp_register_post_type();
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
    MGWPP_Capabilities::mgwpp_add_marketing_team_role();
    MGWPP_Capabilities::mgwpp_capabilities();
    MGWPP_Album_Capabilities::mgwpp_album_capabilities();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'mgwpp_plugin_activate');

// Deactivation Hook
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
    // Delete galleries
    $sowar = get_posts(
        array(
        'post_type' => 'mgwpp_soora',
        'numberposts' => -1,
        'post_status' => 'any'
        )
    );
    foreach ($sowar as $gallery_image) {
        wp_delete_post($gallery_image->ID, true);
    }

    // Delete albums
    $albums = get_posts(
        array(
        'post_type' => 'mgwpp_album',
        'numberposts' => -1,
        'post_status' => 'any'
        )
    );
    foreach ($albums as $album) {
        wp_delete_post($album->ID, true);
    }

    // Clean up roles and capabilities
    remove_role('marketing_team');
    
    // Remove album-specific capabilities from existing roles
    $roles = array('administrator', 'marketing_team');
    $album_capabilities = array(
        'edit_mgwpp_album',
        'read_mgwpp_album',
        'delete_mgwpp_album',
        'edit_mgwpp_albums',
        'edit_others_mgwpp_albums',
        'publish_mgwpp_albums',
        'read_private_mgwpp_albums'
    );

    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            foreach ($album_capabilities as $cap) {
                $role->remove_cap($cap);
            }
        }
    }
}
register_uninstall_hook(__FILE__, 'mgwpp_plugin_uninstall');