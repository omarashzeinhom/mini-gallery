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
            'show_in_elementor' => true,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-testimonial',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'testimonial',
            'map_meta_cap' => true,
            'capabilities' => [
                'edit_post' => 'edit_testimonial',
                'read_post' => 'read_testimonial',
                'delete_post' => 'delete_testimonial',
                'edit_posts' => 'edit_testimonials',
                'edit_others_posts' => 'edit_others_testimonials',
                'publish_posts' => 'publish_testimonials',
                'read_private_posts' => 'read_private_testimonials',
                'delete_posts' => 'delete_testimonials',
                'delete_private_posts' => 'delete_private_testimonials',
                'delete_published_posts' => 'delete_published_testimonials',
                'delete_others_posts' => 'delete_others_testimonials',
                'edit_private_posts' => 'edit_private_testimonials',
                'edit_published_posts' => 'edit_published_testimonials',
                'create_posts' => 'create_testimonials'
            ]
        ];

        register_post_type('testimonial', $args);
    }
}
