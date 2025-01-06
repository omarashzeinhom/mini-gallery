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
    mgwpp_register_post_type();
    mgwpp_add_marketing_team_role();
    mgwpp_capabilities();
}
add_action('init' , 'mgwpp_initialize_plugin');



