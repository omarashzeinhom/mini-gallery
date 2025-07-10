<?php
if (! defined('ABSPATH')) {
    exit;
}
require_once plugin_dir_path(__FILE__) . 'class-mgwpp-assets.php';
require_once plugin_dir_path(__FILE__) . 'class-mgwpp-settings.php';

class MGWPP_Admin_Assets
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'register_assets']);
        add_action('admin_enqueue_scripts', [$this, 'maybe_enqueue_assets']);
    }

    public function register_assets()
    {
        // Register variables first
        wp_register_style(
            'mgwpp-variables',
            MG_PLUGIN_URL . '/includes/admin/css/variables.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/css/variables.css')
        );

        // Main admin styles with dependency
        wp_register_style(
            'mg-admin-styles',
            MG_PLUGIN_URL . '/includes/admin/css/mg-admin-styles.css',
            ['mgwpp-variables'],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/css/mg-admin-styles.css')
        );

        // Dark mode toggle script
        wp_register_script(
            'mgwpp-dark-mode',
            MG_PLUGIN_URL . '/includes/admin/js/dark-mode.js',
            ['jquery'],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/js/dark-mode.js'),
            true
        );
    }

    public function maybe_enqueue_assets($hook)
    {
        if (!$this->is_plugin_page($hook)) {
            return;
        }

        // Enqueue core assets
        wp_enqueue_style('mg-admin-styles');
        wp_enqueue_script('mgwpp-dark-mode');

        // Localize script
        wp_localize_script('mgwpp-dark-mode', 'mgwppDarkMode', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp-dark-mode-nonce'),
            'currentTheme' => MGWPP_Inner_Header::get_user_theme_preference()
        ]);
    }

    public function enqueue_assets($hook)
    {
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

    private function is_plugin_page($hook)
    {
        $plugin_pages = [
            'toplevel_page_mgwpp_dashboard',      // Dashboard
            'gallery_page_mgwpp_galleries',       // Galleries
            'gallery_page_mgwpp_albums',          // Albums
            'gallery_page_mgwpp_testimonials',    // Testimonials
            'gallery_page_mgwpp_security',         // Security
            'gallery_page_mgwpp_settings',
        ];

        return in_array($hook, $plugin_pages);
    }


    private function load_media_dependencies()
    {
        // Only load media on pages that need it
        wp_enqueue_media();
        wp_enqueue_script('media-views');
        wp_enqueue_script('media-grid');
        wp_enqueue_script('media-editor');
    }

    private function localize_scripts()
    {
        wp_localize_script('mgwpp-admin-scripts', 'mgwppMedia', [
            'current_theme' => MGWPP_Inner_Header::get_user_theme_preference(),
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
