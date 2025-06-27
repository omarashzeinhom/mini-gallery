<?php
if (!defined('ABSPATH')) exit;
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

        // Get plugin version for cache busting
        $plugin_data = get_file_data(__FILE__, ['Version' => 'Version']);
        $plugin_version = $plugin_data['Version'];

        // Enqueue main admin CSS
        wp_enqueue_style(
            'mgwpp-admin-galleries',
            plugins_url('admin/views/galleries/mgwpp-galleries-view.css', dirname(__FILE__, 3)),
            array(),
            $plugin_version
        );

        // Enqueue custom admin JS
        wp_enqueue_script(
            'mgwpp-admin-galleries-js',
            plugins_url('admin/views/galleries/mgwpp-galleries-view.js', dirname(__FILE__, 3)),
            $plugin_version,
            true
        );

        // Add inline styles for dynamic elements
        wp_add_inline_style('mgwpp-admin-galleries', '
            .mgwpp-media-preview {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 10px;
            }
            
            .mgwpp-media-thumbnail {
                width: 80px;
                height: 80px;
                overflow: hidden;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                transition: transform 0.2s ease;
            }
            
            .mgwpp-media-thumbnail:hover {
                transform: scale(1.05);
            }
        ');

        // Localize script for AJAX and translations
        // In your enqueue_gallery_scripts() method, update the localized script:
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

    public function render()
    { ?>
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
                        <hr class="wp-header-end">

                        <?php if (empty($this->items)) : ?>
                            <div class="mgwpp-empty-state">
                                <img src="<?php echo esc_url(MG_PLUGIN_URL . '/includes/admin/images/empty-galleries.webp'); ?>"
                                    alt="<?php esc_attr_e('No galleries', 'mini-gallery'); ?>">
                                <h3><?php esc_html_e('No galleries found', 'mini-gallery'); ?></h3>
                                <p><?php esc_html_e('Create your first gallery to get started', 'mini-gallery'); ?></p>
                                <button class="button button-primary mgwpp-open-create-modal">
                                    <?php esc_html_e('Create Gallery', 'mini-gallery'); ?>
                                </button>
                            </div>
                        <?php else : ?>
                            <div class="mgwpp-gallery-grid">
                                <?php foreach ($this->items as $item) : ?>
                                    <div class="mgwpp-gallery-card">
                                        <div class="mgwpp-card-header">
                                            <div class="mgwpp-gallery-preview">
                                                <?php echo $this->get_gallery_preview($item['ID']); ?>
                                            </div>
                                            <div class="mgwpp-card-actions">
                                                <?php echo $item['actions']; ?>
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
        // Render modal and scripts
        self::render_create_gallery_modal();
        self::enqueue_gallery_scripts();
    }
    private function get_gallery_preview($gallery_id)
    {
        // Validate gallery ID
        if (empty($gallery_id) || !is_numeric($gallery_id)) {
            return $this->get_fallback_preview();
        }

        // Get gallery images with proper sanitization
        $images = get_post_meta($gallery_id, 'gallery_images', true);

        // If we have actual images, use them for preview
        if (!empty($images)) {
            return $this->render_image_thumbnails($images);
        }

        // Try featured image as secondary option
        $featured_image = get_the_post_thumbnail_url($gallery_id, 'medium');
        if ($featured_image) {
            return '<img src="' . esc_url($featured_image) . '" class="mgwpp-card-image" alt="' . esc_attr__('Gallery preview', 'mini-gallery') . '">';
        }

        // Fall back to gallery type specific image
        $gallery_type = get_post_meta($gallery_id, 'gallery_type', true);
        if (!empty($gallery_type) && isset(self::$gallery_types[$gallery_type])) {
            $type_image_url = MG_PLUGIN_URL . '/includes/admin/images/galleries-preview/' . self::$gallery_types[$gallery_type][1];
            return '<img src="' . esc_url($type_image_url) . '" class="mgwpp-card-image" alt="' . esc_attr(self::$gallery_types[$gallery_type][0]) . '">';
        }

        // Ultimate fallback
        return $this->get_fallback_preview();
    }

    /**
     * Render thumbnails from gallery images
     */
    private function render_image_thumbnails($images)
    {
        // Normalize images to array
        if (is_string($images)) {
            $images = array_filter(explode(',', $images));
        }

        if (!is_array($images) || empty($images)) {
            return $this->get_fallback_preview();
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

            // Check if attachment exists and is an image
            if (!wp_attachment_is_image($image_id)) {
                continue;
            }

            // Get thumbnail URL
            $thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail');
            if (!$thumb_url) {
                continue;
            }

            // Get image alt text for accessibility
            $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            if (empty($alt_text)) {
                $alt_text = get_the_title($image_id);
            }
            if (empty($alt_text)) {
                $alt_text = __('Gallery image', 'mini-gallery');
            }

            $output .= sprintf(
                '<img src="%s" class="mgwpp-preview-thumb" alt="%s" loading="lazy">',
                esc_url($thumb_url),
                esc_attr($alt_text)
            );

            $count++;
        }

        // If no valid images were found, return fallback
        if ($count === 0) {
            return $this->get_fallback_preview();
        }

        // Add count indicator if there are more images
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
     * Get fallback preview image
     */
    private function get_fallback_preview()
    {
        $fallback_url = MG_PLUGIN_URL . '/includes/admin/images/default-gallery.webp';
        return '<img src="' . esc_url($fallback_url) . '" class="mgwpp-card-image" alt="' . esc_attr__('Default gallery preview', 'mini-gallery') . '">';
    }

    private static function render_create_gallery_modal()
    {
    ?>
        <div id="mgwpp-create-gallery" style="display:none;">
            <div class="mgwpp-modal-content">
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
                                <button type="button" class="button button-primary mgwpp-media-upload">
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
                                    <?php foreach (self::$gallery_types as $key => $type): ?>
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

if (!class_exists('MGWPP_Galleries_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    // Add this in your plugin's main file or admin handler
    add_action('admin_post_mgwpp_delete_gallery', function () {
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'mgwpp_delete_gallery')) {
            wp_die(__('Security check failed', 'mini-gallery'));
        }

        $gallery_id = intval($_GET['gallery_id']);

        if (wp_delete_post($gallery_id, true)) {
            wp_redirect(admin_url('admin.php?page=mgwpp-galleries&deleted=1'));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=mgwpp-galleries&error=1'));
        exit;
    });
    class MGWPP_Galleries_List_Table extends WP_List_Table
    {
        public function __construct()
        {
            parent::__construct([
                'singular' => 'gallery',
                'plural'   => 'galleries',
                'ajax'     => false
            ]);
        }

        public function get_columns()
        {
            return [
                'title'     => esc_html__('Title', 'mini-gallery'),
                'type'      => esc_html__('Type', 'mini-gallery'),
                'shortcode' => esc_html__('Shortcode', 'mini-gallery'),
                'actions'   => esc_html__('Actions', 'mini-gallery')
            ];
        }

        public function prepare_items()
        {
            $galleries = get_posts([
                'post_type'      => 'mgwpp_soora',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC'
            ]);

            $data = [];
            foreach ($galleries as $gallery) {
                $thumbnail = get_the_post_thumbnail_url($gallery->ID, 'medium') ?:
                    MG_PLUGIN_URL . '/includes/admin/images/default-gallery.webp';

                $data[] = [
                    'ID'        => $gallery->ID,
                    'title'     => $gallery->post_title,
                    'type'      => $this->get_gallery_type($gallery->ID),
                    'shortcode' => '[mgwpp_gallery id="' . $gallery->ID . '"]',
                    'thumbnail' => $thumbnail,
                    'date'      => get_the_date('', $gallery->ID),
                    'actions'   => $this->get_row_actions($gallery->ID)
                ];
            }

            $this->items = $data;
        }

        public function column_default($item, $column_name)
        {
            return $item[$column_name];
        }

        public function column_title($item)
        {
            return sprintf(
                '<strong>%1$s</strong><div class="row-actions"><span class="id">ID: %2$s</span></div>',
                esc_html($item['title']),
                esc_html($item['ID'])
            );
        }

        public function column_shortcode($item)
        {
            return sprintf(
                '<input type="text" readonly value="%1$s" class="shortcode-input" onclick="this.select()">',
                esc_attr($item['shortcode'])
            );
        }

        public function column_actions($item)
        {
            return sprintf(
                '<a href="%1$s" class="button">%3$s</a> ' .
                    '<a href="%2$s" class="button button-link-delete" onclick="return confirm(\'%4$s\')">%5$s</a>',
                esc_url(wp_nonce_url(
                    admin_url('admin.php?page=mgwpp-edit-gallery&gallery_id=' . $item['ID']),
                    'mgwpp_edit_gallery'
                )),
                esc_url(wp_nonce_url(
                    admin_url('admin-post.php?action=mgwpp_delete_gallery&gallery_id=' . $item['ID']),
                    'mgwpp_delete_gallery'
                )),
                esc_html__('Edit', 'mini-gallery'),
                esc_attr__('Are you sure you want to delete this gallery?', 'mini-gallery'),
                esc_html__('Delete', 'mini-gallery')
            );
        }

        private function get_gallery_type($gallery_id)
        {
            $type = get_post_meta($gallery_id, 'gallery_type', true);
            $types = [
                "single_carousel" => "Single Carousel",
                "multi_carousel" => "Multi Carousel",
                "grid" => "Grid Layout",
                "mega_slider" => "Mega Slider",
                "full_page_slider" => "Full Page Slider",
                "pro_carousel" => "Pro Multi Card Carousel",
                "neon_carousel" => "Neon Carousel",
                "threed_carousel" => "3D Carousel",
                "spotlight_carousel" => "Spotlight Carousel",
                "testimonials_carousel" => "Testimonials Carousel"
            ];

            return isset($types[$type]) ? $types[$type] : ucwords(str_replace('_', ' ', $type));
        }

        private function get_row_actions($gallery_id)
        {
            $edit_url = wp_nonce_url(
                admin_url('admin.php?page=mgwpp-edit-gallery&gallery_id=' . $gallery_id),
                'mgwpp_edit_gallery'
            );

            $delete_url = wp_nonce_url(
                admin_url('admin-post.php?action=mgwpp_delete_gallery&gallery_id=' . $gallery_id),
                'mgwpp_delete_gallery'
            );

            return sprintf(
                '<a href="%s" class="button">%s</a>
        <a href="%s" class="button button-link-delete" onclick="return confirm(\'%s\')">%s</a>',
                esc_url($edit_url),
                esc_html__('Edit', 'mini-gallery'),
                esc_url($delete_url),
                esc_attr__('Are you sure you want to delete this gallery?', 'mini-gallery'),
                esc_html__('Delete', 'mini-gallery')
            );
        }
    }
}
