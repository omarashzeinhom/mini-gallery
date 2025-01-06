<?php

/**
 * Plugin Name: Mini Gallery
 * Description: A WordPress plugin to display a simple custom gallery.
 * Version: 1.1
 * Author: Omar Ashraf Zeinhom AbdElRahman | ANDGOEDU
 * License: GPLv2
 */

 if (!defined('ABSPATH')) exit;

 require_once plugin_dir_path( __FILE__ ) . 'includes/class-mgwpp-post-type.php';
 require_once plugin_dir_path(__FILE__) . 'includes/class-mgwpp-capabilities.php';
 require_once plugin_dir_path(__FILE__) . 'includes/class-mgwpp-upload.php';
 require_once plugin_dir_path(__FILE__) . 'includes/class-mgwpp-admin.php';
 
 function mgwpp_initialize_plugin(){
     // Call the static method correctly to register the custom post type
     MGWPP_Post_Type::mgwpp_register_post_type();
     
     // Call the function to add custom roles if needed
     MGWPP_Capabilities::mgwpp_add_marketing_team_role(); // This should be a method in MGWPP_Capabilities class
 
     // Call the function to set up custom capabilities if needed
     MGWPP_Capabilities::mgwpp_capabilities(); // This should be a method in MGWPP_Capabilities class
 }
 
 add_action('init', 'mgwpp_initialize_plugin');
?> 