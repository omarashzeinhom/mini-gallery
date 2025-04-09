<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MGWPP_Assets {

    /**
     * Returns a version string based on the file modification time.
     * Falls back to a default version if file doesn't exist.
     *
     * @param string $file Absolute file path.
     * @param string $default Default version string.
     * @return string Version.
     */
    private function get_file_version( $file, $default = '1.0' ) {
        return file_exists( $file ) ? filemtime( $file ) : $default;
    }

    /**
     * Registers frontend assets.
     */
    public function register_assets() {
        // Use the constants to build paths relative to the plugin root.
        $base_url  = MG_PLUGIN_URL . '/public/';
        $base_path = MG_PLUGIN_PATH . 'public/';

        // Single Carousel
        wp_register_script(
            'mg-single-carousel-js',
            $base_url . 'js/mg-single-carousel.js',
            array(),
            '1.0',
            true
        );
        wp_register_style(
            'mg-single-carousel-styles',
            $base_url . 'css/mg-single-carousel.css',
            array(),
            '1.0'
        );

        // Multi Carousel
        wp_register_script(
            'mg-multi-carousel-js',
            $base_url . 'js/mg-multi-carousel.js',
            array(),
            '1.0',
            true
        );
        wp_register_style(
            'mg-multi-carousel-styles',
            $base_url . 'css/mg-multi-carousel.css',
            array(),
            '1.0'
        );

        // Grid Styles
        wp_register_style(
            'mg-grid-styles',
            $base_url . 'css/mg-grid.css',
            array(),
            '1.0'
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
            $this->get_file_version( $base_path . 'js/mg-mega-carousel.js' ),
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
            MG_PLUGIN_URL . '/public/css/mg-neon-carousel.css',
            array(),
            $this->get_file_version( MG_PLUGIN_PATH . 'public/css/mg-neon-carousel.css' )
        );
        wp_register_script(
            'mgwpp-neon-carousel-js',
            MG_PLUGIN_URL . '/public/js/mg-neon-carousel.js',
            array('jquery'),
            $this->get_file_version( MG_PLUGIN_PATH . 'public/js/mg-neon-carousel.js' ),
            true
        );

        // 3D Carousel
        wp_register_style(
            'mgwpp-threed-carousel-styles',
            MG_PLUGIN_URL . '/public/css/mg-threed-carousel.css',
            array(),
            $this->get_file_version( MG_PLUGIN_PATH . 'public/css/mg-threed-carousel.css' )
        );
        wp_register_script(
            'mgwpp-threed-carousel-js',
            MG_PLUGIN_URL . '/public/js/mg-threed-carousel.js',
            array('jquery'),
            $this->get_file_version( MG_PLUGIN_PATH . 'public/js/mg-threed-carousel.js' ),
            true
        );

        // Testimonials Carousel
        wp_register_style(
            'mgwpp-testimonial-carousel-styles',
            MG_PLUGIN_URL . '/public/css/mgwpp-testimonial-carousel.css',
            array('jquery'),
            $this->get_file_version( MG_PLUGIN_PATH . 'public/css/mgwpp-testimonial-carousel.css' )
        );
        wp_register_script(
            'mgwpp-testimonial-carousel-js',
            MG_PLUGIN_URL . '/public/js/mgwpp-testimonial-carousel.js',
            array(),
            $this->get_file_version( MG_PLUGIN_PATH . 'public/js/mgwpp-testimonial-carousel.js' ),
            true
        );

        // Lightbox for Testimonials
        wp_register_script(
            'mgwpp-lightbox-js',
            MG_PLUGIN_URL . '/public/js/mg-lightbox.js',
            array('jquery'),
            $this->get_file_version( MG_PLUGIN_PATH . 'public/js/mg-lightbox.js' ),
            true
        );

        // FullPage Slider
        wp_register_style(
            'mg-fullpage-slider-styles',
            MG_PLUGIN_URL . '/public/css/mg-full-page-slider.css',
            array(),
            $this->get_file_version( MG_PLUGIN_PATH . 'public/css/mg-full-page-slider.css' )
        );
        wp_register_script(
            'mg-fullpage-slider-js',
            MG_PLUGIN_URL . '/public/js/mg-full-page-slider.js',
            array('jquery'),
            $this->get_file_version( MG_PLUGIN_PATH . 'public/js/mg-full-page-slider.js' ),
            true
        );

        // Spotlight Slider
        wp_register_style(
            'mg-spotlight-slider-styles',
            MG_PLUGIN_URL . '/public/css/mg-spotlight-carousel.css',
            array(),
            $this->get_file_version( MG_PLUGIN_PATH . 'public/css/mg-spotlight-carousel.css' )
        );
        wp_register_script(
            'mg-spotlight-slider-js',
            MG_PLUGIN_URL . '/public/js/mg-spotlight-carousel.js',
            array(),
            $this->get_file_version( MG_PLUGIN_PATH . 'public/js/mg-spotlight-carousel.js' ),
            true
        );
    }

    /**
     * Enqueues frontend assets conditionally.
     */
    public function enqueue_assets() {
        // Always enqueue the universal init script.
        wp_enqueue_script( 'mg-universal-init' );

        // Only load gallery assets on singular gallery posts.
        if ( is_singular( 'mgwpp_gallery' ) ) {
            $gallery_type = get_post_meta( get_the_ID(), 'gallery_type', true );
            switch ( $gallery_type ) {
                case 'single_carousel':
                    wp_enqueue_script( 'mg-single-carousel-js' );
                    wp_enqueue_style( 'mg-single-carousel-styles' );
                    break;
                case 'multi_carousel':
                    wp_enqueue_script( 'mg-multi-carousel-js' );
                    wp_enqueue_style( 'mg-multi-carousel-styles' );
                    break;
                case 'grid':
                    wp_enqueue_style( 'mg-grid-styles' );
                    break;
                case 'mega_slider':
                    wp_enqueue_script( 'mg-mega-carousel-js' );
                    wp_enqueue_style( 'mg-mega-carousel-styles' );
                    break;
                case 'pro_carousel':
                    wp_enqueue_style( 'mgwpp-pro-carousel-styles' );
                    wp_enqueue_script( 'mgwpp-pro-carousel-js' );
                    break;
                case 'neon_carousel':
                    wp_enqueue_script( 'mgwpp-neon-carousel-js' );
                    wp_enqueue_style( 'mgwpp-neon-carousel-styles' );
                    break;
                case 'threed_carousel':
                    wp_enqueue_script( 'mgwpp-threed-carousel-js' );
                    wp_enqueue_style( 'mgwpp-threed-carousel-styles' );
                    break;
                case 'testimonials_carousel':
                    wp_enqueue_script( 'mgwpp-testimonial-carousel-js' );
                    wp_enqueue_style( 'mgwpp-testimonial-carousel-styles' );
                    break;
                default:
                    // No recognized gallery type; no additional assets enqueued.
                    break;
            }
        }
    }

    /**
     * Enqueues admin assets conditionally.
     *
     * @param string $hook_suffix Current admin page hook.
     */
    public function enqueue_admin_assets( $hook_suffix ) {
        // Only load assets on post editing screens.
        if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
            $screen = get_current_screen();
            if ( $screen && ( 'post' === $screen->base || 'page' === $screen->base ) ) {
                wp_enqueue_script( 'mg-admin-carousel' );
                wp_enqueue_style( 'mg-admin-styles' );
                wp_enqueue_style( 'mg-styles' );
            }
        }
    }

    /**
     * Hooks registration and enqueue functions into WordPress.
     */
    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }
}

// Hook the class initialization on 'init' so that all hooks and conditions are ready.
add_action( 'init', function() {
    $mgwpp_assets = new MGWPP_Assets();
    $mgwpp_assets->init();
});
