<?php
if (!defined('ABSPATH')) {
    exit;
}

// File: includes/admin/class-mgwpp-admin-core.php
class MGWPP_Admin_Core
{
    public function __construct() {
        $this->load_dependencies();
        $this->module_loader = new MGWPP_Module_Loader(); // Store as property
        $this->menu_manager  = new MGWPP_Admin_Menu($this->module_loader);
        $this->init_components();
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
        require_once __DIR__ . '/class-mgwpp-admin-menu.php';
        require_once __DIR__ . '/class-mgwpp-admin-assets.php';
        require_once __DIR__ . '/class-mgwpp-module-loader.php';
        require_once __DIR__ . '/class-mgwpp-admin-metaboxes.php';
        require_once __DIR__ . '/class-mgwpp-admin-edit-gallery.php';

        // Views
        require_once __DIR__ . '/views/class-mgwpp-albums-view.php';
        require_once __DIR__ . '/views/class-mgwpp-security-view.php';
        require_once __DIR__ . '/views/class-mgwpp-galleries-view.php';
        require_once __DIR__ . '/views/class-mgwpp-testimonials-view.php';
        require_once __DIR__ . '/views/dashboard/class-mgwpp-dashboard-view.php';
        require_once __DIR__ . '/views/class-mgwpp-modules-view.php';
    }

    private function init_components()
    {
        add_action('admin_menu', [$this->menu_manager, 'register_menus']);
        add_action('admin_menu', [$this, 'init_view_classes']);
    }

     public function init_assets()
    {
        $this->asset_manager = new MGWPP_Admin_Assets();
    }

    public function init_view_classes()
    {
        // Initialize views with required dependencies
        new MGWPP_Galleries_View($this->asset_manager);
        new MGWPP_Security_View($this->asset_manager);
        new MGWPP_Albums_View($this->asset_manager);
        new MGWPP_Dashboard_View($this->asset_manager);
        
        // Initialize modules view with module loader
        new MGWPP_Modules_View($this->module_loader);
    }

    public function run()
    {
        // Register menus first
        add_action('admin_menu', [$this->menu_manager, 'register_menus']);

        // Initialize views AFTER menu registration
        add_action('admin_menu', [$this, 'init_view_classes']);
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
