<?php
if (!defined('ABSPATH')) exit;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class MG_Elementor_Mega_Carousel extends \Elementor\Widget_Base {
    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
    }
    
    public function register_scripts() {
        wp_register_script(
            'mg-mega-carousel',
            plugins_url('assets/js/mg-mega-carousel.js', __FILE__),
            [],
            '1.0.0',
            true
        );
    }

    public function get_name() {
        return 'mg_mega_carousel';
    }

    public function get_title() {
        return __('Mini Gallery Mega Carousel', 'mini-gallery');
    }

    public function get_icon() {
        return 'eicon-slider-push';
    }

    public function get_categories() {
        return ['minigallery'];
    }

    protected function _register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'mini-gallery'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'gallery_id',
            [
                'label' => esc_html__('Select Gallery', 'mini-gallery'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_galleries(),
                'default' => '',
            ]
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'mini-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'viewport_height',
            [
                'label' => __('Viewport Height', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 600,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mg-carousel__viewport' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'primary_color',
            [
                'label' => __('Primary Color', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#666',
                'selectors' => [
                    '{{WRAPPER}} .mg-dot.active' => 'background: {{VALUE}};',
                    '{{WRAPPER}} .mg-carousel__nav:hover' => 'background: rgba(0, 0, 0, 0.9);',
                    '{{WRAPPER}} .mg-dot.active' => 'background: linear-gradient(90deg, color-mix(in srgb, {{VALUE}}, white 15%), {{VALUE}}, color-mix(in srgb, {{VALUE}}, black 15%));',
                ],
            ]
        );

        $this->add_control(
            'dot_inactive_color',
            [
                'label' => __('Inactive Dot Color', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ddd',
                'selectors' => [
                    '{{WRAPPER}} .mg-dot' => 'background: linear-gradient(145deg, color-mix(in srgb, {{VALUE}}, white 15%), color-mix(in srgb, {{VALUE}}, black 10%));',
                ],
            ]
        );

        $this->add_control(
            'dot_size',
            [
                'label' => __('Dot Size', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 5,
                        'max' => 20,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mg-dot' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dot_spacing',
            [
                'label' => __('Dot Spacing', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 5,
                        'max' => 30,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 12,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mg-dots-container' => 'padding: 0 {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .mg-dot' => 'margin: 0 calc({{SIZE}}{{UNIT}}/2);',
                ],
            ]
        );

        $this->end_controls_section();
    }
    protected function render() {
        wp_enqueue_script('mg-mega-carousel');
        $settings = $this->get_settings_for_display();
        
        if (empty($settings['gallery_id'])) {
            echo '<div class="elementor-alert elementor-alert-info">';
            echo esc_html__('Please select a gallery.', 'mini-gallery');
            echo '</div>';
            return;
        }

        $images = get_attached_media('image', absint($settings['gallery_id']));
        
        if (empty($images)) {
            echo '<div class="elementor-alert elementor-alert-warning">';
            echo esc_html__('No images found in selected gallery.', 'mini-gallery');
            echo '</div>';
            return;
        }

        if (!class_exists('MGWPP_Mega_Slider')) {
            echo '<div class="elementor-alert elementor-alert-danger">';
            echo esc_html__('Mega Slider class not available.', 'mini-gallery');
            echo '</div>';
            return;
        }

        // Get safe HTML output
        $output = MGWPP_Mega_Slider::render(
            absint($settings['gallery_id']), 
            $images
        );
        
        echo wp_kses_post($output);
    }
    
    private function get_galleries() {
        $post_type = apply_filters('mg_carousel_gallery_post_type', 'mgwpp_soora');
        
        $galleries = get_posts([
            'post_type'    => sanitize_key($post_type),
            'numberposts' => 100, // Limit for performance
            'post_status' => 'publish',
        ]);
    
        $options = ['' => esc_html__('Select Gallery', 'mini-gallery')];
        
        foreach ($galleries as $gallery) {
            $options[$gallery->ID] = esc_html($gallery->post_title);
        }
    
        return $options;
    }
}