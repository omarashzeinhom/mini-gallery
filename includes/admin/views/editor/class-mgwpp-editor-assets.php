<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Editor_Assets
{
    public function register()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_editor_assets']);
    }

    public function enqueue_editor_assets()
    {
        // CSS
        wp_enqueue_style(
            'mgwpp-editor-core-css',
            MG_PLUGIN_URL . '/includes/admin/editor/assets/css/mgwpp-editor-core.css',
            [],
            MGWPP_ASSET_VERSION
        );

        // JS
        wp_enqueue_script(
            'mgwpp-editor-core-js',
            MG_PLUGIN_URL . '/includes/admin/editor/assets/js/editor-core.js',
            ['jquery', 'wp-i18n',],
            MGWPP_ASSET_VERSION,
            true
        );
    }
}
