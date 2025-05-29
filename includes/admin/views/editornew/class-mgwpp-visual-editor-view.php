<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Visual_Editor_View {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->init();
    }
    
    public function init() {
        $this->create_gallery_post_type();
        $this->create_database_tables();
       
    }
    
    public function render() {
        $gallery_id = isset($_GET['gallery_id']) ? intval($_GET['gallery_id']) : 0;
        include MG_PLUGIN_PATH . 'includes/admin/views/editornew/templates/admin-page.php';
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'mgwpp_visual-editor') === false) {
            return;
        }
        
        wp_enqueue_script(
            'mg-visual-editor',
            MG_PLUGIN_URL . 'editor/assets/js/visual-editor.js',
            ['jquery', 'wp-util'],
            MGWPP_ASSET_VERSION,
            true
        );
        
        wp_enqueue_style(
            'mg-visual-editor',
            MG_PLUGIN_URL . 'editor/assets/css/visual-editor.css',
            [],
            MGWPP_ASSET_VERSION
        );
        
        wp_localize_script('mg-visual-editor', 'mgAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mg_visual_editor_nonce'),
            'pluginUrl' => MG_PLUGIN_URL
        ]);
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_script(
            'mg-gallery-frontend',
            MG_PLUGIN_URL . 'editor/assets/js/gallery-frontend.js',
            ['jquery'],
            MGWPP_ASSET_VERSION,
            true
        );
        
        wp_enqueue_style(
            'mg-gallery-frontend',
            MG_PLUGIN_URL . 'editor/assets/css/gallery-frontend.css',
            [],
            MGWPP_ASSET_VERSION
        );
    }
    
    public function ajax_save_gallery() {
        check_ajax_referer('mg_visual_editor_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
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
        
        if (!current_user_can('manage_options')) {
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
        
        if (!current_user_can('manage_options')) {
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
    
    public function render_gallery_shortcode($atts) {
        $atts = shortcode_atts([
            'id' => 0,
            'type' => 'grid'
        ], $atts);
        
        $gallery_renderer = new MG_Gallery_Renderer();
        return $gallery_renderer->render_gallery($atts['id'], $atts['type']);
    }
    
    private function create_gallery_post_type() {
        register_post_type('mg_gallery', [
            'labels' => [
                'name' => 'Galleries',
                'singular_name' => 'Gallery'
            ],
            'public' => false,
            'show_ui' => false,
            'supports' => ['title', 'editor']
        ]);
    }
    
    private function create_database_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mg_galleries';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            gallery_data longtext NOT NULL,
            gallery_type varchar(50) DEFAULT 'grid',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}