<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';


// Ensure WP_List_Table is loaded before the custom Albums Table
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Now load your custom table
require_once MG_PLUGIN_PATH . 'includes/admin/tables/class-mgwpp-albums-table.php';

class MGWPP_Albums_View
{

    public static function render()
    {
        // Enqueue necessary scripts and styles
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_media();

        // Add custom styles and scripts
        wp_enqueue_style('mgwpp-admin-styles', plugin_dir_url(MGWPP_PLUGIN_FILE) . 'assets/css/admin-styles.css', array(), MGWPP_ASSET_VERSION);
        wp_enqueue_script('mgwpp-admin-script', plugin_dir_url(MGWPP_PLUGIN_FILE) . 'assets/js/admin-script.js', array('jquery', 'jquery-ui-tabs', 'jquery-ui-sortable'), MGWPP_ASSET_VERSION, true);

        // Get counts for dashboard stats
        $albums_count = self::get_albums_count();
?>
        <?php MGWPP_Inner_Header::render(); ?>

        <div class="wrap">
            <div class="mgwpp-tabs-container">
                <div id="mgwpp-tabs">
                    <ul class="mgwpp-tabs-nav">
                        <li><a href="#tab-albums"><?php esc_html_e('Albums', 'mini-gallery'); ?></a></li>
                        <li><a href="#tab-create"><?php esc_html_e('Create New', 'mini-gallery'); ?></a></li>
                        <li><a href="#tab-settings"><?php esc_html_e('Settings', 'mini-gallery'); ?></a></li>
                    </ul>

                    <div id="tab-albums" class="mgwpp-tab-content">
                        <div class="mgwpp-search-filter">
                            <input type="text" id="mgwpp-album-search" placeholder="<?php esc_attr_e('Search albums...', 'mini-gallery'); ?>">
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

                    <div id="tab-settings" class="mgwpp-tab-content">
                        <div class="mgwpp-settings-card">
                            <h2><?php esc_html_e('Album Display Settings', 'mini-gallery'); ?></h2>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="mgwpp_save_album_settings">
                                <?php wp_nonce_field('mgwpp_album_settings_nonce', 'mgwpp_album_settings_nonce'); ?>

                                <div class="mgwpp-setting-row">
                                    <label for="albums_per_page"><?php esc_html_e('Albums Per Page:', 'mini-gallery'); ?></label>
                                    <input type="number" name="albums_per_page" id="albums_per_page" value="<?php echo esc_attr(get_option('mgwpp_albums_per_page', 12)); ?>" min="1" max="100">
                                </div>

                                <div class="mgwpp-setting-row">
                                    <label for="album_layout"><?php esc_html_e('Default Layout:', 'mini-gallery'); ?></label>
                                    <select name="album_layout" id="album_layout">
                                        <option value="grid" <?php selected(get_option('mgwpp_album_layout', 'grid'), 'grid'); ?>><?php esc_html_e('Grid', 'mini-gallery'); ?></option>
                                        <option value="masonry" <?php selected(get_option('mgwpp_album_layout', 'grid'), 'masonry'); ?>><?php esc_html_e('Masonry', 'mini-gallery'); ?></option>
                                        <option value="carousel" <?php selected(get_option('mgwpp_album_layout', 'grid'), 'carousel'); ?>><?php esc_html_e('Carousel', 'mini-gallery'); ?></option>
                                    </select>
                                </div>

                                <button type="submit" class="button button-primary">
                                    <span class="dashicons dashicons-saved"></span> <?php esc_html_e('Save Settings', 'mini-gallery'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Initialize tabs
                $('#mgwpp-tabs').tabs();

                // Initialize search functionality
                $('#mgwpp-album-search').on('keyup', function() {
                    var value = $(this).val().toLowerCase();
                    $('.mgwpp-albums-table tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                });

                // Initialize filter functionality
                $('#mgwpp-album-filter').on('change', function() {
                    var value = $(this).val();
                    if (value === '') {
                        $('.mgwpp-albums-table tbody tr').show();
                    } else if (value === 'recent') {
                        // This is a simplified example - would need server-side implementation for real filtering
                        $('.mgwpp-albums-table tbody tr').sort(function(a, b) {
                            return $(b).data('created') - $(a).data('created');
                        }).appendTo('.mgwpp-albums-table tbody');
                    } else if (value === 'popular') {
                        // This is a simplified example - would need server-side implementation for real filtering
                        $('.mgwpp-albums-table tbody tr').sort(function(a, b) {
                            return $(b).data('galleries') - $(a).data('galleries');
                        }).appendTo('.mgwpp-albums-table tbody');
                    }
                });
            });
        </script>
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

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mgwpp-album-form">
                    <input type="hidden" name="action" value="mgwpp_create_album">

                    <?php
                    // must match your handler:
                    wp_nonce_field('mgwpp_album_submit_nonce', 'mgwpp_album_submit_nonce');
                    ?>

                    <div class="mgwpp-form-row">
                        <label for="album_title">
                            <span class="dashicons dashicons-edit"></span>
                            <?php esc_html_e('Album Title', 'mini-gallery'); ?>
                        </label>
                        <input type="text" name="album_title" id="album_title" required placeholder="<?php esc_attr_e('Enter album title...', 'mini-gallery'); ?>">
                    </div>

                    <div class="mgwpp-form-row">
                        <label for="album_description">
                            <span class="dashicons dashicons-text"></span>
                            <?php esc_html_e('Description (Optional)', 'mini-gallery'); ?>
                        </label>
                        <textarea name="album_description" id="album_description" rows="3" placeholder="<?php esc_attr_e('Enter album description...', 'mini-gallery'); ?>"></textarea>
                    </div>

                    <div class="mgwpp-form-row">
                        <label for="album_cover">
                            <span class="dashicons dashicons-format-image"></span>
                            <?php esc_html_e('Album Cover (Optional)', 'mini-gallery'); ?>
                        </label>
                        <div class="mgwpp-media-uploader">
                            <div id="album-cover-preview" class="mgwpp-cover-preview"></div>
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
                        <label>
                            <span class="dashicons dashicons-format-gallery"></span>
                            <?php esc_html_e('Select Galleries', 'mini-gallery'); ?>
                        </label>
                        <div class="mgwpp-gallery-selector-container">
                            <div class="mgwpp-gallery-filter">
                                <input type="text" id="gallery-search" placeholder="<?php esc_attr_e('Search galleries...', 'mini-gallery'); ?>">
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
                        <img src="<?php echo esc_url(plugin_dir_url(MGWPP_PLUGIN_FILE) . 'assets/images/placeholder-album.jpg'); ?>" alt="<?php esc_attr_e('Album Preview', 'mini-gallery'); ?>" id="preview-cover-image">
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

        <script>
            jQuery(document).ready(function($) {
                // Media uploader for album cover
                $('.mgwpp-upload-cover-btn').click(function(e) {
                    e.preventDefault();

                    var image_frame;

                    if (image_frame) {
                        image_frame.open();
                        return;
                    }

                    image_frame = wp.media({
                        title: '<?php esc_html_e('Select Album Cover Image', 'mini-gallery'); ?>',
                        multiple: false,
                        library: {
                            type: 'image'
                        }
                    });

                    image_frame.on('select', function() {
                        var attachment = image_frame.state().get('selection').first().toJSON();
                        $('#album_cover_id').val(attachment.id);
                        $('#album-cover-preview').html('<img src="' + attachment.url + '" alt="<?php esc_attr_e('Album Cover', 'mini-gallery'); ?>">');
                        $('#preview-cover-image').attr('src', attachment.url);
                        $('.mgwpp-remove-cover-btn').show();
                    });

                    image_frame.open();
                });

                // Remove cover image
                $('.mgwpp-remove-cover-btn').click(function(e) {
                    e.preventDefault();
                    $('#album_cover_id').val('');
                    $('#album-cover-preview').html('');
                    $('#preview-cover-image').attr('src', '<?php echo esc_url(plugin_dir_url(MGWPP_PLUGIN_FILE) . 'assets/images/placeholder-album.jpg'); ?>');
                    $(this).hide();
                });

                // Live preview for album title
                $('#album_title').on('input', function() {
                    var title = $(this).val();
                    if (title) {
                        $('#preview-title').text(title);
                    } else {
                        $('#preview-title').text('<?php esc_html_e('Album Title', 'mini-gallery'); ?>');
                    }
                });

                // Live preview for album description
                $('#album_description').on('input', function() {
                    var description = $(this).val();
                    if (description) {
                        $('#preview-description').text(description);
                    } else {
                        $('#preview-description').text('<?php esc_html_e('Album description will appear here...', 'mini-gallery'); ?>');
                    }
                });

                // Gallery search functionality
                $('#gallery-search').on('keyup', function() {
                    var value = $(this).val().toLowerCase();
                    $('.mgwpp-gallery-item').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                });

                // Update selected galleries preview
                $('.mgwpp-gallery-item input').on('change', function() {
                    updateGalleriesPreview();
                });

                function updateGalleriesPreview() {
                    var selectedGalleries = [];
                    $('.mgwpp-gallery-item input:checked').each(function() {
                        selectedGalleries.push($(this).parent().text().trim());
                    });

                    if (selectedGalleries.length > 0) {
                        var html = '';
                        $.each(selectedGalleries, function(index, gallery) {
                            html += '<li>' + gallery + '</li>';
                        });
                        $('#preview-galleries-list').html(html);
                    } else {
                        $('#preview-galleries-list').html('<li class="mgwpp-empty-selection"><?php esc_html_e('No galleries selected', 'mini-gallery'); ?></li>');
                    }
                }

                // Reset form button
                $('.mgwpp-reset-btn').click(function() {
                    setTimeout(function() {
                        $('#preview-title').text('<?php esc_html_e('Album Title', 'mini-gallery'); ?>');
                        $('#preview-description').text('<?php esc_html_e('Album description will appear here...', 'mini-gallery'); ?>');
                        $('#preview-cover-image').attr('src', '<?php echo esc_url(plugin_dir_url(MGWPP_PLUGIN_FILE) . 'assets/images/placeholder-album.jpg'); ?>');
                        $('#preview-galleries-list').html('<li class="mgwpp-empty-selection"><?php esc_html_e('No galleries selected', 'mini-gallery'); ?></li>');
                        $('#album-cover-preview').html('');
                        $('.mgwpp-remove-cover-btn').hide();
                    }, 100);
                });
            });
        </script>
    <?php
    }

    private static function render_gallery_selector()
    {
        $galleries = get_posts([
            'post_type'      => 'mgwpp_soora',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC'
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
                        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : plugin_dir_url(MGWPP_PLUGIN_FILE) . 'assets/images/placeholder-gallery.jpg';
                        $image_count = get_post_meta($gallery->ID, 'mgwpp_image_count', true);
                    ?>
                        <label class="mgwpp-gallery-item">
                            <div class="mgwpp-gallery-checkbox">
                                <input type="checkbox"
                                    name="album_galleries[]"
                                    value="<?php echo absint($gallery->ID); ?>">
                                <span class="mgwpp-checkmark"></span>
                            </div>
                            <div class="mgwpp-gallery-thumbnail">
                                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($gallery->post_title); ?>">
                            </div>
                            <div class="mgwpp-gallery-info">
                                <h4><?php echo esc_html($gallery->post_title); ?></h4>
                                <span class="mgwpp-image-count">
                                    <span class="dashicons dashicons-images-alt"></span>
                                    <?php echo esc_html($image_count ? $image_count : 0); ?>
                                    <?php echo esc_html(_n('image', 'images', $image_count ? $image_count : 0, 'mini-gallery')); ?>
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
        global $wpdb;
        $table_name = $wpdb->prefix . 'mgwpp_albums';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    private static function get_galleries_count()
    {
        return wp_count_posts('mgwpp_soora')->publish;
    }

    private static function get_images_count()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mgwpp_gallery_images';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
}
