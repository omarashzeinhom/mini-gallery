<?php
if (!defined('ABSPATH')) exit;

// File: includes/admin/class-mgwpp-admin-core.php
class MGWPP_Admin_Core
{
    private $menu_manager;
    private $asset_manager;
    private $module_loader;

    public static function init()
    {
        $instance = new self();
        $instance->run();
    }

    public function __construct()
    {
        $this->load_dependencies();
        $this->init_components();
    }

    private function load_dependencies()
    {
        require_once __DIR__ . '/class-mgwpp-admin-menu.php';
        require_once __DIR__ . '/class-mgwpp-admin-assets.php';
        require_once __DIR__ . '/class-mgwpp-module-loader.php';
        // Views
        require_once __DIR__ . '/views/class-mgwpp-albums-view.php';
        require_once __DIR__ . '/views/class-mgwpp-security-view.php';
        require_once __DIR__ . '/views/class-mgwpp-galleries-view.php';
        require_once __DIR__ . '/views/class-mgwpp-testimonials-view.php';
        require_once __DIR__ . '/views/class-mgwpp-dashboard-view.php';
    }


    private function init_components()
    {
        $this->menu_manager = new MGWPP_Admin_Menu();
        $this->asset_manager = new MGWPP_Admin_Assets();
        $this->module_loader = new MGWPP_Module_Loader();
    }

    public function run()
    {
        // Register menus and assets
        add_action('admin_menu', [$this->menu_manager, 'register_menus']);
        add_action('admin_enqueue_scripts', [$this->asset_manager, 'enqueue_assets']);

        // Initialize your view classes
        add_action('admin_init', [$this, 'init_view_classes']);
    }

    public function init_view_classes()
    {
        // Initialize view classes with their dependencies
        new MGWPP_Galleries_View($this->asset_manager);
        new MGWPP_Security_View($this->asset_manager);
        new MGWPP_Albums_View($this->asset_manager);
    }
}
