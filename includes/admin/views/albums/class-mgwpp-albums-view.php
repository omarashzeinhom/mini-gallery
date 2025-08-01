<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';
require_once MG_PLUGIN_PATH . 'includes/admin/tables/class-mgwpp-albums-table.php';

class MGWPP_Albums_View
{
    public static function render()
    {
        MGWPP_Inner_Header::enqueue_assets();
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_media();

        wp_enqueue_style(
            'mgwpp-album-admin-styles',
            plugin_dir_url(__FILE__) . 'albums/mgwpp-albums-view.css',
            array(),
            MGWPP_ASSET_VERSION
        );

        wp_enqueue_script(
            'mgwpp-album-admin-scripts',
            plugin_dir_url(__FILE__) . 'albums/mgwpp-albums-view.js',
            array('jquery', 'jquery-ui-tabs', 'jquery-ui-sortable', 'clipboard'),
            MGWPP_ASSET_VERSION,
            true
        );

        // Add inline scripts
        wp_add_inline_script('mgwpp-album-admin-scripts', '
            jQuery(document).ready(function($) {
                // Initialize tabs
                $("#mgwpp-tabs").tabs();
                
                // Search functionality
                $("#mgwpp-album-search").on("keyup", function() {
                    var value = $(this).val().toLowerCase();
                    $(".mgwpp-albums-table tbody tr").filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                });
                
                // Form submission handler
                $("#mgwpp-album-creation-form").on("submit", function(e) {
                    var $submitBtn = $(this).find(".mgwpp-submit-btn");
                    if ($submitBtn.prop("disabled")) {
                        e.preventDefault();
                        return false;
                    }
                    $submitBtn.prop("disabled", true);
                    $submitBtn.html(\'<span class="dashicons dashicons-update spin"></span> ' . esc_js(__('Creating...', 'mini-gallery')) . '\');
                });
            });
        ', 'after');

        // Localize script with proper translations
        wp_localize_script('mgwpp-album-admin-scripts', 'mgwpp_admin_vars', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp_nonce'),
            'confirm_delete' => __('Are you sure you want to delete the selected albums?', 'mini-gallery'),
            'confirm_delete_single' => __('Are you sure you want to delete this album?', 'mini-gallery'),
            'album_deleted' => __('Album deleted successfully!', 'mini-gallery'),
            'delete_error' => __('Failed to delete album. Please try again.', 'mini-gallery')
        ]);
?>

        <div class="mgwpp-dashboard-container">
            <?php MGWPP_Inner_Header::render(); ?>
            <div class="wrap">
                <div class="mgwpp-tabs-container">
                    <div id="mgwpp-tabs">
                        <ul class="mgwpp-tabs-nav">
                            <li><a href="#tab-albums"><?php esc_html_e('Albums', 'mini-gallery'); ?></a></li>
                            <li><a href="#tab-create"><?php esc_html_e('Create New', 'mini-gallery'); ?></a></li>
                        </ul>

                        <div id="tab-albums" class="mgwpp-tab-content">
                            <div class="mgwpp-search-filter">
                                <input type="text" id="mgwpp-album-search"
                                    placeholder="<?php esc_attr_e('Search albums...', 'mini-gallery'); ?>">
                                <select id="mgwpp-album-filter">
                                    <option value=""><?php esc_html_e('All Albums', 'mini-gallery'); ?></option>
                                    <option value="recent"><?php esc_html_e('Recently Added', 'mini-gallery'); ?></option>
                                    <option value="popular"><?php esc_html_e('Most Galleries', 'mini-gallery'); ?></option>
                                </select>
                            </div>
                            <?php self::render_albums_table(); ?>
                        </div>

                        <div id="tab-create" class="mgwpp-tab-content">
                            <?php self::render_creation_form(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    private static function render_creation_form()
    {
    ?>
        <div class="mgwpp-album-form-container">
            <div class="mgwpp-album-form-card">
                <div class="mgwpp-form-header">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <h2><?php esc_html_e('Create New Album', 'mini-gallery') ?></h2>
                </div>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                    class="mgwpp-album-form" id="mgwpp-album-creation-form">
                    <input type="hidden" name="action" value="mgwpp_create_album">
                    <?php wp_nonce_field('mgwpp_album_submit_nonce', 'mgwpp_album_submit_nonce'); ?>

                    <div class="mgwpp-form-row">
                        <label for="album_title">
                            <span class="dashicons dashicons-edit"></span>
                            <?php esc_html_e('Album Title', 'mini-gallery'); ?>
                        </label>
                        <input type="text" name="album_title" id="album_title" required
                            placeholder="<?php esc_attr_e('Enter album title...', 'mini-gallery'); ?>">
                    </div>

                    <div class="mgwpp-form-row">
                        <label for="album_description">
                            <span class="dashicons dashicons-text"></span>
                            <?php esc_html_e('Description (Optional)', 'mini-gallery'); ?>
                        </label>
                        <textarea name="album_description" id="album_description" rows="3"
                            placeholder="<?php esc_attr_e('Enter album description...', 'mini-gallery'); ?>"></textarea>
                    </div>

                    <div class="mgwpp-form-row">
                        <label for="album_cover">
                            <span class="dashicons dashicons-format-image"></span>
                            <?php esc_html_e('Album Cover (Optional)', 'mini-gallery'); ?>
                        </label>
                        <div class="mgwpp-media-uploader">
                            <div id="album-cover-preview" class="mgwpp-cover-preview">
                                <?php echo wp_kses_post(self::get_plugin_placeholder_image()); ?>
                            </div>
                            <input type="hidden" name="album_cover_id" id="album_cover_id" value="">
                            <button type="button" class="button mgwpp-upload-cover-btn">
                                <?php esc_html_e('Select Image', 'mini-gallery'); ?>
                            </button>
                            <button type="button" class="button mgwpp-remove-cover-btn" style="display:none;">
                                <?php esc_html_e('Remove', 'mini-gallery'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="mgwpp-form-row">
                        <div class="mgwpp-gallery-selector-container">
                            <div class="mgwpp-gallery-filter">
                                <input type="text" id="gallery-search"
                                    placeholder="<?php esc_attr_e('Search galleries...', 'mini-gallery'); ?>">
                            </div>
                            <?php self::render_gallery_selector(); ?>
                        </div>
                    </div>

                    <div class="mgwpp-form-actions">
                        <button type="submit" class="button button-primary mgwpp-submit-btn">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php esc_html_e('Create Album', 'mini-gallery'); ?>
                        </button>
                        <button type="reset" class="button mgwpp-reset-btn">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php esc_html_e('Reset Form', 'mini-gallery'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <div class="mgwpp-album-preview-card">
                <div class="mgwpp-preview-header">
                    <h3><?php esc_html_e('Album Preview', 'mini-gallery'); ?></h3>
                </div>
                <div class="mgwpp-album-preview">
                    <div class="mgwpp-preview-cover">
                        <img id="preview-cover-image" src="<?php echo esc_url(plugin_dir_url(MGWPP_PLUGIN_FILE) . 'images/placeholder.jpg'); ?>" alt="<?php esc_attr_e('Album Preview', 'mini-gallery'); ?>">
                    </div>
                    <div class="mgwpp-preview-details">
                        <h4 id="preview-title"><?php esc_html_e('Album Title', 'mini-gallery'); ?></h4>
                        <p id="preview-description"><?php esc_html_e('Album description will appear here...', 'mini-gallery'); ?></p>
                        <div class="mgwpp-preview-galleries">
                            <p><?php esc_html_e('Selected Galleries:', 'mini-gallery'); ?></p>
                            <ul id="preview-galleries-list">
                                <li class="mgwpp-empty-selection"><?php esc_html_e('No galleries selected', 'mini-gallery'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    private static function get_plugin_placeholder_image()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="#f0f0f1"/><text x="50" y="50" font-size="10" fill="#8d96a0" text-anchor="middle" dominant-baseline="middle">' . esc_html__('Album Preview', 'mini-gallery') . '</text></svg>';
        $encoded = base64_encode($svg);

        return sprintf(
            '<img src="data:image/svg+xml;base64,%s" alt="%s">',
            $encoded,
            esc_attr__('Album Preview', 'mini-gallery')
        );
    }

    private static function render_gallery_selector()
    {
        $galleries = get_posts([
            'post_type'      => 'mgwpp_soora',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish'
        ]);
    ?>
        <div class="mgwpp-gallery-selector">
            <?php if (empty($galleries)) : ?>
                <div class="mgwpp-no-galleries">
                    <p><?php esc_html_e('No galleries found. Create galleries first.', 'mini-gallery'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mgwpp-galleries')); ?>" class="button">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php esc_html_e('Create Gallery', 'mini-gallery'); ?>
                    </a>
                </div>
            <?php else : ?>
                <div class="mgwpp-gallery-grid">
                    <?php foreach ($galleries as $gallery) :
                        $thumbnail_id = get_post_thumbnail_id($gallery->ID);
                        $image_count = get_post_meta($gallery->ID, 'mgwpp_image_count', true) ?: 0;
                    ?>
                        <label class="mgwpp-gallery-item">
                            <div class="mgwpp-gallery-checkbox">
                                <input type="checkbox" name="album_galleries[]"
                                    value="<?php echo absint($gallery->ID); ?>"
                                    class="mgwpp-gallery-checkbox-input">
                                <span class="mgwpp-checkmark"></span>
                            </div>

                            <div class="mgwpp-gallery-info">
                                <h4><?php echo esc_html($gallery->post_title); ?></h4>
                                <span class="mgwpp-image-count">
                                    <span class="dashicons dashicons-images-alt"></span>
                                    <?php echo esc_html($image_count); ?>
                                    <?php echo esc_html(_n('image', 'images', $image_count, 'mini-gallery')); ?>
                                </span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php
    }

    private static function render_albums_table()
    {
        $table = new MGWPP_Albums_Table();
        $table->prepare_items();
    ?>
        <div class="mgwpp-albums-table-container">
            <form method="post">
                <?php $table->display(); ?>
            </form>
        </div>
<?php
    }
    // Helper methods for stats
    private static function get_albums_count()
    {
        $cache_key = 'mgwpp_albums_count';
        $count = wp_cache_get($cache_key, 'mini-gallery');

        if (false === $count) {
            $count = get_transient($cache_key);

            if (false === $count) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'mgwpp_albums';
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $count = (int) $wpdb->get_var(
                    "SELECT COUNT(*) FROM `" . esc_sql($table_name) . "`"
                );
                set_transient($cache_key, $count, 12 * HOUR_IN_SECONDS);
            }

            wp_cache_set($cache_key, $count, 'mini-gallery', 0);
        }

        return $count;
    }

    private static function get_galleries_count()
    {
        $cache_key = 'mgwpp_galleries_count';
        $count = wp_cache_get($cache_key, 'mini-gallery');

        if (false === $count) {
            $count = (int) wp_count_posts('mgwpp_soora')->publish;
            wp_cache_set($cache_key, $count, 'mini-gallery', 0);
        }

        return $count;
    }

    private static function get_images_count()
    {
        $cache_key = 'mgwpp_images_count';
        $count = wp_cache_get($cache_key, 'mini-gallery');

        if (false === $count) {
            $count = get_transient($cache_key);

            if (false === $count) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'mgwpp_gallery_images';
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $count = (int) $wpdb->get_var(
                    "SELECT COUNT(*) FROM `" . esc_sql($table_name) . "`"
                );
                set_transient($cache_key, $count, 12 * HOUR_IN_SECONDS);
            }

            wp_cache_set($cache_key, $count, 'mini-gallery', 0);
        }

        return $count;
    }
}
