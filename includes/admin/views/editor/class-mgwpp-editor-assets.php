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
        // Only load assets on editor pages
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'post') {
            return;
        }

        // CSS - Corrected path
        wp_enqueue_style(
            'mgwpp-editor-core-css',
            MG_PLUGIN_URL . 'includes/admin/editor/assets/css/mgwpp-editor-core.css',
            [],
            filemtime(MG_PLUGIN_PATH . 'includes/admin/editor/assets/css/mgwpp-editor-core.css')
        );

        // JS - Corrected path
        wp_enqueue_script(
            'mgwpp-editor-core-js',
            MG_PLUGIN_URL . 'includes/admin/editor/assets/js/mgwpp-editor-core.js',
            ['jquery', 'wp-i18n', 'wp-blocks', 'wp-components', 'wp-editor'],
            filemtime(MG_PLUGIN_PATH . 'includes/admin/editor/assets/js/mgwpp-editor-core.js'),
            true
        );

        // Localize script data
        wp_localize_script('mgwpp-editor-core-js', 'mgwppEditorVars', [
            'pluginUrl' => MG_PLUGIN_URL,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp-editor-nonce')
        ]);
    }
}
