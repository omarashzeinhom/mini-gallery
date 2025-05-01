<?php
if (! defined('ABSPATH')) {
    exit;
}
require_once plugin_dir_path(__FILE__) . 'class-mgwpp-assets.php';

class MGWPP_Admin_Assets {
    public function __construct() {
        // Hook registration and enqueueing separately
        add_action('admin_enqueue_scripts', [$this, 'register_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_assets() {
        // Register styles first
        wp_register_style(
            'mg-admin-styles', // Fixed handle to match what you're trying to enqueue
            MG_PLUGIN_URL . '/admin/css/mg-admin-styles.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/admin/css/mg-admin-styles.css')
        );

        wp_register_style(
            'mgwpp-editor-styles', // Renamed for clarity
            MG_PLUGIN_URL . '/admin/css/editor.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/admin/css/editor.css')
        );

        // Register scripts
        wp_register_script(
            'mgwpp-admin-scripts',
            MG_PLUGIN_URL . '/admin/js/mg-admin-scripts.js',
            ['jquery', 'media-views', 'wp-i18n', 'wp-color-picker'],
            filemtime(MG_PLUGIN_PATH . '/admin/js/mg-admin-scripts.js'),
            true
        );
    }

    public function enqueue_assets($hook) {
        // Only load on specific plugin pages
        if (!$this->is_plugin_page($hook)) {
            return;
        }

        // Load media components for pages that need them
        $this->load_media_dependencies();

        // Enqueue core assets for all plugin pages
        wp_enqueue_style('mg-admin-styles');
        wp_enqueue_style('mgwpp-editor-styles');
        wp_enqueue_script('mgwpp-admin-scripts');

        // Localize only when script is enqueued
        $this->localize_scripts();
    }

    private function is_plugin_page($hook) {
        $plugin_pages = [
            'toplevel_page_mgwpp_dashboard',      // Dashboard
            'gallery_page_mgwpp_galleries',       // Galleries
            'gallery_page_mgwpp_albums',          // Albums
            'gallery_page_mgwpp_testimonials',    // Testimonials
            'gallery_page_mgwpp_security'         // Security
        ];

        return in_array($hook, $plugin_pages);
    }


    private function load_media_dependencies() {
        // Only load media on pages that need it
        wp_enqueue_media();
        wp_enqueue_script('media-views');
        wp_enqueue_script('media-grid');
        wp_enqueue_script('media-editor');
    }

    private function localize_scripts() {
        wp_localize_script('mgwpp-admin-scripts', 'mgwppMedia', [
            'gallery_success' => __('Gallery saved successfully!', 'mini-gallery'),
            'album_success' => __('Album updated successfully!', 'mini-gallery'),
            'generic_error' => __('An error occurred. Please try again.', 'mini-gallery'),
            'text_title' => __('Select Gallery Images', 'mini-gallery'),
            'text_select' => __('Add to Gallery', 'mini-gallery'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp_nonce')
        ]);

        // Load translations
        wp_set_script_translations(
            'mgwpp-admin-scripts',
            'mini-gallery',
            MG_PLUGIN_PATH . '/languages'
        );
    }
}