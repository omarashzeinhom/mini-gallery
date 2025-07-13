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
        add_action('template_redirect', array(__CLASS__, 'mgwpp_handle_preview_request'));

        // AJAX handlers
        add_action('template_redirect', 'mgwpp_handle_preview_request');
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
    public static function mgwpp_handle_preview_request()
    {
        // 1. First check if this is a preview request
        if (!isset($_GET['mgwpp_preview']) || $_GET['mgwpp_preview'] !== '1') {
            return;
        }

        // 2. Verify nonce with proper action
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'mgwpp_preview')) {
            wp_die(
                '<h1>' . esc_html__('Preview Authorization Failed', 'mini-gallery') . '</h1>' .
                    '<p>' . esc_html__('Please return to the admin and click the preview button again.', 'mini-gallery') . '</p>' .
                    '<p><a href="' . esc_url(admin_url('edit.php?post_type=mgwpp_soora')) . '">' .
                    esc_html__('Return to Galleries', 'mini-gallery') . '</a></p>'
            );
        }

        // 3. Validate gallery ID
        $gallery_id = isset($_GET['gallery_id']) ? absint($_GET['gallery_id']) : 0;
        if (!$gallery_id) {
            wp_die(esc_html__('Invalid gallery ID format.', 'mini-gallery'));
        }

        // 4. Verify gallery exists
        $gallery = get_post($gallery_id);
        if (!$gallery || 'mgwpp_soora' !== $gallery->post_type) {
            wp_die(esc_html__('The requested gallery no longer exists.', 'mini-gallery'));
        }

        // 5. Force asset loading
        add_action('wp_enqueue_scripts', function () use ($gallery_id) {
            // Get gallery type
            $gallery_type = get_post_meta($gallery_id, 'gallery_type', true);

            // Enqueue frontend assets
            wp_enqueue_style('mgwpp-frontend');

            // Enqueue specific gallery type assets
            switch ($gallery_type) {
                case 'single_carousel':
                    wp_enqueue_style('mg-single-carousel-styles');
                    wp_enqueue_script('mg-single-carousel-js');
                    break;
                case 'multi_carousel':
                    wp_enqueue_style('mg-multi-carousel-styles');
                    wp_enqueue_script('mg-multi-carousel-js');
                    break;
                case 'grid':
                    wp_enqueue_style('mg-grid-styles');
                    wp_enqueue_script('mg-grid-gallery-js');
                    break;
                case 'mega_slider':
                    wp_enqueue_style('mg-mega-carousel-styles');
                    wp_enqueue_script('mg-mega-carousel-js');
                    break;
                case 'pro_carousel':
                    wp_enqueue_style('mgwpp-pro-carousel-styles');
                    wp_enqueue_script('mgwpp-pro-carousel-js');
                    break;
                case 'neon_carousel':
                    wp_enqueue_style('mgwpp-neon-carousel-styles');
                    wp_enqueue_script('mgwpp-neon-carousel-js');
                    break;
                case 'threed_carousel':
                    wp_enqueue_style('mgwpp-threed-carousel-styles');
                    wp_enqueue_script('mgwpp-threed-carousel-js');
                    break;
                case 'testimonials_carousel':
                    wp_enqueue_style('mgwpp-testimonial-carousel-styles');
                    wp_enqueue_script('mgwpp-testimonial-carousel-js');
                    break;
                case 'full_page_slider':
                    wp_enqueue_style('mg-fullpage-slider-styles');
                    wp_enqueue_script('mg-fullpage-slider-js');
                    break;
                case 'spotlight_carousel':
                    wp_enqueue_style('mg-spotlight-slider-styles');
                    wp_enqueue_script('mg-spotlight-slider-js');
                    break;
            }

            // Add initialization script
            add_action('wp_footer', function () use ($gallery_type) {
                echo '<script>';
                switch ($gallery_type) {
                    case 'single_carousel':
                        echo 'if (typeof MGWPP_SingleCarousel !== "undefined") MGWPP_SingleCarousel.init();';
                        break;
                    case 'multi_carousel':
                        echo 'if (typeof MGWPP_MultiCarousel !== "undefined") MGWPP_MultiCarousel.init();';
                        break;
                        // Add other gallery types as needed
                }
                echo '</script>';
            }, 999);
        });

        // 6. Show preview template
        get_header();
        echo '<div class="mgwpp-preview-container">';
        echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]');
        echo '</div>';
        get_footer();
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

        // Get images
        $gallery_images = get_post_meta($gallery_id, 'gallery_images', true);
        $gallery_images = !empty($gallery_images) ? (array) $gallery_images : [];
        $gallery_images = array_map('absint', $gallery_images);
        $gallery_images = array_filter($gallery_images);

        // Always use the proper preview template
        $preview_template = MG_PLUGIN_PATH . 'templates/preview-gallery.php';
        if (file_exists($preview_template)) {
            include $preview_template;
        } else {
            // Minimal fallback if template is missing
            echo '<div class="mgwpp-preview-fallback">';
            echo '<h3>' . esc_html($gallery->post_title) . '</h3>';
            echo '<p>' . esc_html__('Preview template not found', 'mini-gallery') . '</p>';
            echo '</div>';
        }
    }

    /**
     * AJAX callback to preview a gallery
     */
    public static function preview_gallery()
    {
        // Verify nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'mgwpp_preview_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'mini-gallery')]);
        }

        // Check permissions
        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'mini-gallery')]);
        }

        $gallery_id = isset($_REQUEST['gallery_id']) ? absint($_REQUEST['gallery_id']) : 0;
        if (!$gallery_id) {
            wp_send_json_error(['message' => __('Invalid gallery ID', 'mini-gallery')]);
        }

        // Generate preview URL
        $preview_url = add_query_arg([
            'mgwpp_preview' => '1',
            'gallery_id'    => $gallery_id,
            '_wpnonce'      => wp_create_nonce('mgwpp_preview')
        ], home_url('/'));

        wp_send_json_success([
            'preview_url' => $preview_url,
            'message'     => __('Preview URL generated', 'mini-gallery')
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
