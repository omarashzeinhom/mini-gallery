<?php
/**
 * Media Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Media_Handler
{
    public function handle_upload()
    {
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
                'post_title' => sanitize_file_name(pathinfo($movefile['file'], PATHINFO_FILENAME)),
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
                'title' => $attachment['post_title'],
                'thumbnail' => wp_get_attachment_thumb_url($attach_id)
            );
        } else {
            throw new Exception($movefile['error']);
        }
    }

    public function get_media_library()
    {
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => array('image/jpeg', 'image/png', 'image/gif', 'image/webp'),
            'post_status' => 'inherit',
            'posts_per_page' => -1,
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
                'type' => 'image' // Since we're only getting images
            );
        }

        return $media_items;
    }

    public function render_media_selector($editor_id)
    {
        ?>
        <div class="mgwpp-media-selector">
            <button type="button" class="button mgwpp-open-media-library" 
                    data-editor="<?php echo esc_attr($editor_id); ?>">
                <?php esc_html_e('Select Images', 'mini-gallery'); ?>
            </button>
            
            <div class="mgwpp-selected-media-container">
                <ul class="mgwpp-selected-media"></ul>
            </div>
            
            <input type="hidden" class="mgwpp-selected-media-ids" name="mgwpp_selected_media_ids">
        </div>
        <?php
    }

    public function enqueue_media_scripts()
    {
        if (!did_action('wp_enqueue_media')) {
            wp_enqueue_media();
        }
        
        wp_enqueue_script(
            'mgwpp-media-handler-script',
            MG_PLUGIN_URL . 'editor/assets/js/mgwpp-media-handler.js',
            array('jquery'),
            MGWPP_ASSET_VERSION,
            true
        );
        
        wp_localize_script('mgwpp-media-handler-script', 'mgMedia', array(
            'title' => __('Select Gallery Images', 'mini-gallery'),
            'button' => __('Add to Gallery', 'mini-gallery'),
            'remove' => __('Remove', 'mini-gallery'),
        ));
    }
}