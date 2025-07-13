<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * MGWPP Admin Core
 * Main admin initialization class
 */
class MGWPP_Admin_Core
{
    private $menu_manager;
    private $asset_manager;
    private $module_loader;

    public function __construct()
    {
        $this->load_dependencies();
        $this->init_components();
    }

    public static function init()
    {
        $instance = new self();
        $instance->run();
    }

    private function load_dependencies()
    {
        // Load required files
        $base_path = dirname(__FILE__);

        // Views
        require_once $base_path . '/views/albums/class-mgwpp-albums-view.php';
        require_once $base_path . '/views/security/class-mgwpp-security-view.php';
        require_once $base_path . '/views/galleries/class-mgwpp-galleries-view.php';
        require_once $base_path . '/views/testimonials/class-mgwpp-testimonials-view.php';
        require_once $base_path . '/views/dashboard/class-mgwpp-dashboard-view.php';
        require_once $base_path . '/views/submodules/class-mgwpp-submodules-view.php';
        require_once $base_path . '/views/extensions/mgwpp-extensions.php';

        // Admin classes
        require_once $base_path . '/class-mgwpp-admin-menu.php';
        require_once $base_path . '/class-mgwpp-admin-assets.php';

        // Module manager (if exists)
        if (class_exists('MGWPP_Module_Manager')) {
            $this->module_loader = new MGWPP_Module_Manager();
        }
    }

    private function init_components()
    {
        // Initialize menu manager
        $this->menu_manager = new MGWPP_Admin_Menu($this->module_loader);

        // Initialize asset manager
        $this->asset_manager = new MGWPP_Admin_Assets();

        // Register hooks
        add_action('admin_menu', [$this->menu_manager, 'register_menus']);
    }

    public function run()
    {
        // Register menus
        add_action('admin_menu', [$this->menu_manager, 'register_menus']);
    }

    public function add_gallery_preview_iframe($post)
    {
        if ('mgwpp_soora' !== $post->post_type) {
            return;
        }

        $gallery_id = absint($post->ID);
        $nonce = wp_create_nonce('mgwpp_preview_nonce');

        $preview_url = add_query_arg(
            [
                'action' => 'mgwpp_preview',
                'gallery_id' => $gallery_id,
                'nonce' => $nonce
            ],
            admin_url('admin-ajax.php')
        );

        echo '<h3>' . esc_html__('Gallery Preview', 'mini-gallery') . '</h3>';
        printf(
            '<iframe src="%s" width="100%%" height="600px" frameborder="0"></iframe>',
            esc_url($preview_url)
        );
    }
}
