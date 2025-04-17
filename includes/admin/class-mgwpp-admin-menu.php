<?php
if (! defined('ABSPATH')) {
    exit;
}
// File: includes/admin/class-mgwpp-admin-menu.php
class MGWPP_Admin_Menu {
    private $view_classes = [];

    public function register_menus() {
        $this->setup_menu_structure();
        
        add_menu_page(
            __('Mini Gallery', 'mini-gallery'),
            __('Mini Gallery', 'mini-gallery'),
            'manage_options',
            'mgwpp_dashboard',
            [$this, 'render_dashboard'],
            MG_PLUGIN_URL . '/admin/images/mgwpp-logo-panel.png',
            20
        );

        $this->register_submenus();
    }

    private function setup_menu_structure() {
        $this->view_classes = [
            'dashboard' => [
                'page_title' => __('Dashboard', 'mini-gallery'),
                'callback' => [MGWPP_Dashboard_View::class, 'render_dashboard'] // Corrected callback
            ],
            'galleries' => [
                'page_title' => __('Galleries', 'mini-gallery'),
                'callback' => [MGWPP_Galleries_View::class, 'render']
            ],
            'albums' => [
                'page_title' => __('Albums', 'mini-gallery'),
                'callback' => [MGWPP_Albums_View::class, 'render']
            ],
            'testimonials' => [
                'page_title' => __('Testimonials', 'mini-gallery'),
                'callback' => [MGWPP_Testimonials_View::class, 'render']
            ],
            'security' => [
                'page_title' => __('Security', 'mini-gallery'),
                'callback' => [MGWPP_Security_View::class, 'render']
            ]
        ];
    }

    private function register_submenus() {
        foreach ($this->view_classes as $slug => $menu_item) {
            add_submenu_page(
                'mgwpp_dashboard',
                $menu_item['page_title'],
                $menu_item['page_title'],
                'manage_options',
                'mgwpp_' . $slug,
                $menu_item['callback']
            );
        }
    }

    public function render_dashboard() {
        // Dashboard rendering logic
    }
}
