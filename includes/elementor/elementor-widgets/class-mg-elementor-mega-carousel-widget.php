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
            echo __('Please select a gallery.', 'mini-gallery');
            return;
        }

        // Include the file where MGWPP_Mega_Slider is defined.
        //require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-mega-slider.php';

        // Get the images from the gallery post.
        $images = get_attached_media('image', $gallery_id);

        // Render your gallery using MGWPP_Mega_Slider.
        echo MGWPP_Mega_Slider::render($gallery_id, $images); // Pass both $gallery_id and $images
    }

    private function get_galleries() {
        $galleries = get_posts([
            'post_type' => 'mgwpp_soora',
            'numberposts' => -1,
        ]);

        $options = [];
        foreach ($galleries as $gallery) {
            $options[$gallery->ID] = $gallery->post_title;
        }

        return $options;
    }
}