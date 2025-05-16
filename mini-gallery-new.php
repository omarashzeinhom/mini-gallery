<?php
/*
Plugin Name: Mini Gallery
Plugin URI: https://wordpress.org/plugins/mini-gallery/
Description: A Fully Open Source WordPress Gallery, Slider and Carousel Alternative for Premium Plugin Sliders. Choose one of our 10 Default Ones, or create your own.
Version: 1.4
Author: AGWS | And Go Web Solutions
Author URI: https://andgowebsolutions.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: mini-gallery
Domain Path: /languages

Contribute: https://github.com/omarashzeinhom/mini-gallery-dev/
Docs: https://minigallery.andgowebsolutions.com/docs/
*/

if (!defined('ABSPATH')) exit;

class Plugin_Core
{
    private static $instance;

    // Core components
    private $post_types;
    private $capabilities;
    private $shortcodes;
    private $admin_core;
    private $integrations;

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_components();
        $this->register_hooks();
    }

    private function define_constants()
    {
        define('MG_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('MG_PLUGIN_URL', plugins_url('', __FILE__));
        define('MGWPP_ASSET_VERSION', filemtime(__FILE__));
    }

    private function load_dependencies()
    {
        require_once MG_PLUGIN_PATH . 'includes/class-autoloader.php';
        Autoloader::register();
    }

    private function init_components()
    {
        // Initialize core components
        $this->post_types = new Post_Type_Manager();
        $this->capabilities = new Capability_Manager();
        $this->shortcodes = new Shortcode_Manager();
        $this->admin_core = new Admin\Admin_Core();
        $this->integrations = new Integration_Manager();
    }

    private function register_hooks()
    {
        register_activation_hook(__FILE__, [Activation_Handler::class, 'activate']);
        register_deactivation_hook(__FILE__, [Deactivation_Handler::class, 'deactivate']);
        register_uninstall_hook(__FILE__, [Uninstall_Handler::class, 'uninstall']);

        add_action('init', [$this, 'init_plugin']);
        add_action('after_setup_theme', [$this, 'add_theme_support']);
        add_filter('template_include', [Template_Loader::class, 'custom_templates']);
    }

    public function init_plugin()
    {
        $this->post_types->register_all();
        $this->capabilities->setup_all();
        $this->shortcodes->register_all();
        $this->integrations->init();
    }

    public function add_theme_support()
    {
        if (!current_theme_supports('post-thumbnails')) {
            add_theme_support('post-thumbnails');
        }
    }
}

// Main initialization
Plugin_Core::get_instance();

// Helper function for external access
function mgwpp()
{
    return Plugin_Core::get_instance();
}
