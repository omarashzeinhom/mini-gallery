<?php
class MGWPP_Album_Post_Type
{
    public static function mgwpp_register_album_post_type()
    {
        $args = array(
            'public' => true,
            //'label' => 'Gallery Albums',
            'description' => 'Organize your galleries into albums',
            'show_in_rest' => true,
            'show_in_menu' => false/*'mini-gallery' */,
            'rest_base' => 'album-api',
            'menu_icon' => 'dashicons-album',
            'has_archive' => true,
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'capability_type' => 'mgwpp_album',
            'map_meta_cap' => true,
            'capabilities' => array(
                'edit_post' => 'edit_mgwpp_album',
                'read_post' => 'read_mgwpp_album',
                'delete_post' => 'delete_mgwpp_album',
                'edit_posts' => 'edit_mgwpp_albums',
                'edit_others_posts' => 'edit_others_mgwpp_albums',
                'publish_posts' => 'publish_mgwpp_albums',
                'read_private_posts' => 'read_private_mgwpp_albums',
                'delete_posts' => 'delete_mgwpp_albums',
                'delete_private_posts' => 'delete_private_mgwpp_albums',
                'delete_published_posts' => 'delete_published_mgwpp_albums',
                'delete_others_posts' => 'delete_others_mgwpp_albums',
                'edit_private_posts' => 'edit_private_mgwpp_albums',
                'edit_published_posts' => 'edit_published_mgwpp_albums',
                'create_posts' => 'create_mgwpp_albums'
            )
        );
        register_post_type('mgwpp_album', $args);

        // Register meta box for gallery selection
        add_action('add_meta_boxes', array(__CLASS__, 'add_album_galleries_meta_box'));
        add_action('save_post_mgwpp_album', array(__CLASS__, 'save_album_galleries_meta'));
    }

    public static function add_album_galleries_meta_box()
    {
        add_meta_box(
            'mgwpp_album_galleries',
            'Album Galleries',
            array(__CLASS__, 'render_album_galleries_meta_box'),
            'mgwpp_album',
            'normal',
            'high'
        );
    }

    public static function render_album_galleries_meta_box($post)
    {
        wp_nonce_field('mgwpp_album_galleries_nonce', 'mgwpp_album_galleries_nonce');

        // Get currently selected galleries
        $selected_galleries = get_post_meta($post->ID, '_mgwpp_album_galleries', true);
        if (!is_array($selected_galleries)) {
            $selected_galleries = array();
        }

        // Get all galleries
        $galleries = get_posts(array(
            'post_type' => 'mgwpp_soora',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        echo '<div class="mgwpp-album-galleries-container">';
        foreach ($galleries as $gallery) {
            $checked = in_array($gallery->ID, $selected_galleries) ? 'checked="checked"' : '';
            echo sprintf(
                '<label><input type="checkbox" name="mgwpp_album_galleries[]" value="%d" %s> %s</label><br>',
                absint( $gallery->ID ),
                esc_attr( $checked ),
                esc_html( $gallery->post_title )
            );
            
        }
        echo '</div>';
    }

    public static function save_album_galleries_meta($post_id)
    {
        // Verify nonce
        if (
            !isset( $_POST['mgwpp_album_galleries_nonce'] ) ||
            !wp_verify_nonce( wp_unslash( sanitize_text_field( $_POST['mgwpp_album_galleries_nonce'] ) ), 'mgwpp_album_galleries_nonce' )
        ) {
            return;
        }
        
        

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save galleries
        $galleries = isset($_POST['mgwpp_album_galleries']) ?
            array_map('intval', $_POST['mgwpp_album_galleries']) : array();
        update_post_meta($post_id, '_mgwpp_album_galleries', $galleries);
    }
}

// Register the album post type during the 'init' hook
add_action('init', array('MGWPP_Album_Post_Type', 'mgwpp_register_album_post_type'));
