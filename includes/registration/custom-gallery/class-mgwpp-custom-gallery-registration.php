<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class MGWPP_Custom_Gallery {

public static function init() {
    add_action('init', [__CLASS__, 'register_post_type']);
}

public static function register_post_type() {
    $args = [
        'public' => true,
        'label'  => 'MGWPP Gallery',
        'show_in_rest' => true,
        'supports' => ['title', 'elementor'],
        'menu_icon' => 'dashicons-slides',
        'capability_type' => 'mgwpp_gallery',
        'map_meta_cap' => true,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'rewrite' => ['slug' => 'mgwpp-gallery']
    ];

    register_post_type('mgwpp_gallery', $args);

    // Add Elementor support
    add_post_type_support('mgwpp_gallery', 'elementor');
}
}
MGWPP_Custom_Gallery::init();