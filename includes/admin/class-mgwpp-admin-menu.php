<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Admin_Menu
{
    private $view_classes = [];
    private $modules_view;
    
    public function __construct($module_loader) {
        $this->modules_view = new MGWPP_Modules_View($module_loader);
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
            MG_PLUGIN_URL . '/admin/images/mgwpp-logo-panel.png',
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
                'callback' => [MGWPP_Galleries_View::class, 'render'],
                'capability' => 'edit_posts'
            ],
            'albums' => [
                'page_title' => __('Albums', 'mini-gallery'),
                'callback' => [MGWPP_Albums_View::class, 'render'],
                'capability' => 'edit_posts'
            ],
            'testimonials' => [
                'page_title' => __('Testimonials', 'mini-gallery'),
                'callback' => [MGWPP_Testimonials_View::class, 'render'],
                'capability' => 'manage_options'
            ],
            'security' => [
                'page_title' => __('Security', 'mini-gallery'),
                'callback' => [MGWPP_Security_View::class, 'render'],
                'capability' => 'manage_options'
            ],
            'modules' => [
                'page_title' => __('Modules', 'mini-gallery'),
                'callback' => [$this->modules_view, 'render'],
                'capability' => 'manage_options'
            ]
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