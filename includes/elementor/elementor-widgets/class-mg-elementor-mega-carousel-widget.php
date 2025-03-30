<?php
if (!defined('ABSPATH')) exit;

class MG_Elementor_Mega_Carousel extends \Elementor\Widget_Base {

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
                'label' => __('Content', 'mini-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'gallery_id',
            [
                'label' => __('Select Gallery', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SELECT,
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
        $settings = $this->get_settings_for_display();
        $gallery_id = $settings['gallery_id'];
    
        if (!$gallery_id) {
            // Use esc_html__() to escape plain text.
            echo esc_html__('Please select a gallery.', 'mini-gallery');
            return;
        }
    
        // Get the images from the gallery post.
        $images = get_attached_media('image', $gallery_id);
    
        // Wrap the rendered output in wp_kses_post() to allow safe HTML markup.
        echo wp_kses_post( MGWPP_Mega_Slider::render($gallery_id, $images) );
    }
    
    private function get_galleries() {
        $galleries = get_posts([
            'post_type'    => 'mgwpp_soora',
            'numberposts'  => -1,
        ]);
    
        $options = [];
        foreach ($galleries as $gallery) {
            // Escape the gallery title to ensure safe output.
            $options[$gallery->ID] = esc_html($gallery->post_title);
        }
    
        return $options;
    }
    
}