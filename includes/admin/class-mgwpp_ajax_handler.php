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
        // Generate nonce first before verification
        $nonce = isset($_GET['nonce']) ? sanitize_key($_GET['nonce']) : '';

        if (!wp_verify_nonce($nonce, 'mgwpp_preview_nonce')) {
            // Create a new nonce if verification fails
            $nonce = wp_create_nonce('mgwpp_preview_nonce');
        }


        // Sanitize and validate input
        $gallery_id = isset($_GET['gallery_id']) ? absint($_GET['gallery_id']) : 0;

        if (!$gallery_id) {
            wp_die(esc_html__('Invalid gallery ID.', 'mini-gallery'));
        }

        // Verify gallery exists
        $gallery = get_post($gallery_id);
        if (!$gallery || 'mgwpp_soora' !== $gallery->post_type) {
            wp_die(esc_html__('Gallery not found.', 'mini-gallery'));
        }

        // Output preview HTML
?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>

        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <title><?php echo esc_html($gallery->post_title); ?> - <?php esc_html_e('Preview', 'mini-gallery'); ?></title>
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    font-family: -apple-system, BlinkMacSystemFont, sans-serif;
                }

                .preview-header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 20px;
                    border-bottom: 1px solid #ddd;
                }
            </style>
            <?php
            // Print scripts in head
            wp_print_scripts(['jquery']);
            ?>
        </head>

        <body>
            <div class="preview-header">
                <h1><?php echo esc_html($gallery->post_title); ?></h1>
                <p><?php esc_html_e('Gallery Preview', 'mini-gallery'); ?></p>
            </div>

            <?php
            // Output the gallery shortcode
            echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]');
            wp_print_scripts();
            ?>
        </body>

        </html>
<?php
        exit;
    }
}
