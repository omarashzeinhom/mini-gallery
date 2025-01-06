<?php
class MGWPP_Admin
{
    public static function enqueue_asset()
    {
        wp_register_script('mgwpp-admin-scripts', plugin_dir_url(__FILE__) . 'admin/js/mg-scripts.js', array('jquery', '1.0', true));
        wp_enqueue_script('mgwpp-admin-scripts');
        wp_register_style('mgwpp-admin-styles', plugin_dir_url(__FILE__) . 'admin/css/mg-styles.css', array(), '1.0');
        wp_enqueue_style('mgwpp-admin-styles');
    }
}
add_action('admin_enqueue_scripts', array('MGWPP_Admin', 'enqueue_assets'));


add_action('admin_menu', function () {
    add_menu_page(
        'Mini Gallery',
        'Mini Gallery',
        'edit_mgwpp_sooras',
        'mini-gallery',
        array('MGWPP_Admin', 'render_plugin_page'),
        'dashicons-format-gallery',
        6
    );
});
