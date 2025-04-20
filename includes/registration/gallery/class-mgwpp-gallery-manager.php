<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Gallery_Manager
{

    // Function to delete the gallery
    public static function mgwpp_delete_gallery()
    {
        // Security check
        if (!isset($_GET['gallery_id']) || !isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mgwpp_delete_gallery')) {
            wp_die('Security check failed for deleting gallery');
        }

        $gallery_id = intval($_GET['gallery_id']);

        // Check user capabilities
        if (!current_user_can('delete_mgwpp_soora', $gallery_id)) {
            wp_die('You do not have permission to delete this gallery');
        }

        // Delete the gallery post
        wp_delete_post($gallery_id, true);

        // Redirect to the gallery page
        wp_redirect(esc_url_raw(admin_url('admin.php?page=mini-gallery')));
        exit;
    }

    // Register the action for gallery deletion
    public static function mgwpp_register_gallery_delete_action()
    {
        add_action('admin_post_mgwpp_delete_gallery', array('MGWPP_Gallery_Manager', 'mgwpp_delete_gallery'));
    }
}
