<?php
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
            ]
        ]);
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

        foreach ($assets['styles'] as $handle) {
            if (wp_style_is($handle, 'registered')) wp_enqueue_style($handle);
        }

        foreach ($assets['scripts'] as $handle) {
            if (wp_script_is($handle, 'registered')) wp_enqueue_script($handle);
        }
    }
}

new MGWPP_VC_Integration();