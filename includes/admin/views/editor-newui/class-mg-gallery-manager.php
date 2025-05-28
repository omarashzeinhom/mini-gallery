<?php
/**
 * Gallery Manager Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class MG_Gallery_Manager {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'mg_galleries';
    }
    
    public function save_gallery($gallery_id, $gallery_data) {
        global $wpdb;
        
        $data = array(
            'title' => sanitize_text_field($gallery_data['title']),
            'gallery_data' => wp_json_encode($gallery_data),
            'gallery_type' => sanitize_text_field($gallery_data['type']),
            'updated_at' => current_time('mysql')
        );
        
        if ($gallery_id > 0) {
            // Update existing gallery
            $result = $wpdb->update(
                $this->table_name,
                $data,
                array('id' => $gallery_id),
                array('%s', '%s', '%s', '%s'),
                array('%d')
            );
            
            return array(
                'gallery_id' => $gallery_id,
                'message' => 'Gallery updated successfully'
            );
        } else {
            // Create new gallery
            $data['created_at'] = current_time('mysql');
            
            $result = $wpdb->insert(
                $this->table_name,
                $data,
                array('%s', '%s', '%s', '%s', '%s')
            );
            
            return array(
                'gallery_id' => $wpdb->insert_id,
                'message' => 'Gallery created successfully'
            );
        }
    }
    
    public function load_gallery($gallery_id) {
        global $wpdb;
        
        $gallery = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $gallery_id
            )
        );
        
        if (!$gallery) {
            return array(
                'title' => 'New Gallery',
                'type' => 'grid',
                'items' => array(),
                'settings' => array()
            );
        }
        
        return json_decode($gallery->gallery_data, true);
    }
    
    public function get_all_galleries() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT id, title, gallery_type, created_at, updated_at FROM {$this->table_name} ORDER BY updated_at DESC"
        );
    }
    
    public function delete_gallery($gallery_id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $gallery_id),
            array('%d')
        );
    }
    
    public function duplicate_gallery($gallery_id) {
        global $wpdb;
        
        $gallery = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $gallery_id
            )
        );
        
        if (!$gallery) {
            return false;
        }
        
        $gallery_data = json_decode($gallery->gallery_data, true);
        $gallery_data['title'] = $gallery->title . ' (Copy)';
        
        return $this->save_gallery(0, $gallery_data);
    }
}
