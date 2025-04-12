<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class MG_Elementor_Gallery_Multi extends Widget_Base {

    public function get_name() {
        return 'mg_gallery_multi';
    }

    public function get_title() {
        return __( 'Mini Gallery Multi Carousel', 'mini-gallery' );
    }

    public function get_icon() {
        return 'eicon-carousel';
    }

    public function get_categories() {
        return [ 'minigallery' ];
    }

    protected function _register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'mini-gallery' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'gallery_id',
            [
                'label'   => __( 'Select Gallery', 'mini-gallery' ),
                'type'    => Controls_Manager::SELECT,
                'options' => $this->get_galleries(),
                'default' => '',
            ]
        );

        $this->add_control(
            'images_per_page',
            [
                'label'   => __( 'Images per Page', 'mini-gallery' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
                'min'     => 2,
                'step'    => 1,
            ]
        );

        $this->add_control(
            'display_mode',
            [
                'label'   => __( 'Display Mode', 'mini-gallery' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => __( 'Full Width', 'mini-gallery' ),
                    'cards'   => __( 'Product Cards', 'mini-gallery' ),
                ],
            ]
        );

        $this->add_control(
            'auto_rotate_speed',
            [
                'label'       => __( 'Auto Rotate Speed (ms)', 'mini-gallery' ),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 3000,
                'description' => __( 'Set to 0 to disable auto-rotation.', 'mini-gallery' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings   = $this->get_settings_for_display();
        $gallery_id = $settings['gallery_id'];

        if (empty($gallery_id)) {
            echo esc_html__('Please select a gallery.', 'mini-gallery');
            return;
        }

        $gallery_post = get_post($gallery_id);

        if (!$gallery_post || $gallery_post->post_type !== 'mgwpp_soora') {
            echo esc_html__('Invalid gallery selected.', 'mini-gallery');
            return;
        }

        $images = array_values(get_attached_media('image', $gallery_id));

        if (empty($images)) {
            echo esc_html__('No images found for this gallery.', 'mini-gallery');
            return;
        }

        $args = [
            'images_per_page'   => intval($settings['images_per_page']),
            'display_mode'      => $settings['display_mode'],
            'auto_rotate_speed' => intval($settings['auto_rotate_speed']),
        ];

        echo wp_kses_post(MGWPP_Gallery_Multi::render($gallery_id, $images , $args));
    }

    private function get_galleries() {
        $galleries = get_posts([
            'post_type'   => 'mgwpp_soora',
            'numberposts' => 15,
            'post_status' => 'publish',
        ]);

        $options = ['' => __('Select Gallery', 'mini-gallery')];

        foreach ($galleries as $gallery) {
            if ($gallery instanceof WP_Post) {
                $options[$gallery->ID] = esc_html($gallery->post_title);
            }
        }

        return $options;
    }
}
