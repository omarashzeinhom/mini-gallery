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
    }

    public function get_columns()
    {
        return [
            'thumbnail'     => esc_html__('Preview', 'mini-gallery'),
            'title'         => esc_html__('Title', 'mini-gallery'),
            'gallery_count' => esc_html__('Galleries', 'mini-gallery'),
            'shortcode'     => esc_html__('Shortcode', 'mini-gallery'),
            'date'          => esc_html__('Date', 'mini-gallery'),
            'actions'       => esc_html__('Actions', 'mini-gallery')
        ];
    }

    protected function column_default($item, $column_name)
    {
        return isset($item->$column_name) ? $item->$column_name : '';
    }

    // Render thumbnail column
    protected function column_thumbnail($item)
    {
        // Default icon if no images found
        $image_html = '<span class="dashicons dashicons-format-gallery" style="font-size:48px;"></span>';

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
                            'alt' => esc_attr__('Album preview', 'mini-gallery')
                        ]
                    );
                }
            }
        }

        return $image_html;
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $this->_column_headers = [$columns, [], []];

        $args = [
            'post_type'      => 'mgwpp_album',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ];

        $this->items = get_posts($args);
    }

    protected function column_title($item)
    {
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
                esc_html__('Edit', 'mini-gallery')
            ),
        ]);
    }


    protected function column_gallery_count($item)
    {
        $galleries = get_post_meta($item->ID, '_mgwpp_album_galleries', true);
        return is_array($galleries) ? count($galleries) : 0;
    }

    protected function column_shortcode($item)
    {
        return sprintf(
            '<input type="text" readonly value="[mgwpp_album id=&quot;%d&quot;]" class="mgwpp-shortcode-code">',
            absint($item->ID)
        );
    }

    protected function column_date($item)
    {
        return get_the_date('', $item);
    }

    protected function column_actions($item)
    {
        $preview_url = add_query_arg([
            'album_id' => $item->ID,
            'preview' => 'true'
        ], home_url());

        return sprintf(
            '<a href="%s" class="button" target="_blank">%s</a>',
            esc_url($preview_url),
            esc_html__('Preview', 'mini-gallery')
        );
    }


    public function single_row($item)
    {
        // Get current row classes from parent
        $parent_row_classes = $this->get_row_class();

        echo '<tr class="' . esc_attr($parent_row_classes) . '">';
        $this->single_row_columns($item);
        echo '</tr>';

        echo '<tr class="mgwpp-album-details-row ' . esc_attr($parent_row_classes) . '">';
        echo '<td colspan="' . count($this->get_columns()) . '">';
        $this->album_details_content($item);
        echo '</td>';
        echo '</tr>';
    }

    // Helper method to get row classes
    protected function get_row_class()
    {
        static $alternate = '';
        $alternate = ($alternate == '' ? 'alternate' : '');

        $classes = array();
        if ($alternate) {
            $classes[] = $alternate;
        }

        return implode(' ', $classes);
    }
    private function album_details_content($item)
    {
        $galleries = get_post_meta($item->ID, '_mgwpp_album_galleries', true);
        echo '<div class="mgwpp-album-details"><h4>';
        esc_html_e('Album Contents', 'mini-gallery');
        echo '</h4>';

        if (!empty($galleries)) {
            echo '<ul class="mgwpp-album-galleries">';
            foreach ($galleries as $gallery_id) {
                $gallery = get_post($gallery_id);
                if ($gallery) {
                    echo '<li><a href="' . esc_url(get_edit_post_link($gallery_id)) . '">'
                        . esc_html($gallery->post_title) . '</a><span class="mgwpp-gallery-type">'
                        . esc_html(get_post_meta($gallery_id, 'gallery_type', true)) . '</span></li>';
                }
            }
            echo '</ul>';
        } else {
            echo '<p>';
            esc_html_e('No galleries in this album.', 'mini-gallery');
            echo '</p>';
        }
        echo '</div>';
    }
}


add_action('admin_post_mgwpp_delete_album', function () {
    // Verify nonce first
    if (!isset($_REQUEST['_wpnonce'])) {
        wp_die(esc_html__('Security verification failed', 'mini-gallery'));
    }

    // Get album ID safely
    $album_id = isset($_GET['album_id']) ? absint(wp_unslash($_GET['album_id'])) : 0;

    // Verify nonce with dynamic action
    if (!wp_verify_nonce(sanitize_key(wp_unslash($_REQUEST['_wpnonce'])), 'mgwpp_delete_album_' . $album_id)) {
        wp_die(esc_html__('Security verification failed', 'mini-gallery'));
    }

    if (!$album_id) {
        wp_die(esc_html__('Invalid request parameters', 'mini-gallery'));
    }

    if (!get_post($album_id)) {
        wp_die(esc_html__('Specified album does not exist', 'mini-gallery'));
    }

    if (!current_user_can('delete_mgwpp_album', $album_id)) {
        wp_die(esc_html__('You lack permissions for this action', 'mini-gallery'));
    }

    $deletion_result = wp_delete_post($album_id, true);
    $redirect_url = admin_url('edit.php?post_type=mgwpp_album');

    if ($deletion_result) {
        $redirect_url = add_query_arg('mgwpp_deleted', 1, $redirect_url);
    } else {
        $redirect_url = add_query_arg('mgwpp_delete_error', 1, $redirect_url);
    }

    wp_safe_redirect($redirect_url);
    exit;
});


add_action('admin_notices', function () {
    // Check if we're on the albums page and user has permission
    $screen = get_current_screen();
    if (!$screen || 'edit-mgwpp_album' !== $screen->id || !current_user_can('manage_options')) {
        return;
    }

    // Check nonce for security
    if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_REQUEST['_wpnonce'])), 'mgwpp_album_notice')) {
        return;
    }

    // Safely check for success/error flags
    $deleted = isset($_GET['mgwpp_deleted']) ? absint($_GET['mgwpp_deleted']) : 0;
    $delete_error = isset($_GET['mgwpp_delete_error']) ? absint($_GET['mgwpp_delete_error']) : 0;

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
