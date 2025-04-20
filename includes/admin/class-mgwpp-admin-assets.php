<?php
if (! defined('ABSPATH')) {
    exit;
}
// File: includes/admin/class-mgwpp-admin-assets.php
class MGWPP_Admin_Assets
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'register_assets'], 5); // Early registration
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets'], 10);
    }

    public static function init()
    {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function enqueue_assets($hook)
    {
        // Only load on our plugin pages (check if $hook contains "mgwpp_")
        if (strpos($hook, 'mgwpp_') === false) {
            return;
        }

        // Enqueue WordPress media and thickbox assets
        wp_enqueue_media();
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');



        // Enqueue Main admin script (with dependencies including color picker)
        wp_enqueue_script(
            'mgwpp-admin-js',
            MG_PLUGIN_URL . '/admin/js/mg-admin-scripts.js',
            ['jquery', 'media-upload', 'thickbox', 'wp-color-picker'], // added 'wp-color-picker'
            filemtime(MG_PLUGIN_PATH . '/admin/js/mg-admin-scripts.js'),
            true
        );

        // Enqueue Admin styles
        wp_enqueue_style(
            'mgwpp-admin-styles',
            MG_PLUGIN_URL . '/admin/css/mg-admin-styles.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/admin/css/mg-admin-styles.css')
        );

        // Localize
        wp_localize_script(
            'mgwpp-admin-js',
            'mgwppMedia',
            [
                'ajax_url'        => admin_url('admin-ajax.php'),
                'nonce'           => wp_create_nonce('mgwpp_nonce'),
                'text_title'      => __('Select Gallery Images', 'mini-gallery'),
                'text_select'     => __('Use Selected', 'mini-gallery'),
                'gallery_success' => __('Gallery created successfully!', 'mini-gallery'),
                'generic_error'   => __('An error occurred. Please try again.', 'mini-gallery'),
            ]
        );
    }
     // Add this new method to force load on dashboard
    public function force_load_dashboard_styles($hook)
    {
        if ($hook === 'toplevel_page_mgwpp_dashboard') {
            wp_enqueue_style('mg-admin-styles');
        }
    }
}
