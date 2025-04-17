<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Upload {
    public static function mgwpp_create_gallery() {
        // Verify nonce
        if (!isset($_POST['mgwpp_gallery_nonce']) || 
            !wp_verify_nonce($_POST['mgwpp_gallery_nonce'], 'mgwpp_create_gallery')) {
            wp_die('Security check failed');
        }

        // Validate required fields
        if (empty($_POST['gallery_title']) || empty($_POST['gallery_type'])) {
            wp_die('Missing required fields');
        }

        // Create gallery post
        $post_id = wp_insert_post([
            'post_title'   => sanitize_text_field($_POST['gallery_title']),
            'post_type'    => 'mgwpp_soora',
            'post_status'  => 'publish',
            'post_content' => ''
        ]);

        if (is_wp_error($post_id)) {
            wp_die(esc_html($post_id->get_error_message()));
        }

        // Save gallery type
        update_post_meta($post_id, 'gallery_type', 
            sanitize_text_field($_POST['gallery_type']));

        // Handle media attachments
        if (!empty($_POST['selected_media'])) {
            $media_ids = explode(',', sanitize_text_field($_POST['selected_media']));
            
            foreach ($media_ids as $media_id) {
                $attachment_post = get_post($media_id);
                if ($attachment_post && $attachment_post->post_type === 'attachment') {
                    wp_update_post([
                        'ID' => $media_id,
                        'post_parent' => $post_id
                    ]);
                }
            }
        }

        wp_redirect(admin_url('admin.php?page=mgwpp_galleries'));
        exit;
    }
}

// Register handler
add_action('admin_post_mgwpp_create_gallery', ['MGWPP_Upload', 'mgwpp_create_gallery']);