<?php

class MG_Elementor_3D_Carousel extends \Elementor\Widget_Base
{
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
    }

    public function register_assets()
    {
        wp_register_script(
            'mg-3d-carousel',
            plugins_url('public/js/mg-3d-carousel.js', __FILE__),
            [],
            '1.0.0',
            true
        );

        wp_register_style(
            'mg-3d-carousel-style',
            plugins_url('public/css/mg-3d-carousel.css', __FILE__),
            [],
            '1.0.0'
        );
    }

    public function get_name()
    {
        return 'mg_3d_carousel';
    }

    public function get_title()
    {
        return __('3D Carousel', 'mini-gallery');
    }

    public function get_icon()
    {
        return 'eicon-carousel';
    }

    protected function _register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'mini-gallery'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'gallery_id',
            [
                'label' => __('Select Gallery', 'mini-gallery'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_galleries(),
                'default' => '',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'settings_section',
            [
                'label' => __('3D Settings', 'mini-gallery'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'radius',
            [
                'label' => __('Radius', 'mini-gallery'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 240,
                ],
            ]
        );

        $this->add_control(
            'auto_rotate',
            [
                'label' => __('Auto Rotate', 'mini-gallery'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'mini-gallery'),
                'label_off' => __('No', 'mini-gallery'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'rotate_speed',
            [
                'label' => __('Rotation Speed', 'mini-gallery'),
                'type' => Controls_Manager::NUMBER,
                'min' => -100,
                'max' => 100,
                'step' => 1,
                'default' => -60,
            ]
        );

        $this->add_control(
            'img_width',
            [
                'label' => __('Image Width', 'mini-gallery'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 300,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 120,
                ],
            ]
        );

        $this->add_control(
            'img_height',
            [
                'label' => __('Image Height', 'mini-gallery'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 300,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 170,
                ],
            ]
        );

        $this->end_controls_section();
    }
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        // Sanitize gallery ID early
        $gallery_id = !empty($settings['gallery_id']) ? absint($settings['gallery_id']) : 0;

        if (!$gallery_id) {
            printf(
                '<div class="elementor-alert elementor-alert-info">%s</div>',
                esc_html__('Please select a gallery.', 'mini-gallery')
            );
            return;
        }

        // Get images with validation
        $images = get_attached_media('image', $gallery_id);

        if (empty($images)) {
            printf(
                '<div class="elementor-alert elementor-alert-warning">%s</div>',
                esc_html__('No images found in selected gallery.', 'mini-gallery')
            );
            return;
        }

        // Sanitize widget ID
        $widget_id = sanitize_key($this->get_id());

        // Validate numeric settings
        $safe_settings = [
            'radius' => isset($settings['radius']['size']) ? absint($settings['radius']['size']) : 240,
            'auto_rotate' => sanitize_text_field($settings['auto_rotate']),
            'rotate_speed' => intval($settings['rotate_speed']),
            'img_width' => isset($settings['img_width']['size']) ? absint($settings['img_width']['size']) : 120,
            'img_height' => isset($settings['img_height']['size']) ? absint($settings['img_height']['size']) : 170,
        ];

        // Output with HTML sanitization
        echo wp_kses_post(
            MGWPP_3D_Carousel::render(
                $widget_id,
                $images,
                $safe_settings
            )
        );
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
            $options[$gallery->ID] = esc_html($gallery->post_title);
        }

        return $options;
    }
}
