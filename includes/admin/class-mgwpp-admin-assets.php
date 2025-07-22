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
            'admin_page_mgwpp-edit-gallery' //  this for the edit page
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

        add_action('admin_enqueue_scripts', [$this, 'conditional_asset_loading'], 20);
    }
    public function conditional_asset_loading()
    {
        // Only run on our plugin pages
        $screen = get_current_screen();
        if (strpos($screen->id, 'mgwpp') === false) return;

        $module_loader = new MGWPP_Module_Manager();
        $submodules_view = new MGWPP_SubModules_View($module_loader);
        $submodules_view->conditional_asset_loading();
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

    /**
     * Get version for gallery type assets - uses plugin version or file modification time
     */
    private static function get_gallery_asset_version($asset_path)
    {
        // Try to get plugin version first (if available)
        if (defined('MG_VERSION')) {
            return MGWPP_ASSET_VERSION;
        }
        
        // Fallback to file modification time if file exists
        if (defined('MG_PLUGIN_PATH') && file_exists(MG_PLUGIN_PATH . $asset_path)) {
            return filemtime(MG_PLUGIN_PATH . $asset_path);
        }
        
        // Final fallback to current timestamp
        return time();
    }

    public static function enqueue_preview_assets($gallery_id)
    {
        // Get gallery type directly from post meta
        $gallery_type = get_post_meta($gallery_id, 'gallery_type', true);

        // Enqueue common frontend assets with version
        if (!wp_style_is('mgwpp-frontend', 'registered')) {
            wp_register_style(
                'mgwpp-frontend',
                MG_PLUGIN_URL . '/includes/assets/css/mgwpp-frontend.css',
                [],
                self::get_gallery_asset_version('/includes/assets/css/mgwpp-frontend.css')
            );
        }
        wp_enqueue_style('mgwpp-frontend');

        // Enqueue specific gallery type assets with versions
        switch ($gallery_type) {
            case 'single_carousel':
                if (!wp_style_is('mg-single-carousel-styles', 'registered')) {
                    wp_register_style(
                        'mg-single-carousel-styles',
                        MG_PLUGIN_URL . '/includes/assets/css/single-carousel.css',
                        ['mgwpp-frontend'],
                        self::get_gallery_asset_version('/includes/assets/css/single-carousel.css')
                    );
                }
                if (!wp_script_is('mg-single-carousel-js', 'registered')) {
                    wp_register_script(
                        'mg-single-carousel-js',
                        MG_PLUGIN_URL . '/includes/assets/js/single-carousel.js',
                        ['jquery'],
                        self::get_gallery_asset_version('/includes/assets/js/single-carousel.js'),
                        true
                    );
                }
                wp_enqueue_style('mg-single-carousel-styles');
                wp_enqueue_script('mg-single-carousel-js');
                break;

            case 'multi_carousel':
                if (!wp_style_is('mg-multi-carousel-styles', 'registered')) {
                    wp_register_style(
                        'mg-multi-carousel-styles',
                        MG_PLUGIN_URL . '/includes/assets/css/multi-carousel.css',
                        ['mgwpp-frontend'],
                        self::get_gallery_asset_version('/includes/assets/css/multi-carousel.css')
                    );
                }
                if (!wp_script_is('mg-multi-carousel-js', 'registered')) {
                    wp_register_script(
                        'mg-multi-carousel-js',
                        MG_PLUGIN_URL . '/includes/assets/js/multi-carousel.js',
                        ['jquery'],
                        self::get_gallery_asset_version('/includes/assets/js/multi-carousel.js'),
                        true
                    );
                }
                wp_enqueue_style('mg-multi-carousel-styles');
                wp_enqueue_script('mg-multi-carousel-js');
                break;

            case 'grid':
                if (!wp_style_is('mg-grid-styles', 'registered')) {
                    wp_register_style(
                        'mg-grid-styles',
                        MG_PLUGIN_URL . '/includes/assets/css/grid.css',
                        ['mgwpp-frontend'],
                        self::get_gallery_asset_version('/includes/assets/css/grid.css')
                    );
                }
                if (!wp_script_is('mg-grid-gallery-js', 'registered')) {
                    wp_register_script(
                        'mg-grid-gallery-js',
                        MG_PLUGIN_URL . '/includes/assets/js/grid.js',
                        ['jquery'],
                        self::get_gallery_asset_version('/includes/assets/js/grid.js'),
                        true
                    );
                }
                wp_enqueue_style('mg-grid-styles');
                wp_enqueue_script('mg-grid-gallery-js');
                break;

            case 'mega_slider':
                if (!wp_style_is('mg-mega-carousel-styles', 'registered')) {
                    wp_register_style(
                        'mg-mega-carousel-styles',
                        MG_PLUGIN_URL . '/includes/assets/css/mega-slider.css',
                        ['mgwpp-frontend'],
                        self::get_gallery_asset_version('/includes/assets/css/mega-slider.css')
                    );
                }
                if (!wp_script_is('mg-mega-carousel-js', 'registered')) {
                    wp_register_script(
                        'mg-mega-carousel-js',
                        MG_PLUGIN_URL . '/includes/assets/js/mega-slider.js',
                        ['jquery'],
                        self::get_gallery_asset_version('/includes/assets/js/mega-slider.js'),
                        true
                    );
                }
                wp_enqueue_style('mg-mega-carousel-styles');
                wp_enqueue_script('mg-mega-carousel-js');
                break;

            case 'pro_carousel':
                if (!wp_style_is('mgwpp-pro-carousel-styles', 'registered')) {
                    wp_register_style(
                        'mgwpp-pro-carousel-styles',
                        MG_PLUGIN_URL . '/includes/assets/css/pro-carousel.css',
                        ['mgwpp-frontend'],
                        self::get_gallery_asset_version('/includes/assets/css/pro-carousel.css')
                    );
                }
                if (!wp_script_is('mgwpp-pro-carousel-js', 'registered')) {
                    wp_register_script(
                        'mgwpp-pro-carousel-js',
                        MG_PLUGIN_URL . '/includes/assets/js/pro-carousel.js',
                        ['jquery'],
                        self::get_gallery_asset_version('/includes/assets/js/pro-carousel.js'),
                        true
                    );
                }
                wp_enqueue_style('mgwpp-pro-carousel-styles');
                wp_enqueue_script('mgwpp-pro-carousel-js');
                break;

            case 'neon_carousel':
                if (!wp_style_is('mgwpp-neon-carousel-styles', 'registered')) {
                    wp_register_style(
                        'mgwpp-neon-carousel-styles',
                        MG_PLUGIN_URL . '/includes/assets/css/neon-carousel.css',
                        ['mgwpp-frontend'],
                        self::get_gallery_asset_version('/includes/assets/css/neon-carousel.css')
                    );
                }
                if (!wp_script_is('mgwpp-neon-carousel-js', 'registered')) {
                    wp_register_script(
                        'mgwpp-neon-carousel-js',
                        MG_PLUGIN_URL . '/includes/assets/js/neon-carousel.js',
                        ['jquery'],
                        self::get_gallery_asset_version('/includes/assets/js/neon-carousel.js'),
                        true
                    );
                }
                wp_enqueue_style('mgwpp-neon-carousel-styles');
                wp_enqueue_script('mgwpp-neon-carousel-js');
                break;

            case 'threed_carousel':
                if (!wp_style_is('mgwpp-threed-carousel-styles', 'registered')) {
                    wp_register_style(
                        'mgwpp-threed-carousel-styles',
                        MG_PLUGIN_URL . '/includes/assets/css/threed-carousel.css',
                        ['mgwpp-frontend'],
                        self::get_gallery_asset_version('/includes/assets/css/threed-carousel.css')
                    );
                }
                if (!wp_script_is('mgwpp-threed-carousel-js', 'registered')) {
                    wp_register_script(
                        'mgwpp-threed-carousel-js',
                        MG_PLUGIN_URL . '/includes/assets/js/threed-carousel.js',
                        ['jquery'],
                        self::get_gallery_asset_version('/includes/assets/js/threed-carousel.js'),
                        true
                    );
                }
                wp_enqueue_style('mgwpp-threed-carousel-styles');
                wp_enqueue_script('mgwpp-threed-carousel-js');
                break;

            case 'testimonials_carousel':
                if (!wp_style_is('mgwpp-testimonial-carousel-styles', 'registered')) {
                    wp_register_style(
                        'mgwpp-testimonial-carousel-styles',
                        MG_PLUGIN_URL . '/includes/assets/css/testimonials-carousel.css',
                        ['mgwpp-frontend'],
                        self::get_gallery_asset_version('/includes/assets/css/testimonials-carousel.css')
                    );
                }
                if (!wp_script_is('mgwpp-testimonial-carousel-js', 'registered')) {
                    wp_register_script(
                        'mgwpp-testimonial-carousel-js',
                        MG_PLUGIN_URL . '/includes/assets/js/testimonials-carousel.js',
                        ['jquery'],
                        self::get_gallery_asset_version('/includes/assets/js/testimonials-carousel.js'),
                        true
                    );
                }
                wp_enqueue_style('mgwpp-testimonial-carousel-styles');
                wp_enqueue_script('mgwpp-testimonial-carousel-js');
                break;

            case 'full_page_slider':
                if (!wp_style_is('mg-fullpage-slider-styles', 'registered')) {
                    wp_register_style(
                        'mg-fullpage-slider-styles',
                        MG_PLUGIN_URL . '/includes/assets/css/fullpage-slider.css',
                        ['mgwpp-frontend'],
                        self::get_gallery_asset_version('/includes/assets/css/fullpage-slider.css')
                    );
                }
                if (!wp_script_is('mg-fullpage-slider-js', 'registered')) {
                    wp_register_script(
                        'mg-fullpage-slider-js',
                        MG_PLUGIN_URL . '/includes/assets/js/fullpage-slider.js',
                        ['jquery'],
                        self::get_gallery_asset_version('/includes/assets/js/fullpage-slider.js'),
                        true
                    );
                }
                wp_enqueue_style('mg-fullpage-slider-styles');
                wp_enqueue_script('mg-fullpage-slider-js');
                break;

            case 'spotlight_carousel':
                if (!wp_style_is('mg-spotlight-slider-styles', 'registered')) {
                    wp_register_style(
                        'mg-spotlight-slider-styles',
                        MG_PLUGIN_URL . '/includes/assets/css/spotlight-carousel.css',
                        ['mgwpp-frontend'],
                        self::get_gallery_asset_version('/includes/assets/css/spotlight-carousel.css')
                    );
                }
                if (!wp_script_is('mg-spotlight-slider-js', 'registered')) {
                    wp_register_script(
                        'mg-spotlight-slider-js',
                        MG_PLUGIN_URL . '/includes/assets/js/spotlight-carousel.js',
                        ['jquery'],
                        self::get_gallery_asset_version('/includes/assets/js/spotlight-carousel.js'),
                        true
                    );
                }
                wp_enqueue_style('mg-spotlight-slider-styles');
                wp_enqueue_script('mg-spotlight-slider-js');
                break;
        }

        //  initialization script
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