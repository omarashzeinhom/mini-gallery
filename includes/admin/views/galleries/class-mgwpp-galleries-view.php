<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';


class MGWPP_Galleries_View
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

    private $items;

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    private static function enqueue_gallery_scripts()
    {
        wp_enqueue_media();
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');

        $plugin_data = get_file_data(__FILE__, ['Version' => 'Version']);
        $plugin_version = $plugin_data['Version'];

        wp_enqueue_style(
            'mgwpp-admin-galleries',
            plugins_url('admin/views/galleries/mgwpp-galleries-view.css', dirname(__FILE__, 3)),
            array(),
            $plugin_version
        );

        // Fixed script enqueueing with explicit dependencies and footer parameter
        wp_enqueue_script(
            'mgwpp-admin-galleries-js',
            plugins_url('admin/views/galleries/mgwpp-galleries-view.js', dirname(__FILE__, 3)),
            array('jquery', 'thickbox'), // Explicit dependencies
            $plugin_version,
            true // Explicitly load in footer
        );

        wp_localize_script('mgwpp-admin-galleries-js', 'mgwppAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp-admin-nonce'),
            'i18n' => [
                'selectImages' => __('Select Images', 'mini-gallery'),
                'createGallery' => __('Create Gallery', 'mini-gallery'),
                'copied' => __('Copied!', 'mini-gallery'),
                'copyFailed' => __('Failed to copy', 'mini-gallery')
            ]
        ]);
    }


    private static function get_plugin_asset_image($relative_path, $attributes = [])
    {
        $src = esc_url(MG_PLUGIN_URL . '/includes/admin/images/' . $relative_path);

        $default_attributes = [
            'loading' => 'lazy'
        ];

        $attributes = array_merge($default_attributes, $attributes);

        $attr_string = '';
        foreach ($attributes as $key => $value) {
            $attr_string .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
        }

        // Return safely escaped image tag
        return sprintf('<img src="%s"%s>', esc_url($src), $attr_string);
    }

    public function render()
    {
?>
        <div class="mgwpp-dashboard-container">
            <div class="mgwpp-dashboard-wrapper">
                <div class="mgwpp-glass-container">

                    <?php MGWPP_Inner_Header::render(); ?>

                    <div class="wrap">
                        <h1 class="wp-heading-inline">
                            <?php esc_html_e('Galleries', 'mini-gallery'); ?>
                        </h1>
                        <a href="#TB_inline?width=600&height=550&inlineId=mgwpp-create-gallery"
                            class="page-title-action thickbox">
                            <?php esc_html_e('Add New', 'mini-gallery'); ?>
                        </a>

                        <!-- Bulk Actions Container -->
                        <div class="mgwpp-bulk-actions" style="display:none;">
                            <select id="mgwpp-bulk-action">
                                <option value="-1"><?php esc_html_e('Bulk Actions', 'mini-gallery'); ?></option>
                                <option value="delete"><?php esc_html_e('Delete', 'mini-gallery'); ?></option>
                            </select>
                            <button type="button" id="mgwpp-apply-bulk-action" class="button action">
                                <?php esc_html_e('Apply', 'mini-gallery'); ?>
                            </button>
                        </div>

                        <hr class="wp-header-end">

                        <?php if (empty($this->items)) : ?>
                            <div class="mgwpp-empty-state">
                                <?php
                                echo wp_kses_post(self::get_plugin_asset_image(
                                    'empty-galleries.webp',
                                    ['alt' => __('No galleries', 'mini-gallery')]
                                ));
                                ?>
                                <h3><?php esc_html_e('No galleries found', 'mini-gallery'); ?></h3>
                                <p><?php esc_html_e('Create your first gallery to get started', 'mini-gallery'); ?></p>
                            </div>
                        <?php else : ?>
                            <div class="mgwpp-gallery-grid">
                                <?php foreach ($this->items as $item) : ?>
                                    <div class="mgwpp-gallery-card">
                                        <div class="mgwpp-card-header">
                                            <div class="mgwpp-gallery-preview">
                                                <input type="checkbox"
                                                    name="bulk_delete[]"
                                                    class="mgwpp-bulk-checkbox"
                                                    value="<?php echo esc_attr($item['ID']); ?>"
                                                    style="position:absolute; top:10px; left:10px; z-index:10;">
                                                <?php echo wp_kses_post($this->get_gallery_preview(esc_attr($item['ID']))); ?>
                                            </div>
                                            <div class="mgwpp-card-actions">
                                                <?php echo wp_kses($item['actions'], [
                                                    'a' => [
                                                        'href' => [],
                                                        'class' => [],
                                                        'data-id' => [],
                                                        'data-nonce' => [],
                                                        'title' => [],
                                                    ],
                                                    'span' => [
                                                        'class' => [],
                                                    ],
                                                ]);  ?>
                                            </div>
                                        </div>

                                        <div class="mgwpp-card-body">
                                            <h3 class="mgwpp-card-title">
                                                <?php echo esc_html($item['title']); ?>
                                            </h3>
                                            <div class="mgwpp-card-meta">
                                                <span class="mgwpp-card-type">
                                                    <?php echo esc_html($item['type']); ?>
                                                </span>
                                                <span class="mgwpp-card-date">
                                                    <?php echo esc_html($item['date']); ?>
                                                </span>
                                            </div>
                                            <div class="mgwpp-card-shortcode">
                                                <input type="text"
                                                    value="<?php echo esc_attr($item['shortcode']); ?>"
                                                    readonly
                                                    class="mgwpp-shortcode-input">
                                                <button class="button mgwpp-copy-shortcode">
                                                    <?php esc_html_e('Copy', 'mini-gallery'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

    <?php
        self::render_create_gallery_modal();
        self::enqueue_gallery_scripts();
    }
    private function get_gallery_preview($gallery_id)
    {
        $image_ids = get_post_meta($gallery_id, 'gallery_images', true);

        if (empty($image_ids)) {
            return $this->get_fallback_preview();
        }

        $image_ids = is_array($image_ids) ? $image_ids : explode(',', $image_ids);

        return $this->render_image_thumbnails($image_ids);
    }


    private function render_image_thumbnails($images)
    {
        if (is_string($images)) {
            $images = array_filter(explode(',', $images));
        }

        if (!is_array($images) || empty($images)) {
            return $this->get_fallback_preview();
        }

        if (count($images) === 1) {
            return $this->render_single_preview($images[0]);
        }

        $output = '<div class="mgwpp-preview-thumbnails">';
        $count = 0;
        $max_thumbnails = 4;

        foreach ($images as $image_id) {
            if ($count >= $max_thumbnails) {
                break;
            }

            // Sanitize and validate image ID
            $image_id = intval(trim($image_id));
            if ($image_id <= 0) {
                continue;
            }

            if (!wp_attachment_is_image($image_id)) {
                continue;
            }

            $image_html = wp_get_attachment_image(
                $image_id,
                'medium',
                false,
                [
                    'class' => 'mgwpp-preview-thumb',
                    'loading' => 'lazy'
                ]
            );

            if ($image_html) {
                $output .= $image_html;
                $count++;
            }
        }

        if ($count === 0) {
            return $this->get_fallback_preview();
        }

        $total_images = count($images);
        if ($total_images > $max_thumbnails) {
            $remaining = $total_images - $max_thumbnails;
            $output .= sprintf(
                '<div class="mgwpp-preview-more">+%d</div>',
                $remaining
            );
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Special preview for single-image galleries using WordPress functions
     */
    private function render_single_preview($image_id)
    {
        $image_id = intval(trim($image_id));
        if ($image_id <= 0 || !wp_attachment_is_image($image_id)) {
            return $this->get_fallback_preview();
        }

        $image_html = wp_get_attachment_image(
            $image_id,
            'medium',
            false,
            [
                'class' => 'mgwpp-preview-thumb',
                'loading' => 'lazy'
            ]
        );

        if (!$image_html) {
            return $this->get_fallback_preview();
        }

        return sprintf(
            '<div class="mgwpp-single-preview">%s</div>',
            $image_html
        );
    }


    private function get_fallback_preview()
    {
        return sprintf(
            '<div class="mgwpp-preview-fallback">%s</div>',
            self::get_plugin_asset_image(
                'default-gallery.webp',
                ['alt' => __('Default gallery preview', 'mini-gallery')]
            )
        );
    }


    private static function render_create_gallery_modal()
    {
    ?>
        <div id="mgwpp-create-gallery" style="display:none;">
            <div class="mgwpp-modal-content">
                <!-- Loading overlay -->
                <div id="mgwpp-create-loading" class="mgwpp-loading-overlay" style="display:none;">
                    <div class="mgwpp-loading-spinner"></div>
                    <p><?php esc_html_e('Creating gallery...', 'mini-gallery'); ?></p>
                </div>
                <h2><?php esc_html_e('Create New Gallery', 'mini-gallery'); ?></h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="mgwpp_create_gallery">
                    <?php wp_nonce_field('mgwpp_create_gallery', 'mgwpp_gallery_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label
                                    for="gallery_title"><?php esc_html_e('Gallery Title:', 'mini-gallery'); ?></label></th>
                            <td><input type="text" id="gallery_title" name="gallery_title" required class="regular-text"></td>
                        </tr>

                        <tr>
                            <th scope="row"><label><?php esc_html_e('Gallery Images:', 'mini-gallery'); ?></label></th>
                            <td>
                                <input type="hidden" name="selected_media" id="selected_media" value="">
                                <button type="button" class="mgwpp-admin-button mgwpp-media-upload">
                                    <?php esc_html_e('Select Images', 'mini-gallery'); ?>
                                </button>
                                <div class="mgwpp-media-preview"></div>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label
                                    for="gallery_type"><?php esc_html_e('Gallery Style:', 'mini-gallery'); ?></label></th>
                            <td>
                                <select id="gallery_type" name="gallery_type" required class="regular-text">
                                    <?php foreach (self::$gallery_types as $key => $type) : ?>
                                        <option value="<?php echo esc_attr($key); ?>">
                                            <?php echo esc_html($type[0]); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" class="button button-primary"
                            value="<?php esc_attr_e('Create Gallery', 'mini-gallery'); ?>">
                    </p>
                </form>
            </div>
        </div>

<?php
    }
}
