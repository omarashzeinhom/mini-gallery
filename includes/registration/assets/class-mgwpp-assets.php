<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Assets
{

    /**
     * Static flag to force assets loading when the shortcode is used.
     *
     * @var bool
     */
    private static $load_assets = false;

    /**
     * Static value for gallery type provided via shortcode.
     *
     * @var string
     */
    private static $shortcode_gallery_type = '';

    /**
     * Enable asset loading via shortcode.
     *
     * Call this method in your shortcode function before output.
     */
    public static function enable_assets()
    {
        self::$load_assets = true;
    }

    /**
     * Set the gallery type to be used when enqueuing assets.
     *
     * @param string $type The gallery type as defined in your code, e.g., 'single_carousel'
     */
    public static function set_gallery_type($type)
    {
        self::$shortcode_gallery_type = $type;
    }

    /**
     * Registers frontend assets.
     */
    public function register_assets()
    {
        $base_url  = MG_PLUGIN_URL . '/public/';
        $base_path = MG_PLUGIN_PATH . 'public/';
        $gallery_types_url = MG_PLUGIN_URL . '/includes/gallery-types/';

        // Single Carousel
        wp_register_script(
            'mg-single-carousel-js',
            $gallery_types_url . 'mgwpp-single-gallery/mgwpp-single-gallery.js',
            array(),
            '1.0',
            true
        );
        wp_register_style(
            'mg-single-carousel-styles',
            $gallery_types_url . 'mgwpp-single-gallery/mgwpp-single-gallery.css',
            array(),
            '1.0'
        );

        // Multi Carousel
        wp_register_script(
            'mg-multi-carousel-js',
            $gallery_types_url . 'mgwpp-multi-gallery/mgwpp-multi-gallery.js',
            array(),
            '1.0',
            true
        );
        wp_register_style(
            'mg-multi-carousel-styles',
            $gallery_types_url . 'mgwpp-multi-gallery/mgwpp-multi-gallery.css',
            array(),
            '1.0'
        );

        // Grid Styles
        wp_register_style(
            'mg-grid-styles',
            $gallery_types_url . 'mgwpp-grid-gallery/mgwpp-grid-gallery.css',
            array(),
            '1.0'
        );
        wp_register_script(
            'mg-grid-gallery-js',
            $gallery_types_url . 'mgwpp-grid-gallery/mgwpp-grid-gallery.js',
            array(),
            file_exists($base_path . 'mgwpp-grid-gallery/mgwpp-grid-gallery.js') ? filemtime($base_path . 'mgwpp-grid-gallery/mgwpp-grid-gallery.js') : '1.0',
            true
        );

        // Mega Carousel
        wp_register_style(
            'mg-mega-carousel-styles',
            $base_url . 'css/mg-mega-carousel-styles.css',
            array(),
            '1.0'
        );
        wp_register_script(
            'mg-mega-carousel-js',
            $base_url . 'js/mg-mega-carousel.js',
            array(),
            file_exists($base_path . 'js/mg-mega-carousel.js') ? filemtime($base_path . 'js/mg-mega-carousel.js') : '1.0',
            true
        );

        // Pro Carousel
        wp_register_style(
            'mgwpp-pro-carousel-styles',
            $base_url . 'css/mg-pro-carousel.css',
            array(),
            '1.0'
        );
        wp_register_script(
            'mgwpp-pro-carousel-js',
            $base_url . 'js/mg-pro-carousel.js',
            array(),
            '1.0',
            true
        );

        // Neon Carousel
        wp_register_style(
            'mgwpp-neon-carousel-styles',
            $base_url . 'css/mg-neon-carousel.css',
            array(),
            file_exists(MG_PLUGIN_PATH . 'public/css/mg-neon-carousel.css') ? filemtime(MG_PLUGIN_PATH . 'public/css/mg-neon-carousel.css') : '1.0'
        );
        wp_register_script(
            'mgwpp-neon-carousel-js',
            $base_url . 'js/mg-neon-carousel.js',
            array('jquery'),
            file_exists(MG_PLUGIN_PATH . 'public/js/mg-neon-carousel.js') ? filemtime(MG_PLUGIN_PATH . 'public/js/mg-neon-carousel.js') : '1.0',
            true
        );

        // 3D Carousel
        wp_register_style(
            'mgwpp-threed-carousel-styles',
            $base_url . 'css/mg-threed-carousel.css',
            array(),
            file_exists(MG_PLUGIN_PATH . 'public/css/mg-threed-carousel.css') ? filemtime(MG_PLUGIN_PATH . 'public/css/mg-threed-carousel.css') : '1.0'
        );
        wp_register_script(
            'mgwpp-threed-carousel-js',
            $base_url . 'js/mg-threed-carousel.js',
            array('jquery'),
            file_exists(MG_PLUGIN_PATH . 'public/js/mg-threed-carousel.js') ? filemtime(MG_PLUGIN_PATH . 'public/js/mg-threed-carousel.js') : '1.0',
            true
        );

        // Testimonials Carousel
        wp_register_style(
            'mgwpp-testimonial-carousel-styles',
            $base_url . 'css/mgwpp-testimonial-carousel.css',
            array(),
            file_exists(MG_PLUGIN_PATH . 'public/css/mgwpp-testimonial-carousel.css') ? filemtime(MG_PLUGIN_PATH . 'public/css/mgwpp-testimonial-carousel.css') : '1.0'
        );
        wp_register_script(
            'mgwpp-testimonial-carousel-js',
            $base_url . 'js/mgwpp-testimonial-carousel.js',
            array('jquery'),
            file_exists(MG_PLUGIN_PATH . 'public/js/mgwpp-testimonial-carousel.js') ? filemtime(MG_PLUGIN_PATH . 'public/js/mgwpp-testimonial-carousel.js') : '1.0',
            true
        );

        // Lightbox for Testimonials
        wp_register_script(
            'mgwpp-lightbox-js',
            $base_url . 'js/mg-lightbox.js',
            array('jquery'),
            file_exists(MG_PLUGIN_PATH . 'public/js/mg-lightbox.js') ? filemtime(MG_PLUGIN_PATH . 'public/js/mg-lightbox.js') : '1.0',
            true
        );

        // FullPage Slider
        wp_register_style(
            'mg-fullpage-slider-styles',
            $gallery_types_url . 'mgwpp-full-page-slider/mgwpp-full-page-slider.css',
            array(),
            file_exists(MG_PLUGIN_PATH . 'mgwpp-full-page-slider/mgwpp-full-page-slider.css') ? filemtime(MG_PLUGIN_PATH . 'mgwpp-full-page-slider/mgwpp-full-page-slider.css') : '1.0'
        );
        wp_register_script(
            'mg-fullpage-slider-js',
            $gallery_types_url . 'mgwpp-full-page-slider/mgwpp-full-page-slider.js',
            array('jquery'),
            file_exists(MG_PLUGIN_PATH . 'mgwpp-full-page-slider/mgwpp-full-page-slider.js') ? filemtime(MG_PLUGIN_PATH . 'mgwpp-full-page-slider/mgwpp-full-page-slider.js') : '1.0',
            true
        );

        // Spotlight Slider
        wp_register_style(
            'mg-spotlight-slider-styles',
            $base_url . 'css/mg-spotlight-carousel.css',
            array(),
            file_exists(MG_PLUGIN_PATH . 'public/css/mg-spotlight-carousel.css') ? filemtime(MG_PLUGIN_PATH . 'public/css/mg-spotlight-carousel.css') : '1.0'
        );
        wp_register_script(
            'mg-spotlight-slider-js',
            $base_url . 'js/mg-spotlight-carousel.js',
            array(),
            file_exists(MG_PLUGIN_PATH . 'public/js/mg-spotlight-carousel.js') ? filemtime(MG_PLUGIN_PATH . 'public/js/mg-spotlight-carousel.js') : '1.0',
            true
        );




        // Album Styles
        wp_register_style(
            'mg-album-styles.css',
            $base_url . 'css/mg-album-styles.css',
            array(),
            file_exists(MG_PLUGIN_PATH . '/public/css/mg-album-styles.css') ? filemtime(MG_PLUGIN_PATH . '/public/css/mg-album-styles.css') : '1.0'
        );
        wp_register_script(
            'mg-albums-styles.js',
            $base_url . '/public/js/mg-albums-styles.js',
            array(),
            file_exists(MG_PLUGIN_PATH . '/public/js/mg-albums-styles.js') ? filemtime(MG_PLUGIN_PATH . '/public/js/mg-albums-styles.js') : '1.0',
            true
        );
    }

    /**
     * Enqueues frontend assets conditionally.
     *
     * Assets will be enqueued if either the page is a singular mgwpp_gallery
     * or if the MGWPP_Assets::enable_assets() flag has been set by the shortcode.
     */
    public function enqueue_assets()
    {
        // Always enqueue the universal init script.
        wp_enqueue_script('mg-universal-init');
        // Albums always enqueue d
        wp_enqueue_script('mg-album-styles.css');
        wp_enqueue_style('mg-album-styles.js');



        // Check if we are on a single gallery page or if the shortcode flag is set.
        if (is_singular('mgwpp_gallery') || self::$load_assets) {

            // When using the shortcode, we might not have the proper post meta available.
            // If a gallery type was set via the shortcode, use that instead.
            if (self::$shortcode_gallery_type) {
                $gallery_type = self::$shortcode_gallery_type;

                // ✅ ADD DEBUG LOG HERE
                error_log('MGWPP: Enqueueing assets for gallery type: ' . $gallery_type);
            } else {
                // Otherwise, try the post meta.
                $gallery_type = get_post_meta(get_the_ID(), 'gallery_type', true);
            }

            // Fallback to a default type if still empty.
            if (empty($gallery_type)) {
                $gallery_type = 'single_carousel';
            }

            switch ($gallery_type) {
                case 'single_carousel':
                    wp_enqueue_script('mg-single-carousel-js');
                    wp_enqueue_style('mg-single-carousel-styles');
                    break;
                case 'multi_carousel':
                    wp_enqueue_script('mg-multi-carousel-js');
                    wp_enqueue_style('mg-multi-carousel-styles');
                    break;
                case 'grid':
                    wp_enqueue_style('mg-grid-styles');
                    wp_enqueue_script('mg-grid-gallery');

                    break;
                case 'mega_slider':
                    wp_enqueue_script('mg-mega-carousel-js');
                    wp_enqueue_style('mg-mega-carousel-styles');
                    break;
                case 'pro_carousel':
                    wp_enqueue_style('mgwpp-pro-carousel-styles');
                    wp_enqueue_script('mgwpp-pro-carousel-js');
                    break;
                case 'neon_carousel':
                    wp_enqueue_script('mgwpp-neon-carousel-js');
                    wp_enqueue_style('mgwpp-neon-carousel-styles');
                    break;
                case 'threed_carousel':
                    wp_enqueue_script('mgwpp-threed-carousel-js');
                    wp_enqueue_style('mgwpp-threed-carousel-styles');
                    break;
                case 'testimonials_carousel':
                    wp_enqueue_script('mgwpp-testimonial-carousel-js');
                    wp_enqueue_style('mgwpp-testimonial-carousel-styles');
                    break;
                default:
                    // No additional assets enqueued if the gallery type is not recognized.
                    break;
            }
        }
    }

    /**
     * Enqueues admin assets conditionally.
     *
     * @param string $hook_suffix Current admin page hook.
     */
    public function enqueue_admin_assets($hook_suffix)
    {
        // Only load assets on post editing screens.
        if (in_array($hook_suffix, array('post.php', 'post-new.php'), true)) {
            $screen = get_current_screen();
            if ($screen && ('post' === $screen->base || 'page' === $screen->base)) {
                wp_enqueue_script('mg-admin-carousel');
                wp_enqueue_style('mg-admin-styles');
                wp_enqueue_style('mg-styles');
            }
        }
    }

    /**
     * Hooks registration and enqueue functions into WordPress.
     */
    public function init()
    {
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'), 20);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public static function enqueue_assets_for($gallery_type)
    {
        $style_handle  = 'mgwpp-' . $gallery_type . '-styles';
        $script_handle = 'mgwpp-' . $gallery_type . '-js';

        if (wp_style_is($style_handle, 'registered')) {
            wp_enqueue_style($style_handle);
        }

        if (wp_script_is($script_handle, 'registered')) {
            wp_enqueue_script($script_handle);
        }
    }
}

// Hook the class initialization on 'init' so that all hooks are ready.
add_action('init', function () {
    $mgwpp_assets = new MGWPP_Assets();
    $mgwpp_assets->init();
});
