<?php
if (!defined('ABSPATH')) {
    exit;
}

// Ensure WP_List_Table is loaded before the custom Albums Table
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Now load your custom table
require_once MG_PLUGIN_PATH . 'includes/admin/tables/class-mgwpp-albums-table.php';

class MGWPP_Albums_View {

    public static function render() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Albums Management', 'mini-gallery') ?></h1>
            
            <div class="mgwpp-albums-section">
                <?php self::render_creation_form(); ?>
                <?php self::render_albums_table(); ?>
            </div>
        </div>
        <?php
    }

    private static function render_creation_form() {
        ?>
        <div class="mgwpp-album-form">
            <h2><?php esc_html_e('Create New Album', 'mini-gallery') ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="mgwpp_create_album">
                <?php wp_nonce_field('mgwpp_album_creation', '_wpnonce'); ?>
                
                <div class="form-field">
                    <label for="album_title"><?php esc_html_e('Title:', 'mini-gallery'); ?></label>
                    <input type="text" name="album_title" required>
                </div>

                <div class="form-field">
                    <label><?php esc_html_e('Galleries:', 'mini-gallery'); ?></label>
                    <?php self::render_gallery_selector(); ?>
                </div>

                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Create Album', 'mini-gallery'); ?>
                </button>
            </form>
        </div>
        <?php
    }

    private static function render_gallery_selector() {
        $galleries = get_posts([
            'post_type'      => 'mgwpp_soora',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC'
        ]);
        ?>
        <div class="mgwpp-gallery-selector">
            <?php foreach ($galleries as $gallery) : ?>
                <label class="mgwpp-gallery-item">
                    <input type="checkbox" 
                           name="album_galleries[]" 
                           value="<?php echo absint($gallery->ID); ?>">
                    <?php echo esc_html($gallery->post_title); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private static function render_albums_table() {
        $table = new MGWPP_Albums_Table();
        $table->prepare_items();
        ?>
        <div class="mgwpp-albums-table">
            <h2><?php esc_html_e('Existing Albums', 'mini-gallery'); ?></h2>
            <?php $table->display(); ?>
        </div>
        <?php
    }
}
