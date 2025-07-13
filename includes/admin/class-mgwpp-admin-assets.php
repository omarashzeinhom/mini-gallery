<?php
if (! defined('ABSPATH')) {
    exit;
}
// File: includes/admin/class-mgwpp-admin-assets.php
class MGWPP_Admin_Assets
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'mgwpp_enqueue_admin_assets']);
    }

    public function mgwpp_enqueue_admin_assets($hook)
    {
        // Allow these plugin pages
        $plugin_pages = [
            'toplevel_page_mgwpp_dashboard',
            'gallery_page_mgwpp-galleries',
            'gallery_page_mgwpp-edit-gallery',
            'admin_page_mgwpp-edit-gallery' // Add this for the edit page
        ];

        // Check if it's one of our plugin pages
        if (!in_array($hook, $plugin_pages) && strpos($hook, 'mgwpp') === false) {
            return;
        }
        // Load core assets first
        $this->load_core_assets();

        // Load dashboard-specific assets
        if ($hook === 'toplevel_page_mgwpp_dashboard') {
            $this->load_dashboard_assets();
        }

        // WordPress media + thickbox
        wp_enqueue_media();
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');

        // Main admin JS
        wp_enqueue_script(
            'mgwpp-admin-js',
            MG_PLUGIN_URL . '/includes/admin/js/mgwpp-admin-scripts.js',
            ['jquery', 'media-upload', 'thickbox', 'wp-color-picker'],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/js/mgwpp-admin-scripts.js'),
            true
        );

        // Admin CSS
        wp_enqueue_style(
            'mgwpp-admin-styles',
            MG_PLUGIN_URL . '/includes/admin/css/mgwpp-admin-styles.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/css/mgwpp-admin-styles.css')
        );

        // Localize JS
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

    private function load_core_assets()
    {
        // Main admin Colours Root Palette for Dark/Light Mode
        wp_enqueue_style(
            'mgwpp-admin-core-css-variables',
            MG_PLUGIN_URL . '/includes/admin/css/variables.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/css/variables.css')
        );


        // Main admin CSS
        wp_enqueue_style(
            'mgwpp-admin-core',
            MG_PLUGIN_URL . '/includes/admin/css/mgwpp-admin-styles.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/css/mgwpp-admin-styles.css')
        );

        // Main admin JS
        wp_enqueue_script(
            'mgwpp-admin-core',
            MG_PLUGIN_URL . '/includes/admin/js/mgwpp-admin-scripts.js',
            ['jquery', 'media-upload', 'thickbox', 'wp-color-picker'],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/js/mgwpp-admin-scripts.js'),
            true
        );


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


        /* DARK LIGHT MODE Toggle SWITCH Theme toggle assets */


        wp_enqueue_style(
            'mgwpp-theme-toggle',
            MG_PLUGIN_URL . '/includes/admin/views/inner-header/mgwpp-inner-header.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/views/inner-header/mgwpp-inner-header.css')
        );

        wp_enqueue_script(
            'mgwpp-theme-toggle',
            MG_PLUGIN_URL . '/includes/admin/views/inner-header/mgwpp-inner-header.js',
            ['jquery'],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/views/inner-header/mgwpp-inner-header.js'),
            true
        );

        // Localize for ALL admin pages
        wp_localize_script('mgwpp-theme-toggle', 'mgwppHeader', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp-theme-nonce')
        ]);
    }


    private function load_dashboard_assets()
    {
        $dashboard_path = '/includes/admin/views/dashboard/';

        // Dashboard CSS
        wp_enqueue_style(
            'mgwpp-dashboard',
            MG_PLUGIN_URL . $dashboard_path . 'mgwpp-dashboard-view.css',
            ['mgwpp-admin-core'], // Correct dependency
            filemtime(MG_PLUGIN_PATH . $dashboard_path . 'mgwpp-dashboard-view.css')
        );

        // Dashboard JS
        wp_enqueue_script(
            'mgwpp-dashboard',
            MG_PLUGIN_URL . $dashboard_path . 'mgwpp-dashboard-view.js',
            ['mgwpp-admin-core'], // Correct dependency
            filemtime(MG_PLUGIN_PATH . $dashboard_path . 'mgwpp-dashboard-view.js'),
            true
        );
    }

    
    public static function enqueue_preview_assets($gallery_id)
    {
        // Get gallery type directly from post meta
        $gallery_type = get_post_meta($gallery_id, 'gallery_type', true);

        // Enqueue common frontend assets
        wp_enqueue_style('mgwpp-frontend');

        // Enqueue specific gallery type assets
        switch ($gallery_type) {
            case 'single_carousel':
                wp_enqueue_style('mg-single-carousel-styles');
                wp_enqueue_script('mg-single-carousel-js');
                break;
            case 'multi_carousel':
                wp_enqueue_style('mg-multi-carousel-styles');
                wp_enqueue_script('mg-multi-carousel-js');
                break;
            case 'grid':
                wp_enqueue_style('mg-grid-styles');
                wp_enqueue_script('mg-grid-gallery-js');
                break;
            case 'mega_slider':
                wp_enqueue_style('mg-mega-carousel-styles');
                wp_enqueue_script('mg-mega-carousel-js');
                break;
            case 'pro_carousel':
                wp_enqueue_style('mgwpp-pro-carousel-styles');
                wp_enqueue_script('mgwpp-pro-carousel-js');
                break;
            case 'neon_carousel':
                wp_enqueue_style('mgwpp-neon-carousel-styles');
                wp_enqueue_script('mgwpp-neon-carousel-js');
                break;
            case 'threed_carousel':
                wp_enqueue_style('mgwpp-threed-carousel-styles');
                wp_enqueue_script('mgwpp-threed-carousel-js');
                break;
            case 'testimonials_carousel':
                wp_enqueue_style('mgwpp-testimonial-carousel-styles');
                wp_enqueue_script('mgwpp-testimonial-carousel-js');
                break;
            case 'full_page_slider':
                wp_enqueue_style('mg-fullpage-slider-styles');
                wp_enqueue_script('mg-fullpage-slider-js');
                break;
            case 'spotlight_carousel':
                wp_enqueue_style('mg-spotlight-slider-styles');
                wp_enqueue_script('mg-spotlight-slider-js');
                break;
        }

        // Add initialization script
        add_action('wp_footer', function () use ($gallery_type) {
            echo '<script>';
            switch ($gallery_type) {
                case 'single_carousel':
                    echo 'if (typeof MGWPP_SingleCarousel !== "undefined") MGWPP_SingleCarousel.init();';
                    break;
                case 'multi_carousel':
                    echo 'if (typeof MGWPP_MultiCarousel !== "undefined") MGWPP_MultiCarousel.init();';
                    break;
                case 'mega_slider':
                    echo 'if (typeof MGWPP_MegaSlider !== "undefined") MGWPP_MegaSlider.init();';
                    break;
                case 'pro_carousel':
                    echo 'if (typeof MGWPP_ProCarousel !== "undefined") MGWPP_ProCarousel.init();';
                    break;
            }
            echo '</script>';
        }, 999);
    }
}
