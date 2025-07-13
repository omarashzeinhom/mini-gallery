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
        // Preview handling
        add_action('template_redirect', array(__CLASS__, 'handle_preview_request'));

        // AJAX handlers
        add_action('wp_ajax_mgwpp_preview', array(__CLASS__, 'preview_gallery'));
        add_action('wp_ajax_mgwpp_save_gallery_order', array(__CLASS__, 'save_gallery_order'));
        add_action('wp_ajax_mgwpp_create_gallery', array(__CLASS__, 'create_gallery'));
        add_action('wp_ajax_mgwpp_delete_gallery', array(__CLASS__, 'delete_gallery'));

        // Handle form submissions
        add_action('admin_post_mgwpp_create_gallery', array(__CLASS__, 'handle_create_gallery'));
        add_action('admin_post_mgwpp_save_gallery', array(__CLASS__, 'handle_save_gallery'));
    }

    /**
     * Handle preview requests
     */
    public static function handle_preview_request()
    {
        // Check if this is a preview request
        if (!isset($_GET['mgwpp_preview']) || $_GET['mgwpp_preview'] !== '1') {
            return;
        }

        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'mgwpp_preview')) {
            wp_die(
                '<h1>' . esc_html__('Preview Authorization Failed', 'mini-gallery') . '</h1>' .
                    '<p>' . esc_html__('Please return to the admin and click the preview button again.', 'mini-gallery') . '</p>' .
                    '<p><a href="' . esc_url(admin_url('edit.php?post_type=mgwpp_soora')) . '">' .
                    esc_html__('Return to Galleries', 'mini-gallery') . '</a></p>'
            );
        }

        // Validate gallery ID
        $gallery_id = absint($_GET['gallery_id'] ?? 0);
        if (!$gallery_id) {
            wp_die(esc_html__('Invalid gallery ID.', 'mini-gallery'));
        }

        // Get gallery
        $gallery = get_post($gallery_id);
        if (!$gallery || 'mgwpp_soora' !== $gallery->post_type) {
            wp_die(esc_html__('Gallery not found.', 'mini-gallery'));
        }

        // Check if user has permission to preview this gallery
        if (!current_user_can('edit_post', $gallery_id)) {
            wp_die(esc_html__('You do not have permission to preview this gallery.', 'mini-gallery'));
        }

        // Load preview template
        self::load_preview_template($gallery_id);
        exit;
    }

    /**
     * Load preview template
     */
    private static function load_preview_template($gallery_id)
    {
        // Get gallery data
        $gallery = get_post($gallery_id);
        $gallery_type = get_post_meta($gallery_id, 'gallery_type', true);
        // Ensure images are always in array format
        $gallery_images = get_post_meta($gallery_id, 'gallery_images', true);
        if (!is_array($gallery_images)) {
            $gallery_images = !empty($gallery_images) ? explode(',', $gallery_images) : [];
        }
        $gallery_images = array_map('absint', $gallery_images);
        $gallery_images = array_filter($gallery_images); // Remove empty values        
        // Ensure images are in array format and properly ordered
        if (!empty($gallery_images)) {
            $gallery_images = is_array($gallery_images) ? $gallery_images : explode(',', $gallery_images);
            $gallery_images = array_map('absint', $gallery_images);
            $gallery_images = array_filter($gallery_images); // Remove empty values
        } else {
            $gallery_images = [];
        }

        // Check if preview template exists
        $preview_template = MG_PLUGIN_PATH . 'templates/preview-gallery.php';
        if (file_exists($preview_template)) {
            include $preview_template;
        } else {
            // Fallback preview HTML
            self::render_fallback_preview($gallery, $gallery_type, $gallery_images);
        }
    }

    /**
     * Render fallback preview if template doesn't exist
     */
    private static function render_fallback_preview($gallery, $gallery_type, $gallery_images)
    {
?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>

        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo esc_html($gallery->post_title); ?> - <?php esc_html_e('Gallery Preview', 'mini-gallery'); ?></title>
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: #f1f1f1;
                }

                .preview-container {
                    max-width: 1200px;
                    margin: 0 auto;
                    background: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                }

                .preview-header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 1px solid #eee;
                }

                .preview-gallery {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                    gap: 20px;
                }

                .preview-image {
                    border-radius: 4px;
                    overflow: hidden;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                }

                .preview-image img {
                    width: 100%;
                    height: 200px;
                    object-fit: cover;
                    transition: transform 0.3s ease;
                }

                .preview-image:hover img {
                    transform: scale(1.05);
                }

                .no-images {
                    text-align: center;
                    color: #666;
                    font-style: italic;
                    padding: 40px;
                }

                .gallery-type {
                    background: #007cba;
                    color: white;
                    padding: 4px 12px;
                    border-radius: 12px;
                    font-size: 12px;
                    display: inline-block;
                    margin-top: 10px;
                }
            </style>
        </head>

        <body>
            <div class="preview-container">
                <div class="preview-header">
                    <h1><?php echo esc_html($gallery->post_title); ?></h1>
                    <div class="gallery-type"><?php echo esc_html(ucfirst(str_replace('_', ' ', $gallery_type))); ?></div>
                </div>

                <div class="preview-gallery">
                    <?php if (!empty($gallery_images)): ?>
                        <?php foreach ($gallery_images as $image_id): ?>
                            <?php
                            $image_url = wp_get_attachment_image_url($image_id, 'medium');
                            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                            if (!$image_alt) {
                                $image_alt = get_the_title($image_id);
                            }
                            ?>
                            <?php if ($image_url): ?>
                                <div class="preview-image">
                                    <img src="<?php echo esc_url($image_url); ?>"
                                        alt="<?php echo esc_attr($image_alt); ?>"
                                        loading="lazy">
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-images">
                            <p><?php esc_html_e('No images added to this gallery yet.', 'mini-gallery'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </body>

        </html>
<?php
    }

    /**
     * AJAX callback to preview a gallery
     */
    public static function preview_gallery()
    {
        // Verify nonce
        if (!wp_verify_nonce($_REQUEST['nonce'] ?? '', 'mgwpp_preview')) {
            wp_send_json_error(['message' => __('Security check failed', 'mini-gallery')]);
        }

        // Check permissions
        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'mini-gallery')]);
        }

        $gallery_id = absint($_REQUEST['gallery_id'] ?? 0);
        if (!$gallery_id) {
            wp_send_json_error(['message' => __('Invalid gallery ID', 'mini-gallery')]);
        }

        // Generate preview URL
        $preview_url = add_query_arg([
            'mgwpp_preview' => '1',
            'gallery_id' => $gallery_id,
            '_wpnonce' => wp_create_nonce('mgwpp_preview')
        ], home_url('/'));

        wp_send_json_success([
            'preview_url' => $preview_url,
            'message' => __('Preview URL generated', 'mini-gallery')
        ]);
    }

    /**
     * Save gallery order via AJAX
     */
    public static function save_gallery_order()
    {
        try {
            // Verify nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mgwpp_edit_gallery')) {
                wp_send_json_error(['message' => __('Security check failed', 'mini-gallery')]);
            }

            // Check permissions
            if (!current_user_can('edit_mgwpp_sooras')) {
                wp_send_json_error(['message' => __('Insufficient permissions', 'mini-gallery')]);
            }

            // Get gallery ID and images
            $gallery_id = isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
            $image_ids = isset($_POST['image_ids']) ? array_map('absint', $_POST['image_ids']) : [];

            // Validate inputs
            if (!$gallery_id || get_post_type($gallery_id) !== 'mgwpp_soora') {
                wp_send_json_error(['message' => __('Invalid gallery ID', 'mini-gallery')]);
            }

            // Filter out invalid image IDs and ensure they're actual image attachments
            $valid_ids = [];
            foreach ($image_ids as $image_id) {
                if ($image_id > 0 && wp_attachment_is_image($image_id)) {
                    $valid_ids[] = $image_id;
                }
            }

            // Save the new order - this preserves the exact order from the frontend
            $result = update_post_meta($gallery_id, 'gallery_images', $valid_ids);

            if ($result !== false) {
                wp_send_json_success([
                    'message' => __('Image order saved successfully', 'mini-gallery'),
                    'total_images' => count($valid_ids),
                    'image_ids' => $valid_ids // Return the saved order for verification
                ]);
            } else {
                wp_send_json_error(['message' => __('Failed to save image order', 'mini-gallery')]);
            }
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle gallery creation form submission
     */
    public static function handle_create_gallery()
    {
        // Verify nonce
        if (!isset($_POST['mgwpp_gallery_nonce']) || !wp_verify_nonce($_POST['mgwpp_gallery_nonce'], 'mgwpp_create_gallery')) {
            wp_die(__('Security check failed.', 'mini-gallery'));
        }

        // Check permissions
        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_die(__('You do not have sufficient permissions.', 'mini-gallery'));
        }

        // Get form data
        $gallery_title = sanitize_text_field($_POST['gallery_title'] ?? '');
        $gallery_type = sanitize_text_field($_POST['gallery_type'] ?? 'grid');
        $selected_media = sanitize_text_field($_POST['selected_media'] ?? '');

        if (empty($gallery_title)) {
            wp_die(__('Gallery title is required.', 'mini-gallery'));
        }

        // Create gallery post
        $gallery_id = wp_insert_post([
            'post_title' => $gallery_title,
            'post_type' => 'mgwpp_soora',
            'post_status' => 'publish',
            'meta_input' => [
                'gallery_type' => $gallery_type,
                'gallery_images' => !empty($selected_media) ? explode(',', $selected_media) : []
            ]
        ]);

        if (is_wp_error($gallery_id)) {
            wp_die(__('Failed to create gallery.', 'mini-gallery'));
        }

        // Redirect to edit page
        $redirect_url = add_query_arg([
            'gallery_id' => $gallery_id,
            '_wpnonce' => wp_create_nonce('mgwpp_edit_gallery'),
            'created' => 1
        ], admin_url('admin.php?page=mgwpp-edit-gallery'));

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Handle gallery save form submission
     */
    public static function handle_save_gallery()
    {

        // Verify nonce and permissions
        if (!isset($_POST['mgwpp_gallery_nonce']) || !wp_verify_nonce($_POST['mgwpp_gallery_nonce'], 'mgwpp_save_gallery_data')) {
            wp_die(__('Security check failed.', 'mini-gallery'));
        }

        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_die(__('You do not have sufficient permissions.', 'mini-gallery'));
        }

        $gallery_id = isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
        if (!$gallery_id || get_post_type($gallery_id) !== 'mgwpp_soora') {
            wp_die(__('Invalid gallery ID.', 'mini-gallery'));
        }

        // Update title
        if (isset($_POST['post_title'])) {
            wp_update_post([
                'ID' => $gallery_id,
                'post_title' => sanitize_text_field($_POST['post_title'])
            ]);
        }

        // Update gallery type
        if (isset($_POST['gallery_type'])) {
            update_post_meta($gallery_id, 'gallery_type', sanitize_text_field($_POST['gallery_type']));
        }

        // Update gallery images - preserve order from form
        if (isset($_POST['gallery_images']) && is_array($_POST['gallery_images'])) {
            $images = array_map('absint', $_POST['gallery_images']);
            $valid_images = array_filter($images, function ($id) {
                return $id > 0 && wp_attachment_is_image($id);
            });
            update_post_meta($gallery_id, 'gallery_images', array_values($valid_images));
        }
        if (isset($_POST['gallery_images']) && is_array($_POST['gallery_images'])) {
            $images = array_map('absint', $_POST['gallery_images']);
            $valid_images = array_filter($images);
            update_post_meta($gallery_id, 'gallery_images', $valid_images);
        }
        // Redirect back with success message
        $redirect_url = add_query_arg([
            'gallery_id' => $gallery_id,
            '_wpnonce' => wp_create_nonce('mgwpp_edit_gallery'),
            'updated' => 1
        ], admin_url('admin.php?page=mgwpp-edit-gallery'));

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Create gallery via AJAX
     */
    public static function create_gallery()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mgwpp-admin-nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'mini-gallery')]);
        }

        // Check permissions
        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'mini-gallery')]);
        }

        // Get form data
        $gallery_title = sanitize_text_field($_POST['gallery_title'] ?? '');
        $gallery_type = sanitize_text_field($_POST['gallery_type'] ?? 'grid');
        $selected_media = sanitize_text_field($_POST['selected_media'] ?? '');

        if (empty($gallery_title)) {
            wp_send_json_error(['message' => __('Gallery title is required', 'mini-gallery')]);
        }

        // Create gallery post
        $gallery_id = wp_insert_post([
            'post_title' => $gallery_title,
            'post_type' => 'mgwpp_soora',
            'post_status' => 'publish',
            'meta_input' => [
                'gallery_type' => $gallery_type,
                'gallery_images' => !empty($selected_media) ? explode(',', $selected_media) : []
            ]
        ]);

        if (is_wp_error($gallery_id)) {
            wp_send_json_error(['message' => __('Failed to create gallery', 'mini-gallery')]);
        }

        wp_send_json_success([
            'message' => __('Gallery created successfully', 'mini-gallery'),
            'gallery_id' => $gallery_id,
            'redirect_url' => add_query_arg([
                'gallery_id' => $gallery_id,
                '_wpnonce' => wp_create_nonce('mgwpp_edit_gallery')
            ], admin_url('admin.php?page=mgwpp-edit-gallery'))
        ]);
    }

    /**
     * Delete gallery via AJAX
     */
    public static function delete_gallery()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mgwpp-admin-nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'mini-gallery')]);
        }

        // Check permissions
        if (!current_user_can('delete_mgwpp_sooras')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'mini-gallery')]);
        }

        $gallery_id = absint($_POST['gallery_id'] ?? 0);
        if (!$gallery_id || get_post_type($gallery_id) !== 'mgwpp_soora') {
            wp_send_json_error(['message' => __('Invalid gallery ID', 'mini-gallery')]);
        }

        $result = wp_delete_post($gallery_id, true);
        if ($result) {
            wp_send_json_success(['message' => __('Gallery deleted successfully', 'mini-gallery')]);
        } else {
            wp_send_json_error(['message' => __('Failed to delete gallery', 'mini-gallery')]);
        }
    }
}

// Initialize the AJAX handler
MGWPP_Ajax_Handler::init();
