<?php
if ( ! class_exists( 'MGWPP_Galleries_List_Table' ) ) {

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class MGWPP_Galleries_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => 'gallery', // Singular name for the records
            'plural'   => 'galleries', // Plural name for the records
            'ajax'     => false, // Enable AJAX for this table
        ) );
    }

    /**
     * Prepare the items for display
     */
    public function prepare_items() {
        $columns = $this->get_columns();
        $this->_column_headers = array( $columns, array(), array() );

        // Fetch galleries (custom post type)
        $galleries = get_posts( array(
            'post_type' => 'mgwpp_soora',
            'posts_per_page' => -1, // Get all galleries
        ) );

        $data = array();
        foreach ( $galleries as $gallery ) {
            $gallery_type = get_post_meta( $gallery->ID, 'gallery_type', true );
            $data[] = array(
                'ID'            => $gallery->ID,
                'title'         => $gallery->post_title,
                'type'          => ucfirst( $gallery_type ),
                'shortcode'     => '[mgwpp_gallery id="' . $gallery->ID . '"]',
                'actions'       => $this->get_actions( $gallery->ID )
            );
        }

        $this->items = $data;
    }

    /**
     * Get the columns for the table
     */
    public function get_columns() {
        $columns = array(
            'title'     => esc_html__( 'Gallery Title', 'mini-gallery' ),
            'type'      => esc_html__( 'Gallery Type', 'mini-gallery' ),
            'shortcode' => esc_html__( 'Shortcode', 'mini-gallery' ),
            'actions'   => esc_html__( 'Actions', 'mini-gallery' ),
        );
        return $columns;
    }

    /**
     * Get actions for each gallery
     */
    public function get_actions( $gallery_id ) {
        $delete_url = wp_nonce_url( admin_url( 'admin-post.php?action=mgwpp_delete_gallery&gallery_id=' . $gallery_id ), 'mgwpp_delete_gallery' );
        $edit_url = wp_nonce_url( admin_url( 'admin.php?page=mgwpp-edit-gallery&gallery_id=' . $gallery_id ), 'mgwpp_edit_gallery' );

        return sprintf(
            '<a href="%s" class="button button-secondary">%s</a> <a href="%s" class="button button-primary">%s</a>',
            esc_url( $delete_url ),
            esc_html__( 'Delete Gallery', 'mini-gallery' ),
            esc_url( $edit_url ),
            esc_html__( 'Edit Gallery', 'mini-gallery' )
        );
    }

    /**
     * Display the table in the page
     */
    public function display_table() {
        $this->prepare_items();
        $this->display();
    }
}
}
