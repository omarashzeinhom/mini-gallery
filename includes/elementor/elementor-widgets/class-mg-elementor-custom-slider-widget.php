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
        'label' => __('Slider Content', 'mini-gallery'),
        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
    ]);

    $this->add_control('selected_gallery', [
        'label' => __('Select Gallery', 'mini-gallery'),
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

private function render_slides( $gallery_id ) {
    // Fetch IDs only (lighter, no extra pagination query)
    $slides = get_posts([
        'post_type'              => 'elementor_library',
        'posts_per_page'         => -1,
        'fields'                 => 'ids',           // only pull back IDs
        'no_found_rows'          => true,            // skip pagination count
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'meta-query' => [
            'key'=> '_mgwpp_parent_gallery',
            'value'=> $gallery_id,
            'compare'=> '=',
        ]
    ]);

    if ( ! empty( $slides ) ) {
        echo '<div class="mgwpp-slider__wrapper">';
        foreach ( $slides as $slide_id ) {
            echo '<div class="mgwpp-slider__slide">';
            // Use the HTML version to avoid loading full post objects twice
            echo esc_html(\Elementor\Plugin::instance()->frontend->get_builder_content( $slide_id ));
            echo '</div>';
        }
        echo '</div>';
    }
}

}