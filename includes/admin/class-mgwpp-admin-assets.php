<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// File: includes/admin/class-mgwpp-admin-assets.php

class MGWPP_Admin_Assets {
    public function enqueue_assets( $hook ) {
        // Only load on our plugin pages (check if $hook contains "mgwpp_")
        if ( strpos( $hook, 'mgwpp_' ) === false ) {
            return;
        }

        // Enqueue WordPress media and thickbox assets
        wp_enqueue_media();
        wp_enqueue_script( 'thickbox' );
        wp_enqueue_style( 'thickbox' );

        // Enqueue Main admin script
        wp_enqueue_script(
            'mgwpp-admin-js',
            MG_PLUGIN_URL . '/admin/js/mg-admin-scripts.js',
            array( 'jquery', 'media-upload', 'thickbox' ),
            filemtime( MG_PLUGIN_PATH . '/admin/js/mg-admin-scripts.js' )
        );

        // Enqueue Admin styles
        wp_enqueue_style(
            'mgwpp-admin-styles',
            MG_PLUGIN_URL . '/admin/css/mg-admin-styles.css',
            array(),
            filemtime( MG_PLUGIN_PATH . '/admin/css/mg-admin-styles.css' )
        );

        // Localize script with translations and other data using matching handle
        wp_localize_script( 'mgwpp-admin-js', 'mgwppData', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'mgwpp_nonce' ),
            'i18n'     => array(
                'select_images'  => __( 'Select Images', 'mini-gallery' ),
                'add_to_gallery' => __( 'Add to Gallery', 'mini-gallery' )
            )
        ) );
    }
}
