<?php
if (!defined('ABSPATH')) exit;


class MG_Elementor_Neon_Carousel extends \Elementor\Widget_Base {

    public function get_name() {
        return 'mg_neon_carousel';
    }

    public function get_title() {
        return __('Mini Gallery Neon Carousel', 'mini-gallery');
    }

    public function get_icon() {
        return 'eicon-slider-album';
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

        $this->add_control(
            'autoplay',
            [
                'label' => __('Autoplay', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'mini-gallery'),
                'label_off' => __('No', 'mini-gallery'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'autoplay_speed',
            [
                'label' => __('Autoplay Speed (ms)', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5000,
                'condition' => ['autoplay' => 'yes'],
            ]
        );

        $this->add_control(
            'show_dots',
            [
                'label' => __('Show Navigation Dots', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_previews',
            [
                'label' => __('Show Image Previews', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'zoom_effect',
            [
                'label' => __('Zoom Effect', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
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
            'neon_primary_color',
            [
                'label' => __('Neon Primary Color', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#00f3ff',
                'selectors' => ['{{WRAPPER}}' => '--neon-primary: {{VALUE}};'],
            ]
        );

        $this->add_control(
            'neon_secondary_color',
            [
                'label' => __('Neon Secondary Color', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ff007f',
                'selectors' => ['{{WRAPPER}}' => '--neon-secondary: {{VALUE}};'],
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => __('Background Color', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0a0a0a',
                'selectors' => ['{{WRAPPER}}' => '--dark-bg: {{VALUE}};'],
            ]
        );

        $this->add_responsive_control(
            'slide_height',
            [
                'label' => __('Slide Height', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['vh', 'px'],
                'range' => [
                    'vh' => ['min' => 30, 'max' => 100],
                    'px' => ['min' => 300, 'max' => 2000],
                ],
                'default' => ['unit' => 'vh', 'size' => 93],
                'selectors' => ['{{WRAPPER}} .neon-slider' => 'height: {{SIZE}}{{UNIT}};'],
            ]
        );

        $this->add_control(
            'overlay_opacity',
            [
                'label' => __('Overlay Opacity', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => ['px' => ['min' => 0, 'max' => 1, 'step' => 0.1]],
                'default' => ['size' => 0.9],
                'selectors' => [
                    '{{WRAPPER}} .neon-slide::before' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Typography Tab
        $this->start_controls_section(
            'typography_section',
            [
                'label' => __('Typography', 'mini-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => ['{{WRAPPER}} .neon-title' => 'color: {{VALUE}};'],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Title Typography', 'mini-gallery'),
                'selector' => '{{WRAPPER}} .neon-title',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $gallery_id = $settings['gallery_id'];
    
        if (!$gallery_id) {
            echo esc_html__('Please select a gallery.', 'mini-gallery');
            return;
        }
    
        // Get gallery images from the selected gallery
        $images = get_post_meta($gallery_id, 'mgwpp_gallery_images', true);
        $images = is_array($images) ? $images : [];
    
        $options = [
            'autoplay' => $settings['autoplay'] === 'yes',
            'autoplay_speed' => $settings['autoplay_speed'],
            'show_dots' => $settings['show_dots'] === 'yes',
            'show_previews' => $settings['show_previews'] === 'yes',
            'zoom_effect' => $settings['zoom_effect'] === 'yes',
        ];
    
        echo '<div class="mg-neon-carousel" data-settings="'.esc_attr(wp_json_encode($options)).'">';
        echo MGWPP_Neon_Carousel::render($gallery_id, $images); // Pass images array
        echo '</div>';
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

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        
        // Load scripts on frontend AND editor
        add_action('elementor/frontend/before_enqueue_scripts', [$this, 'enqueue_neon_scripts']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_scripts']);
    }
    
// In MG_Elementor_Neon_Carousel class:

public function enqueue_neon_scripts() {
    wp_enqueue_style(
        'mg-neon-carousel-styles',
        MG_PLUGIN_URL . '/public/css/mg-neon-carousel.css',
        [],
        filemtime(MG_PLUGIN_PATH . 'public/css/mg-neon-carousel.css')
    );

    wp_enqueue_script(
        'mg-neon-carousel-scripts',
        MG_PLUGIN_URL . '/public/js/mg-neon-carousel.js',
        ['elementor-frontend'],
        filemtime(MG_PLUGIN_PATH . 'public/js/mg-neon-carousel.js'),
        true
    );
}

public function enqueue_editor_scripts() {
    wp_enqueue_style(
        'mg-neon-carousel-editor',
        plugins_url('admin/css/editor.css', __FILE__),
        [],
        filemtime(plugin_dir_path(__FILE__) . 'admin/css/editor.css')
    );
}








    
}