<?php
// File: includes/admin/class-mgwpp-module-loader.php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Module_Loader
{
    private $core_modules = [];
    private $custom_modules = [];
    private $cdn_modules = [];
    private $registered_modules = [];

    public function __construct()
    {
        $this->load_core_modules();
        $this->load_custom_modules();
        $this->load_cdn_modules();
    }

    /**
     * Load core gallery modules from /includes/gallery-types/
     */
    private function load_core_modules()
    {
        $gallery_path = plugin_dir_path(__FILE__) . '../gallery-types/';
        $module_folders = glob($gallery_path . '*', GLOB_ONLYDIR);

        foreach ($module_folders as $folder) {
            $module_name = basename($folder);
            $module_class = 'MGWPP_' . $this->sanitize_module_name($module_name);
            $module_file = $folder . '/class-' . $module_name . '.php';

            if (file_exists($module_file)) {
                include_once $module_file;
                
                if (class_exists($module_class)) {
                    $this->core_modules[$module_name] = [
                        'type' => 'core',
                        'class' => $module_class,
                        'config' => $this->get_module_config($folder),
                        'path' => $folder
                    ];
                }
            }
        }
    }

    /**
     * Load user-created galleries from uploads directory
     */
    private function load_custom_modules()
    {
        $upload_dir = wp_upload_dir();
        $custom_path = $upload_dir['basedir'] . '/mgwpp-galleries/';
        
        if (!file_exists($custom_path)) {
            return;
        }
        
        $custom_folders = glob($custom_path . '*', GLOB_ONLYDIR);

        foreach ($custom_folders as $folder) {
            $module_name = basename($folder);
            $module_class = 'MGWPP_Custom_' . $this->sanitize_module_name($module_name);
            $module_file = $folder . '/class-custom-' . $module_name . '.php';

            if (file_exists($module_file)) {
                include_once $module_file;
                
                if (class_exists($module_class)) {
                    $this->custom_modules[$module_name] = [
                        'type' => 'custom',
                        'class' => $module_class,
                        'config' => $this->get_module_config($folder),
                        'path' => $folder
                    ];
                }
            }
        }
    }

    /**
     * Load modules from CDN (Future implementation)
     */
    private function load_cdn_modules()
    {
        // Implementation for CDN module loading
        // Would use transient caching and remote API check
    }

    /**
     * Get module configuration from module.json
     */
    private function get_module_config($path)
    {
        $config_file = $path . '/module.json';
        
        if (!file_exists($config_file)) {
            return false;
        }
        
        $config = json_decode(file_get_contents($config_file), true);
        return $this->validate_module_config($config) ? $config : false;
    }

    /**
     * Validate module configuration
     */
    private function validate_module_config($config)
    {
        $required = ['name', 'version', 'author', 'assets'];
        foreach ($required as $key) {
            if (!isset($config[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Sanitize module name for class creation
     */
    private function sanitize_module_name($name)
    {
        return str_replace('-', '_', ucfirst($name));
    }

    /**
     * Register all modules with the system
     */
    public function register_modules()
    {
        $this->registered_modules = array_merge(
            $this->core_modules,
            $this->custom_modules,
            $this->cdn_modules
        );

        // Initialize each module
        foreach ($this->registered_modules as $name => $module) {
            if (class_exists($module['class'])) {
                new $module['class']($module['config']);
            }
        }

        // Add filter for third-party extensions
        $this->registered_modules = apply_filters(
            'mgwpp_registered_modules',
            $this->registered_modules
        );
    }

    /**
     * Get all registered modules
     */
    public function get_modules()
    {
        return $this->registered_modules;
    }

    /**
     * Get module-specific assets
     */
    public function get_module_assets($module_name)
    {
        return $this->registered_modules[$module_name]['config']['assets'] ?? [];
    }

    /**
     * Template for new gallery creation
     */
    public function create_custom_gallery_template()
    {
        return [
            'structure' => [
                'php' => 'class-custom-{slug}.php',
                'css' => 'assets/css/style.css',
                'js' => 'assets/js/script.js',
                'config' => 'module.json'
            ],
            'config_template' => [
                "name" => "New Gallery",
                "version" => "1.0.0",
                "author" => "User Name",
                "description" => "Custom gallery type",
                "assets" => [
                    "css" => "style.css",
                    "js" => "script.js"
                ],
                "options" => [
                    "transition_speed" => [
                        "type" => "number",
                        "default" => 0.5,
                        "label" => "Transition Speed"
                    ]
                ]
            ]
        ];
    }
}
