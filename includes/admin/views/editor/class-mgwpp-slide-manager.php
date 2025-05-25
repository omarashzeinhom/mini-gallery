<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Slide_Manager {
    private $gallery_id;
    
    public function __construct($gallery_id) {
        $this->gallery_id = $gallery_id;
    }

    public function get_slides() {
        return get_post_meta($this->gallery_id, '_mgwpp_slides', true) ?: [];
    }

    public function save_slides($slides) {
        update_post_meta($this->gallery_id, '_mgwpp_slides', $slides);
    }
    
    public function add_slide($slide_data) {
        $slides = $this->get_slides();
        $slides[] = wp_parse_args($slide_data, [
            'id' => uniqid('slide_'),
            'layers' => [],
            'settings' => []
        ]);
        $this->save_slides($slides);
    }
}