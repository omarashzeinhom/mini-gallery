<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Editor_Assets {
    public function register() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue() {
        // CSS
        wp_enqueue_style(
            'mgwpp-editor-core',
            MG_PLUGIN_URL . '/includes/admin/editor/assets/css/editor-core.css',
            [],
            MGWPP_ASSET_VERSION
        );
        
        // JS
        wp_enqueue_script(
            'mgwpp-editor-core',
            MG_PLUGIN_URL . '/includes/admin/editor/assets/js/editor-core.js',
            ['jquery', 'wp-i18n', 'interact'],
            MGWPP_ASSET_VERSION,
            true
        );
    }
}