<?php
if (!defined('ABSPATH')) {
    exit();
}

class MGWPP_Settings
{
    private static $modules = [];

    public static function init()
    {
        // Default module configuration
        $default_modules = [
            'gallery' => true,  // Enabled by default
            'album' => false,
            'testimonial' => false,
        ];

        // Allow users to filter the default configuration
        self::$modules = apply_filters('mgwpp_modules_config', $default_modules);

        // Initialize active modules
        add_action('plugins_loaded', [__CLASS__, 'initialize_modules']);
    }

    public static function initialize_modules()
    {
        foreach (self::$modules as $module => $is_active) {
            if ($is_active) {
                self::load_module($module);
            }
        }
    }

    private static function load_module($module)
    {
        $method_name = "initialize_{$module}";
        if (method_exists(__CLASS__, $method_name)) {
            self::$method_name();
        }
    }

    private static function initialize_album()
    {
        require_once MG_PLUGIN_URL . 'modules/class-album-post-type.php';
        MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
        MGWPP_Album_Capabilities::mgwpp_album_capabilities();
    }

    private static function initialize_testimonial()
    {
        require_once MG_PLUGIN_URL . 'modules/class-testimonial-capabilities.php';
        MGWPP_Testimonial_Capabilities::mgwpp_testimonial_capabilities();
    }
}

if (is_admin()) {
    new MGWPP_Settings();
}
