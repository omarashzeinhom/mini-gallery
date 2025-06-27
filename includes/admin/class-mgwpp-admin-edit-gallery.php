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
        add_action('wp_ajax_mgwpp_refresh_preview', [__CLASS__, 'refresh_preview']);
    }

    public static function enqueue_assets($hook)
    {
        if ($hook === 'mini-gallery_page_mgwpp-edit-gallery') {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_media();
            
            // Enqueue admin styles
            wp_enqueue_style(
                'mgwpp-admin-edit-styles',
                plugins_url('css/mg-admin-edit.css', __FILE__),
                [],
                time()
            );
            
            // Enqueue scripts
            wp_enqueue_script(
                'mgwpp-admin-edit',
                plugins_url('js/mg-admin-edit.js', __FILE__),
                ['jquery', 'wp-color-picker', 'jquery-ui-sortable'],
                time(),
                true
            );

            // Localize script data
            wp_localize_script('mgwpp-admin-edit', 'mgwpp_preview', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mgwpp_preview_nonce'),
                'loading_msg' => __('Loading preview...', 'mini-gallery'),
                'error_msg' => __('Preview could not be loaded', 'mini-gallery')
            ]);
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
    {
        // Validate gallery ID
        $gallery_id = isset($_GET['gallery_id']) ? absint($_GET['gallery_id']) : 0;
        
        // Verify nonce
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field($_GET['_wpnonce']) : '';
        if (!wp_verify_nonce($nonce, 'mgwpp_edit_gallery')) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Security check failed.', 'mini-gallery') . '</p></div>';
            return;
        }

        // Verify gallery exists
        $gallery = get_post($gallery_id);
        if (!$gallery || 'mgwpp_soora' !== $gallery->post_type) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Invalid gallery.', 'mini-gallery') . '</p></div>';
            return;
        }

        // Process form submission
        if (isset($_POST['mgwpp_edit_gallery_submit'])) {
            $submitted_nonce = isset($_POST['mgwpp_edit_gallery_nonce']) ? sanitize_text_field($_POST['mgwpp_edit_gallery_nonce']) : '';
            if (!wp_verify_nonce($submitted_nonce, 'mgwpp_edit_gallery_save')) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Form security check failed.', 'mini-gallery') . '</p></div>';
                return;
            }

            // Save gallery settings
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
                    update_post_meta($gallery_id, $field, call_user_func($sanitizer, $_POST[$field]));
                }
            }

            // Save images and CTAs
            $image_ctas = [];
            if (isset($_POST['mgwpp_gallery_images']) && is_array($_POST['mgwpp_gallery_images'])) {
                foreach ($_POST['mgwpp_gallery_images'] as $index => $cta) {
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

        // Get current values
        $current_values = [
            'gallery_type' => get_post_meta($gallery_id, 'mgwpp_gallery_type', true) ?: 'single_carousel',
            'nav_color' => get_post_meta($gallery_id, 'mgwpp_nav_color', true) ?: '#ffffff',
            'nav_bg' => get_post_meta($gallery_id, 'mgwpp_nav_bg', true) ?: 'rgba(0,0,0,0.5)',
            'overlay_type' => get_post_meta($gallery_id, 'mgwpp_overlay_type', true) ?: 'none',
            'gradient' => get_post_meta($gallery_id, 'mgwpp_gradient', true) ?: 'linear-gradient(90deg, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0.3) 100%)',
            'mask_enabled' => get_post_meta($gallery_id, 'mgwpp_mask_enabled', true) ?: 'no',
            'cta_text' => get_post_meta($gallery_id, 'mgwpp_gallery_cta_text', true),
            'cta_link' => get_post_meta($gallery_id, 'mgwpp_gallery_cta_link', true),
            'images' => get_post_meta($gallery_id, 'mgwpp_gallery_images', true) ?: []
        ];

        // Get gallery images
        $gallery_images = [];
        if (!empty($current_values['images'])) {
            foreach ($current_values['images'] as $image_data) {
                if ($image_data['id']) {
                    $gallery_images[] = [
                        'id' => $image_data['id'],
                        'url' => wp_get_attachment_url($image_data['id']),
                        'cta_text' => $image_data['cta_text'] ?? '',
                        'cta_link' => $image_data['cta_link'] ?? ''
                    ];
                }
            }
        }
        ?>
        <div class="wrap mgwpp-edit-wrapper">
            <h1 class="mgwpp-edit-title">
                <?php esc_html_e('Edit Gallery', 'mini-gallery') ?>
                <span class="mgwpp-gallery-id">ID: <?php echo intval($gallery_id) ?></span>
            </h1>

            <form method="post" id="mgwpp-edit-form">
                <?php wp_nonce_field('mgwpp_edit_gallery_save', 'mgwpp_edit_gallery_nonce'); ?>
                
                <div class="mgwpp-edit-columns">
                    <div class="mgwpp-settings-column">
                        <h2><?php esc_html_e('Gallery Settings', 'mini-gallery'); ?></h2>
                        
                        <div class="mgwpp-form-section">
                            <label for="mgwpp_gallery_type"><?php esc_html_e('Gallery Type', 'mini-gallery'); ?></label>
                            <select name="mgwpp_gallery_type" id="mgwpp_gallery_type">
                                <?php foreach (self::$gallery_types as $key => $label) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($current_values['gallery_type'], $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mgwpp-form-section">
                            <label for="mgwpp_nav_color"><?php esc_html_e('Navigation Color', 'mini-gallery'); ?></label>
                            <input type="text" name="mgwpp_nav_color" id="mgwpp_nav_color" 
                                value="<?php echo esc_attr($current_values['nav_color']); ?>" 
                                class="mgwpp-color-field" 
                                data-default-color="#ffffff">
                        </div>

                        <div class="mgwpp-form-section">
                            <label for="mgwpp_nav_bg"><?php esc_html_e('Navigation Background', 'mini-gallery'); ?></label>
                            <input type="text" name="mgwpp_nav_bg" id="mgwpp_nav_bg" 
                                value="<?php echo esc_attr($current_values['nav_bg']); ?>" 
                                class="mgwpp-color-field" 
                                data-default-color="rgba(0,0,0,0.5)">
                        </div>

                        <div class="mgwpp-form-section">
                            <label for="mgwpp_gallery_cta_text"><?php esc_html_e('Global CTA Text', 'mini-gallery'); ?></label>
                            <input type="text" name="mgwpp_gallery_cta_text" id="mgwpp_gallery_cta_text" 
                                value="<?php echo esc_attr($current_values['cta_text']); ?>">
                        </div>

                        <div class="mgwpp-form-section">
                            <label for="mgwpp_gallery_cta_link"><?php esc_html_e('Global CTA Link', 'mini-gallery'); ?></label>
                            <input type="url" name="mgwpp_gallery_cta_link" id="mgwpp_gallery_cta_link" 
                                value="<?php echo esc_url($current_values['cta_link']); ?>">
                        </div>

                        <h2><?php esc_html_e('Gallery Images', 'mini-gallery'); ?></h2>
                        <div id="mgwpp-images-container" class="mgwpp-sortable-container">
                            <?php foreach ($gallery_images as $index => $image) : ?>
                                <div class="mgwpp-image-item" data-index="<?php echo esc_attr($index); ?>">
                                    <div class="mgwpp-image-preview">
                                        <img src="<?php echo esc_url($image['url']); ?>" alt="<?php esc_attr_e('Gallery image', 'mini-gallery'); ?>">
                                        <button type="button" class="mgwpp-remove-image">×</button>
                                    </div>
                                    <input type="hidden" name="mgwpp_gallery_images[<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($image['id']); ?>">
                                    
                                    <div class="mgwpp-image-cta">
                                        <label><?php esc_html_e('CTA Text:', 'mini-gallery'); ?></label>
                                        <input type="text" name="mgwpp_gallery_images[<?php echo esc_attr($index); ?>][cta_text]" 
                                            value="<?php echo esc_attr($image['cta_text']); ?>" 
                                            placeholder="<?php esc_attr_e('Button text', 'mini-gallery'); ?>">
                                        
                                        <label><?php esc_html_e('CTA Link:', 'mini-gallery'); ?></label>
                                        <input type="url" name="mgwpp_gallery_images[<?php echo esc_attr($index); ?>][cta_link]" 
                                            value="<?php echo esc_url($image['cta_link']); ?>" 
                                            placeholder="<?php esc_attr_e('https://example.com', 'mini-gallery'); ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" id="mgwpp-add-images" class="button button-secondary">
                            <?php esc_html_e('Add Images', 'mini-gallery'); ?>
                        </button>

                        <div class="mgwpp-form-section">
                            <button type="submit" name="mgwpp_edit_gallery_submit" class="button button-primary">
                                <?php esc_html_e('Save Changes', 'mini-gallery'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="mgwpp-preview-column">
                        <h2>
                            <?php esc_html_e('Live Preview', 'mini-gallery'); ?>
                            <button type="button" id="mgwpp-refresh-preview" class="button button-secondary">
                                <?php esc_html_e('Refresh Preview', 'mini-gallery'); ?>
                            </button>
                        </h2>
                        <div id="mgwpp-preview-container">
                            <div class="mgwpp-preview-loading">
                                <p><?php esc_html_e('Loading preview...', 'mini-gallery'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    public static function refresh_preview()
    {
        // Validate request
        if (!isset($_POST['nonce']) || 
            !wp_verify_nonce($_POST['nonce'], 'mgwpp_preview_nonce') || 
            !isset($_POST['gallery_id'])) {
            wp_send_json_error(__('Invalid request', 'mini-gallery'), 400);
        }

        $gallery_id = absint($_POST['gallery_id']);
        $gallery = get_post($gallery_id);

        if (!$gallery || 'mgwpp_soora' !== $gallery->post_type) {
            wp_send_json_error(__('Invalid gallery', 'mini-gallery'), 404);
        }

        // Generate preview
        $preview_html = do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]');

        wp_send_json_success([
            'html' => $preview_html
        ]);
    }
}

MGWPP_Admin_Edit_Gallery::init();


