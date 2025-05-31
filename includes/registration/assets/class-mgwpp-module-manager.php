<?php
// File: includes/class-mgwpp-module-manager.php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Module_Manager
{
    private static $enabled_modules;
    private static $sub_modules = [
        'single_carousel' => [
            'config' => [
                'name' => 'Single Carousel',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'A carousel gallery with a single item at a time.',
            ],
        ],
        'multi_carousel' => [
            'config' => [
                'name' => 'Multi Carousel',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'A carousel gallery with multiple items at a time.',
            ],
        ],
        'grid' => [
            'config' => [
                'name' => 'Grid Gallery',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'A grid layout gallery.',
            ],
        ],
        'mega_slider' => [
            'config' => [
                'name' => 'Mega Slider',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'A mega slider gallery.',
            ],
        ],
        'pro_carousel' => [
            'config' => [
                'name' => 'Pro Carousel',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'A professional carousel gallery.',
            ],
        ],
        'neon_carousel' => [
            'config' => [
                'name' => 'Neon Carousel',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'A neon themed carousel gallery.',
            ],
        ],
        'threed_carousel' => [
            'config' => [
                'name' => '3D Carousel',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'A 3D carousel gallery.',
            ],
        ],
        'testimonials_carousel' => [
            'config' => [
                'name' => 'Testimonials Carousel',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'A testimonials carousel.',
            ],
        ],
        'lightbox' => [
            'config' => [
                'name' => 'Lightbox',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'A lightbox for images.',
            ],
        ],
        'fullpage_slider' => [
            'config' => [
                'name' => 'Fullpage Slider',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'A fullpage slider gallery.',
            ],
        ],
        'spotlight_slider' => [
            'config' => [
                'name' => 'Spotlight Slider',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'A spotlight slider gallery.',
            ],
        ],
        'albums' => [
            'config' => [
                'name' => 'Albums',
                'version' => '1.0.0',
                'author' => 'Mini Gallery Team',
                'description' => 'Gallery albums.',
            ],
        ],
    ];

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

    public function get_sub_modules() {
        $modules = self::$sub_modules;
        
        // Apply translations at runtime
        foreach ($modules as $slug => &$module) {
            $module['config']['name'] = __($module['config']['name'], 'mini-gallery');
            $module['config']['description'] = __($module['config']['description'], 'mini-gallery');
        }
        
        return $modules;
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