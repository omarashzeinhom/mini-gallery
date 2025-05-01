<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MGWPP_Ajax_Handler
 *
 * Handles AJAX requests for Mini Gallery plugin.
 */
class MGWPP_Ajax_Handler
{

    /**
     * Initialize all AJAX hooks.
     */
    public static function init()
    {
        add_action('wp_ajax_mgwpp_preview', array(__CLASS__, 'preview_gallery'));
    }

    /**
     * AJAX callback to preview a gallery.
     */
    public static function preview_gallery()
    {
        if (isset($_GET['nonce'])){
        $nonce = sanitize_key(wp_unslash($_GET['nonce']));
        }
        // Verify nonce first
        if (!isset($nonce) || !wp_verify_nonce($nonce, 'mgwpp_preview_nonce')) {
            wp_die(esc_html__('Security verification failed.', 'mini-gallery'));
        }

        // Sanitize and validate input
        $gallery_id = isset($_GET['gallery_id']) ? absint(wp_unslash($_GET['gallery_id'])) : 0;
        
        if (!$gallery_id) {
            wp_die(esc_html__('Invalid gallery ID.', 'mini-gallery'));
        }

        // Verify gallery exists
        $gallery = get_post($gallery_id);
        if (!$gallery || 'mgwpp_soora' !== $gallery->post_type) {
            wp_die(esc_html__('Gallery not found.', 'mini-gallery'));
        }

        // Output preview
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <title><?php echo esc_html($gallery->post_title); ?> - Preview</title>
            <?php
            // Enqueue styles safely
            if (wp_style_is('mgwpp-gallery-style', 'registered')) {
                wp_enqueue_style('mgwpp-gallery-style');
                wp_print_styles();
            }
            ?>
            <style>body{margin:0;padding:0;}</style>
        </head>
        <body>
            <?php echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]'); ?>
            
            <?php
            // Enqueue scripts safely
            if (wp_script_is('mgwpp-gallery-script', 'registered')) {
                wp_enqueue_script('mgwpp-gallery-script');
                wp_print_scripts();
            }
            ?>
        </body>
        </html>
        <?php
        exit;
    }
}
