<?php
if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class MG_Elementor_Gallery_Single extends Widget_Base
{
    public function get_name()
    {
        return 'mg_gallery_single';
    }

    public function get_title()
    {
        return esc_html__('Mini Gallery Single Carousel', 'mini-gallery');
    }

    public function get_icon()
    {
        return 'eicon-slider-device';
    }

    public function get_categories()
    {
        return [sanitize_key('minigallery')];
    }

    protected function _register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'mini-gallery'),
                'tab'   => Controls_Manager::TAB_CONTENT,
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

        $this->add_control(
            'bg_color',
            [
                'label'   => esc_html__('Background Color', 'mini-gallery'),
                'type'    => Controls_Manager::COLOR,
                'default' => 'transparent',
            ]
        );

        $this->add_control(
            'transition_speed',
            [
                'label'   => esc_html__('Transition Speed (s)', 'mini-gallery'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 0.5,
                'step'    => 0.1,
                'min'     => 0,
                'max'     => 2,
            ]
        );

        $this->add_control(
            'auto_rotate_speed',
            [
                'label'       => esc_html__('Auto Rotate Speed (ms)', 'mini-gallery'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 5000,
                'description' => esc_html__('Set to 0 to disable auto-rotation.', 'mini-gallery'),
            ]
        );

        $this->add_control(
            'show_nav',
            [
                'label'     => esc_html__('Show Navigation', 'mini-gallery'),
                'type'      => Controls_Manager::SWITCHER,
                'label_on'  => esc_html__('Show', 'mini-gallery'),
                'label_off' => esc_html__('Hide', 'mini-gallery'),
                'default'   => 'yes',
            ]
        );

        $this->add_control(
            'swipe_threshold',
            [
                'label'   => esc_html__('Swipe Threshold (px)', 'mini-gallery'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 30,
                'min'     => 10,
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings   = $this->get_settings_for_display();
        $gallery_id = isset($settings['gallery_id']) ? absint($settings['gallery_id']) : 0;

        if (empty($gallery_id)) {
            echo esc_html__('Please select a gallery.', 'mini-gallery');
            return;
        }

        $images = get_attached_media('image', $gallery_id);
        if (empty($images)) {
            echo esc_html__('No images found for this gallery.', 'mini-gallery');
            return;
        }

        $args = [
            'bg_color'          => sanitize_hex_color($settings['bg_color']),
            'transition_speed'  => floatval($settings['transition_speed']) . 's',
            'auto_rotate_speed' => absint($settings['auto_rotate_speed']),
            'show_nav'          => $settings['show_nav'] === 'yes',
            'swipe_threshold'   => absint($settings['swipe_threshold']),
        ];

        echo wp_kses_post(MGWPP_Gallery_Single::render($gallery_id, $images, $args));
    }

    private function get_galleries()
    {
        $galleries = get_posts([
            'post_type'      => 'mgwpp_soora',
            'numberposts'    => 100,
            'post_status'    => 'publish',
            'suppress_filters' => false,
        ]);

        $options = [
            '' => esc_html__('Select Gallery', 'mini-gallery'),
        ];

        foreach ($galleries as $gallery) {
            if ($gallery instanceof WP_Post) {
                $options[$gallery->ID] = esc_html($gallery->post_title);
            }
        }

        return $options;
    }
}
