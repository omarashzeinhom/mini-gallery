<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class MG_Elementor_Gallery_Grid extends Widget_Base {

    public function get_name() {
        return 'mg_gallery_grid';
    }

    public function get_title() {
        return __( 'Mini Gallery Grid', 'mini-gallery' );
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
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

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
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

        echo MGWPP_Gallery_Grid::render($gallery_id, $images);
    }

    private function get_galleries() {
        $galleries = get_posts([
            'post_type'   => 'mgwpp_soora',
            'numberposts' => 100,
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
