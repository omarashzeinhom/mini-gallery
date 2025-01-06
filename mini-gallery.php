<?php

/**
 * Plugin Name: Mini Gallery
 * Description: A WordPress plugin to display a simple custom gallery.
 * Version: 1.1
 * Author: Omar Ashraf Zeinhom AbdElRahman | ANDGOEDU
 * License: GPLv2
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-mgwpp-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mgwpp-capabilities.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mgwpp-upload.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mgwpp-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mgwpp-gallery-manager.php'; // Include the gallery manager class
require_once plugin_dir_path(__FILE__) . 'includes/class-mgwpp-uninstall.php'; // Include the uninstall class
//require_once plugin_dir_path(__FILE__) . 'includes/class-mgwpp-admin-menu.php'; // Include the admin menu class

// Initialize plugin
function mgwpp_initialize_plugin()
{
    // Call the static methods correctly
    MGWPP_Post_Type::mgwpp_register_post_type();
    MGWPP_Capabilities::mgwpp_add_marketing_team_role();
    MGWPP_Capabilities::mgwpp_capabilities();
    MGWPP_Gallery_Manager::mgwpp_register_gallery_delete_action(); // Register gallery deletion
    MGWPP_Uninstall::mgwpp_register_uninstall_hook(); // Register the uninstall hook
    MGWPP_Gallery_ShortCode::mgwpp_gallery_register_shortcode();
   //MGWPP_Gallery_ShortCode::mgwpp_render_gallery($atts);
    MGWPP_Admin::mgwpp_register_menu(); // Register the admin menu
}
add_action('init', 'mgwpp_initialize_plugin');
