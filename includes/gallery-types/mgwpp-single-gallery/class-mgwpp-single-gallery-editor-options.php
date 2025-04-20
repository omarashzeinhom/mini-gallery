<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Single_Gallery_Editor_Options
{

    public static function register_meta_boxes($post)
    {
        add_meta_box(
            'mgwpp_single_options',
            __('Single Carousel Options', 'mini-gallery'),
            [ __CLASS__, 'render_meta_box' ],
            'mgwpp_soora',
            'normal',
            'high'
        );
    }

    public static function render_meta_box($post)
    {
        wp_nonce_field('mgwpp_single_options_nonce', 'mgwpp_single_options_nonce');
        // Grab any existing meta
        $nav_color = get_post_meta($post->ID, 'mgwpp_nav_color', true) ?: '#000000';
        ?>
        <p>
          <label for="mgwpp_nav_color"><?php esc_html_e('Navigation Color', 'mini-gallery'); ?></label><br>
          <input 
            name="mgwpp_nav_color" 
            id="mgwpp_nav_color" 
            type="text" 
            value="<?php echo esc_attr($nav_color); ?>" 
            class="color-picker" 
          />
        </p>
        <?php
    }

    public static function save_meta($post_id)
    {
        if (! isset($_POST['mgwpp_single_options_nonce'])) {
            return;
        }
        if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mgwpp_single_options_nonce'])), 'mgwpp_single_options_nonce')) {
            return;
        }

        if (isset($_POST['mgwpp_nav_color'])) {
            $color = sanitize_hex_color(wp_unslash($_POST['mgwpp_nav_color']));
            update_post_meta($post_id, 'mgwpp_nav_color', $color);
        }
    }
}
