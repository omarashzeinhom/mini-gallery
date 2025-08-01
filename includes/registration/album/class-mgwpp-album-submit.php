<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Album_Submit
{
    public static function init()
    {
        add_action('admin_post_mgwpp_create_album', [__CLASS__, 'handle_album_submission']);
        add_action('admin_post_nopriv_mgwpp_create_album', [__CLASS__, 'handle_album_submission']);
        add_action('save_post_mgwpp_album', [__CLASS__, 'save_album_submission'], 10, 3);
    }

    public static function handle_album_submission()
    {
        // Verify nonce
        if (
            !isset($_POST['mgwpp_album_submit_nonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_POST['mgwpp_album_submit_nonce'])), 'mgwpp_album_submit_nonce')
        ) {
            wp_die('Security check failed', 'Error', ['response' => 403]);
        }

        // Check permissions
        if (!current_user_can('create_mgwpp_albums')) {
            wp_die('Permission denied', 'Error', ['response' => 403]);
        }

        // Validate required fields
        if (empty($_POST['album_title'])) {
            wp_die('Album title is required', 'Error', ['response' => 400]);
        }

        // Sanitize input
        $album_title = sanitize_text_field(wp_unslash($_POST['album_title']));
        $album_description = isset($_POST['album_description']) ? sanitize_textarea_field(wp_unslash($_POST['album_description'])) : '';
        $galleries = isset($_POST['album_galleries']) ? array_map('intval', wp_unslash($_POST['album_galleries'])) : [];
        $cover_id = isset($_POST['album_cover_id']) ? intval(wp_unslash($_POST['album_cover_id'])) : 0;

        // Create album
        $new_album_id = wp_insert_post([
            'post_title'   => $album_title,
            'post_content' => $album_description,
            'post_status'  => 'publish',
            'post_type'    => 'mgwpp_album',
        ]);

        if (is_wp_error($new_album_id)) {
            wp_die(
                esc_html__('Album creation failed: ', 'mini-gallery') . esc_html($new_album_id->get_error_message()),
                esc_html__('Error', 'mini-gallery'),
                ['response' => 500]
            );
        }

        // Save meta
        update_post_meta($new_album_id, '_mgwpp_album_galleries', $galleries);

        if ($cover_id) {
            set_post_thumbnail($new_album_id, $cover_id);
        }

        wp_safe_redirect(admin_url('admin.php?page=mgwpp_albums&album_created=1'));
        exit;
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