<?php 
if (!defined('ABSPATH')){
    exit;
}

class MGWPP_Editor_Core {
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_menu', [$this, 'add_editor_page']);
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'mgwpp-editor-css',
            MG_PLUGIN_URL . '/includes/admin/css/mgwpp-editor.css',
            [],
            MGWPP_ASSET_VERSION
        );

        wp_enqueue_script(
            'mgwpp-editor-js',
            MG_PLUGIN_URL . '/includes/admin/js/mgwpp-editor.js',
            ['jquery', 'wp-i18n', 'wp-blocks', 'wp-element'],
            MGWPP_ASSET_VERSION,
            true
        );
    }

    public function add_editor_page() {
        add_submenu_page(
            'mgwpp-dashboard',
            __('Gallery Editor', 'mini-gallery'),
            __('Editor', 'mini-gallery'),
            'edit_mgwpp_galleries',
            'mgwpp-editor',
            [$this, 'render_editor']
        );
    }

    public function render_editor() {
        include_once plugin_dir_path(__FILE__) . 'includes/admin/views/editor/main.php';
    }
}