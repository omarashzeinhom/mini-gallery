<?php
if (!defined('ABSPATH')) exit;

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


    public  function render()
    {
        if (empty($this->items)) {
            echo '<div class="mgwpp-empty-state">';
            echo '<img src="' . MG_PLUGIN_URL . '/admin/images/empty-galleries.svg" alt="No galleries">';
            echo '<h3>' . __('No galleries found', 'mini-gallery') . '</h3>';
            echo '<p>' . __('Create your first gallery to get started', 'mini-gallery') . '</p>';
            echo '<button class="button button-primary mgwpp-open-create-modal">'
                . __('Create Gallery', 'mini-gallery')
                . '</button>';
            echo '</div>';
            return;
        } else {
            echo '<div class="mgwpp-gallery-grid">';
            foreach ($this->items as $item) {
                echo '
                <div class="mgwpp-gallery-card">
                    <div class="mgwpp-card-header">
                        <img src="' . esc_url($item['thumbnail']) . '" 
                             alt="' . esc_attr($item['title']) . '"
                             class="mgwpp-card-image">
                        <div class="mgwpp-card-actions">
                            ' . $item['actions'] . '
                        </div>
                    </div>
                    
                    <div class="mgwpp-card-body">
                        <h3 class="mgwpp-card-title">' . esc_html($item['title']) . '</h3>
                        <div class="mgwpp-card-meta">
                            <span class="mgwpp-card-type">' . esc_html($item['type']) . '</span>
                            <span class="mgwpp-card-date">' . esc_html($item['date']) . '</span>
                        </div>
                        <div class="mgwpp-card-shortcode">
                            <input type="text" value="' . esc_attr($item['shortcode']) . '" 
                                   readonly 
                                   class="mgwpp-shortcode-input"
                                   onclick="this.select()">
                        </div>
                    </div>
                </div>';
            }
        }

        echo '</div>';

        self::render_create_gallery_modal();
        self::enqueue_gallery_scripts();
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
                            <th scope="row"><label for="gallery_title"><?php esc_html_e('Gallery Title:', 'mini-gallery'); ?></label></th>
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
                            <th scope="row"><label for="gallery_type"><?php esc_html_e('Gallery Style:', 'mini-gallery'); ?></label></th>
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
                        <input type="submit" class="button button-primary" value="<?php esc_attr_e('Create Gallery', 'mini-gallery'); ?>">
                    </p>
                </form>
            </div>
        </div>
<?php
    }

    private static function enqueue_gallery_scripts()
    {
        wp_enqueue_media();
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');

        // Get the correct path to admin directory
        $admin_dir = dirname(__FILE__, 2); // Goes up 2 levels from views directory

        // CSS file path
        $css_path = $admin_dir . '/css/mg-admin-dashboard-styles.css';
        $css_url = plugins_url('admin/css/mg-admin-dashboard-styles.css', dirname(__FILE__, 3));

        // Enqueue with filemtime check
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'mgwpp-admin',
                $css_url,
                array(),
                filemtime($css_path)
            );
        } else {
            // Fallback with version number
            wp_enqueue_style(
                'mgwpp-admin',
                $css_url,
                array(),
                '1.0.0'
            );
        }

        wp_add_inline_style('thickbox', '
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
                border: 1px solid #ddd;
                border-radius: 3px;
            }
            .mgwpp-media-thumbnail img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            #mgwpp-create-gallery {
                padding: 20px;
            }
        ');
        // Add JavaScript
        wp_add_inline_script('thickbox', '
 jQuery(document).ready(function($) {
     // Show modal when button is clicked
     $(".mgwpp-open-create-modal").click(function(e) {
         e.preventDefault();
         tb_show("Create Gallery", "#TB_inline?width=600&height=550&inlineId=mgwpp-create-gallery");
     });
 });
');
    }
}

if (!class_exists('MGWPP_Galleries_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

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
                    MG_PLUGIN_URL . '/admin/images/default-gallery.webp';

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
            $preview_url = wp_nonce_url(
                admin_url('admin.php?page=mgwpp-galleries&action=preview&gallery_id=' . $gallery_id),
                'mgwpp_preview_gallery'
            );

            return sprintf(
                '<a href="%s" class="button thickbox">%s</a>',
                esc_url($preview_url . '&TB_iframe=true&width=800&height=600'),
                esc_html__('Preview', 'mini-gallery')
            );
        }
    }
}
