<?php
/**
 * Media Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class MG_Media_Handler {
    
    public function handle_upload() {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploadedfile = $_FILES['file'];
        
        $upload_overrides = array(
            'test_form' => false
        );
        
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            // Create attachment
            $attachment = array(
                'post_mime_type' => $movefile['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $movefile['file']);
            
            if (!function_exists('wp_generate_attachment_metadata')) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
            }
            
            $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);
            
            return array(
                'id' => $attach_id,
                'url' => $movefile['url'],
                'type' => strpos($movefile['type'], 'image') !== false ? 'image' : 'video',
                'title' => basename($movefile['file']),
                'thumbnail' => wp_get_attachment_thumb_url($attach_id)
            );
        } else {
            throw new Exception($movefile['error']);
        }
    }
    
    public function get_media_library() {
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => array('image', 'video'),
            'post_status' => 'inherit',
            'posts_per_page' => 50,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $attachments = get_posts($args);
        $media_items = array();
        
        foreach ($attachments as $attachment) {
            $media_items[] = array(
                'id' => $attachment->ID,
                'title' => $attachment->post_title,
                'url' => wp_get_attachment_url($attachment->ID),
                'thumbnail' => wp_get_attachment_thumb_url($attachment->ID),
                'type' => strpos($attachment->post_mime_type, 'image') !== false ? 'image' : 'video'
            );
        }
        
        return $media_items;
    }
}
