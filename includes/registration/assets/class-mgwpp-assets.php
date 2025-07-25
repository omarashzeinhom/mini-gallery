<?php

class MGWPP_Assets
{
    private static $load_assets = false;
    private static $shortcode_gallery_type = '';

    public static function get_enabled_sub_modules()
    {
        $manager = new MGWPP_Module_Manager();
        $sub_modules = $manager->get_sub_modules();
        return array_keys($sub_modules);
    }

    public static function get_enabled_main_modules()
    {
        return MGWPP_Module_Manager::get_enabled_modules();
    }

    public static function enable_assets()
    {
        self::$load_assets = true;
    }

    public static function set_gallery_type($type)
    {
        self::$shortcode_gallery_type = $type;
    }

    public function register_assets()
    {
        $enabled_sub = $this->get_enabled_sub_modules();
        $enabled_main = $this->get_enabled_main_modules();

        $base_url = MG_PLUGIN_URL . '/public/';
        $base_path = MG_PLUGIN_PATH . 'public/';
        $gallery_types_url = MG_PLUGIN_URL . '/includes/gallery-types/';
        $gallery_types_path = MG_PLUGIN_PATH . 'includes/gallery-types/';

        if (in_array('single_carousel', $enabled_sub)) {
            $single_css_path = $gallery_types_path . 'mgwpp-single-gallery/mgwpp-single-gallery.css';
            wp_register_script(
                'mg-single-carousel-js',
                $gallery_types_url . 'mgwpp-single-gallery/mgwpp-single-gallery.js',
                ['jquery'],
                file_exists($gallery_types_path . 'mgwpp-single-gallery/mgwpp-single-gallery.js')
                    ? filemtime($gallery_types_path . 'mgwpp-single-gallery/mgwpp-single-gallery.js')
                    : '1.0',
                true
            );
            wp_register_style(
                'mg-single-carousel-styles',
                $gallery_types_url . 'mgwpp-single-gallery/mgwpp-single-gallery.css',
                [],
                file_exists($single_css_path) ? filemtime($single_css_path) : '1.0'
            );
        }

        if (in_array('multi_carousel', $enabled_sub)) {
            $multi_css_path = $gallery_types_path . 'mgwpp-multi-gallery/mgwpp-multi-gallery.css';
            wp_register_script(
                'mg-multi-carousel-js',
                $gallery_types_url . 'mgwpp-multi-gallery/mgwpp-multi-gallery.js',
                [],
                file_exists($gallery_types_path . 'mgwpp-multi-gallery/mgwpp-multi-gallery.js')
                    ? filemtime($gallery_types_path . 'mgwpp-multi-gallery/mgwpp-multi-gallery.js')
                    : '1.0',
                true
            );
            wp_register_style(
                'mg-multi-carousel-styles',
                $gallery_types_url . 'mgwpp-multi-gallery/mgwpp-multi-gallery.css',
                [],
                file_exists($multi_css_path) ? filemtime($multi_css_path) : '1.0'
            );
        }

        if (in_array('grid', $enabled_sub)) {
            $grid_css_path = $gallery_types_path . 'mgwpp-grid-gallery/mgwpp-grid-gallery.css';
            wp_register_style(
                'mg-grid-styles',
                $gallery_types_url . 'mgwpp-grid-gallery/mgwpp-grid-gallery.css',
                [],
                file_exists($grid_css_path) ? filemtime($grid_css_path) : '1.0'
            );
            wp_register_script(
                'mg-grid-gallery-js',
                $gallery_types_url . 'mgwpp-grid-gallery/mgwpp-grid-gallery.js',
                [],
                file_exists($base_path . 'mgwpp-grid-gallery/mgwpp-grid-gallery.js')
                    ? filemtime($base_path . 'mgwpp-grid-gallery/mgwpp-grid-gallery.js')
                    : '1.0',
                true
            );
        }

        if (in_array('mega_slider', $enabled_sub)) {
            $mega_css_path = $gallery_types_path . 'mgwpp-mega-slider/mgwpp-mega-slider.css';
            wp_register_style(
                'mg-mega-carousel-styles',
                $gallery_types_url . 'mgwpp-mega-slider/mgwpp-mega-slider.css',
                [],
                file_exists($mega_css_path) ? filemtime($mega_css_path) : '1.0'
            );
            wp_register_script(
                'mg-mega-carousel-js',
                $gallery_types_url . 'mgwpp-mega-slider/mgwpp-mega-slider.js',
                [],
                file_exists($base_path . 'mgwpp-mega-slider/mgwpp-mega-slider.js')
                    ? filemtime($base_path . 'mgwpp-mega-slider/mgwpp-mega-slider.js')
                    : '1.0',
                true
            );
        }

        if (in_array('pro_carousel', $enabled_sub)) {
            $pro_css_path = $gallery_types_path . 'mgwpp-pro-carousel/mgwpp-pro-carousel.css';
            wp_register_style(
                'mgwpp-pro-carousel-styles',
                $gallery_types_url . 'mgwpp-pro-carousel/mgwpp-pro-carousel.css',
                [],
                file_exists($pro_css_path) ? filemtime($pro_css_path) : '1.0'
            );
            wp_register_script(
                'mgwpp-pro-carousel-js',
                $gallery_types_url . 'mgwpp-pro-carousel/mgwpp-pro-carousel.js',
                [],
                file_exists($gallery_types_path . 'mgwpp-pro-carousel/mgwpp-pro-carousel.js')
                    ? filemtime($gallery_types_path . 'mgwpp-pro-carousel/mgwpp-pro-carousel.js')
                    : '1.0',
                true
            );
        }

        if (in_array('neon_carousel', $enabled_sub)) {
            $neon_css_path = $gallery_types_path . 'mgwpp-neon-carousel/mgwpp-neon-carousel.css';
            wp_register_style(
                'mgwpp-neon-carousel-styles',
                $gallery_types_url . 'mgwpp-neon-carousel/mgwpp-neon-carousel.css',
                [],
                file_exists($neon_css_path) ? filemtime($neon_css_path) : '1.0'
            );
            wp_register_script(
                'mgwpp-neon-carousel-js',
                $gallery_types_url . 'mgwpp-neon-carousel/mgwpp-neon-carousel.js',
                ['jquery'],
                file_exists(MG_PLUGIN_PATH . 'mgwpp-neon-carousel/mgwpp-neon-carousel.js')
                    ? filemtime(MG_PLUGIN_PATH . 'mgwpp-neon-carousel/mgwpp-neon-carousel.js')
                    : '1.0',
                true
            );
        }

        if (in_array('threed_carousel', $enabled_sub)) {
            $threed_css_path = $gallery_types_path . 'mgwpp-threed-carousel/mgwpp-threed-carousel.css';
            wp_register_style(
                'mgwpp-threed-carousel-styles',
                $gallery_types_url . 'mgwpp-threed-carousel/mgwpp-threed-carousel.css',
                [],
                file_exists($threed_css_path) ? filemtime($threed_css_path) : '1.0'
            );
            wp_register_script(
                'mgwpp-threed-carousel-js',
                $gallery_types_url . 'mgwpp-threed-carousel/mgwpp-threed-carousel.js',
                ['jquery'],
                file_exists(MG_PLUGIN_PATH . 'mgwpp-threed-carousel/mgwpp-threed-carousel.js')
                    ? filemtime(MG_PLUGIN_PATH . 'mgwpp-threed-carousel/mgwpp-threed-carousel.js')
                    : '1.0',
                true
            );
        }

        if (in_array('testimonials_carousel', $enabled_sub)) {
            $testimonial_css_path = $base_path . 'css/mgwpp-testimonial-carousel.css';
            wp_register_style(
                'mgwpp-testimonial-carousel-styles',
                $base_url . 'css/mgwpp-testimonial-carousel.css',
                [],
                file_exists($testimonial_css_path) ? filemtime($testimonial_css_path) : '1.0'
            );
            wp_register_script(
                'mgwpp-testimonial-carousel-js',
                $base_url . 'js/mgwpp-testimonial-carousel.js',
                ['jquery'],
                file_exists(MG_PLUGIN_PATH . 'public/js/mgwpp-testimonial-carousel.js')
                    ? filemtime(MG_PLUGIN_PATH . 'public/js/mgwpp-testimonial-carousel.js')
                    : '1.0',
                true
            );
        }

        if (in_array('fullpage_slider', $enabled_sub)) {
            $fullpage_css_path = $gallery_types_path . 'mgwpp-full-page-slider/mgwpp-full-page-slider.css';
            wp_register_style(
                'mg-fullpage-slider-styles',
                $gallery_types_url . 'mgwpp-full-page-slider/mgwpp-full-page-slider.css',
                [],
                file_exists($fullpage_css_path) ? filemtime($fullpage_css_path) : '1.0'
            );
            wp_register_script(
                'mg-fullpage-slider-js',
                $gallery_types_url . 'mgwpp-full-page-slider/mgwpp-full-page-slider.js',
                ['jquery'],
                file_exists(MG_PLUGIN_PATH . 'mgwpp-full-page-slider/mgwpp-full-page-slider.js')
                    ? filemtime(MG_PLUGIN_PATH . 'mgwpp-full-page-slider/mgwpp-full-page-slider.js')
                    : '1.0',
                true
            );
        }

        if (in_array('spotlight_slider', $enabled_sub)) {
            $spotlight_css_path = $gallery_types_path . 'mgwpp-spotlight-carousel/mgwpp-spotlight-carousel.css';
            wp_register_style(
                'mg-spotlight-slider-styles',
                $gallery_types_url . 'mgwpp-spotlight-carousel/mgwpp-spotlight-carousel.css',
                [],
                file_exists($spotlight_css_path) ? filemtime($spotlight_css_path) : '1.0'
            );
            wp_register_script(
                'mg-spotlight-slider-js',
                $gallery_types_url . 'mgwpp-spotlight-carousel/mgwpp-spotlight-carousel.js',
                [],
                file_exists(MG_PLUGIN_PATH . 'mgwpp-spotlight-carousel/mgwpp-spotlight-carousel.js')
                    ? filemtime(MG_PLUGIN_PATH . 'mgwpp-spotlight-carousel/mgwpp-spotlight-carousel.js')
                    : '1.0',
                true
            );
        }

        if (in_array('albums', $enabled_sub)) {
            $albums_css_path = $base_path . 'css/mgwpp-albums.css';
            wp_register_style(
                'mg-album-styles',
                $base_url . 'css/mgwpp-albums.css',
                [],
                file_exists($albums_css_path) ? filemtime($albums_css_path) : '1.0'
            );
        }
    }

    public function enqueue_assets()
    {
        $enabled = $this->get_enabled_sub_modules();

        wp_enqueue_script('mg-universal-init');

        if (in_array('albums', $enabled)) {
            wp_enqueue_style('mg-album-styles');
            wp_enqueue_script('mg-albums-script');
        }

        if (is_singular('mgwpp_gallery') || self::$load_assets) {
            $gallery_type = self::$shortcode_gallery_type ?: get_post_meta(get_the_ID(), 'gallery_type', true) ?: 'single_carousel';

            switch ($gallery_type) {
                case 'single_carousel':
                    if (in_array('single_carousel', $enabled)) {
                        wp_enqueue_script('mg-single-carousel-js');
                        wp_enqueue_style('mg-single-carousel-styles');
                    }
                    break;
                case 'multi_carousel':
                    if (in_array('multi_carousel', $enabled)) {
                        wp_enqueue_script('mg-multi-carousel-js');
                        wp_enqueue_style('mg-multi-carousel-styles');
                    }
                    break;
                case 'grid':
                    if (in_array('grid', $enabled)) {
                        wp_enqueue_style('mg-grid-styles');
                        wp_enqueue_script('mg-grid-gallery');
                    }
                    break;
                case 'mega_slider':
                    if (in_array('mega_slider', $enabled)) {
                        wp_enqueue_script('mg-mega-carousel-js');
                        wp_enqueue_style('mg-mega-carousel-styles');
                    }
                    break;
                case 'pro_carousel':
                    if (in_array('pro_carousel', $enabled)) {
                        wp_enqueue_style('mgwpp-pro-carousel-styles');
                        wp_enqueue_script('mgwpp-pro-carousel-js');
                    }
                    break;
                case 'neon_carousel':
                    if (in_array('neon_carousel', $enabled)) {
                        wp_enqueue_script('mgwpp-neon-carousel-js');
                        wp_enqueue_style('mgwpp-neon-carousel-styles');
                    }
                    break;
                case 'threed_carousel':
                    if (in_array('threed_carousel', $enabled)) {
                        wp_enqueue_script('mgwpp-threed-carousel-js');
                        wp_enqueue_style('mgwpp-threed-carousel-styles');
                    }
                    break;
                case 'testimonials_carousel':
                    if (in_array('testimonials_carousel', $enabled)) {
                        wp_enqueue_script('mgwpp-testimonial-carousel-js');
                        wp_enqueue_style('mgwpp-testimonial-carousel-styles');
                    }
                    break;
                case 'full_page_slider':
                    if (in_array('full_page_slider', $enabled)) {
                        wp_enqueue_script('mgwpp-fullpage-slider-js');
                        wp_enqueue_style('mgwpp-fullpage-slider-styles');
                    }
                    break;
            }
        }
    }

    public function enqueue_admin_assets($hook_suffix)
    {
        if (in_array($hook_suffix, array('post.php', 'post-new.php'))) {
            $screen = get_current_screen();
            if ($screen && ('post' === $screen->base || 'page' === $screen->base)) {
                wp_enqueue_style('mgwpp-admin-styles');
            }
        }
    }

    public function init()
    {
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'), 20);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public static function enqueue_assets_for($gallery_type)
    {
        $enabled = self::get_enabled_sub_modules();
        $style_handle = 'mgwpp-' . $gallery_type . '-styles';
        $script_handle = 'mgwpp-' . $gallery_type . '-js';

        if (in_array($gallery_type, $enabled)) {
            if (wp_style_is($style_handle, 'registered')) {
                wp_enqueue_style($style_handle);
            }
            if (wp_script_is($script_handle, 'registered')) {
                wp_enqueue_script($script_handle);
            }
        }
    }

    public static function enqueue_preview_assets($gallery_type)
    {
        $enabled = self::get_enabled_sub_modules();
        if (in_array($gallery_type, $enabled)) {
            // Force enable assets
            self::$load_assets = true;
            self::$shortcode_gallery_type = $gallery_type;

            // Manually register and enqueue
            $instance = new self();
            $instance->register_assets();
            $instance->enqueue_assets();
        }
    }

    public static function print_inline_scripts($gallery_type)
    {
        switch ($gallery_type) {
            case 'single_carousel':
                echo '<script>MGWPP_SingleCarousel.initAll();</script>';
                break;
            case 'multi_carousel':
                echo '<script>MGWPP_MultiCarousel.initAll();</script>';
                break;
                //  other gallery types as needed
        }
    }
}

add_action('init', function () {
    $mgwpp_assets = new MGWPP_Assets();
    $mgwpp_assets->init();
});



add_action('wp_ajax_mgwpp_preview', function () {
    // Verify nonce first before processing any data
    if (!isset($_GET['nonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_GET['nonce'])), 'mgwpp_preview_nonce')) {
        wp_send_json_error(__('Invalid request', 'mini-gallery'), 403);
        exit;
    }

    // Validate gallery_id exists in GET request
    if (!isset($_GET['gallery_id'])) {
        wp_send_json_error(__('Missing gallery ID', 'mini-gallery'), 400);
        exit;
    }

    $gallery_id = intval(sanitize_key(wp_unslash($_GET['gallery_id'])));
    $gallery_type = get_post_meta($gallery_id, 'gallery_type', true);

    // Generate minimal HTML document
?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php esc_html_e('Gallery Preview', 'mini-gallery'); ?></title>

        <?php
        // Load critical CSS directly
        $critical_css = file_get_contents(MG_PLUGIN_PATH . 'public/css/mgwpp-preview-critical.css');
        echo wp_kses_post('<style>' . $critical_css . '</style>');

        // Enqueue gallery-specific assets
        MGWPP_Assets::enqueue_preview_assets($gallery_type);
        wp_print_styles();
        ?>
    </head>

    <body class="mgwpp-preview-body">
        <div class="mgwpp-preview-container">
            <?php echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]'); ?>
        </div>

        <?php
        wp_print_scripts();
        MGWPP_Assets::print_inline_scripts($gallery_type);
        ?>
    </body>

    </html>
<?php
    exit;
});
