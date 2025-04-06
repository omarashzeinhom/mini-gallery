<?php
if (! defined('ABSPATH')) {
    exit;
}
// /admin/assets/class-mgwpp-admin-assets.php
class MGWPP_Admin_Assets {

    /**
     * Register and enqueue admin assets (scripts and styles).
     */
    public static function enqueue_assets() {
        wp_register_script(
            'mgwpp-admin-scripts', 
            MG_PLUGIN_URL . '/admin/js/mg-admin-scripts.js', 
            array('jquery'), 
            '1.0', 
            true
        );
        wp_enqueue_script('mgwpp-admin-scripts');

        wp_register_style(
            'mgwpp-admin-styles', 
            MG_PLUGIN_URL . '/admin/css/mg-admin-styles.css', 
            array(), 
            '1.0'
        );
        wp_enqueue_style('mgwpp-admin-styles');
    }
}
