<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Upload
{
    public static function mgwpp_create_gallery()
    {
        // Verify nonce (with unslashing and sanitization)
        if (!isset($_POST['mgwpp_gallery_nonce'])) {
            wp_die('Security check failed');
        }
        $nonce = sanitize_text_field(wp_unslash($_POST['mgwpp_gallery_nonce']));
        if (!wp_verify_nonce($nonce, 'mgwpp_create_gallery')) {
            wp_die('Security check failed');
        }

        // Validate required fields (with unslashing)
        $gallery_title = isset($_POST['gallery_title'])
            ? sanitize_text_field(wp_unslash($_POST['gallery_title']))
            : '';
        $gallery_type = isset($_POST['gallery_type'])
            ? sanitize_text_field(wp_unslash($_POST['gallery_type']))
            : '';

        if (empty($gallery_title) || empty($gallery_type)) {
            wp_die('Missing required fields');
        }

        // Create gallery post
        $post_id = wp_insert_post([
            'post_title'   => $gallery_title, // Already sanitized
            'post_type'    => 'mgwpp_soora',
            'post_status'  => 'publish',
            'post_content' => ''
        ]);

        if (is_wp_error($post_id)) {
            wp_die(esc_html($post_id->get_error_message()));
        }

        // Save gallery type (already sanitized)
        update_post_meta($post_id, 'gallery_type', $gallery_type);
        // ADD THIS LINE TO SAVE IMAGES TO GALLERY
        // Handle media attachments safely
        if (!empty($_POST['selected_media'])) {
            $media_input = sanitize_text_field(wp_unslash($_POST['selected_media']));
            $media_ids = array_filter(array_map('absint', explode(',', $media_input)));

            // SAVE IMAGE IDS TO GALLERY_META - CRITICAL MISSING LINE
            update_post_meta($post_id, 'gallery_images', $media_ids);

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
        update_post_meta($post_id, 'gallery_images', $media_ids);

        wp_redirect(admin_url('admin.php?page=mgwpp_galleries'));
        exit;
    }
}

// Register handler
add_action('admin_post_mgwpp_create_gallery', ['MGWPP_Upload', 'mgwpp_create_gallery']);
