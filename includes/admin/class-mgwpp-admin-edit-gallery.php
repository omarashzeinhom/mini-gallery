<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class MGWPP_Admin_Edit_Gallery
 *
 * Handles the registration and rendering of the custom "Edit Gallery" admin page.
 */
class MGWPP_Admin_Edit_Gallery {

    /**
     * Initialize hooks.
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_edit_gallery_page' ) );
    }

    /**
     * Registers the "Edit Gallery" page as a submenu.
     */
    public static function register_edit_gallery_page() {
        // Change 'mini-gallery' to your parent menu slug if necessary.
        add_submenu_page(
            'mini-gallery',
            __( 'Edit Gallery', 'mini-gallery' ),
            __( 'Edit Gallery', 'mini-gallery' ),
            'manage_options',
            'mgwpp-edit-gallery',
            array( __CLASS__, 'render_edit_gallery_page' )
        );
    }

    /**
     * Renders the "Edit Gallery" page.
     */
    public static function render_edit_gallery_page() {
        $gallery_id = isset( $_GET['gallery_id'] ) ? intval( $_GET['gallery_id'] ) : 0;
        if ( ! $gallery_id ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'No gallery specified.', 'mini-gallery' ) . '</p></div>';
            return;
        }
    
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mgwpp_edit_gallery' ) ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Security check failed.', 'mini-gallery' ) . '</p></div>';
            return;
        }
    
        $gallery = get_post( $gallery_id );
        if ( ! $gallery || 'mgwpp_soora' !== $gallery->post_type ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid gallery.', 'mini-gallery' ) . '</p></div>';
            return;
        }
    
        // Process form submission
        if ( isset( $_POST['mgwpp_edit_gallery_submit'] ) ) {
            if ( ! isset( $_POST['mgwpp_edit_gallery_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mgwpp_edit_gallery_nonce'] ) ), 'mgwpp_edit_gallery_save' ) ) {
                echo '<div class="notice notice-error"><p>' . esc_html__( 'Form security check failed.', 'mini-gallery' ) . '</p></div>';
                return;
            }
    
            update_post_meta( $gallery_id, 'mgwpp_gallery_overlay', sanitize_text_field( $_POST['mgwpp_gallery_overlay'] ) );
            update_post_meta( $gallery_id, 'mgwpp_gallery_navigation', sanitize_text_field( $_POST['mgwpp_gallery_navigation'] ) );
            update_post_meta( $gallery_id, 'mgwpp_gallery_cta_text', sanitize_text_field( $_POST['mgwpp_gallery_cta_text'] ) );
            update_post_meta( $gallery_id, 'mgwpp_gallery_cta_link', esc_url_raw( $_POST['mgwpp_gallery_cta_link'] ) );
    
            // Save per-image CTA
            if ( isset( $_POST['mgwpp_gallery_images'] ) ) {
                update_post_meta( $gallery_id, 'mgwpp_gallery_images', $_POST['mgwpp_gallery_images'] );
            }
    
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Gallery updated successfully.', 'mini-gallery' ) . '</p></div>';
        }
    
        $overlay    = get_post_meta( $gallery_id, 'mgwpp_gallery_overlay', true );
        $navigation = get_post_meta( $gallery_id, 'mgwpp_gallery_navigation', true );
        $cta_text   = get_post_meta( $gallery_id, 'mgwpp_gallery_cta_text', true );
        $cta_link   = get_post_meta( $gallery_id, 'mgwpp_gallery_cta_link', true );
        $images     = get_post_meta( $gallery_id, 'mgwpp_gallery_images', true );
    
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Edit Gallery', 'mini-gallery' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'mgwpp_edit_gallery_save', 'mgwpp_edit_gallery_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="mgwpp_gallery_overlay"><?php esc_html_e( 'Overlay Mask', 'mini-gallery' ); ?></label></th>
                        <td><input type="text" name="mgwpp_gallery_overlay" id="mgwpp_gallery_overlay" value="<?php echo esc_attr( $overlay ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="mgwpp_gallery_navigation"><?php esc_html_e( 'Navigation', 'mini-gallery' ); ?></label></th>
                        <td>
                            <select name="mgwpp_gallery_navigation" id="mgwpp_gallery_navigation">
                                <option value="dots" <?php selected( $navigation, 'dots' ); ?>>Dots</option>
                                <option value="arrows" <?php selected( $navigation, 'arrows' ); ?>>Arrows</option>
                                <option value="pagination" <?php selected( $navigation, 'pagination' ); ?>>Pagination</option>
                                <option value="dots_arrows" <?php selected( $navigation, 'dots_arrows' ); ?>>Dots + Arrows</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="mgwpp_gallery_cta_text"><?php esc_html_e( 'CTA Button Text', 'mini-gallery' ); ?></label></th>
                        <td><input type="text" name="mgwpp_gallery_cta_text" id="mgwpp_gallery_cta_text" value="<?php echo esc_attr( $cta_text ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="mgwpp_gallery_cta_link"><?php esc_html_e( 'CTA Button Link', 'mini-gallery' ); ?></label></th>
                        <td><input type="url" name="mgwpp_gallery_cta_link" id="mgwpp_gallery_cta_link" value="<?php echo esc_attr( $cta_link ); ?>" class="regular-text"></td>
                    </tr>
                </table>
    
                <h2><?php esc_html_e( 'Image CTA Settings', 'mini-gallery' ); ?></h2>
                <?php if ( ! empty( $images ) ) : ?>
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>CTA Text</th>
                                <th>CTA Link</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $images as $index => $img ) : ?>
                            <tr>
                                <td>
                                    <?php echo wp_get_attachment_image( $img['id'], [80,80] ); ?>
                                </td>
                                <td>
                                    <input type="text" name="mgwpp_gallery_images[<?php echo $index; ?>][cta_text]" value="<?php echo esc_attr( $img['cta_text'] ?? '' ); ?>" class="regular-text">
                                </td>
                                <td>
                                    <input type="url" name="mgwpp_gallery_images[<?php echo $index; ?>][cta_link]" value="<?php echo esc_attr( $img['cta_link'] ?? '' ); ?>" class="regular-text">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p><?php esc_html_e( 'No images found in this gallery.', 'mini-gallery' ); ?></p>
                <?php endif; ?>
    
                <?php submit_button( __( 'Save Changes', 'mini-gallery' ), 'primary', 'mgwpp_edit_gallery_submit' ); ?>
            </form>
    
            <h2><?php esc_html_e( 'Gallery Preview', 'mini-gallery' ); ?></h2>
            <?php
            // Directly render the gallery using the gallery ID
            echo do_shortcode( '[mgwpp_gallery id="' . $gallery_id . '"]' );            ?>
    
            <h2><?php esc_html_e( 'Copy Shortcode', 'mini-gallery' ); ?></h2>
            <input type="text" value='[mgwpp_gallery id="<?php echo $gallery_id; ?>"]'>            
            <button class="button" type="button" onclick="copyMGWPPShortcode()">Copy Shortcode</button>
            
            
            <br><br>
        <!-- Preview Button -->
        <button id="mgwpp_preview_button" class="button">Preview Gallery</button>
        
            <script>
            function copyMGWPPShortcode() {
                var copyText = document.getElementById("mgwpp_shortcode");
                copyText.select();
                document.execCommand("copy");
                alert("Shortcode copied!");
            }

            jQuery(document).ready(function($){
                $('#mgwpp_preview_button').on('click', function(e) {
                    e.preventDefault();
                    var gallery_id = <?php echo $gallery_id; ?>;

                    // Trigger the AJAX request to preview the gallery
                    $.ajax({
                        url: ajaxurl, // WordPress' AJAX handler
                        method: 'GET',
                        data: {
                            action: 'mgwpp_preview',
                            gallery_id: gallery_id
                        },
                        success: function(response) {
                            // Open a new window or display the preview in an iframe
                            var previewWindow = window.open('', 'Gallery Preview', 'width=800,height=600');
                            previewWindow.document.write(response);
                        },
                        error: function() {
                            alert('Error generating preview.');
                        }
                    });
                });
            });
            </script>
    
        </div>
        <?php
    }
    

    
}


// Initialize the Edit Gallery page.
MGWPP_Admin_Edit_Gallery::init();
