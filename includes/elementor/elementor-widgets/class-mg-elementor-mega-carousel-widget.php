<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class MG_Elementor_Mega_Carousel extends Widget_Base
{
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
        add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
    }


    public function get_name()
    {
        return 'mg_mega_carousel';
    }

    public function get_title()
    {
        return __('Mini Gallery Mega Carousel', 'mini-gallery');
    }

    public function get_icon()
    {
        return 'eicon-slider-push';
    }

    public function get_categories()
    {
        return ['minigallery'];
    }

    protected function _register_controls()
    {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'mini-gallery'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'disable_first_image',
            [
                'label'        => __('Disable First Image on Load', 'mini-gallery'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'mini-gallery'),
                'label_off'    => __('No', 'mini-gallery'),
                'return_value' => 'yes',
                'default'      => 'no',
            ]
        );

        $this->add_control(
            'gallery_id',
            [
                'label'   => esc_html__('Select Gallery', 'mini-gallery'),
                'type'    => Controls_Manager::SELECT,
                'options' => $this->get_galleries(),
                'default' => '',
            ]
        );

        // Add a placeholder image control
        $this->add_control(
            'placeholder_image',
            [
                'label'   => __('Placeholder Image', 'mini-gallery'),
                'type'    => Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'mini-gallery'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'viewport_height',
            [
                'label'      => __('Viewport Height', 'mini-gallery'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 600,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .mg-carousel__viewport' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'primary_color',
            [
                'label'     => __('Primary Color', 'mini-gallery'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#666',
                'selectors' => [
                    '{{WRAPPER}} .mg-mega-carousel-dot.active'              => 'background: {{VALUE}};',
                    '{{WRAPPER}} .mg-carousel__nav:hover'       => 'background: rgba(0, 0, 0, 0.9);',
                    '{{WRAPPER}} .mg-mega-carousel-dot.active'              => 'background: linear-gradient(90deg, color-mix(in srgb, {{VALUE}}, white 15%), {{VALUE}}, color-mix(in srgb, {{VALUE}}, black 15%));',
                ],
            ]
        );

        $this->add_control(
            'dot_inactive_color',
            [
                'label'     => __('Inactive Dot Color', 'mini-gallery'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ddd',
                'selectors' => [
                    '{{WRAPPER}} .mg-mega-carousel-dot' => 'background: linear-gradient(145deg, color-mix(in srgb, {{VALUE}}, white 15%), color-mix(in srgb, {{VALUE}}, black 10%));',
                ],
            ]
        );

        $this->add_control(
            'dot_size',
            [
                'label'      => __('Dot Size', 'mini-gallery'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'min' => 5,
                        'max' => 20,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .mg-mega-carousel-dot' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dot_spacing',
            [
                'label'      => __('Dot Spacing', 'mini-gallery'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'min' => 5,
                        'max' => 30,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 12,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .mg-mega-carousel-dots-container' => 'padding: 0 {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .mg-mega-carousel-dot'            => 'margin: 0 calc({{SIZE}}{{UNIT}}/2);',
                ],
            ]
        );

        // Additional options
        $this->add_control(
            'show_arrows',
            [
                'label'        => __('Show Navigation Arrows', 'mini-gallery'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'mini-gallery'),
                'label_off'    => __('No', 'mini-gallery'),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_dots',
            [
                'label'        => __('Show Dots', 'mini-gallery'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'mini-gallery'),
                'label_off'    => __('No', 'mini-gallery'),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label'        => __('Autoplay', 'mini-gallery'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'mini-gallery'),
                'label_off'    => __('No', 'mini-gallery'),
                'return_value' => 'yes',
                'default'      => 'no',
            ]
        );

        $this->add_control(
            'autoplay_delay',
            [
                'label'     => __('Autoplay Delay (ms)', 'mini-gallery'),
                'type'      => Controls_Manager::SLIDER,
                'condition' => [
                    'autoplay' => 'yes',
                ],
                'size_units' => ['ms'],
                'range'     => [
                    'ms' => [
                        'min'  => 1000,
                        'max'  => 10000,
                        'step' => 500,
                    ],
                ],
                'default'   => [
                    'unit' => 'ms',
                    'size' => 3000,
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
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

        // Skip the first image if enabled
        if ('yes' === $settings['disable_first_image']) {
            array_shift($images);
        }

        if (!class_exists('MGWPP_Mega_Slider')) {
            echo '<div class="elementor-alert elementor-alert-danger">';
            echo esc_html__('Mega Slider class not available.', 'mini-gallery');
            echo '</div>';
            return;
        }

        $output = MGWPP_Mega_Slider::render(
            absint($settings['gallery_id']),
            $images,
            $settings
        );

        echo wp_kses_post($output);
    }

    private function get_galleries()
    {
        $galleries = get_posts([
            'post_type' => 'mgwpp_soora',
            'numberposts' => 100,
            'post_status' => 'publish',
        ]);

        $options = ['' => __('Select Gallery', 'mini-gallery')];

        foreach ($galleries as $gallery) {
            if (!$gallery instanceof WP_Post) {
                continue;
            }
            $options[$gallery->ID] = esc_html($gallery->post_title);
        }

        return $options;
    }
}
