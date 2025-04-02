<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register Custom Post Type: Testimonial
add_action('init', 'mgwpp_register_testimonial_cpt');
function mgwpp_register_testimonial_cpt() {
    $labels = [
        'name' => _x('Testimonials', 'Post Type General Name', 'mini-gallery'),
        'singular_name' => _x('Testimonial', 'Post Type Singular Name', 'mini-gallery'),
        'menu_name' => __('Testimonials', 'mini-gallery'),
        'all_items' => __('All Testimonials', 'mini-gallery'),
        'add_new_item' => __('Add New Testimonial', 'mini-gallery'),
        'add_new' => __('Add New', 'mini-gallery'),
        'edit_item' => __('Edit Testimonial', 'mini-gallery'),
        'update_item' => __('Update Testimonial', 'mini-gallery'),
        'view_item' => __('View Testimonial', 'mini-gallery'),
    ];

    $args = [
        'label' => __('Testimonial', 'mini-gallery'),
        'description' => __('Customer testimonials and reviews', 'mini-gallery'),
        'labels' => $labels,
        'supports' => ['title', 'editor', 'thumbnail'],
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 25,
        'menu_icon' => 'dashicons-testimonial',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
    ];

    register_post_type('testimonial', $args);
}