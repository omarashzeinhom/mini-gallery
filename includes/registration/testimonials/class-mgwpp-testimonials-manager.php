<?php
if (!defined('ABSPATH')) {
    exit;
}
class MGWPP_Testimonial_Manager {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_testimonial_meta'), 10, 1);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'mgwpp_testimonial_details',
            esc_html__('Testimonial Details', 'mini-gallery'),
            array($this, 'render_meta_box'),
            'testimonial',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('mgwpp_testimonial_nonce', 'mgwpp_testimonial_nonce');
        
        $author = get_post_meta($post->ID, '_mgwpp_testimonial_author', true);
        $position = get_post_meta($post->ID, '_mgwpp_testimonial_position', true);
        
        echo '<p>';
        echo '<label for="mgwpp_testimonial_author">' . esc_html__('Author Name:', 'mini-gallery') . '</label>';
        printf(
            '<input type="text" id="mgwpp_testimonial_author" name="mgwpp_testimonial_author" value="%s" style="width: 100%%; margin-top: 5px;">',
            esc_attr($author)
        );
        echo '</p>';
        
        echo '<p>';
        echo '<label for="mgwpp_testimonial_position">' . esc_html__('Position/Company:', 'mini-gallery') . '</label>';
        printf(
            '<input type="text" id="mgwpp_testimonial_position" name="mgwpp_testimonial_position" value="%s" style="width: 100%%; margin-top: 5px;">',
            esc_attr($position)
        );
        echo '</p>';
    }

    public function save_testimonial_meta($post_id) {
        if (
            !isset($_POST['mgwpp_testimonial_nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mgwpp_testimonial_nonce'])), 'mgwpp_testimonial_nonce') ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
            !current_user_can('edit_post', $post_id)
        ) {
            return;
        }

        if (isset($_POST['mgwpp_testimonial_author'])) {
            update_post_meta(
                $post_id,
                '_mgwpp_testimonial_author',
                sanitize_text_field(wp_unslash($_POST['mgwpp_testimonial_author']))
            );
        }
        
        if (isset($_POST['mgwpp_testimonial_position'])) {
            update_post_meta(
                $post_id,
                '_mgwpp_testimonial_position',
                sanitize_text_field(wp_unslash($_POST['mgwpp_testimonial_position']))
            );
        }
    }
}

new MGWPP_Testimonial_Manager();