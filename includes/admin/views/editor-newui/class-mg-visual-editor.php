<?php
/**
 * Main Visual Editor Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class MG_Visual_Editor {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'mg-visual-editor') === false) {
            return;
        }
        
        wp_enqueue_script(
            'mg-visual-editor',
            MG_PLUGIN_URL . 'editor/assets/js/visual-editor.js',
            array('jquery', 'wp-util'),
            MGWPP_ASSET_VERSION,
            true
        );
        
        wp_enqueue_style(
            'mg-visual-editor',
            MG_PLUGIN_URL . 'editor/assets/css/visual-editor.css',
            array(),
            MGWPP_ASSET_VERSION
        );
        
        wp_localize_script('mg-visual-editor', 'mgAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mg_visual_editor_nonce'),
            'pluginUrl' => MG_PLUGIN_URL
        ));
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_script(
            'mg-gallery-frontend',
            MG_PLUGIN_URL . 'editor/assets/js/gallery-frontend.js',
            array('jquery'),
            MGWPP_ASSET_VERSION,
            true
        );
        
        wp_enqueue_style(
            'mg-gallery-frontend',
            MG_PLUGIN_URL . 'editor/assets/css/gallery-frontend.css',
            array(),
            MGWPP_ASSET_VERSION
        );
    }
    
    public function admin_page() {
        $gallery_id = isset($_GET['gallery_id']) ? intval($_GET['gallery_id']) : 0;
        include MG_PLUGIN_PATH . 'editor/templates/admin-page.php';
    }
    
    public function all_galleries_page() {
        include MG_PLUGIN_PATH . 'editor/templates/all-galleries.php';
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
    
    public function render_gallery_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'type' => 'grid'
        ), $atts);
        
        $gallery_renderer = new MG_Gallery_Renderer();
        return $gallery_renderer->render_gallery($atts['id'], $atts['type']);
    }

}
