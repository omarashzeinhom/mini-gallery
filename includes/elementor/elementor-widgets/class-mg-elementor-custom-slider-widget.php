<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MGWPP_Slider_Widget extends \Elementor\Widget_Base {

public function get_name() { return 'mgwpp-slider'; }

public function get_title() { return 'MGWPP Slider'; }

public function get_icon() { return 'eicon-slides'; }

protected function register_controls() {
    // Gallery Selection
    $this->start_controls_section('content_section', [
        'label' => __('Slider Content', 'mgwpp'),
        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
    ]);

    $this->add_control('selected_gallery', [
        'label' => __('Select Gallery', 'mgwpp'),
        'type' => \Elementor\Controls_Manager::SELECT2,
        'options' => $this->get_galleries(),
        'label_block' => true
    ]);

    $this->end_controls_section();
}

private function get_galleries() {
    $galleries = get_posts([
        'post_type' => 'mgwpp_gallery',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);

    return wp_list_pluck($galleries, 'post_title', 'ID');
}

protected function render() {
    $settings = $this->get_settings_for_display();
    echo '<div class="mgwpp-slider">';
    $this->render_slides($settings['selected_gallery']);
    echo '</div>';
}

private function render_slides($gallery_id) {
    $slides = get_posts([
        'post_type' => 'elementor_library', // Use Elementor templates
        'meta_query' => [[
            'key' => '_mgwpp_parent_gallery',
            'value' => $gallery_id
        ]]
    ]);

    if ($slides) {
        echo '<div class="mgwpp-slider__wrapper">';
        foreach ($slides as $slide) {
            echo '<div class="mgwpp-slider__slide">';
            echo \Elementor\Plugin::instance()->frontend->get_builder_content($slide->ID);
            echo '</div>';
        }
        echo '</div>';
    }
}
}