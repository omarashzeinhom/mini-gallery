<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Upload
{
    public static function mgwpp_create_gallery()
    {
        // Verify nonce
        if (!isset($_POST['mgwpp_gallery_nonce'])) {
            wp_die('Security check failed');
        }
        $nonce = sanitize_text_field(wp_unslash($_POST['mgwpp_gallery_nonce']));
        if (!wp_verify_nonce($nonce, 'mgwpp_create_gallery')) {
            wp_die('Security check failed');
        }

        // Validate required fields
        $gallery_title = isset($_POST['gallery_title'])
            ? sanitize_text_field(wp_unslash($_POST['gallery_title']))
            : '';
        $gallery_type = isset($_POST['gallery_type'])
            ? sanitize_text_field(wp_unslash($_POST['gallery_type']))
            : '';

        if (empty($gallery_title) || empty($gallery_type)) {
            wp_die('Missing required fields');
        }

        // 1. CHECK FOR DUPLICATES BEFORE CREATING
        $existing = get_posts([
            'post_type' => 'mgwpp_soora',
            'title' => $gallery_title,
            'posts_per_page' => 1,
            'post_status' => ['publish', 'pending', 'draft', 'future', 'private']
        ]);

        if ($existing) {
            wp_die(
                __('Gallery with this name already exists! Please use a unique name.', 'mini-gallery'),
                __('Duplicate Gallery', 'mini-gallery'),
                ['response' => 400]
            );
        }

        // 2. CREATE GALLERY AFTER DUPLICATE CHECK
        $post_id = wp_insert_post([
            'post_title'   => $gallery_title,
            'post_type'    => 'mgwpp_soora',
            'post_status'  => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            wp_die(esc_html($post_id->get_error_message()));
        }

        // Save gallery type
        update_post_meta($post_id, 'gallery_type', $gallery_type);

        // Handle media attachments
        $media_ids = [];
        if (!empty($_POST['selected_media'])) {
            $media_input = sanitize_text_field(wp_unslash($_POST['selected_media']));
            $media_ids = array_filter(array_map('absint', explode(',', $media_input)));
        }

        // 3. SAVE MEDIA IDs WITHOUT SETTING PARENT
        update_post_meta($post_id, 'gallery_images', $media_ids);

        wp_redirect(admin_url('admin.php?page=mgwpp_galleries'));
        exit;
    }
    public static function mgwpp_create_gallery_ajax()
    {
        // Verify nonce
        if (!isset($_POST['mgwpp_gallery_nonce'])) {
            wp_send_json_error('Security check failed', 403);
        }
        
        $nonce = sanitize_text_field(wp_unslash($_POST['mgwpp_gallery_nonce']));
        if (!wp_verify_nonce($nonce, 'mgwpp_create_gallery')) {
            wp_send_json_error('Security check failed', 403);
        }

        // Validate required fields
        $gallery_title = isset($_POST['gallery_title'])
            ? sanitize_text_field(wp_unslash($_POST['gallery_title']))
            : '';
        $gallery_type = isset($_POST['gallery_type'])
            ? sanitize_text_field(wp_unslash($_POST['gallery_type']))
            : '';

        if (empty($gallery_title) || empty($gallery_type)) {
            wp_send_json_error('Missing required fields', 400);
        }

        // Check for duplicates
        $existing = get_posts([
            'post_type' => 'mgwpp_soora',
            'title' => $gallery_title,
            'posts_per_page' => 1,
            'post_status' => ['publish', 'pending', 'draft', 'future', 'private']
        ]);

        if ($existing) {
            wp_send_json_error(
                __('Gallery with this name already exists! Please use a unique name.', 'mini-gallery'),
                400
            );
        }

        // Create gallery post
        $post_id = wp_insert_post([
            'post_title'   => $gallery_title,
            'post_type'    => 'mgwpp_soora',
            'post_status'  => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            wp_send_json_error($post_id->get_error_message(), 500);
        }

        // Save gallery type
        update_post_meta($post_id, 'gallery_type', $gallery_type);

        // Handle media attachments
        $media_ids = [];
        if (!empty($_POST['selected_media'])) {
            $media_input = sanitize_text_field(wp_unslash($_POST['selected_media']));
            $media_ids = array_filter(array_map('absint', explode(',', $media_input)));
        }

        // Save media IDs
        update_post_meta($post_id, 'gallery_images', $media_ids);

        // Return success response
        wp_send_json_success([
            'message' => __('Gallery created successfully!', 'mini-gallery'),
            'redirect' => admin_url('admin.php?page=mgwpp_galleries')
        ]);
    }
}

// Register handlers
add_action('admin_post_mgwpp_create_gallery', ['MGWPP_Upload', 'mgwpp_create_gallery']);
