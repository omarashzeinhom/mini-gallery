<?php
class MGWPP_Gallery_Post_Type { 

    public static function mgwpp_register_gallery_post_type()
    {
        $args = array(
            'public' => true,
            'label' => 'Gallery Image',
            'description' => 'Manage your galleries here',
            'show_in_rest' => true,
            'show_in_menu' => false,
            'rest_base' => 'soora-api',
            'menu_icon' => 'dashicons-format-gallery',
            'has_archive' => true,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
            'capability_type' => 'mgwpp_soora',
            'map_meta_cap' => true,
            'capabilities' => array(
                'edit_post' => 'edit_mgwpp_soora',
                'read_post' => 'read_mgwpp_soora',
                'delete_post' => 'delete_mgwpp_soora',
                'edit_posts' => 'edit_mgwpp_sooras',
                'edit_others_posts' => 'edit_others_mgwpp_sooras',
                'publish_posts' => 'publish_mgwpp_sooras',
                'read_private_posts' => 'read_private_mgwpp_sooras',
                'delete_posts' => 'delete_mgwpp_sooras',
                'delete_private_posts' => 'delete_private_mgwpp_sooras',
                'delete_published_posts' => 'delete_published_mgwpp_sooras',
                'delete_others_posts' => 'delete_others_mgwpp_sooras',
                'edit_private_posts' => 'edit_private_mgwpp_sooras',
                'edit_published_posts' => 'edit_published_mgwpp_sooras',
                'create_posts' => 'create_mgwpp_sooras',
            )
        );
        register_post_type('mgwpp_soora', $args);
    }
}

// Register the custom post type during the 'init' hook
add_action('init', array('MGWPP_Gallery_Post_Type', 'mgwpp_register_gallery_post_type'));

?>