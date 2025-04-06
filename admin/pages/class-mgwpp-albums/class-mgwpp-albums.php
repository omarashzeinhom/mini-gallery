<?php

namespace MGWPP\Admin\Pages;

use WP_List_Table;

class MGWPP_Admin_Albums extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'album',
            'plural'   => 'albums',
            'ajax'     => false
        ]);
    }

    public static function render_page()
    {
        ?>
        <div id="mgwpp_albums_content" class="mgwpp-tab-content">
            <h2><?php echo esc_html__('Create New Album', 'mini-gallery'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="mgwpp_create_album">
                <input type="hidden" name="mgwpp_album_submit_nonce"
                    value="<?php echo esc_attr(wp_create_nonce('mgwpp_album_submit_nonce')); ?>">
                <table class="form-table">
                    <tr>
                        <td><label for="album_title"><?php echo esc_html__('Album Title:', 'mini-gallery'); ?></label></td>
                        <td><input type="text" id="album_title" name="album_title" required></td>
                    </tr>
                    <tr>
                        <td><label for="album_description"><?php echo esc_html__('Album Description:', 'mini-gallery'); ?></label></td>
                        <td><textarea id="album_description" name="album_description" rows="3"></textarea></td>
                    </tr>
                    <tr>
                        <td><label><?php echo esc_html__('Select Galleries:', 'mini-gallery'); ?></label></td>
                        <td>
                            <?php
                            $galleries = get_posts([
                                'post_type' => 'mgwpp_soora',
                                'numberposts' => -1,
                                'orderby' => 'title',
                                'order' => 'ASC'
                            ]);
                            if ($galleries) {
                                foreach ($galleries as $gallery) {
                                    echo '<label style="display: block; margin-bottom: 5px;">';
                                    echo '<input type="checkbox" name="album_galleries[]" value="' . esc_attr($gallery->ID) . '"> ';
                                    echo esc_html($gallery->post_title);
                                    echo '</label>';
                                }
                            } else {
                                echo '<p>' . esc_html__('No galleries available. Create some galleries first.', 'mini-gallery') . '</p>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <input type="submit" class="button button-primary" value="<?php echo esc_attr__('Create Album', 'mini-gallery'); ?>">
                        </td>
                    </tr>
                </table>
            </form>

            <h2><?php echo esc_html__('Existing Albums', 'mini-gallery'); ?></h2>
            <?php
            $albums = new self();
            $albums->prepare_items(); // WP_List_Table method
            $albums->display(); // WP_List_Table method
            ?>
        </div>
        <?php
    }

    public function get_columns()
    {
        return [
            'cb'               => '<input type="checkbox" />',
            'album_title'      => esc_html__('Album Title', 'mini-gallery'),
            'gallery_count'    => esc_html__('Number of Galleries', 'mini-gallery'),
            'shortcode'        => esc_html__('Shortcode', 'mini-gallery'),
            'actions'          => esc_html__('Actions', 'mini-gallery')
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'album_title':
                return esc_html($item->post_title);
            case 'gallery_count':
                $galleries = get_post_meta($item->ID, '_mgwpp_album_galleries', true);
                return count($galleries);
            case 'shortcode':
                return '<pre>[mgwpp_album id="' . esc_attr($item->ID) . '"]</pre>';
            case 'actions':
                return $this->get_action_links($item->ID);
            default:
                return '';
        }
    }

    public function get_action_links($album_id)
    {
        $edit_link = get_edit_post_link($album_id);
        $delete_link = wp_nonce_url(admin_url('admin-post.php?action=mgwpp_delete_album&album_id=' . $album_id), 'mgwpp_delete_album_' . $album_id);
        return '<a href="' . esc_url($edit_link) . '" class="button button-secondary">' . esc_html__('Edit', 'mini-gallery') . '</a> 
                <a href="' . esc_url($delete_link) . '" class="button button-secondary" onclick="return confirm(\'' . esc_js(__('Are you sure you want to delete this album?', 'mini-gallery')) . '\')">' . esc_html__('Delete', 'mini-gallery') . '</a>';
    }

    public function prepare_items()
    {
        $this->_column_headers = [$this->get_columns(), [], []];
        $this->items = get_posts(['post_type' => 'mgwpp_album', 'numberposts' => -1]);
    }
}
