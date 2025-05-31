<?php
// File: includes/class-mgwpp-module-manager.php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Module_Manager
{
    private static $enabled_modules;

    public static function get_enabled_modules()
    {
        if (self::$enabled_modules === null) {
            $default = [
                'gallery',
                'albums',
                'testimonials',
                'editor',
                'embed_editor'
            ];
            
            self::$enabled_modules = get_option('mgwpp_enabled_modules', $default);
        }
        return self::$enabled_modules;
    }

    public static function load_enabled_modules()
    {
        $enabled = self::get_enabled_modules();
        
        // Load galleries module first as it's foundational
        if (in_array('gallery', $enabled)) {
            require_once MG_PLUGIN_PATH . 'includes/registration/gallery/class-mgwpp-gallery-post-type.php';
            require_once MG_PLUGIN_PATH . 'includes/registration/gallery/class-mgwpp-gallery-capabilities.php';
            require_once MG_PLUGIN_PATH . 'includes/registration/gallery/class-mgwpp-gallery-manager.php';
        }
        
        // Load other modules
        foreach ($enabled as $module) {
            switch ($module) {
                case 'albums':
                    require_once MG_PLUGIN_PATH . 'includes/registration/album/class-mgwpp-album-post-type.php';
                    require_once MG_PLUGIN_PATH . 'includes/registration/album/class-mgwpp-album-capabilities.php';
                    require_once MG_PLUGIN_PATH . 'includes/registration/album/class-mgwpp-album-display.php';
                    break;
                    
                case 'testimonials':
                    require_once MG_PLUGIN_PATH . 'includes/registration/testimonials/class-mgwpp-testimonials-post-type.php';
                    require_once MG_PLUGIN_PATH . 'includes/registration/testimonials/class-mgwpp-testimonials-capabilties.php';
                    require_once MG_PLUGIN_PATH . 'includes/registration/testimonials/class-mgwpp-testimonials-manager.php';
                    break;
                    
                case 'editor':
                    // Visual editor files
                    require_once MG_PLUGIN_PATH . 'includes/admin/class-mgwpp-admin-editors.php';
                    require_once MG_PLUGIN_PATH . 'includes/admin/views/class-mgwpp-visual-editor-view.php';
                    break;
                    
                case 'embed_editor':
                    // Embed editor files
                    require_once MG_PLUGIN_PATH . 'includes/admin/views/class-mgwpp-embed-editor-view.php';
                    break;
            }
        }
    }
}