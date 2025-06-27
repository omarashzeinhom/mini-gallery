<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Edit_Gallery_View
{
    private static $gallery_types = [
        "single_carousel" => ["Single Carousel", "single-carousel.webp"],
        "multi_carousel" => ["Multi Carousel", "multi-carousel.webp"],
        "grid" => ["Grid Layout", "grid.webp"],
        "mega_slider" => ["Mega Slider", "mega-slider.webp"],
        "full_page_slider" => ["Full Page Slider", "full-page-slider.webp"],
        "pro_carousel" => ["Pro Multi Card Carousel", "pro-carousel.webp"],
        "neon_carousel" => ["Neon Carousel", "neon-carousel.webp"],
        "threed_carousel" => ["3D Carousel", "3d-carousel.webp"],
        "spotlight_carousel" => ["Spotlight Carousel", "spotlight-carousel.webp"],
        "testimonials_carousel" => ["Testimonials Carousel", "testimonials.webp"]
    ];

    public static function init()
    {

        add_action('admin_menu', [self::class, 'register_edit_page']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
        add_action('admin_post_mgwpp_save_gallery', [self::class, 'handle_save_gallery']);
        add_action('wp_ajax_mgwpp_save_gallery_order', [self::class, 'handle_save_gallery_order']);
    }

    public static function register_edit_page()
    {
        add_submenu_page(
            '',
            'Edit Gallery',
            'Edit Gallery',
            'edit_mgwpp_sooras',
            'mgwpp-edit-gallery',
            [self::class, 'render_edit_page']
        );
    }

    // CORRECTED: Use gallery edit specific assets
    public static function enqueue_assets($hook)
    {
        if ($hook !== 'gallery_page_mgwpp-edit-gallery') return;

        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');

        // Load GALLERY EDIT specific CSS
        wp_enqueue_style(
            'mgwpp-edit-gallery-styles',
            MG_PLUGIN_URL . "/includes/admin/views/edit-gallery/mgwpp-edit-gallery.css",
            [],
            filemtime(MG_PLUGIN_PATH . "/includes/admin/views/edit-gallery/mgwpp-edit-gallery.css")
        );

        // Load GALLERY EDIT specific JS
        wp_enqueue_script(
            'mgwpp-edit-gallery-scripts',
            MG_PLUGIN_URL . "/includes/admin/views/edit-gallery/mgwpp-edit-gallery.js",
            ['jquery'],
            filemtime(MG_PLUGIN_PATH . "/includes/admin/views/edit-gallery/mgwpp-edit-gallery.js"),
            true
        );

        // Localize to GALLERY EDIT script
        wp_localize_script('mgwpp-edit-gallery-scripts', 'mgwppEdit', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp_edit_gallery'),
            'i18n' => [
                'reorderImages' => __('Drag to reorder images', 'mini-gallery'),
                'saveOrder' => __('Save Order', 'mini-gallery'),
                'saving' => __('Saving...', 'mini-gallery'),
                'saved' => __('Order saved!', 'mini-gallery'),
                'saveFailed' => __('Failed to save order', 'mini-gallery')
            ]
        ]);
    }

    public static function render_edit_page()
    {
        // Check permissions
        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'mini-gallery'));
        }

        // Get gallery ID and verify nonce
        $gallery_id = isset($_GET['gallery_id']) ? intval($_GET['gallery_id']) : 0;
        if (!$gallery_id || !isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'mgwpp_edit_gallery')) {
            wp_die(esc_html__('Invalid gallery or security check failed.', 'mini-gallery'));
        }

        // Get gallery data
        $gallery = get_post($gallery_id);
        if (!$gallery || $gallery->post_type !== 'mgwpp_soora') {
            wp_die(esc_html__('Gallery not found.', 'mini-gallery'));
        }

        // Get gallery data
        $gallery_type = get_post_meta($gallery_id, 'gallery_type', true);
        $gallery_images = get_post_meta($gallery_id, 'gallery_images', true);
        $images = !empty($gallery_images) ? (is_array($gallery_images) ? $gallery_images : explode(',', $gallery_images)) : [];

        self::render_editor($gallery, $gallery_type, $images);
    }

    private static function render_editor($gallery, $current_type, $images)
    {
        // Get preview URL
        $preview_url = wp_nonce_url(
            add_query_arg([
                'action' => 'mgwpp_preview',
                'gallery_id' => $gallery->ID
            ], admin_url('admin-ajax.php')),
            'mgwpp_preview_nonce'
        );
?>
        <div class="mgwpp-dashboard-container">
            <h1><?php
                echo esc_html(
                    sprintf(
                        __('Edit Gallery: %s', 'mini-gallery'),
                        $gallery->post_title
                    )
                );
                ?></h1>

            <div class="mgwpp-glass-container">
                <div class="mgwpp-editor-column">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="mgwpp-gallery-form">
                        <input type="hidden" name="action" value="mgwpp_save_gallery">
                        <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gallery->ID); ?>">
                        <?php wp_nonce_field('mgwpp_save_gallery_data', 'mgwpp_gallery_nonce'); ?>

                        <div class="mgwpp-edit-section">
                            <h2><?php esc_html_e('Gallery Title', 'mini-gallery'); ?></h2>
                            <input type="text" name="post_title" value="<?php echo esc_attr($gallery->post_title); ?>" class="widefat">
                        </div>

                        <div class="mgwpp-preview-column">
                            <div class="mgwpp-preview-container">
                                <h2><?php esc_html_e('Gallery Preview', 'mini-gallery'); ?></h2>
                                <div class="mgwpp-preview-frame-container">
                                    <iframe id="mgwpp-preview-frame" src="<?php echo esc_url($preview_url); ?>"></iframe>
                                </div>
                                <p class="description"><?php esc_html_e('Preview updates automatically when you save changes.', 'mini-gallery'); ?></p>
                            </div>
                        </div>

                        <div class="mgwpp-edit-section">
                            <h2><?php esc_html_e('Gallery Type', 'mini-gallery'); ?></h2>
                            <div class="mgwpp-gallery-types">
                                <?php foreach (self::$gallery_types as $type => $details) :
                                    $type_image_url = MG_PLUGIN_URL . '/includes/admin/images/galleries-preview/' . $details[1];
                                ?>
                                    <div class="mgwpp-gallery-type <?php echo $type === $current_type ? 'active' : ''; ?>">
                                        <label>
                                            <input type="radio" name="gallery_type" value="<?php echo esc_attr($type); ?>"
                                                <?php checked($type, $current_type); ?>>
                                            <div class="mgwpp-stats-grid">
                                                <img class="mgwpp-stat-card" src="<?php echo esc_url($type_image_url); ?>" width="75" height="75"
                                                    alt="<?php echo esc_attr($details[0]); ?>">
                                                <span><?php echo esc_html($details[0]); ?></span>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mgwpp-edit-section">
                            <h2>
                                <?php esc_html_e('Gallery Images', 'mini-gallery'); ?>
                                <span class="mgwpp-reorder-hint"><?php esc_html_e('(Drag to reorder)', 'mini-gallery'); ?></span>
                            </h2>
                            <div class="mgwpp-image-manager">
                                <div class="mgwpp-image-container sortable">
                                    <?php if (!empty($images)) :
                                        foreach ($images as $image_id) :
                                            if ($image_url = wp_get_attachment_url($image_id)) :
                                                $thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                    ?>
                                                <div class="mgwpp-image-item" data-id="<?php echo esc_attr($image_id); ?>">
                                                    <img src="<?php echo esc_url($thumb_url); ?>">
                                                    <input type="hidden" name="gallery_images[]" value="<?php echo esc_attr($image_id); ?>">
                                                    <button type="button" class="mgwpp-remove-image" title="<?php esc_attr_e('Remove image', 'mini-gallery'); ?>">×</button>
                                                </div>
                                        <?php endif;
                                        endforeach;
                                    else : ?>
                                        <p class="mgwpp-no-images"><?php esc_html_e('No images added to this gallery yet.', 'mini-gallery'); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="mgwpp-image-actions">
                                    <button type="button" class="button button-primary mgwpp-add-images">
                                        <?php esc_html_e('Add Images', 'mini-gallery'); ?>
                                    </button>
                                    <button type="button" class="button mgwpp-save-order" id="mgwpp-save-order-btn">
                                        <?php esc_html_e('Save Order', 'mini-gallery'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mgwpp-edit-actions">
                            <button type="button" class="button mgwpp-preview-gallery" data-preview-url="<?php echo esc_url($preview_url); ?>">
                                <?php esc_html_e('Preview Gallery', 'mini-gallery'); ?>
                            </button>
                            <?php submit_button(__('Save Changes', 'mini-gallery'), 'primary', 'submit', false); ?>
                        </div>
                    </form>
                </div>


            </div>
        </div>
<?php
    }
    public static function handle_save_gallery()
    {
        // Verify nonce and permissions
        if (!isset($_POST['mgwpp_gallery_nonce']) || !wp_verify_nonce($_POST['mgwpp_gallery_nonce'], 'mgwpp_save_gallery_data')) {
            wp_die(__('Security check failed.', 'mini-gallery'));
        }

        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_die(__('You do not have sufficient permissions.', 'mini-gallery'));
        }

        $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : 0;
        if (!$gallery_id) {
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
        if (isset($_POST['gallery_type']) && array_key_exists($_POST['gallery_type'], self::$gallery_types)) {
            update_post_meta($gallery_id, 'gallery_type', sanitize_text_field($_POST['gallery_type']));
        }

        // Update gallery images
        if (isset($_POST['gallery_images'])) {
            $images = array_map('intval', $_POST['gallery_images']);
            update_post_meta($gallery_id, 'gallery_images', $images);
        }

        // Redirect back
        wp_redirect(admin_url('admin.php?page=mgwpp-edit-gallery&gallery_id=' . $gallery_id . '&updated=1'));
        exit;
    }


    public static function handle_save_gallery_order()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mgwpp_edit_gallery')) {
            wp_send_json_error(['message' => __('Security check failed', 'mini-gallery')]);
        }

        // Check permissions
        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'mini-gallery')]);
        }

        // Get gallery ID and images
        $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : 0;
        $image_ids = isset($_POST['image_ids']) ? array_map('intval', $_POST['image_ids']) : [];

        if (!$gallery_id) {
            wp_send_json_error(['message' => __('Invalid gallery ID', 'mini-gallery')]);
        }

        // Update gallery images with new order
        update_post_meta($gallery_id, 'gallery_images', $image_ids);

        wp_send_json_success(['message' => __('Image order saved', 'mini-gallery')]);
    }
}

MGWPP_Edit_Gallery_View::init();
