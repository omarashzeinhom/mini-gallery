<?php
if (!defined('ABSPATH')) {
    exit;
}

// File: includes/admin/class-mgwpp-admin-core.php
class MGWPP_Admin_Core
{
    public function __construct()
    {
        $this->load_dependencies();
        $this->menu_manager  = new MGWPP_Admin_Menu();
        $this->asset_manager = new MGWPP_Admin_Assets();  // ← instantiate here, not in a hook
        add_action('admin_menu', [$this->menu_manager, 'register_menus']);
        add_action('admin_menu', [$this,             'init_view_classes']);
    }

    private $menu_manager;
    private $asset_manager;
    private $module_loader;

    public static function init()
    {
        $instance = new self();
        $instance->run();
    }




    private function load_dependencies()
    {
        // Load files but don't initialize yet
        include_once __DIR__ . '/class-mgwpp-admin-menu.php';
        include_once __DIR__ . '/class-mgwpp-admin-assets.php';
        include_once __DIR__ . '/class-mgwpp-module-loader.php';
        include_once __DIR__ . '/class-mgwpp-admin-metaboxes.php';
        include_once __DIR__ . '/class-mgwpp-admin-edit-gallery.php';

        // Views
        include_once __DIR__ . '/views/class-mgwpp-albums-view.php';
        include_once __DIR__ . '/views/class-mgwpp-security-view.php';
        include_once __DIR__ . '/views/class-mgwpp-galleries-view.php';
        include_once __DIR__ . '/views/class-mgwpp-testimonials-view.php';
        include_once __DIR__ . '/views/class-mgwpp-dashboard-view.php';
    }

    private function init_components()
    {
        // Initialize components that hook into WordPress actions
        $this->menu_manager = new MGWPP_Admin_Menu();
        $this->module_loader = new MGWPP_Module_Loader();

        // Initialize assets manager LAST and hook it properly
        add_action('admin_enqueue_scripts', [$this, 'init_assets']);
    }

    public function init_assets()
    {
        $this->asset_manager = new MGWPP_Admin_Assets();
        // Add force-load hook
        //add_action('admin_enqueue_scripts', [$this->asset_manager, 'force_load_dashboard_styles'], 20);
    }
    
    public function run()
    {
        // Register menus first
        add_action('admin_menu', [$this->menu_manager, 'register_menus']);

        // Initialize views AFTER menu registration
        add_action('admin_menu', [$this, 'init_view_classes']);
    }

    public function init_view_classes()
    {
        $asset_manager = $this->asset_manager;
        // Initialize view classes with their dependencies
        new MGWPP_Galleries_View($this->asset_manager);
        new MGWPP_Security_View($this->asset_manager);
        new MGWPP_Albums_View($this->asset_manager);
        new MGWPP_Dashboard_View($asset_manager);
    }

    public function add_gallery_preview_iframe($post)
    {
        // Only show the preview for gallery posts (you may need to adjust the post type check)
        if ('mgwpp_soora' === $post->post_type) {
            // Output the iframe
            $gallery_id = $post->ID;
            echo '<h3>Gallery Preview</h3>';
            echo '<iframe src="' . esc_url(
                add_query_arg(
                    [
                        'action' => 'mgwpp_preview',
                        'gallery_id' => absint($gallery_id)
                    ],
                    admin_url('admin-ajax.php')
                )
            ) . '" width="100%" height="600px" frameborder="0"></iframe>';
        }
    }
}
