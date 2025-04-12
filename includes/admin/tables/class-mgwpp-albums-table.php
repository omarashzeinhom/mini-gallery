<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Albums_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'album',
            'plural'   => 'albums',
            'ajax'     => false,
            'screen'   => 'mgwpp-albums'
        ]);
    }

    public function get_columns() {
        return [
            'title'        => __('Title', 'mini-gallery'),
            'gallery_count' => __('Galleries', 'mini-gallery'),
            'shortcode'    => __('Shortcode', 'mini-gallery'),
            'date'         => __('Date', 'mini-gallery'),
            'actions'      => __('Actions', 'mini-gallery')
        ];
    }

    public function prepare_items() {
        $this->_column_headers = [$this->get_columns(), [], []];
        
        $args = [
            'post_type'      => 'mgwpp_album',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ];
        
        $this->items = get_posts($args);
    }

    protected function column_title($item) {
        $edit_url = get_edit_post_link($item->ID);
        $delete_url = wp_nonce_url(
            admin_url('admin-post.php?action=mgwpp_delete_album&album_id=' . $item->ID),
            'mgwpp_delete_album_' . $item->ID
        );

        $title = sprintf(
            '<strong><a class="row-title" href="%s">%s</a></strong>',
            esc_url($edit_url),
            esc_html($item->post_title)
        );

        return $title . $this->row_actions([
            'edit' => sprintf(
                '<a href="%s">%s</a>',
                esc_url($edit_url),
                __('Edit', 'mini-gallery')
            ),
            'delete' => sprintf(
                '<a href="%s" class="submitdelete">%s</a>',
                esc_url($delete_url),
                __('Delete', 'mini-gallery')
            )
        ]);
    }

    protected function column_gallery_count($item) {
        $galleries = get_post_meta($item->ID, '_mgwpp_album_galleries', true);
        return is_array($galleries) ? count($galleries) : 0;
    }

    protected function column_shortcode($item) {
        return sprintf(
            '<input type="text" readonly value="[mgwpp_album id=&quot;%d&quot;]" class="mgwpp-shortcode-code">',
            absint($item->ID)
        );
    }

    protected function column_date($item) {
        return get_the_date('', $item);
    }

    protected function column_actions($item) {
        $preview_url = add_query_arg([
            'album_id' => $item->ID,
            'preview' => 'true'
        ], home_url());

        return sprintf(
            '<a href="%s" class="button" target="_blank">%s</a>',
            esc_url($preview_url),
            __('Preview', 'mini-gallery')
        );
    }

    public function single_row($item) {
        echo '<tr>';
        $this->single_row_columns($item);
        echo '</tr>';
        
        // Add expanded row with gallery list
        echo '<tr class="mgwpp-album-details-row">';
        echo '<td colspan="5">';
        $this->album_details_content($item);
        echo '</td>';
        echo '</tr>';
    }

    private function album_details_content($item) {
        $galleries = get_post_meta($item->ID, '_mgwpp_album_galleries', true);
        ?>
        <div class="mgwpp-album-details">
            <h4><?php esc_html_e('Album Contents', 'mini-gallery'); ?></h4>
            <?php if (!empty($galleries) && is_array($galleries)) : ?>
                <ul class="mgwpp-album-galleries">
                    <?php foreach ($galleries as $gallery_id) : 
                        $gallery = get_post($gallery_id);
                        if ($gallery) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_edit_post_link($gallery_id)); ?>">
                                    <?php echo esc_html($gallery->post_title); ?>
                                </a>
                                <span class="mgwpp-gallery-type">
                                    <?php echo esc_html(get_post_meta($gallery_id, 'gallery_type', true)); ?>
                                </span>
                            </li>
                        <?php endif;
                    endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php esc_html_e('No galleries in this album.', 'mini-gallery'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
}

class MGWPP_Admin {
    // ... existing code ...

    public static function mgwpp_render_albums_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Albums', 'mini-gallery'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=mgwpp-albums&action=new')); ?>" class="page-title-action">
                <?php esc_html_e('Add New', 'mini-gallery'); ?>
            </a>
            
            <?php self::render_album_creation_form(); ?>
            
            <hr class="wp-header-end">
            
            <div class="mgwpp-albums-table-wrap">
                <?php
                $table = new MGWPP_Albums_Table();
                $table->prepare_items();
                $table->display();
                ?>
            </div>
        </div>
        <?php
    }

    private static function render_album_creation_form() {
        if (!isset($_GET['action']) || 'new' !== $_GET['action']) return;
        
        $galleries = get_posts([
            'post_type' => 'mgwpp_soora',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'publish'
        ]);
        ?>
        <div class="mgwpp-album-form">
            <h2><?php esc_html_e('Create New Album', 'mini-gallery'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="mgwpp_create_album">
                <?php wp_nonce_field('mgwpp_create_album', 'mgwpp_album_nonce'); ?>
                
                <div class="form-field">
                    <label for="album_title"><?php esc_html_e('Album Title', 'mini-gallery'); ?></label>
                    <input type="text" name="album_title" required class="regular-text">
                </div>
                
                <div class="form-field">
                    <label><?php esc_html_e('Select Galleries', 'mini-gallery'); ?></label>
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
                </div>
                
                <div class="form-field">
                    <label for="album_description"><?php esc_html_e('Description', 'mini-gallery'); ?></label>
                    <textarea name="album_description" rows="3" class="large-text"></textarea>
                </div>
                
                <?php submit_button(__('Create Album', 'mini-gallery')); ?>
            </form>
        </div>
        <?php
    }

    // ... rest of the existing class ...
}

MGWPP_Admin::init();