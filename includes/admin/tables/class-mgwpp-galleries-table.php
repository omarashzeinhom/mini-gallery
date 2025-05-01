<?php
if (!defined('ABSPATH')) {
    exit;
}
// File: includes/admin/tables/class-mgwpp-galleries-table.php

class MGWPP_Galleries_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'gallery',
            'plural'   => 'galleries',
            'ajax'     => false,
            'screen'   => 'mgwpp-galleries'
        ]);
    }

    public function get_columns() {
        return [
            'title'     => __('Title', 'mini-gallery'),
            'type'      => __('Type', 'mini-gallery'),
            'shortcode' => __('Shortcode', 'mini-gallery'),
            'actions'   => __('Actions', 'mini-gallery')
        ];
    }

    public function prepare_items() {
        $this->_column_headers = [$this->get_columns(), [], []];
        
        $this->items = get_posts([
            'post_type'      => 'mgwpp_soora',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ]);
    }

    protected function column_title($item) {
        return sprintf('<strong>%s</strong>', esc_html($item->post_title));
    }

    protected function column_type($item) {
        $type = get_post_meta($item->ID, 'gallery_type', true);
        return ucfirst(str_replace('_', ' ', $type));
    }

    protected function column_shortcode($item) {
        return sprintf('<code>[mgwpp_gallery id="%d"]</code>', $item->ID);
    }

    protected function column_actions($item) {
        $delete_url = wp_nonce_url(
            admin_url('admin-post.php?action=mgwpp_delete_gallery&gallery_id=' . $item->ID),
            'mgwpp_delete_gallery'
        );

        $edit_url = add_query_arg([
            'page'       => 'mgwpp-edit-gallery',
            'gallery_id' => $item->ID
        ], admin_url('admin.php'));

        return sprintf(
            '<a href="%s" class="button button-primary">%s</a> ' .
            '<a href="%s" class="button button-danger" onclick="return confirm(\'%s\')">%s</a>',
            esc_url($edit_url),
            __('Edit', 'mini-gallery'),
            esc_url($delete_url),
            esc_js(__('Are you sure?', 'mini-gallery')),
            __('Delete', 'mini-gallery')
        );
    }
}