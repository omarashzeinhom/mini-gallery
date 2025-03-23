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
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $gallery_id = $settings['gallery_id'];

        if (!$gallery_id) {
            echo __('Please select a gallery.', 'mini-gallery');
            return;
        }

        // Render your gallery
        echo MGWPP_Mega_Carousel::render($gallery_id);
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