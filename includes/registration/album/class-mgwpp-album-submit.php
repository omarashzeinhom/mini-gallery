<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class MGWPP_Album_Submit
{
    public static function init()
    {
        // Register both admin and public submission handlers
        add_action('admin_post_mgwpp_create_album', array(__CLASS__, 'handle_album_submission'));
        add_action('admin_post_nopriv_mgwpp_create_album', array(__CLASS__, 'handle_album_submission'));
        add_action('save_post_mgwpp_album', array(__CLASS__, 'save_album_submission'), 10, 3);
    }

    public static function handle_album_submission()
    {
        // Verify nonce
        if (
            !isset($_POST['mgwpp_album_submit_nonce']) ||
            !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mgwpp_album_submit_nonce'] ) ), 'mgwpp_album_submit_nonce' )
        ) {
            wp_die('Security check failed for Submitting Album', 'Error', array('response' => 403));
        }
        

        // Check permissions
        if (!current_user_can('create_mgwpp_albums')) {
            wp_die('Permission denied', 'Error', array('response' => 403));
        }
    
        // Validate required fields
        if (empty($_POST['album_title'])) {
            wp_die('Album title is required', 'Error', array('response' => 400));
        }
    
        // Sanitize input
        $album_title = sanitize_text_field(wp_unslash($_POST['album_title']));
        $album_description = isset($_POST['album_description']) ?
            sanitize_textarea_field(wp_unslash($_POST['album_description'])) : '';
        $galleries = isset($_POST['album_galleries']) ? array_map('intval', $_POST['album_galleries']) : array();
    
        // Create post
        $new_album_id = wp_insert_post(array(
            'post_title' => $album_title,
            'post_content' => $album_description,
            'post_status' => 'publish',
            'post_type' => 'mgwpp_album',
        ));
    
        if (is_wp_error($new_album_id)) {
            wp_die(
                esc_html__('Album Creation Failed', 'mini-gallery') . esc_html($new_album_id->get_error_message()),
                esc_html__('Error', 'mini-gallery'),
                array('response' => 500)
            );
        }
    
        // Save meta
        update_post_meta($new_album_id, '_mgwpp_album_galleries', $galleries);
    
        // Redirect with success message
        wp_safe_redirect(add_query_arg(
            'message',
            'album-created',
            admin_url("post.php?post=$new_album_id&action=edit")
        ));
        exit;
    }
    
    public static function save_album_submission($post_id, $post, $update)
    {
        // Early return if not our post type
        if ($post->post_type !== 'mgwpp_album') {
            return;
        }

        // Skip autosaves
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Verify nonce
        if (
            !isset($_POST['mgwpp_album_galleries_nonce']) ||
            !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mgwpp_album_galleries_nonce'] ) ), 'mgwpp_album_galleries_nonce' )
        ) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save galleries
        $galleries = isset($_POST['mgwpp_album_galleries']) ?
            array_map('intval', $_POST['mgwpp_album_galleries']) : array();
        update_post_meta($post_id, '_mgwpp_album_galleries', $galleries);
    }

   public static function delete_album_submisison($post_id, $post){
    add_action('wp_ajax_delete_album', function() {
        check_ajax_referer('mgwpp_nonce', 'nonce');
        
        $album_id = intval($_GET['id']);
        if (!current_user_can('delete_post', $album_id)) {
          wp_send_json_error('Unauthorized', 403);
        }
      
        wp_delete_post($album_id, true);
        wp_send_json_success();
      });

   }
      
}

// Initialize the class
MGWPP_Album_Submit::init();