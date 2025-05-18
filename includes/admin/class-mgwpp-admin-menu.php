<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Admin_Menu
{
    private $view_classes = [];
    private $modules_view;
    private $galleries_view; // Add instance variables
    private $albums_view;
    private $testimonials_view;
    private $security_view;
    private $settings_view;

    // In class-mgwpp-admin-menu.php
    public function __construct($module_loader)
    {
        // Get gallery data
        $list_table = new MGWPP_Galleries_List_Table();
        $list_table->prepare_items();

        // Initialize view with items
        $this->galleries_view = new MGWPP_Galleries_View($list_table->items);

        // Initialize other views
        $this->modules_view = new MGWPP_Modules_View($module_loader);
        $this->albums_view = new MGWPP_Albums_View();
        $this->testimonials_view = new MGWPP_Testimonials_View();
        $this->security_view = new MGWPP_Security_View();
        $this->settings_view = new MGWPP_Settings_View();
    }

    public function register_menus()
    {
        $this->setup_menu_structure();

        // Main menu
        add_menu_page(
            __('Mini Gallery', 'mini-gallery'),
            __('Mini Gallery', 'mini-gallery'),
            'manage_options',
            'mgwpp_dashboard',
            [$this, 'render_dashboard'],
            MG_PLUGIN_URL . '/includes/admin/images/logo/mgwpp-logo-panel.png',
            20
        );

        // Submenus
        add_submenu_page(
            'mgwpp_dashboard',
            __('Dashboard', 'mini-gallery'),
            __('Dashboard', 'mini-gallery'),
            'manage_options',
            'mgwpp_dashboard',
            [$this, 'render_dashboard']
        );

        $this->register_remaining_submenus();
    }

    private function setup_menu_structure()
    {
        $this->view_classes = [
            'galleries' => [
                'page_title' => __('Galleries', 'mini-gallery'),
                'callback' => [$this->galleries_view, 'render'],
                'capability' => 'edit_posts'
            ],
            'albums' => [
                'page_title' => __('Albums', 'mini-gallery'),
                'callback' => [$this->albums_view, 'render'],
                'capability' => 'edit_posts'
            ],
            'testimonials' => [
                'page_title' => __('Testimonials', 'mini-gallery'),
                'callback' => [$this->testimonials_view, 'render'],
                'capability' => 'manage_options'
            ],
            'security' => [
                'page_title' => __('Security', 'mini-gallery'),
                'callback' => [$this->security_view, 'render'],
                'capability' => 'manage_options'
            ],
            'modules' => [
                'page_title' => __('Modules', 'mini-gallery'),
                'callback' => [$this->modules_view, 'render'],
                'capability' => 'manage_options'
            ],
            'settings' => [
                'page_title' => __('Settings', 'mini-gallery'), // Corrected key
                'callback' => [$this->settings_view, 'render'],
                'capability' => 'manage_options'
            ],
        ];
    }

    private function register_remaining_submenus()
    {
        foreach ($this->view_classes as $slug => $menu_item) {
            add_submenu_page(
                'mgwpp_dashboard',
                $menu_item['page_title'],
                $menu_item['page_title'],
                $menu_item['capability'],
                'mgwpp_' . $slug,
                $menu_item['callback']
            );
        }
    }

    public function render_dashboard()
    {
        MGWPP_Dashboard_View::render_dashboard();
    }
}
