<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class MG_Elementor_Pro_Carousel extends Widget_Base {

    public function get_script_depends() {
        return ['mgwpp-pro-carousel-js'];
    }
    
    public function get_style_depends() {
        return ['mgwpp-pro-carousel-styles'];
    }
    
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
        return ['minigallery']; // Your custom category
    }

    protected function _register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'mini-gallery'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'gallery_id',
            [
                'label'   => __('Select Gallery', 'mini-gallery'),
                'type'    => Controls_Manager::SELECT,
                'options' => $this->get_galleries(),
                'default' => '',
            ]
        );

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
                'label' => __('Carousel Style', 'mini-gallery'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_width',
            [
                'label'      => __('Card Width', 'mini-gallery'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'min'  => 200,
                        'max'  => 800,
                        'step' => 10,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 300,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .mg-pro-carousel' => '--card-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'gap',
            [
                'label'      => __('Card Gap', 'mini-gallery'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 100,
                        'step' => 5,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 30,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .mg-pro-carousel' => '--gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'image_height',
            [
                'label'      => __('Image Height', 'mini-gallery'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range'      => [
                    'px' => [
                        'min'  => 50,
                        'max'  => 600,
                        'step' => 10,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 300,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .mg-pro-carousel__image' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
                ],
            ]
        );

        $this->add_control(
            'fixed_height',
            [
                'label'        => __('Fixed Height', 'mini-gallery'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'mini-gallery'),
                'label_off'    => __('No', 'mini-gallery'),
                'return_value' => 'yes',
                'default'      => '',
                'selectors'    => [
                    '{{WRAPPER}} .mg-pro-carousel__card' => 'height: {{WRAPPER}} .mg-pro-carousel__image;',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings   = $this->get_settings_for_display();
        $gallery_id = $settings['gallery_id'];

        if (!$gallery_id) {
            echo esc_html__('Please select a gallery.', 'mini-gallery');
            return;
        }

        $images = get_attached_media('image', $gallery_id);
        echo wp_kses_post(MGWPP_Pro_Carousel::render($gallery_id, $images, $settings));
    }

    private function get_galleries() {
        $galleries = get_posts([
            'post_type' => 'mgwpp_soora',  // Your custom gallery post type
            'numberposts' => 100,
            'post_status' => 'publish',
        ]);
    
        $options = ['' => __('Select Gallery', 'mini-gallery')];
    
        foreach ($galleries as $gallery) {
            if (!$gallery instanceof WP_Post) {
                //error_log('[Mini Gallery] Skipping invalid gallery object: ' . print_r($gallery, true));
                continue;
            }
            $options[$gallery->ID] = esc_html($gallery->post_title);
        }
    
        return $options;
    }


    public function enqueue_pro_carousel_editor_scripts() {
        $editor_css_path = MG_PLUGIN_PATH . 'includes/admin/css/editor.css';
        wp_enqueue_style(
            'mg-neon-carousel-editor',
            MG_PLUGIN_URL . '/includes/admin/css/editor.css',
            [],
            filemtime($editor_css_path)
        );
    }
}
?>
