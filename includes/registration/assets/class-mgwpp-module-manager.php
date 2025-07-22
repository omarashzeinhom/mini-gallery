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

    // Gallery type file mappings
    private static $gallery_type_files = [
        'single_carousel'     => 'mgwpp-single-gallery/class-mgwpp-single-gallery.php',
        'multi_carousel'      => 'mgwpp-multi-gallery/class-mgwpp-multi-gallery.php',
        'grid'                => 'mgwpp-grid-gallery/class-mgwpp-grid-gallery.php',
        'mega_slider'         => 'mgwpp-mega-slider/class-mgwpp-mega-slider.php',
        'pro_carousel'        => 'mgwpp-pro-carousel/class-mgwpp-pro-carousel.php',
        'neon_carousel'       => 'mgwpp-neon-carousel/class-mgwpp-neon-carousel.php',
        'threed_carousel'     => 'mgwpp-threed-carousel/class-mgwpp-threed-carousel.php',
        'testimonials_carousel' => 'class-mgwpp-testimonial-carousel.php', // Note: This is a file, not directory
        'fullpage_slider'     => 'mgwpp-full-page-slider/class-mgwpp-full-page-slider.php',
        'spotlight_slider'    => 'mgwpp-spotlight-carousel/class-mgwpp-spotlight-carousel.php',
        'albums'              => '', // Handled by main module loader
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

    public static function get_enabled_sub_modules()
    {
        return get_option('mgwpp_enabled_sub_modules', array_keys(self::$sub_modules));
    }

    public function get_sub_modules()
    {
        $modules = self::$sub_modules;
        
        foreach ($modules as $slug => &$module) {
            $module['config']['name'] = esc_html__($module['config']['name'], 'mini-gallery');
            $module['config']['description'] = esc_html__($module['config']['description'], 'mini-gallery');
        }
        
        return $modules;
    }

    public static function load_enabled_modules()
    {
        $enabled = self::get_enabled_modules();
        
        // Load galleries module first
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
                    require_once MG_PLUGIN_PATH . 'includes/admin/class-mgwpp-admin-editors.php';
                    require_once MG_PLUGIN_PATH . 'includes/admin/views/class-mgwpp-visual-editor-view.php';
                    break;
                    
                case 'embed_editor':
                    require_once MG_PLUGIN_PATH . 'includes/admin/views/class-mgwpp-embed-editor-view.php';
                    break;
            }
        }
        
        // Load enabled gallery types
        self::load_enabled_gallery_types();
    }
    
    public static function load_enabled_gallery_types()
    {
        $enabled_types = self::get_enabled_sub_modules();
        
        foreach ($enabled_types as $type) {
            $file = self::get_gallery_type_path($type);
            if ($file && file_exists($file)) {
                require_once $file;
            }
        }
    }
    
    private static function get_gallery_type_path($type)
    {
        if (!isset(self::$gallery_type_files[$type])) {
            return false;
        }
        
        $file = self::$gallery_type_files[$type];
        if (empty($file)) {
            return false;
        }
        
        // Handle testimonials special case (file instead of directory)
        if ($type === 'testimonials_carousel') {
            return MG_PLUGIN_PATH . 'includes/gallery-types/' . $file;
        }
        
        return MG_PLUGIN_PATH . 'includes/gallery-types/' . $file;
    }
    
    public static function reset_to_defaults()
    {
        update_option('mgwpp_enabled_sub_modules', array_keys(self::$sub_modules));
        return array_keys(self::$sub_modules);
    }
}
