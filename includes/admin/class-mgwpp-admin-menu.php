<?php
if (! defined('ABSPATH')) {
    exit;
}
// File: includes/admin/class-mgwpp-admin-menu.php
class MGWPP_Admin_Menu {
    private $menu_items = [];

    public function register_menus() {
        $this->setup_menu_structure();
        $this->register_main_menu();
        $this->register_submenus();
    }

    private function setup_menu_structure() {
        $this->menu_items = [
            'dashboard' => [
                'page_title' => __('Dashboard', 'mini-gallery'),
                'capability' => 'manage_options',
                'callback' => [MGWPP_Dashboard::class, 'render'],
                'icon' => MG_PLUGIN_URL . '/admin/images/mgwpp-logo-panel.png'
            ],
            'galleries' => [
                'page_title' => __('Galleries', 'mini-gallery'),
                'capability' => 'manage_options',
                'callback' => [MGWPP_Galleries_Manager::class, 'render']
            ],
            'albums' => [
                'page_title' => __('Albums', 'mini-gallery'),
                'capability' => 'manage_options',
                'callback' => [MGWPP_Albums_Manager::class, 'render']
            ],
            'security' => [
                'page_title' => __('Security', 'mini-gallery'),
                'capability' => 'manage_options',
                'callback' => [MGWPP_Security_Manager::class, 'render']
            ]
        ];
    }

    private function register_main_menu() {
        add_menu_page(
            __('Mini Gallery', 'mini-gallery'),
            __('Mini Gallery', 'mini-gallery'),
            'manage_options',
            'mgwpp_dashboard',
            $this->menu_items['dashboard']['callback'],
            $this->menu_items['dashboard']['icon'],
            20
        );
    }

    private function register_submenus() {
        foreach ($this->menu_items as $slug => $item) {
            if ($slug === 'dashboard') continue;
            
            add_submenu_page(
                'mgwpp_dashboard',
                $item['page_title'],
                $item['page_title'],
                $item['capability'],
                'mgwpp_' . $slug,
                $item['callback']
            );
        }
    }
}