<?php
if (!defined('ABSPATH')) {
    exit;
}
class MGWPP_Testimonial_Manager {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_testimonial', [$this, 'save_testimonial'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }




    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        // Only load on testimonial edit screens
        if (('post.php' !== $hook && 'post-new.php' !== $hook) || 'testimonial' !== $post_type) {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();
       
    }

    public function add_meta_boxes() {
        add_meta_box(
            'mgwpp_testimonial_details',
            __('Testimonial Details', 'mini-gallery'),
            [$this, 'render_testimonial_meta'],
            'testimonial',
            'normal',
            'high'
        );
    }

    public function render_testimonial_meta($post) {
        wp_nonce_field('mgwpp_testimonial_nonce', 'mgwpp_testimonial_nonce');
        
        $author = get_post_meta($post->ID, '_mgwpp_author', true);
        $position = get_post_meta($post->ID, '_mgwpp_position', true);
        $image_id = get_post_meta($post->ID, '_mgwpp_image_id', true);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
        ?>
        
        <div class="mgwpp-meta-field">
            <label for="mgwpp_author"><?php esc_html_e('Author Name:', 'mini-gallery'); ?></label>
            <input type="text" id="mgwpp_author" name="mgwpp_author" value="<?php echo esc_attr($author); ?>" class="widefat">
        </div>

        <div class="mgwpp-meta-field" style="margin-top:15px;">
            <label for="mgwpp_position"><?php esc_html_e('Position/Company:', 'mini-gallery'); ?></label>
            <input type="text" id="mgwpp_position" name="mgwpp_position" value="<?php echo esc_attr($position); ?>" class="widefat">
        </div>

      
        </div>
        <?php
    }

    public function save_testimonial($post_id, $post) {
        if (!isset($_POST['mgwpp_testimonial_nonce']) || 
            !wp_verify_nonce($_POST['mgwpp_testimonial_nonce'], 'mgwpp_testimonial_nonce') ||
            !current_user_can('edit_post', $post_id) ||
            wp_is_post_autosave($post_id) ||
            wp_is_post_revision($post_id)
        ) {
            return;
        }

        update_post_meta($post_id, '_mgwpp_author', sanitize_text_field($_POST['mgwpp_author']));
        update_post_meta($post_id, '_mgwpp_position', sanitize_text_field($_POST['mgwpp_position']));
        update_post_meta($post_id, '_mgwpp_image_id', absint($_POST['mgwpp_image_id']));
    }
}

new MGWPP_Testimonial_Manager();
