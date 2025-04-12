<?php
if (! defined('ABSPATH')) {
    exit;
}
// File: includes/admin/class-mgwpp-admin-assets.php
class MGWPP_Admin_Assets {
    private $asset_config = [];

    public function __construct() {
        $this->setup_assets();
    }

    private function setup_assets() {
        $this->asset_config = [
            'global' => [
                'css' => [
                    'mgwpp-admin' => [
                        'src' => MG_PLUGIN_URL . '/admin/css/mg-admin-styles.css',
                        'deps' => []
                    ]
                ],
                'js' => [
                    'mgwpp-admin' => [
                        'src' => MG_PLUGIN_URL . '/admin/js/mg-admin-scripts.js',
                        'deps' => ['jquery', 'wp-i18n'],
                        'footer' => true
                    ]
                ]
            ],
            'galleries' => [
                'js' => [
                    'mgwpp-galleries' => [
                        'src' => MG_PLUGIN_URL . '/admin/js/galleries.js',
                        'deps' => ['mgwpp-admin'],
                        'footer' => true
                    ]
                ]
            ]
        ];
    }

    public function enqueue_assets($hook) {
        $this->enqueue_global_assets();
        $this->enqueue_section_assets($hook);
    }

    private function enqueue_global_assets() {
        foreach ($this->asset_config['global']['css'] as $handle => $asset) {
            wp_enqueue_style($handle, $asset['src'], $asset['deps']);
        }

        foreach ($this->asset_config['global']['js'] as $handle => $asset) {
            wp_enqueue_script($handle, $asset['src'], $asset['deps'], null, $asset['footer']);
        }

        $this->localize_scripts();
    }

    private function enqueue_section_assets($hook) {
        if (strpos($hook, 'mgwpp_galleries') !== false) {
            wp_enqueue_script('mgwpp-galleries');
        }
    }

    private function localize_scripts() {
        wp_localize_script('mgwpp-admin', 'mgwppAdmin', [
            'nonce' => wp_create_nonce('mgwpp_admin_nonce'),
            'texts' => [
                'confirmDelete' => __('Are you sure you want to delete this item?', 'mini-gallery'),
                'uploadTitle' => __('Select Gallery Images', 'mini-gallery'),
                'uploadButton' => __('Add to Gallery', 'mini-gallery')
            ]
        ]);
    }
}