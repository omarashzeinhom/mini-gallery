<?php
if (!defined('ABSPATH')) exit;
require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';

class MGWPP_Edit_Gallery_View
{

    public static function init()
    {
        add_action('admin_menu', [self::class, 'register_edit_page']);
        add_action('admin_post_mgwpp_save_gallery', [self::class, 'handle_save_gallery']);
    }

    public static function register_edit_page()
    {
        add_submenu_page(
            '', // Hide from menu - we access it through our custom link
            'Edit Gallery',
            'Edit Gallery',
            'edit_mgwpp_sooras',
            'mgwpp-edit-gallery',
            [self::class, 'render_edit_page']
        );
    }

    // ======================
    // PREVIEW HANDLING (CORRECTED)
    // ======================

    public function mgwpp_handle_preview_request()
    {
        if (!isset($_GET['mgwpp_preview']) || $_GET['mgwpp_preview'] !== '1' || !isset($_GET['gallery_id'])) {
            return;
        }

        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'mgwpp_preview')) {
            wp_die(
                '<h1>' . esc_html__('Preview Authorization Failed', 'mini-gallery') . '</h1>' .
                    '<p>' . esc_html__('Your preview session has expired or is invalid.', 'mini-gallery') . '</p>' .
                    '<p>' . esc_html__('Please return to the admin and try again.', 'mini-gallery') . '</p>' .
                    '<p><a href="' . esc_url(admin_url('edit.php?post_type=mgwpp_soora')) . '">' .
                    esc_html__('Return to Galleries', 'mini-gallery') . '</a></p>',
                403
            );
        }

        $gallery_id = absint($_GET['gallery_id']);
        if (!$gallery_id || 'mgwpp_soora' !== get_post_type($gallery_id)) {
            wp_die(
                '<h1>' . esc_html__('Invalid Gallery', 'mini-gallery') . '</h1>' .
                    '<p>' . esc_html__('The requested gallery does not exist or is no longer available.', 'mini-gallery') . '</p>',
                404
            );
        }

        // Load assets correctly
        add_action('wp_enqueue_scripts', function () {
            // Remove conflicting theme styles
            global $wp_styles, $wp_scripts;
            $wp_styles->queue = [];
            $wp_scripts->queue = [];

            // Enqueue core WordPress scripts
            wp_enqueue_script('jquery');

            // Enqueue plugin assets
            if (class_exists('MGWPP_Assets')) {
                //MGWPP_Assets::enqueue_frontend_assets();
            }
        }, 9999);

        // Start output buffer
        ob_start();
?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>

        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php esc_html_e('Gallery Preview', 'mini-gallery'); ?></title>
            <?php wp_head(); ?>
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    background: #f0f0f1;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .mgwpp-preview-container {
                    max-width: 90%;
                    width: 100%;
                    margin: 0 auto;
                    padding: 20px;
                    background: white;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    border-radius: 4px;
                }
            </style>
        </head>

        <body>
            <div class="mgwpp-preview-container">
                <?php
                // Render the gallery
                echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]');
                ?>
            </div>
            <?php wp_footer(); ?>
        </body>

        </html>
    <?php

        // Output and exit
        echo ob_get_clean();
        exit;
    }
    public static function render_edit_page()
    {
        // Check permissions
        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Get gallery ID and verify nonce
        $gallery_id = isset($_GET['gallery_id']) ? intval($_GET['gallery_id']) : 0;
        if (
            !$gallery_id || !isset($_GET['_wpnonce']) ||
            !wp_verify_nonce($_GET['_wpnonce'], 'mgwpp_edit_gallery')
        ) {
            wp_die(__('Invalid gallery or security check failed.'));
        }

        // Get gallery data
        $gallery = get_post($gallery_id);
        if (!$gallery || $gallery->post_type !== 'mgwpp_soora') {
            wp_die(__('Gallery not found.'));
        }

        // Get meta values
        $image_links = get_post_meta($gallery_id, '_mgwpp_image_links', true) ?: [];
        $cta_links = get_post_meta($gallery_id, '_mgwpp_cta_links', true) ?: [];
        $custom_text = get_post_meta($gallery_id, '_mgwpp_custom_text', true);
        $gallery_type = get_post_meta($gallery_id, 'gallery_type', true);
        $gallery_images = get_post_meta($gallery_id, 'gallery_images', true);

        // Enqueue scripts and styles
        self::enqueue_assets();

        // Render the edit page
    ?>
        <div class="wrap mgwpp-edit-gallery">
            <h1><?php echo esc_html__('Edit Gallery', 'mini-gallery') . ': ' . esc_html($gallery->post_title); ?></h1>

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="mgwpp_save_gallery">
                <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gallery_id); ?>">
                <?php wp_nonce_field('mgwpp_save_gallery_data', 'mgwpp_gallery_nonce'); ?>

                <div class="mgwpp-edit-section">
                    <h2><?php esc_html_e('Image Links', 'mini-gallery'); ?></h2>
                    <div class="mgwpp-image-links-container">
                        <?php if (!empty($gallery_images)) :
                            $images = is_array($gallery_images) ? $gallery_images : explode(',', $gallery_images);
                            foreach ($images as $index => $image_id) :
                                $image_url = wp_get_attachment_url($image_id);
                                if ($image_url) : ?>
                                    <div class="mgwpp-image-link-item">
                                        <img src="<?php echo esc_url(wp_get_attachment_image_url($image_id, 'thumbnail')); ?>" width="150">
                                        <input type="url" name="image_links[<?php echo esc_attr($index); ?>]"
                                            value="<?php echo esc_attr($image_links[$index] ?? ''); ?>"
                                            placeholder="<?php esc_attr_e('Image link', 'mini-gallery'); ?>">
                                    </div>
                        <?php endif;
                            endforeach;
                        endif; ?>
                    </div>
                </div>

                <div class="mgwpp-edit-section">
                    <h2><?php esc_html_e('CTA Links', 'mini-gallery'); ?></h2>
                    <div class="mgwpp-cta-links">
                        <div class="mgwpp-cta-link">
                            <label><?php esc_html_e('Primary CTA:', 'mini-gallery'); ?>
                                <input type="url" name="cta_links[primary]"
                                    value="<?php echo esc_attr($cta_links['primary'] ?? ''); ?>">
                            </label>
                        </div>
                        <div class="mgwpp-cta-link">
                            <label><?php esc_html_e('Secondary CTA:', 'mini-gallery'); ?>
                                <input type="url" name="cta_links[secondary]"
                                    value="<?php echo esc_attr($cta_links['secondary'] ?? ''); ?>">
                            </label>
                        </div>
                    </div>
                </div>

                <?php if (in_array($gallery_type, ['testimonials_carousel', 'full_page_slider', 'pro_carousel'])) : ?>
                    <div class="mgwpp-edit-section">
                        <h2><?php esc_html_e('Custom Text', 'mini-gallery'); ?></h2>
                        <?php wp_editor(
                            $custom_text,
                            'mgwpp_custom_text',
                            [
                                'textarea_name' => 'custom_text',
                                'media_buttons' => false,
                                'teeny' => true
                            ]
                        ); ?>
                    </div>
                <?php endif; ?>

                <?php submit_button(__('Save Changes', 'mini-gallery')); ?>
            </form>
        </div>
<?php
    }

    public static function handle_save_gallery()
    {
        // Verify nonce and permissions
        if (
            !isset($_POST['mgwpp_gallery_nonce']) ||
            !wp_verify_nonce($_POST['mgwpp_gallery_nonce'], 'mgwpp_save_gallery_data')
        ) {
            wp_die(__('Security check failed.'));
        }

        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_die(__('You do not have sufficient permissions.'));
        }

        $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : 0;
        if (!$gallery_id) {
            wp_die(__('Invalid gallery ID.'));
        }

        // Save image links
        if (isset($_POST['image_links'])) {
            update_post_meta(
                $gallery_id,
                '_mgwpp_image_links',
                array_map('esc_url_raw', $_POST['image_links'])
            );
        }

        // Save CTA links
        if (isset($_POST['cta_links'])) {
            update_post_meta(
                $gallery_id,
                '_mgwpp_cta_links',
                array_map('esc_url_raw', $_POST['cta_links'])
            );
        }

        // Save custom text if available
        if (isset($_POST['custom_text'])) {
            update_post_meta(
                $gallery_id,
                '_mgwpp_custom_text',
                wp_kses_post($_POST['custom_text'])
            );
        }

        // Redirect back with success message
        wp_redirect(admin_url('admin.php?page=mgwpp-galleries&updated=1'));
        exit;
    }

    private static function enqueue_assets()
    {
        wp_enqueue_style(
            'mgwpp-edit-gallery',
            plugins_url('admin/views/edit-gallery/mgwpp-edit-gallery.css', dirname(__FILE__, 3)),
            [],
            get_plugin_version()
        );

        wp_enqueue_script(
            'mgwpp-edit-gallery',
            plugins_url('admin/views/edit-gallery/mgwpp-edit-gallery.js', dirname(__FILE__, 3)),
            ['jquery'],
            get_plugin_version(),
            true
        );
    }
}

// Initialize the edit gallery functionality
MGWPP_Edit_Gallery_View::init();
add_action('template_redirect', 'mgwpp_handle_preview_request');
