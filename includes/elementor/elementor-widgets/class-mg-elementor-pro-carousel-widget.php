<?php
if (!defined('ABSPATH')) exit;

class MG_Elementor_Pro_Carousel extends \Elementor\Widget_Base {

    public function get_name() {
        return 'mg_pro_carousel';
    }

    public function get_title() {
        return __('Mini Gallery Pro Carousel', 'mini-gallery');
    }

    public function get_icon() {
        return 'eicon-carousel';
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
                'label' => __('Carousel Style', 'mini-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_width',
            [
                'label' => __('Card Width', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 800,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 300,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mg-pro-carousel' => '--card-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'gap',
            [
                'label' => __('Card Gap', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 30,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mg-pro-carousel' => '--gap: {{SIZE}}{{UNIT}};',
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

        $images = get_attached_media('image', $gallery_id);
        echo MGWPP_Pro_Carousel::render($gallery_id, $images);
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
        
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_pro_carousel_frontend_scripts']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_pro_carousel_editor_scripts']);
    }


    public function enqueue_pro_carousel_frontend_scripts() {
        wp_enqueue_style(
            'mgwpp-pro-carousel-styles',
            plugins_url('public/css/mg-pro-carousel.css', __FILE__),
            [],
            filemtime(plugin_dir_path(__FILE__) . 'public/css/mg-pro-carousel.css')
        );
    
        wp_enqueue_script(
            'mgwpp-pro-carousel-js',
            plugins_url('public/js/mg-pro-carousel.js', __FILE__),
            [],
            filemtime(plugin_dir_path(__FILE__) . 'public/js/mg-pro-carousel.js'),
            true
        );
    }
    
    public function enqueue_pro_carousel_editor_scripts() {
        wp_enqueue_style(
            'mgwpp-pro-carousel-editor',
            plugins_url('admin/css/editor.css', __FILE__),
            [],
            filemtime(plugin_dir_path(__FILE__) . 'admin/css/editor.css')
        );
    }
    
}

