<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Admin_Assets {
    public static function enqueue_admin_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'mgwpp_') === false) {
            return;
        }

        // Enqueue core WordPress media components
        wp_enqueue_media();
        
        // Explicitly load required media dependencies
        wp_enqueue_script('media-views');
        wp_enqueue_script('media-grid');
        wp_enqueue_script('media-editor');

        // Register main script with verified dependencies
        wp_register_script(
            'mgwpp-admin-scripts',
            MG_PLUGIN_URL . '/admin/js/mg-admin-scripts.js',
            ['jquery', 'media-views', 'wp-i18n'],
            filemtime(MG_PLUGIN_PATH . '/admin/js/mg-admin-scripts.js'),
            true
        );

        // Localization
        wp_localize_script('mgwpp-admin-scripts', 'mgwppMedia', [
            'gallery_success' => __('Gallery saved successfully!', 'mini-gallery'),
            'album_success' => __('Album updated successfully!', 'mini-gallery'),
            'generic_error' => __('An error occurred. Please try again.', 'mini-gallery'),
            'text_title' => __('Select Gallery Images', 'mini-gallery'),
            'text_select' => __('Add to Gallery', 'mini-gallery'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp_nonce')
        ]);

        // Enable translations
        wp_set_script_translations('mgwpp-admin-scripts', 'mini-gallery', MG_PLUGIN_PATH . '/languages');

        wp_enqueue_script('mgwpp-admin-scripts');

        // Enqueue styles
        wp_enqueue_style(
            'mgwpp-admin-styles',
            MG_PLUGIN_URL . '/admin/css/mg-admin-styles.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/admin/css/mg-admin-styles.css')
        );
    }
}