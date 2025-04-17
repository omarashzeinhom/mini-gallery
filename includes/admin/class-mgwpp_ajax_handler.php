<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class MGWPP_Ajax_Handler
 *
 * Handles AJAX requests for Mini Gallery plugin.
 */
class MGWPP_Ajax_Handler {

    /**
     * Initialize all AJAX hooks.
     */
    public static function init() {
        // Hook the preview function into AJAX action
        add_action( 'wp_ajax_mgwpp_preview', array( __CLASS__, 'preview_gallery' ) );
    }

    /**
     * AJAX callback to preview a gallery.
     */
    public static function preview_gallery() {
        // Get the gallery ID from the request
        $gallery_id = isset( $_GET['gallery_id'] ) ? intval( $_GET['gallery_id'] ) : 0;

        // Ensure the gallery ID is valid
        if ( ! $gallery_id ) {
            wp_die( 'Missing gallery ID.' );
        }

        // Fetch the gallery post object
        $gallery = get_post( $gallery_id );
        if ( ! $gallery || 'mgwpp_soora' !== $gallery->post_type ) {
            wp_die( 'Invalid gallery.' );
        }

        // Start outputting the preview page content
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo( 'charset' ); ?>">
            <title><?php echo esc_html( $gallery->post_title ); ?> - Preview</title>
            <?php
            // Enqueue the necessary gallery styles
            wp_enqueue_style( 'mgwpp-gallery-style' );
            wp_print_styles();  // Print styles
            ?>
            <style>
                body { margin: 0; padding: 0; }
            </style>
        </head>
        <body>
            <?php
            // Process and output the gallery shortcode dynamically
            echo do_shortcode( '[mgwpp_gallery id="' . $gallery_id . '"]' );            ?>

            <?php
            // Enqueue and print gallery-related scripts (if needed)
            wp_enqueue_script( 'mgwpp-gallery-script' );
            wp_print_scripts();  // Print scripts
            ?>

        </body>
        </html>
        <?php

        exit;
    }
}
