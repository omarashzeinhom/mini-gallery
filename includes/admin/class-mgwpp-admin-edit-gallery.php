<?php
if (!defined('ABSPATH')) {
    exit;
}



class MGWPP_Admin_Edit_Gallery
{

    private static $gallery_types = [
        'single_carousel' => 'Single Carousel',
        'multi_carousel' => 'Multi Carousel',
        'grid' => 'Image Grid',
        'mega_slider' => 'Mega Slider',
        'pro_carousel' => 'Pro Carousel',
        'neon_carousel' => 'Neon Carousel',
        'threed_carousel' => '3D Carousel',
        'full_page_slider' => 'Full Page Slider',
        'spotlight_carousel' => 'Spotlight Carousel',
        'testimonials_carousel' => 'Testimonials'
    ];

    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'register_edit_gallery_page']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function enqueue_assets($hook)
    {
        // Corrected hook name
        if ($hook === 'mini-gallery_page_mgwpp-edit-gallery') {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('mgwpp-admin-edit', plugins_url('js/mg-admin-scripts.js', __FILE__), ['jquery'], time(), true);
        }
    }

    public static function register_edit_gallery_page()
    {
        add_submenu_page(
            'mini-gallery',
            __('Edit Gallery', 'mini-gallery'),
            __('Edit Gallery', 'mini-gallery'),
            'manage_options',
            'mgwpp-edit-gallery',
            [__CLASS__, 'render_edit_gallery_page']
        );
    }

    public static function render_edit_gallery_page()
    { // CSS
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        $plugin_url = plugin_dir_url(dirname(__FILE__, 2)); // Points to plugin root


        wp_enqueue_style(
            'mgwpp-admin-styles',
            $plugin_url . 'includes/admin/css/mg-admin-edit-dashboard-styles.css',
            [],
            filemtime(plugin_dir_path(dirname(__FILE__, 2)) . 'includes/admin/css/mg-admin-edit-dashboard-styles.css')
        );
        // Validate gallery ID with unslashing
        $gallery_id = isset($_GET['gallery_id']) ? absint($_GET['gallery_id']) : 0;

        if (!$gallery_id) {
            echo '<div class="notice notice-error"><p>' . esc_html__('No gallery specified.', 'mini-gallery') . '</p></div>';
            return;
        }

        // Verify nonce with proper unslashing and validation
        // FIXED
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!wp_verify_nonce(sanitize_text_field($nonce), 'mgwpp_edit_gallery')) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Security check failed.', 'mini-gallery') . '</p></div>';
            return;
        }

        // Verify gallery post
        $gallery = get_post($gallery_id);
        if (!$gallery || 'mgwpp_soora' !== $gallery->post_type) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Invalid gallery.', 'mini-gallery') . '</p></div>';
            return;
        }

        // Process form submission with proper unslashing
        if (isset($_POST['mgwpp_edit_gallery_submit'])) {
            $submitted_nonce = isset($_POST['mgwpp_edit_gallery_nonce']) ? sanitize_text_field(wp_unslash($_POST['mgwpp_edit_gallery_nonce'])) : '';
            if (!wp_verify_nonce(sanitize_text_field($submitted_nonce), 'mgwpp_edit_gallery_save')) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Form security check failed.', 'mini-gallery') . '</p></div>';
                return;
            }

            // Sanitize and save all fields with unslashing
            $fields = [
                'mgwpp_gallery_type' => 'sanitize_text_field',
                'mgwpp_nav_color' => 'sanitize_hex_color',
                'mgwpp_nav_bg' => 'sanitize_text_field',
                'mgwpp_overlay_type' => 'sanitize_text_field',
                'mgwpp_gradient' => 'sanitize_text_field',
                'mgwpp_mask_enabled' => 'sanitize_text_field',
                'mgwpp_gallery_cta_text' => 'sanitize_text_field',
                'mgwpp_gallery_cta_link' => 'esc_url_raw'
            ];

            foreach ($fields as $field => $sanitizer) {
                if (isset($_POST[$field])) {
                    $raw_value = sanitize_text_field(wp_unslash($_POST[$field]));
                    update_post_meta($gallery_id, $field, call_user_func($sanitizer, $raw_value));
                }
            }

            // Save image CTAs with unslashing and validation
            $image_ctas = [];
            if (isset($_POST['mgwpp_gallery_images']) && is_array($_POST['mgwpp_gallery_images'])) {
                if (sanitize_post(isset($_POST['mgwpp_gallery_images']))) {
                    $nonce = sanitize_post(wp_unslash(sanitize_key($_POST['mgwpp_gallery_images'])));
                }


                $raw_images = $nonce ? array_map('wp_unslash', (array)wp_unslash(sanitize_key($_POST['mgwpp_gallery_images']))) : [];
                foreach ($raw_images as $index => $cta) {
                    $image_ctas[$index] = [
                        'id' => isset($cta['id']) ? absint($cta['id']) : 0,
                        'cta_text' => sanitize_text_field($cta['cta_text'] ?? ''),
                        'cta_link' => esc_url_raw($cta['cta_link'] ?? '')
                    ];
                }
            }
            update_post_meta($gallery_id, 'mgwpp_gallery_images', $image_ctas);

            echo '<div class="notice notice-success is-dismissible"><p>'
                . esc_html__('Gallery updated successfully.', 'mini-gallery')
                . '</p></div>';
        }

        // Retrieve current values with defaults
        $current_values = [
            'nav_color' => get_post_meta($gallery_id, 'mgwpp_nav_color', true) ?: '#ffffff',
            'nav_bg' => get_post_meta($gallery_id, 'mgwpp_nav_bg', true) ?: 'rgba(0,0,0,0.5)',
            'overlay_type' => get_post_meta($gallery_id, 'mgwpp_overlay_type', true) ?: 'none',
            'gradient' => get_post_meta($gallery_id, 'mgwpp_gradient', true) ?: 'linear-gradient(90deg, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0.3) 100%)',
            'gallery_type' => get_post_meta($gallery_id, 'mgwpp_gallery_type', true) ?: 'single_carousel',
            'mask_enabled' => get_post_meta($gallery_id, 'mgwpp_mask_enabled', true) ?: 'no',
            'cta_text' => get_post_meta($gallery_id, 'mgwpp_gallery_cta_text', true),
            'cta_link' => get_post_meta($gallery_id, 'mgwpp_gallery_cta_link', true),
            'images' => get_post_meta($gallery_id, 'mgwpp_gallery_images', true) ?: []
        ];

?>
        <div class="wrap mgwpp-edit-wrapper">
            <h1 class="mgwpp-edit-title">
                <?php esc_html_e('Edit Gallery', 'mini-gallery') ?>
                <span class="mgwpp-gallery-id">ID: <?php echo intval($gallery_id) ?></span>
            </h1>

            <div class="mgwpp-edit-columns">

                <h2> Editing Is Coming Soon !</h2>

            </div>
        </div>

      
<?php
    }

    public static function refresh_preview()
    {
        // Verify nonce first
        check_ajax_referer('mgwpp_preview_nonce', 'nonce');

        // Check if gallery_id exists and is valid
        if (!isset($_POST['gallery_id']) || empty($_POST['gallery_id'])) {
            wp_send_json_error(
                __('No gallery selected', 'mini-gallery'),
                400
            );
        }

        // Sanitize and validate the gallery ID
        $gallery_id = absint($_POST['gallery_id']);

        // Verify the gallery exists and is correct post type
        $gallery = get_post($gallery_id);
        if (!$gallery || $gallery->post_type !== 'mgwpp_gallery') {
            wp_send_json_error(
                __('Invalid gallery ID', 'mini-gallery'),
                404
            );
        }

        // Check user capabilities
        if (!current_user_can('edit_post', $gallery_id)) {
            wp_send_json_error(
                __('Unauthorized access', 'mini-gallery'),
                403
            );
        }

        // Generate preview HTML
        $preview_html = do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]');

        // Send successful response
        wp_send_json_success(
            [
                'html' => $preview_html
            ]
        );
    }
}
MGWPP_Admin_Edit_Gallery::init();
add_action('wp_ajax_mgwpp_refresh_preview', [MGWPP_Admin_Edit_Gallery::class, 'refresh_preview']);
