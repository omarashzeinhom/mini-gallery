<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Albums_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'album',
            'plural'   => 'albums',
            'ajax'     => false,
            'screen'   => 'mgwpp-albums'
        ]);

        // Add dark mode classes to the table
        add_filter('admin_body_class', [$this, 'add_dark_mode_classes']);
    }

    public function add_dark_mode_classes($classes)
    {
        if (isset($_COOKIE['mgwpp_dark_mode']) && $_COOKIE['mgwpp_dark_mode'] === '1') {
            $classes .= ' mgwpp-dark-mode ';
        }
        return $classes;
    }

    public function get_columns()
    {
        return [
            'cb'            => '<input type="checkbox">',
            'thumbnail'     => esc_html__('Preview', 'mini-gallery'),
            'title'         => esc_html__('Title', 'mini-gallery'),
            'gallery_count' => esc_html__('Galleries', 'mini-gallery'),
            'shortcode'     => esc_html__('Shortcode', 'mini-gallery'),
            'date'          => esc_html__('Date', 'mini-gallery'),
            'actions'       => esc_html__('Actions', 'mini-gallery')
        ];
    }

    protected function get_bulk_actions()
    {
        return [
            'delete' => esc_html__('Delete', 'mini-gallery')
        ];
    }

    protected function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="album[]" value="%s">', $item->ID);
    }

    protected function column_default($item, $column_name)
    {
        return isset($item->$column_name) ? $item->$column_name : '';
    }

    // Render thumbnail column
    protected function column_thumbnail($item)
    {
        // Default icon if no images found
        $image_html = '<span class="dashicons dashicons-format-gallery" style="font-size:48px;color:var(--mgwpp-icon-color);"></span>';

        // Try to get first gallery image
        $galleries = get_post_meta($item->ID, '_mgwpp_album_galleries', true);

        if (!empty($galleries) && is_array($galleries)) {
            $first_gallery_id = $galleries[0];
            $gallery_images = get_post_meta($first_gallery_id, 'gallery_images', true);

            if (!empty($gallery_images)) {
                $image_ids = is_array($gallery_images) ? $gallery_images : explode(',', $gallery_images);

                if (!empty($image_ids)) {
                    $first_image_id = $image_ids[0];

                    // Use WordPress function to get properly escaped image
                    $image_html = wp_get_attachment_image(
                        $first_image_id,
                        [75, 75], // Custom size array
                        false,
                        [
                            'style' => 'object-fit:cover; width:75px; height:75px;',
                            'alt'   => esc_attr__('Album preview', 'mini-gallery'),
                            'class' => 'mgwpp-album-thumbnail'
                        ]
                    );
                }
            }
        }

        return '<div class="mgwpp-album-thumbnail-container">' . $image_html . '</div>';
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $args = [
            'post_type'      => 'mgwpp_album',
            'posts_per_page' => $per_page,
            'offset'         => $offset,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC'
        ];

        $query = new WP_Query($args);
        $this->items = $query->posts;

        $this->set_pagination_args([
            'total_items' => $query->found_posts,
            'per_page'    => $per_page,
            'total_pages' => ceil($query->found_posts / $per_page)
        ]);
    }

    protected function column_title($item)
    {
        // Custom edit URL pointing to edit.php?post_type=mgwpp_album
        $edit_url = admin_url('edit.php?post_type=mgwpp_album&action=edit&post=' . $item->ID);

        $title = sprintf(
            '<strong><a class="row-title" href="%s">%s</a></strong>',
            esc_url($edit_url),
            esc_html($item->post_title)
        );

        return $title;
    }

    protected function column_gallery_count($item)
    {
        $galleries = get_post_meta($item->ID, '_mgwpp_album_galleries', true);
        $count = is_array($galleries) ? count($galleries) : 0;

        return sprintf(
            '<span class="mgwpp-gallery-count">%d %s</span>',
            $count,
            _n('gallery', 'galleries', $count, 'mini-gallery')
        );
    }

    protected function column_shortcode($item)
    {
        return sprintf(
            '<div class="mgwpp-shortcode-container">
                <input type="text" readonly value="[mgwpp_album id=&quot;%d&quot;]" class="mgwpp-shortcode-code">
                <button class="button mgwpp-copy-shortcode" data-clipboard-text="[mgwpp_album id=&quot;%d&quot;]">
                    <span class="dashicons dashicons-clipboard"></span>
                </button>
            </div>',
            absint($item->ID),
            absint($item->ID)
        );
    }

    protected function column_date($item)
    {
        $date = get_the_date('', $item);
        $modified = get_the_modified_date('', $item);

        return sprintf(
            '<span class="mgwpp-date">%s</span><br><small class="mgwpp-modified">%s: %s</small>',
            $date,
            esc_html__('Modified', 'mini-gallery'),
            $modified
        );
    }

    protected function column_actions($item)
    {
        // Custom edit URL pointing to edit.php?post_type=mgwpp_album
        $edit_url = admin_url('edit.php?post_type=mgwpp_album&action=edit&post=' . $item->ID);

        // Custom delete URL pointing to edit.php?post_type=mgwpp_album
        $delete_url = admin_url('edit.php?post_type=mgwpp_album&action=delete&post=' . $item->ID);
        $delete_nonce = wp_create_nonce('mgwpp_delete_album_' . $item->ID);
        $delete_url = add_query_arg('_wpnonce', $delete_nonce, $delete_url);

        $actions = [
            'edit' => sprintf(
                '<a href="%s" class="mgwpp-action-link mgwpp-action-edit">
                <span class="dashicons dashicons-edit"></span> %s
            </a>',
                esc_url($edit_url),
                esc_html__('Edit', 'mini-gallery')
            ),
            'delete' => sprintf(
                '<a href="%s" class="mgwpp-action-link mgwpp-action-delete" data-id="%d" onclick="return confirm(\'%s\');">
                <span class="dashicons dashicons-trash"></span> %s
            </a>',
                esc_url($delete_url),
                absint($item->ID),
                esc_js(__('Are you sure you want to delete this album?', 'mini-gallery')),
                esc_html__('Delete', 'mini-gallery')
            )
        ];

        return '<div class="mgwpp-action-links">' . implode(' | ', $actions) . '</div>';
    }
}

// Initialize table styles
add_action('admin_head', function () {
    $screen = get_current_screen();
    if ($screen && 'toplevel_page_mgwpp_albums' === $screen->id) {
        $table = new MGWPP_Albums_Table();
        if (method_exists($table, 'print_table_styles')) {
            $table->print_table_styles();
        }
    }
});

// Handle edit and delete actions on edit.php?post_type=mgwpp_album
add_action('admin_init', function () {
    // Check if we're on the edit.php page for mgwpp_album post type
    if (!is_admin() || !isset($_GET['post_type']) || sanitize_text_field(wp_unslash($_GET['post_type'])) !== 'mgwpp_album') {
        return;
    }

    $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
    $post_id = isset($_GET['post']) ? absint(wp_unslash($_GET['post'])) : 0;

    if ($action === 'delete' && $post_id) {
        // Handle delete action
        if (!isset($_GET['_wpnonce'])) {
            wp_die(esc_html__('Security verification failed', 'mini-gallery'));
        }

        if (!wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'mgwpp_delete_album_' . $post_id)) {
            wp_die(esc_html__('Security verification failed', 'mini-gallery'));
        }

        if (!get_post($post_id)) {
            wp_die(esc_html__('Specified album does not exist', 'mini-gallery'));
        }

        if (!current_user_can('delete_posts')) {
            wp_die(esc_html__('You lack permissions for this action', 'mini-gallery'));
        }

        $deletion_result = wp_delete_post($post_id, true);
        $redirect_url = admin_url('admin.php?page=mgwpp_albums');

        // Add nonce for admin notice
        $notice_nonce = wp_create_nonce('mgwpp_album_notice');

        if ($deletion_result) {
            $redirect_url = add_query_arg([
                'mgwpp_deleted' => 1,
                '_wpnonce_notice' => $notice_nonce
            ], $redirect_url);
        } else {
            $redirect_url = add_query_arg([
                'mgwpp_delete_error' => 1,
                '_wpnonce_notice' => $notice_nonce
            ], $redirect_url);
        }

        wp_safe_redirect($redirect_url);
        exit;
    }

    if ($action === 'edit' && $post_id) {
        // Handle edit action - you can customize this further
        // For now, it will just load the edit.php page with your post type
        // You might want to include your custom edit form here

        // Verify the post exists and user has permission
        if (!get_post($post_id)) {
            wp_die(esc_html__('Specified album does not exist', 'mini-gallery'));
        }

        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You lack permissions for this action', 'mini-gallery'));
        }

        // The edit.php page will naturally load here
        // You can add custom edit form rendering logic here if needed
    }
});

add_action('admin_notices', function () {
    $screen = get_current_screen();
    if (!$screen || 'toplevel_page_mgwpp_albums' !== $screen->id) {
        return;
    }

    // Verify nonce for admin notices
    if (!isset($_GET['_wpnonce_notice']) || !wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce_notice'])), 'mgwpp_album_notice')) {
        return;
    }

    // Check for success/error flags with proper sanitization
    $deleted = isset($_GET['mgwpp_deleted']) ? absint(wp_unslash($_GET['mgwpp_deleted'])) : 0;
    $delete_error = isset($_GET['mgwpp_delete_error']) ? absint(wp_unslash($_GET['mgwpp_delete_error'])) : 0;

    if ($deleted) {
        echo '<div class="notice notice-success is-dismissible"><p>';
        esc_html_e('Album successfully removed.', 'mini-gallery');
        echo '</p></div>';
    }
    if ($delete_error) {
        echo '<div class="notice notice-error is-dismissible"><p>';
        esc_html_e('Failed to delete album.', 'mini-gallery');
        echo '</p></div>';
    }
});
