<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Album_Submit
{
    public static function init()
    {
        add_action('wp_ajax_mgwpp_create_album_ajax', [__CLASS__, 'handle_album_ajax_submission']);
        add_action('wp_ajax_nopriv_mgwpp_create_album_ajax', [__CLASS__, 'handle_album_ajax_submission']);
        add_action('save_post_mgwpp_album', [__CLASS__, 'save_album_submission'], 10, 3);
    }

    public static function handle_album_ajax_submission()
    {
        // Verify nonce using security parameter
        if (!isset($_POST['security'])) {
            wp_send_json_error('Security nonce not provided', 403);
        }

        if (!wp_verify_nonce(sanitize_key($_POST['security']), 'mgwpp_nonce')) {
            wp_send_json_error('Security verification failed', 403);
        }

        // Check permissions
        if (!current_user_can('create_mgwpp_albums')) {
            wp_send_json_error('Permission denied', 403);
        }

        // Validate required fields
        if (empty($_POST['album_title'])) {
            wp_send_json_error('Album title is required', 400);
        }

        $album_title = sanitize_text_field(wp_unslash($_POST['album_title']));
        $album_description = isset($_POST['album_description']) ? sanitize_textarea_field(wp_unslash($_POST['album_description'])) : '';
        $galleries = isset($_POST['album_galleries']) ? array_map('intval', $_POST['album_galleries']) : [];
        $cover_id = isset($_POST['album_cover_id']) ? intval($_POST['album_cover_id']) : 0;

        // Validate galleries - MUST have at least one selected
        if (empty($galleries)) {
            wp_send_json_error('Please select at least one gallery', 400);
        }

        // Create album
        $new_album_id = wp_insert_post([
            'post_title'   => $album_title,
            'post_content' => $album_description,
            'post_status'  => 'publish',
            'post_type'    => 'mgwpp_album',
        ]);

        if (is_wp_error($new_album_id)) {
            wp_send_json_error('Album creation failed: ' . $new_album_id->get_error_message(), 500);
        }

        // Save meta
        update_post_meta($new_album_id, '_mgwpp_album_galleries', $galleries);

        if ($cover_id) {
            set_post_thumbnail($new_album_id, $cover_id);
        }

        wp_send_json_success([
            'message' => 'Album created successfully!',
            'redirect' => admin_url('admin.php?page=mgwpp_albums&album_created=1')
        ]);
    }
    public static function save_album_submission($post_id, $post, $update)
    {
        // Security checks
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ($post->post_type !== 'mgwpp_album') return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (
            !isset($_POST['mgwpp_album_galleries_nonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_POST['mgwpp_album_galleries_nonce'])), 'mgwpp_album_galleries_nonce')
        ) return;

        // Save galleries
        $galleries = isset($_POST['mgwpp_album_galleries']) ?
            array_map('intval', wp_unslash($_POST['mgwpp_album_galleries'])) : [];
        update_post_meta($post_id, '_mgwpp_album_galleries', $galleries);

        // Save cover
        if (isset($_POST['album_cover_id'])) {
            $cover_id = intval(wp_unslash($_POST['album_cover_id']));
            set_post_thumbnail($post_id, $cover_id);
        }
    }

    public static function ajax_delete_album()
    {
        // Verify nonce first
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_POST['nonce'])), 'mgwpp_nonce')) {
            wp_send_json_error('Invalid nonce', 403);
        }

        // Get album ID from POST data
        $album_id = isset($_POST['id']) ? intval(wp_unslash($_POST['id'])) : 0;

        if (!$album_id || !current_user_can('delete_mgwpp_album', $album_id)) {
            wp_send_json_error('Unauthorized', 403);
        }

        $result = wp_delete_post($album_id, true);

        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Deletion failed', 500);
        }
    }
}

add_action('admin_notices', function () {
    // Add nonce verification for admin notice
    if (
        isset($_GET['album_created']) &&
        $_GET['album_created'] === '1' &&
        isset($_GET['_wpnonce']) &&
        wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'mgwpp_album_created_notice')
    ) {
        echo '<div class="notice notice-success is-dismissible"><p>';
        esc_html_e('Album created successfully!', 'mini-gallery');
        echo '</p></div>';
    }
});

MGWPP_Album_Submit::init();
