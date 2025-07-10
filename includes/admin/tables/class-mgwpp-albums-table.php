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
            'title'         => __('Title', 'mini-gallery'),
            'gallery_count' => __('Galleries', 'mini-gallery'),
            'shortcode'     => __('Shortcode', 'mini-gallery'),
            'date'          => __('Date', 'mini-gallery'),
            'actions'       => __('Actions', 'mini-gallery')
        ];
    }

    protected function column_default($item, $column_name)
    {
        return isset($item->$column_name) ? $item->$column_name : '';
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
                __('Edit', 'mini-gallery')
            ),
            'delete' => sprintf(
                '<a href="%s" class="submitdelete">%s</a>',
                esc_url($delete_url),
                __('Delete', 'mini-gallery')
            )
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
            __('Preview', 'mini-gallery')
        );
    }

    public function single_row($item)
    {
        echo '<tr>';
        $this->single_row_columns($item);
        echo '</tr>';
        echo '<tr class="mgwpp-album-details-row">';
        echo '<td colspan="5">';
        $this->album_details_content($item);
        echo '</td>';
        echo '</tr>';
    }

    private function album_details_content($item)
    {
        $galleries = get_post_meta($item->ID, '_mgwpp_album_galleries', true);
        echo '<div class="mgwpp-album-details"><h4>' . esc_html__('Album Contents', 'mini-gallery') . '</h4>';
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
            echo '<p>' . esc_html__('No galleries in this album.', 'mini-gallery') . '</p>';
        }
        echo '</div>';
    }
}

add_action('admin_post_mgwpp_delete_album', function () {
    if (!isset($_GET['album_id']) || !isset($_REQUEST['_wpnonce'])) {
        wp_die(__('Invalid request parameters', 'mini-gallery'));
    }

    $album_id = intval($_GET['album_id']);
    $nonce = isset($_GET['_wpnonce']) ? sanitize_key($_GET['_wpnonce']) : '';

    if (!wp_verify_nonce($nonce, 'mgwpp_delete_album_' . $album_id)) {
        wp_die(__('Security verification failed', 'mini-gallery'));
    }

    if (!get_post($album_id)) {
        wp_die(__('Specified album does not exist', 'mini-gallery'));
    }

    if (!current_user_can('delete_mgwpp_album', $album_id)) {
        wp_die(__('You lack permissions for this action', 'mini-gallery'));
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
    if (isset($_GET['mgwpp_deleted'])) {
        echo '<div class="notice notice-success is-dismissible"><p>'
            . esc_html__('Album successfully removed.', 'mini-gallery')
            . '</p></div>';
    }
    if (isset($_GET['mgwpp_delete_error'])) {
        echo '<div class="notice notice-error is-dismissible"><p>'
            . esc_html__('Failed to delete album.', 'mini-gallery')
            . '</p></div>';
    }
});
