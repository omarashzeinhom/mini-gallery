<?php
class MGWPP_Album_Submit {
    // Hook into WordPress form submission
    public static function init() {
        add_action('save_post_mgwpp_album', array(__CLASS__, 'save_album_submission'), 10, 3);
        add_action('admin_post_mgwpp_submit_album', array(__CLASS__, 'handle_album_submission'));
    }

    // Handle album form submission from front-end or admin
    public static function mgwpp_handle_album_submission() {
        if (!isset($_POST['mgwpp_album_nonce']) || !wp_verify_nonce($_POST['mgwpp_album_nonce'], 'mgwpp_album_submit_nonce')) {
            wp_die('Permission denied');
        }

        // Check if the user has permission to create albums
        if (!current_user_can('create_mgwpp_albums')) {
            wp_die('You do not have permission to create albums');
        }

        // Sanitize and retrieve form data
        $album_title = sanitize_text_field($_POST['mgwpp_album_title']);
        $album_description = sanitize_textarea_field($_POST['mgwpp_album_description']);
        $galleries = isset($_POST['mgwpp_album_galleries']) ? array_map('intval', $_POST['mgwpp_album_galleries']) : array();

        // Create a new album post
        $new_album_id = wp_insert_post(array(
            'post_title' => $album_title,
            'post_content' => $album_description,
            'post_status' => 'publish',
            'post_type' => 'mgwpp_album',
        ));

        if ($new_album_id) {
            // Save galleries associated with the new album
            update_post_meta($new_album_id, '_mgwpp_album_galleries', $galleries);

            // Redirect to the newly created album's edit page
            wp_redirect(admin_url("post.php?post=$new_album_id&action=edit"));
            exit;
        } else {
            wp_die('Error creating the album');
        }
    }

    // Save album data from the admin edit page
    public static function mgwpp_save_album_submission($post_id, $post, $update) {
        if ($post->post_type !== 'mgwpp_album') {
            return;
        }

        // Only save data when the album is first created or updated
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check for nonce and permissions
        if (!isset($_POST['mgwpp_album_galleries_nonce']) || 
            !wp_verify_nonce($_POST['mgwpp_album_galleries_nonce'], 'mgwpp_album_galleries_nonce')) {
            return;
        }

        // Save the associated galleries
        $galleries = isset($_POST['mgwpp_album_galleries']) ? 
            array_map('intval', $_POST['mgwpp_album_galleries']) : array();
        update_post_meta($post_id, '_mgwpp_album_galleries', $galleries);
    }
}

// Initialize the class
MGWPP_Album_Submit::init();
