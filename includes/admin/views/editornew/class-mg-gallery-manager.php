<?php
/**
 * Simple Integration - Modified Gallery Manager to work with mgwpp_soora post type
 */

if (!defined('ABSPATH')) {
    exit;
}

class MG_Gallery_Manager {
    
    public function save_gallery($gallery_id, $gallery_data) {
        $post_data = array(
            'post_title' => sanitize_text_field($gallery_data['title']),
            'post_type' => 'mgwpp_soora',
            'post_status' => 'publish',
            'meta_input' => array(
                '_mg_gallery_data' => wp_json_encode($gallery_data),
                '_mg_gallery_type' => sanitize_text_field($gallery_data['type']),
                '_mg_is_visual_editor' => true
            )
        );
        
        if ($gallery_id > 0) {
            $post_data['ID'] = $gallery_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
        }
        
        return array(
            'gallery_id' => $result,
            'message' => $gallery_id > 0 ? 'Gallery updated successfully' : 'Gallery created successfully'
        );
    }
    
    public function load_gallery($gallery_id) {
        if ($gallery_id == 0) {
            return array(
                'title' => 'New Gallery',
                'type' => 'grid',
                'items' => array(),
                'settings' => array()
            );
        }
        
        $gallery_data_json = get_post_meta($gallery_id, '_mg_gallery_data', true);
        
        if (empty($gallery_data_json)) {
            $post = get_post($gallery_id);
            return array(
                'title' => $post ? $post->post_title : 'New Gallery',
                'type' => 'grid',
                'items' => array(),
                'settings' => array()
            );
        }
        
        return json_decode($gallery_data_json, true);
    }
    
    public function get_all_galleries() {
        $posts = get_posts(array(
            'post_type' => 'mgwpp_soora',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_mg_is_visual_editor',
                    'value' => true,
                    'compare' => '='
                )
            )
        ));
        
        $galleries = array();
        foreach ($posts as $post) {
            $galleries[] = (object) array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'gallery_type' => get_post_meta($post->ID, '_mg_gallery_type', true),
                'created_at' => $post->post_date,
                'updated_at' => $post->post_modified
            );
        }
        
        return $galleries;
    }
    
    public function delete_gallery($gallery_id) {
        return wp_delete_post($gallery_id, true);
    }
    
    public function duplicate_gallery($gallery_id) {
        $post = get_post($gallery_id);
        if (!$post) {
            return false;
        }
        
        $gallery_data_json = get_post_meta($gallery_id, '_mg_gallery_data', true);
        $gallery_data = json_decode($gallery_data_json, true);
        $gallery_data['title'] = $post->post_title . ' (Copy)';
        
        return $this->save_gallery(0, $gallery_data);
    }
}

/**
 * Add Visual Editor as submenu to existing galleries
 */
class MG_Admin_Integration {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_visual_editor_submenu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_mg_save_gallery', array($this, 'ajax_save_gallery'));
        add_action('wp_ajax_mg_load_gallery', array($this, 'ajax_load_gallery'));
        add_action('wp_ajax_mg_upload_media', array($this, 'ajax_upload_media'));
        add_action('wp_ajax_mg_delete_gallery', array($this, 'ajax_delete_gallery'));
        add_action('wp_ajax_mg_duplicate_gallery', array($this, 'ajax_duplicate_gallery'));
        add_action('wp_ajax_mg_get_media_library', array($this, 'ajax_get_media_library'));
    }

    
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'mg-visual-editor') === false && strpos($hook, 'mg-all-galleries') === false) {
            return;
        }
        
        wp_enqueue_script(
            'mg-visual-editor',
            MG_PLUGIN_URL . 'editornew/assets/js/visual-editor.js',
            array('jquery', 'wp-util'),
            MGWPP_ASSET_VERSION,
            true
        );
        
        wp_enqueue_style(
            'mg-visual-editor',
            MG_PLUGIN_URL . 'editornew/assets/css/visual-editor.css',
            array(),
            MGWPP_ASSET_VERSION
        );
        
        wp_localize_script('mg-visual-editor', 'mgAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mg_visual_editor_nonce'),
            'pluginUrl' => MG_PLUGIN_URL
        ));
    }
    
    public function visual_editor_page() {
        $gallery_id = isset($_GET['gallery_id']) ? intval($_GET['gallery_id']) : 0;
        $media_handler = new MG_Media_Handler();
        $media_items = $media_handler->get_media_library();
        ?>
        <div class="wrap">
            <h1>Visual Gallery Editor</h1>
            <div id="mg-visual-editor-app">
                <div class="editor-loading">
                    <p>Loading Visual Editor...</p>
                </div>
            </div>
            <script type="text/javascript">
                window.mgEditorData = {
                    galleryId: <?php echo intval($gallery_id); ?>,
                    mediaItems: <?php echo wp_json_encode($media_items); ?>,
                    nonce: '<?php echo wp_create_nonce('mg_visual_editor_nonce'); ?>'
                };
            </script>
        </div>
        <?php
    }
    
    public function all_galleries_page() {
        $gallery_manager = new MG_Gallery_Manager();
        $galleries = $gallery_manager->get_all_galleries();
        ?>
        <div class="wrap">
            <h1>All Visual Galleries</h1>
            <div class="mg-galleries-header">
                <a href="<?php echo admin_url('admin.php?page=mg-visual-editor'); ?>" class="button button-primary">
                    Create New Visual Gallery
                </a>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Shortcode</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($galleries)): ?>
                        <tr>
                            <td colspan="6">No visual galleries found. <a href="<?php echo admin_url('admin.php?page=mg-visual-editor'); ?>">Create your first visual gallery</a>.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($galleries as $gallery): ?>
                            <tr>
                                <td><strong><?php echo esc_html($gallery->title); ?></strong></td>
                                <td><?php echo esc_html(ucfirst($gallery->gallery_type)); ?></td>
                                <td><?php echo esc_html(date('M j, Y', strtotime($gallery->created_at))); ?></td>
                                <td><?php echo esc_html(date('M j, Y', strtotime($gallery->updated_at))); ?></td>
                                <td>
                                    <code>[mg_gallery id="<?php echo intval($gallery->id); ?>"]</code>
                                    <button class="button button-small copy-shortcode" data-shortcode='[mg_gallery id="<?php echo intval($gallery->id); ?>"]'>Copy</button>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=mg-visual-editor&gallery_id=' . intval($gallery->id)); ?>" class="button button-small">Edit</a>
                                    <button class="button button-small duplicate-gallery" data-gallery-id="<?php echo intval($gallery->id); ?>">Duplicate</button>
                                    <button class="button button-small button-link-delete delete-gallery" data-gallery-id="<?php echo intval($gallery->id); ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.copy-shortcode').on('click', function() {
                var shortcode = $(this).data('shortcode');
                navigator.clipboard.writeText(shortcode).then(function() {
                    alert('Shortcode copied to clipboard!');
                });
            });
            
            $('.duplicate-gallery').on('click', function() {
                var galleryId = $(this).data('gallery-id');
                if (confirm('Are you sure you want to duplicate this gallery?')) {
                    $.post(ajaxurl, {
                        action: 'mg_duplicate_gallery',
                        gallery_id: galleryId,
                        nonce: '<?php echo wp_create_nonce('mg_visual_editor_nonce'); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error duplicating gallery');
                        }
                    });
                }
            });
            
            $('.delete-gallery').on('click', function() {
                var galleryId = $(this).data('gallery-id');
                if (confirm('Are you sure you want to delete this gallery? This action cannot be undone.')) {
                    $.post(ajaxurl, {
                        action: 'mg_delete_gallery',
                        gallery_id: galleryId,
                        nonce: '<?php echo wp_create_nonce('mg_visual_editor_nonce'); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error deleting gallery');
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    // AJAX handlers
    public function ajax_save_gallery() {
        check_ajax_referer('mg_visual_editor_nonce', 'nonce');
        
        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_die('Unauthorized');
        }
        
        $gallery_data = json_decode(stripslashes($_POST['gallery_data']), true);
        $gallery_id = intval($_POST['gallery_id']);
        
        $gallery_manager = new MG_Gallery_Manager();
        $result = $gallery_manager->save_gallery($gallery_id, $gallery_data);
        
        wp_send_json_success($result);
    }
    
    public function ajax_load_gallery() {
        check_ajax_referer('mg_visual_editor_nonce', 'nonce');
        
        $gallery_id = intval($_POST['gallery_id']);
        $gallery_manager = new MG_Gallery_Manager();
        $gallery_data = $gallery_manager->load_gallery($gallery_id);
        
        wp_send_json_success($gallery_data);
    }
    
    public function ajax_upload_media() {
        check_ajax_referer('mg_visual_editor_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die('Unauthorized');
        }
        
        $media_handler = new MG_Media_Handler();
        $result = $media_handler->handle_upload();
        
        wp_send_json_success($result);
    }
    
    public function ajax_delete_gallery() {
        check_ajax_referer('mg_visual_editor_nonce', 'nonce');
        
        if (!current_user_can('delete_mgwpp_sooras')) {
            wp_die('Unauthorized');
        }
        
        $gallery_id = intval($_POST['gallery_id']);
        $gallery_manager = new MG_Gallery_Manager();
        $result = $gallery_manager->delete_gallery($gallery_id);
        
        if ($result) {
            wp_send_json_success('Gallery deleted successfully');
        } else {
            wp_send_json_error('Error deleting gallery');
        }
    }
    
    public function ajax_duplicate_gallery() {
        check_ajax_referer('mg_visual_editor_nonce', 'nonce');
        
        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_die('Unauthorized');
        }
        
        $gallery_id = intval($_POST['gallery_id']);
        $gallery_manager = new MG_Gallery_Manager();
        $result = $gallery_manager->duplicate_gallery($gallery_id);
        
        if ($result) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error('Error duplicating gallery');
        }
    }
    
    public function ajax_get_media_library() {
        check_ajax_referer('mg_visual_editor_nonce', 'nonce');
        
        $media_handler = new MG_Media_Handler();
        $media_items = $media_handler->get_media_library();
        
        wp_send_json_success($media_items);
    }
}

// Initialize the integration
new MG_Admin_Integration();


?>