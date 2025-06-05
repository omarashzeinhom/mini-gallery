<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Gallery_Post_Type
{

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
            'rewrite' => ['slug' => 'gallery'], // Single page like /gallery/my-gallery-title
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
    public static function mgwpp_add_gallery_meta_boxes()
    {
        add_meta_box(
            'mgwpp_gallery_links',
            'Gallery Links',
            [self::class, 'render_gallery_links_meta_box'],
            'mgwpp_soora',
            'normal',
            'high'
        );

        // Only add text meta box for specific gallery types
        $screen = get_current_screen();
        if ($screen && $post_id = get_the_ID()) {
            $gallery_type = get_post_meta($post_id, 'gallery_type', true);
            if (in_array($gallery_type, ['type1', 'type2', 'type3'])) {
                add_meta_box(
                    'mgwpp_gallery_text',
                    'Gallery Text',
                    [self::class, 'render_gallery_text_meta_box'],
                    'mgwpp_soora',
                    'normal',
                    'high'
                );
            }
        }
    }

    public static function render_gallery_links_meta_box($post)
    {
        // Get existing values
        $image_links = get_post_meta($post->ID, '_mgwpp_image_links', true);
        $cta_links = get_post_meta($post->ID, '_mgwpp_cta_links', true);

        // Nonce field for security
        wp_nonce_field('mgwpp_save_gallery_links', 'mgwpp_gallery_links_nonce');

        // Display fields
        echo '<h3>Image Links</h3>';
        // You'll need to implement your image selection/display logic here
        echo '<div id="mgwpp-image-links-container"></div>';

        echo '<h3>CTA Links</h3>';
        echo '<p><label>Primary CTA: <input type="url" name="mgwpp_cta_links[primary]" value="' . esc_attr($cta_links['primary'] ?? '') . '"></label></p>';
        echo '<p><label>Secondary CTA: <input type="url" name="mgwpp_cta_links[secondary]" value="' . esc_attr($cta_links['secondary'] ?? '') . '"></label></p>';
    }

    public static function render_gallery_text_meta_box($post)
    {
        $custom_text = get_post_meta($post->ID, '_mgwpp_custom_text', true);
        wp_editor(
            $custom_text,
            'mgwpp_custom_text',
            ['textarea_name' => 'mgwpp_custom_text']
        );
    }

    public static function mgwpp_save_gallery_meta($post_id)
    {
        // Verify nonce and permissions
        if (
            !isset($_POST['mgwpp_gallery_links_nonce']) ||
            !wp_verify_nonce($_POST['mgwpp_gallery_links_nonce'], 'mgwpp_save_gallery_links')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_mgwpp_soora', $post_id)) return;

        // Save image links
        if (isset($_POST['mgwpp_image_links'])) {
            update_post_meta($post_id, '_mgwpp_image_links', array_map('esc_url_raw', $_POST['mgwpp_image_links']));
        }

        // Save CTA links
        if (isset($_POST['mgwpp_cta_links'])) {
            update_post_meta($post_id, '_mgwpp_cta_links', array_map('esc_url_raw', $_POST['mgwpp_cta_links']));
        }

        // Save custom text if available
        if (isset($_POST['mgwpp_custom_text'])) {
            update_post_meta($post_id, '_mgwpp_custom_text', sanitize_textarea_field($_POST['mgwpp_custom_text']));
        }
    }
}

// Register the custom post type during the 'init' hook
add_action('init', array('MGWPP_Gallery_Post_Type', 'mgwpp_register_gallery_post_type'));
// Add the hooks
add_action('add_meta_boxes', [MGWPP_Gallery_Post_Type::class, 'mgwpp_add_gallery_meta_boxes']);
add_action('save_post_mgwpp_soora', [MGWPP_Gallery_Post_Type::class, 'mgwpp_save_gallery_meta']);
