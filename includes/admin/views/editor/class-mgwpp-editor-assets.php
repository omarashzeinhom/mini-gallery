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

        // CDN Libraries
        wp_register_script(
            'interact',
            'https://cdn.jsdelivr.net/npm/@interactjs/interactjs/index.min.js',
            [],
            null,
            true
        );


        // CSS
        wp_enqueue_style(
            'mgwpp-editor-core-css',
            MG_PLUGIN_URL . '/includes/admin/editor/assets/css/editor-core.css',
            [],
            MGWPP_ASSET_VERSION
        );

        // JS
        wp_enqueue_script(
            'mgwpp-editor-core-js',
            MG_PLUGIN_URL . '/includes/admin/editor/assets/js/editor-core.js',
            ['jquery', 'wp-i18n', 'interact'],
            MGWPP_ASSET_VERSION,
            true
        );
        add_filter('script_loader_tag', function ($tag, $handle, $src) {
            if ($handle === 'interact') {
                return '<script type="module" src="' . esc_url($src) . '"></script>';
            }
            return $tag;
        }, 10, 3);
        add_filter('script_loader_tag', function ($tag, $handle, $src) {
            if ($handle === 'mgwpp-editor-core-js') {
                return '<script type="module" src="' . esc_url($src) . '"></script>';
            }
            return $tag;
        }, 10, 3);
  }
}
