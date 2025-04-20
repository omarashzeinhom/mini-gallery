<?php
if (!defined('ABSPATH')) {
    exit;
}

// TODO
wp_enqueue_style('wp-color-picker');
wp_enqueue_script('wp-color-picker');
$plugin_url = plugin_dir_url(dirname(__FILE__, 2)); // Points to plugin root
    
// CSS
wp_enqueue_style(
    'mgwpp-admin-styles',
    $plugin_url . 'admin/css/mg-admin-edit-dashboard-styles.css',
    [],
    filemtime(plugin_dir_path(dirname(__FILE__, 2)) . 'admin/css/mg-admin-edit-dashboard-styles.css')
);


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
    {
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
                <!-- Settings Panel -->
                <div class="mgwpp-settings-panel">
                    <form method="post" class="mgwpp-settings-form">
                        <?php wp_nonce_field('mgwpp_edit_gallery_save', 'mgwpp_edit_gallery_nonce'); ?>

                        <!-- Gallery Type Selector -->
                        <div class="mgwpp-settings-section">
                            <h3><?php esc_html_e('Gallery Type', 'mini-gallery') ?></h3>
                            <select name="mgwpp_gallery_type" class="mgwpp-type-selector">
                                <?php foreach (self::$gallery_types as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value) ?>" <?php selected($current_values['gallery_type'], $value) ?>>
                                        <?php echo esc_html($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Visual Style Settings -->
                        <div class="mgwpp-settings-section">
                            <h3><?php esc_html_e('Visual Style', 'mini-gallery') ?></h3>
                            <div class="mgwpp-style-grid">
                                <div class="mgwpp-style-item">
                                    <label><?php esc_html_e('Navigation Color', 'mini-gallery') ?></label>
                                    <input type="text" name="mgwpp_nav_color"
                                        value="<?php echo esc_attr($current_values['nav_color']) ?>"
                                        class="color-picker">
                                </div>

                                <div class="mgwpp-style-item">
                                    <label><?php esc_html_e('Navigation Background', 'mini-gallery') ?></label>
                                    <input type="text" name="mgwpp_nav_bg"
                                        value="<?php echo esc_attr($current_values['nav_bg']) ?>"
                                        class="color-picker">
                                </div>

                                <div class="mgwpp-style-item">
                                    <label><?php esc_html_e('Overlay Type', 'mini-gallery') ?></label>
                                    <select name="mgwpp_overlay_type" class="mgwpp-overlay-select">
                                        <option value="none" <?php selected($current_values['overlay_type'], 'none') ?>><?php esc_html_e('None', 'mini-gallery') ?></option>
                                        <option value="gradient" <?php selected($current_values['overlay_type'], 'gradient') ?>><?php esc_html_e('Gradient', 'mini-gallery') ?></option>
                                        <option value="solid" <?php selected($current_values['overlay_type'], 'solid') ?>><?php esc_html_e('Solid', 'mini-gallery') ?></option>
                                    </select>
                                </div>

                                <div class="mgwpp-style-item mgwpp-gradient-picker"
                                    style="<?php echo ($current_values['overlay_type'] !== 'gradient') ? 'display:none;' : '' ?>">
                                    <label><?php esc_html_e('Gradient', 'mini-gallery') ?></label>
                                    <input type="text" name="mgwpp_gradient"
                                        value="<?php echo esc_attr($current_values['gradient']) ?>"
                                        class="gradient-input">
                                </div>

                                <div class="mgwpp-style-item">
                                    <label><?php esc_html_e('Image Mask', 'mini-gallery') ?></label>
                                    <label class="mgwpp-switch">
                                        <input type="checkbox" name="mgwpp_mask_enabled"
                                            value="yes" <?php checked($current_values['mask_enabled'], 'yes') ?>>
                                        <span class="mgwpp-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Global CTA Settings -->
                        <div class="mgwpp-settings-section">
                            <h3><?php esc_html_e('Call to Action', 'mini-gallery') ?></h3>
                            <div class="mgwpp-cta-grid">
                                <div>
                                    <label><?php esc_html_e('Button Text', 'mini-gallery') ?></label>
                                    <input type="text" name="mgwpp_gallery_cta_text"
                                        value="<?php echo esc_attr($current_values['cta_text']) ?>">
                                </div>
                                <div>
                                    <label><?php esc_html_e('Button Link', 'mini-gallery') ?></label>
                                    <input type="url" name="mgwpp_gallery_cta_link"
                                        value="<?php echo esc_attr($current_values['cta_link']) ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Per-Image CTAs -->
                        <div class="mgwpp-settings-section">
                            <h3><?php esc_html_e('Per-Image CTAs', 'mini-gallery') ?></h3>
                            <div class="mgwpp-image-ctas">
                                <?php foreach ($current_values['images'] as $index => $img) : ?>
                                    <div class="mgwpp-cta-item">
                                        <?php echo wp_get_attachment_image($img['id'], [100, 100]); ?>
                                        <input type="hidden"
                                            name="mgwpp_gallery_images[<?php echo intval($index) ?>][id]"
                                            value="<?php echo intval($img['id']) ?>">
                                        <input type="text"
                                            name="mgwpp_gallery_images[<?php echo intval($index) ?>][cta_text]"
                                            value="<?php echo esc_attr($img['cta_text'] ?? '') ?>"
                                            placeholder="<?php esc_attr_e('CTA Text', 'mini-gallery') ?>">
                                        <input type="url"
                                            name="mgwpp_gallery_images[<?php echo intval($index) ?>][cta_link]"
                                            value="<?php echo esc_attr($img['cta_link'] ?? '') ?>"
                                            placeholder="<?php esc_attr_e('CTA Link', 'mini-gallery') ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php submit_button(__('Save Changes', 'mini-gallery'), 'primary large', 'mgwpp_edit_gallery_submit') ?>
                    </form>
                </div>

                <!-- Live Preview Panel -->
                <div class="mgwpp-preview-panel">
                    <?php // Generate the preview URL
                    // When generating the preview link
                    $preview_url = add_query_arg(
                        [
                        'mgwpp_preview' => '1',
                        'gallery_id' => $gallery_id,
                        '_wpnonce' => wp_create_nonce('mgwpp_preview') // CHANGED NONCE ACTION
                        ],
                        home_url('/')
                    );
                    ?>

                    <div class="mgwpp-preview-container">
                        <iframe src="<?php echo esc_url($preview_url); ?>" width="100%" height="600"></iframe>
                    </div>
                    <div class="mgwpp-shortcode-box">
                        <input type="text" value='[mgwpp_gallery id="<?php echo intval($gallery_id) ?>"]' readonly>
                        <button type="button" class="button mgwpp-copy-shortcode">
                            <?php esc_html_e('Copy Shortcode', 'mini-gallery') ?>
                        </button>
                    </div>
                </div>





            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {

                // Initialize WP color picker on inputs with .color-picker class
                $('.color-picker').wpColorPicker();

                // Toggle visibility of the gradient picker when overlay select changes
                $('.mgwpp-overlay-select').change(function() {
                    $('.mgwpp-gradient-picker').toggle($(this).val() === 'gradient');
                }).trigger('change');

                // Refresh preview via Ajax
                // Refresh preview via Ajax
                $('.mgwpp-refresh-preview').click(function() {
                    var $preview = $('.mgwpp-preview-container');
                    $preview.html('<div class="mgwpp-loading"><?php echo esc_js(__('Loading preview...', 'mini-gallery')); ?></div>');

                    $.post(ajaxurl, {
                        action: 'mgwpp_refresh_preview',
                        gallery_id: <?php echo absint($gallery_id); ?>,
                        nonce: '<?php echo esc_js(wp_create_nonce('mgwpp_preview_nonce')); ?>'
                    }, function(response) {
                        $preview.html(response);
                    });
                });

                // Copy shortcode
                $('.mgwpp-copy-shortcode').click(function() {
                    var $input = $(this).prev('input');
                    $input.select();
                    document.execCommand('copy');
                    $(this).text('<?php echo esc_js(__('Copied!', 'mini-gallery')); ?>');
                    setTimeout(() => {
                        $(this).text('<?php echo esc_js(__('Copy Shortcode', 'mini-gallery')); ?>');
                    }, 2000);
                });
            });
        </script>

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
