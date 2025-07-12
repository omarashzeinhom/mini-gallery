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
        // Verify nonce first
        $nonce = isset($_GET['nonce']) ? sanitize_key($_GET['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'mgwpp_preview_nonce')) {
            wp_die(esc_html__('Security check failed. Please refresh the page.', 'mini-gallery'), 403);
        }

        // Validate gallery ID
        $gallery_id = isset($_GET['gallery_id']) ? absint($_GET['gallery_id']) : 0;
        if (!$gallery_id) {
            wp_die(esc_html__('Invalid gallery ID.', 'mini-gallery'), 400);
        }

        // Check gallery exists
        $gallery = get_post($gallery_id);
        if (!$gallery || 'mgwpp_soora' !== $gallery->post_type) {
            wp_die(esc_html__('Gallery not found.', 'mini-gallery'), 404);
        }

        // Get gallery type for asset loading
        $gallery_type = get_post_meta($gallery_id, 'gallery_type', true);

        // Enqueue necessary assets
        if (class_exists('MGWPP_Assets')) {
            MGWPP_Assets::enqueue_preview_assets($gallery_type);
        }

        // Output preview HTML
?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>

        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>
                <?php
                /* translators: %1$s is the gallery title */
                printf(
                    esc_html__('%1$s - Preview | Mini Gallery', 'mini-gallery'),
                    esc_html($gallery->post_title)
                );
                ?>
            </title>
            <?php wp_head(); ?>
        </head>

        <body>
            <div class="preview-container">
                <div class="preview-header">
                    <h1><?php echo esc_html($gallery->post_title); ?></h1>
                    <p><?php esc_html_e('Gallery Preview', 'mini-gallery'); ?></p>
                </div>

                <?php echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]'); ?>
            </div>
            <?php wp_footer(); ?>
        </body>

        </html>
<?php
        exit;
    }
}

add_action('wp_ajax_mgwpp_bulk_delete_galleries', function () {
    check_ajax_referer('mgwpp-admin-nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
    }

    $ids = $_POST['ids'] ?? [];
    if (empty($ids)) {
        wp_send_json_error('No IDs provided');
    }

    $deleted = 0;
    foreach ($ids as $id) {
        $id = absint($id);
        if ($id && wp_delete_post($id, true)) {
            $deleted++;
        }
    }

    wp_send_json_success([
        'message' => sprintf(__('Deleted %d galleries', 'mini-gallery'), $deleted)
    ]);
});
