<?php
if (! defined('ABSPATH')) {
    exit;
}
// File: includes/admin/class-mgwpp-admin-assets.php
class MGWPP_Admin_Assets {
    public function enqueue_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'mgwpp_') === false) return;

        wp_enqueue_media();
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');

        // Main admin script
        wp_enqueue_script(
            'mgwpp-admin',
            MG_PLUGIN_URL . '/admin/js/admin.js',
            ['jquery', 'media-upload', 'thickbox'],
            filemtime(MG_PLUGIN_PATH . '/admin/js/admin.js')
        );

        // Admin styles
        wp_enqueue_style(
            'mgwpp-admin',
            MG_PLUGIN_URL . '/admin/css/admin.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/admin/css/admin.css')
        );

        // Localize script with translations
        wp_localize_script('mgwpp-admin', 'mgwppData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp_nonce'),
            'i18n' => [
                'select_images' => __('Select Images', 'mini-gallery'),
                'add_to_gallery' => __('Add to Gallery', 'mini-gallery')
            ]
        ]);
    }
}