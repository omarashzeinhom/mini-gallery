<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Testimonial_Post_Type
{

    public static function mgwpp_register_testimonial_post_type()
    {
        $labels = [
            'name' => _x('Testimonials', 'Post Type General Name', 'mini-gallery'),
            'singular_name' => _x('Testimonial', 'Post Type Singular Name', 'mini-gallery'),
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
            'show_in_menu' => false,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-testimonial',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'mgwpp_testimonial',
            'map_meta_cap' => true,
            'capabilities' => [
                'edit_post' => 'edit_mgwpp_testimonial',
                'read_post' => 'read_mgwpp_testimonial',
                'delete_post' => 'delete_mgwpp_testimonial',
                'edit_posts' => 'edit_mgwpp_testimonials',          // Fixed
                'edit_others_posts' => 'edit_others_mgwpp_testimonials',  // Fixed
                'publish_posts' => 'publish_mgwpp_testimonials',    // Fixed
                'read_private_posts' => 'read_private_mgwpp_testimonials', // Fixed
                'delete_posts' => 'delete_mgwpp_testimonials',       // Fixed
                'delete_private_posts' => 'delete_private_mgwpp_testimonials', // Fixed
                'delete_published_posts' => 'delete_published_mgwpp_testimonials', // Fixed
                'delete_others_posts' => 'delete_others_mgwpp_testimonials', // Fixed
                'edit_private_posts' => 'edit_private_mgwpp_testimonials', // Fixed
                'edit_published_posts' => 'edit_published_mgwpp_testimonials', // Fixed
                'create_posts' => 'create_mgwpp_testimonials'       // Fixed
            ]
        ];

        register_post_type('mgwpp_testimonial', $args);
    }
}
