<?php
if (!defined('ABSPATH')) exit;


class MGWPP_VC_Integration {
    private $gallery_types = [
        'single_carousel' => 'Single Carousel',
        'multi_carousel' => 'Multi Carousel',
        'grid' => 'Grid Gallery',
        'mega_slider' => 'Mega Slider',
        'pro_carousel' => 'Pro Carousel',
        'neon_carousel' => 'Neon Carousel',
        'threed_carousel' => '3D Carousel',
        'full_page_slider' => 'Full Page Slider',
        'spotlight_carousel' => 'Spotlight Carousel',
        'testimonials_carousel' => 'Testimonial Carousel'
    ];

    public function __construct() {
        add_action('vc_before_init', [$this, 'vc_integration']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_vc_assets']);
    }

    public function vc_integration() {
        vc_map([
            'name' => 'MiniGallery',
            'base' => 'mgwpp_vc_gallery',
            'category' => esc_html__('Content', 'mini-gallery'),
            'params' => [
                [
                    'type' => 'dropdown',
                    'heading' => 'Gallery Type',
                    'param_name' => 'type',
                    'value' => $this->gallery_types,
                    'admin_label' => true
                ],
                [
                    'type' => 'textfield',
                    'heading' => 'Gallery ID',
                    'param_name' => 'id',
                    'description' => 'Enter your gallery post ID'
                ],
                [
                    'type' => 'checkbox',
                    'heading' => 'Autoplay',
                    'param_name' => 'autoplay',
                    'value' => ['Yes' => 'true']
                ],
                [
                    'type' => 'textfield',
                    'heading' => 'Transition Speed',
                    'param_name' => 'speed',
                    'value' => '8000',
                    'description' => 'In milliseconds'
                ]
            ],
            'content_element' => true, // Allow it to be used in the WPBakery editor
            'show_settings_on_create' => true, // Show settings immediately
            'render_callback' => [$this, 'render_vc_gallery'] // Add the render callback for this gallery
        ]);
    }

    /**
     * Render callback to output the gallery based on user settings
     */
    public function render_vc_gallery($atts, $content = null) {
        // Extract attributes from the WPBakery element
        $atts = shortcode_atts([
            'type' => 'single_carousel', // Default to 'single_carousel'
            'id' => '',                  // Gallery ID
            'autoplay' => 'true',        // Autoplay value
            'speed' => '8000',           // Transition speed
        ], $atts);

        // Get the gallery ID and retrieve images
        $gallery_id = $atts['id'];
        $images = get_attached_media('image', $gallery_id); // Retrieve the images for the gallery
        
        if (empty($images)) {
            return '<div class="mgwpp-error">' . esc_html__('No images found in gallery', 'mini-gallery') . '</div>';
        }

        // Define the gallery type to render the appropriate layout
        switch ($atts['type']) {
            case 'single_carousel':
                // Call a function to render the 'single_carousel' gallery
                return MGWPP_Single_Gallery::render($gallery_id, $images, $atts);
            case 'multi_carousel':
                return MGWPP_Multi_Gallery::render($gallery_id, $images, $atts);
            case 'grid':
                return MGWPP_Grid_Gallery::render($gallery_id, $images, $atts);
            case 'mega_slider':
                return MGWPP_Mega_Slider::render($gallery_id, $images, $atts);
            case 'spotlight_carousel':
                return MGWPP_Spotlight_Carousel::render($gallery_id, $images, $atts); // Spotlight Carousel rendering
            default:
                return '<div class="mgwpp-error">' . esc_html__('Gallery type not supported.', 'mini-gallery') . '</div>';
        }
    }

    public function enqueue_vc_assets() {
        if (!function_exists('vc_is_page_editable') || !vc_is_page_editable()) return;

        // Enqueue all gallery assets
        $assets = [
            'styles' => [
                'mg-single-carousel-styles',
                'mg-multi-carousel-styles',
                'mg-grid-styles',
                'mg-mega-carousel-styles',
                'mgwpp-pro-carousel-styles',
                'mgwpp-neon-carousel-styles',
                'mgwpp-threed-carousel-styles',
                'mg-fullpage-slider-styles',
                'mg-spotlight-slider-styles',
                'mgwpp-testimonial-carousel-styles'
            ],
            'scripts' => [
                'mg-single-carousel-js',
                'mg-multi-carousel-js',
                'mg-mega-carousel-js',
                'mgwpp-pro-carousel-js',
                'mgwpp-neon-carousel-js',
                'mgwpp-threed-carousel-js',
                'mg-fullpage-slider-js',
                'mg-spotlight-slider-js',
                'mgwpp-testimonial-carousel-js',
                'mg-universal-init'
            ]
        ];

        // Enqueue styles
        foreach ($assets['styles'] as $handle) {
            if (wp_style_is($handle, 'registered')) wp_enqueue_style($handle);
        }

        // Enqueue scripts
        foreach ($assets['scripts'] as $handle) {
            if (wp_script_is($handle, 'registered')) wp_enqueue_script($handle);
        }
    }
}

new MGWPP_VC_Integration();
