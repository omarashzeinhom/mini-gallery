<?php 

// Add testimonial meta box
add_action('add_meta_boxes', 'mgwpp_add_testimonial_meta_box');
function mgwpp_add_testimonial_meta_box() {
    add_meta_box(
        'mgwpp_testimonial_details',
        __('Testimonial Details', 'mini-gallery'),
        'mgwpp_testimonial_meta_box_callback',
        'testimonial',
        'normal',
        'high'
    );
}

// Meta box content
function mgwpp_testimonial_meta_box_callback($post) {
    wp_nonce_field('mgwpp_testimonial_nonce', 'mgwpp_testimonial_nonce');
    
    $author = get_post_meta($post->ID, '_mgwpp_testimonial_author', true);
    $position = get_post_meta($post->ID, '_mgwpp_testimonial_position', true);
    
    echo '<p>';
    echo '<label for="mgwpp_testimonial_author">'.__('Author Name:', 'mini-gallery').'</label>';
    echo '<input type="text" id="mgwpp_testimonial_author" name="mgwpp_testimonial_author" value="'.esc_attr($author).'" style="width: 100%; margin-top: 5px;">';
    echo '</p>';
    
    echo '<p>';
    echo '<label for="mgwpp_testimonial_position">'.__('Position/Company:', 'mini-gallery').'</label>';
    echo '<input type="text" id="mgwpp_testimonial_position" name="mgwpp_testimonial_position" value="'.esc_attr($position).'" style="width: 100%; margin-top: 5px;">';
    echo '</p>';
}

// Save meta data
add_action('save_post', 'mgwpp_save_testimonial_meta');
function mgwpp_save_testimonial_meta($post_id) {
    if (!isset($_POST['mgwpp_testimonial_nonce']) ||
        !wp_verify_nonce($_POST['mgwpp_testimonial_nonce'], 'mgwpp_testimonial_nonce') ||
        defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ||
        !current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['mgwpp_testimonial_author'])) {
        update_post_meta($post_id, '_mgwpp_testimonial_author', sanitize_text_field($_POST['mgwpp_testimonial_author']));
    }
    
    if (isset($_POST['mgwpp_testimonial_position'])) {
        update_post_meta($post_id, '_mgwpp_testimonial_position', sanitize_text_field($_POST['mgwpp_testimonial_position']));
    }
}