<?php
// File: includes/admin/class-mgwpp-admin-metaboxes.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register the advanced gallery settings metabox.
function mgwpp_register_advanced_gallery_metabox() {
    add_meta_box(
        'mgwpp_gallery_advanced',
        __( 'Advanced Gallery Settings', 'mini-gallery' ),
        'mgwpp_render_advanced_gallery_metabox',
        'mgwpp_soora', // Your custom post type.
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'mgwpp_register_advanced_gallery_metabox' );

/**
 * Render the advanced metabox.
 *
 * @param WP_Post $post The current gallery post.
 */
function mgwpp_render_advanced_gallery_metabox( $post ) {
    wp_nonce_field( 'mgwpp_save_advanced_gallery', 'mgwpp_gallery_advanced_nonce' );

    // Retrieve existing meta values or use global defaults.
    $overlay_value = get_post_meta( $post->ID, 'mgwpp_gallery_overlay', true );
    $navigation    = get_post_meta( $post->ID, 'mgwpp_gallery_navigation', true );
    $image_limit   = get_post_meta( $post->ID, 'mgwpp_gallery_image_limit', true );
    $disable_lazy  = get_post_meta( $post->ID, 'mgwpp_gallery_disable_lazy', true );
    $cta_text      = get_post_meta( $post->ID, 'mgwpp_gallery_cta_text', true );
    $cta_link      = get_post_meta( $post->ID, 'mgwpp_gallery_cta_link', true );

    // Optionally, grab a global default (if set) for overlay.
    $global_overlay = get_option( 'mgwpp_global_overlay', 'linear-gradient(45deg, #ffea00, #ffc107)' );
    $overlay_value  = $overlay_value ? $overlay_value : $global_overlay;
    ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="mgwpp_gallery_overlay"><?php esc_html_e( 'Overlay Mask (Color/Gradient)', 'mini-gallery' ); ?></label>
            </th>
            <td>
                <input type="text" id="mgwpp_gallery_overlay" name="mgwpp_gallery_overlay" value="<?php echo esc_attr( $overlay_value ); ?>" class="widefat" />
                <p class="description"><?php esc_html_e( 'Example: linear-gradient(45deg, #ffea00, #ffc107)', 'mini-gallery' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="mgwpp_gallery_navigation"><?php esc_html_e( 'Navigation Options', 'mini-gallery' ); ?></label>
            </th>
            <td>
                <select id="mgwpp_gallery_navigation" name="mgwpp_gallery_navigation" class="widefat">
                    <option value="dots" <?php selected( $navigation, 'dots' ); ?>><?php esc_html_e( 'Dots Only', 'mini-gallery' ); ?></option>
                    <option value="arrows" <?php selected( $navigation, 'arrows' ); ?>><?php esc_html_e( 'Arrows Only', 'mini-gallery' ); ?></option>
                    <option value="pagination" <?php selected( $navigation, 'pagination' ); ?>><?php esc_html_e( 'Pagination Only', 'mini-gallery' ); ?></option>
                    <option value="dots_arrows" <?php selected( $navigation, 'dots_arrows' ); ?>><?php esc_html_e( 'Dots + Arrows', 'mini-gallery' ); ?></option>
                </select>
                <p class="description"><?php esc_html_e( 'Tip: Combining pagination with dots may cause style conflicts. It is recommended to select one or two options only.', 'mini-gallery' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="mgwpp_gallery_image_limit"><?php esc_html_e( 'Limit Number of Images', 'mini-gallery' ); ?></label>
            </th>
            <td>
                <input type="number" min="1" id="mgwpp_gallery_image_limit" name="mgwpp_gallery_image_limit" value="<?php echo esc_attr( $image_limit ); ?>" class="small-text" />
                <p class="description"><?php esc_html_e( 'Leave blank to show all images.', 'mini-gallery' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="mgwpp_gallery_disable_lazy"><?php esc_html_e( 'Disable Lazy Loading for First Image', 'mini-gallery' ); ?></label>
            </th>
            <td>
                <input type="checkbox" id="mgwpp_gallery_disable_lazy" name="mgwpp_gallery_disable_lazy" value="1" <?php checked( $disable_lazy, 1 ); ?> />
                <span><?php esc_html_e( 'Disable lazy load for the first image.', 'mini-gallery' ); ?></span>
                <p class="description"><?php esc_html_e( 'For galleries on the homepage, it may be beneficial to disable lazy loading for immediate visibility.', 'mini-gallery' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="mgwpp_gallery_cta_text"><?php esc_html_e( 'CTA Button Text', 'mini-gallery' ); ?></label>
            </th>
            <td>
                <input type="text" id="mgwpp_gallery_cta_text" name="mgwpp_gallery_cta_text" value="<?php echo esc_attr( $cta_text ); ?>" class="widefat" />
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="mgwpp_gallery_cta_link"><?php esc_html_e( 'CTA Button Link', 'mini-gallery' ); ?></label>
            </th>
            <td>
                <input type="url" id="mgwpp_gallery_cta_link" name="mgwpp_gallery_cta_link" value="<?php echo esc_attr( $cta_link ); ?>" class="widefat" />
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Save the metabox data.
 *
 * @param int $post_id The post ID.
 */
function mgwpp_save_advanced_gallery_metabox( $post_id ) {
    if ( ! isset( $_POST['mgwpp_gallery_advanced_nonce'] ) || ! wp_verify_nonce( $_POST['mgwpp_gallery_advanced_nonce'], 'mgwpp_save_advanced_gallery' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( 'mgwpp_soora' !== get_post_type( $post_id ) ) {
        return;
    }

    if ( isset( $_POST['mgwpp_gallery_overlay'] ) ) {
        update_post_meta( $post_id, 'mgwpp_gallery_overlay', sanitize_text_field( $_POST['mgwpp_gallery_overlay'] ) );
    }
    if ( isset( $_POST['mgwpp_gallery_navigation'] ) ) {
        update_post_meta( $post_id, 'mgwpp_gallery_navigation', sanitize_text_field( $_POST['mgwpp_gallery_navigation'] ) );
    }
    if ( isset( $_POST['mgwpp_gallery_image_limit'] ) ) {
        update_post_meta( $post_id, 'mgwpp_gallery_image_limit', absint( $_POST['mgwpp_gallery_image_limit'] ) );
    }
    $disable_lazy = isset( $_POST['mgwpp_gallery_disable_lazy'] ) && $_POST['mgwpp_gallery_disable_lazy'] == 1 ? 1 : 0;
    update_post_meta( $post_id, 'mgwpp_gallery_disable_lazy', $disable_lazy );

    if ( isset( $_POST['mgwpp_gallery_cta_text'] ) ) {
        update_post_meta( $post_id, 'mgwpp_gallery_cta_text', sanitize_text_field( $_POST['mgwpp_gallery_cta_text'] ) );
    }
    if ( isset( $_POST['mgwpp_gallery_cta_link'] ) ) {
        update_post_meta( $post_id, 'mgwpp_gallery_cta_link', esc_url_raw( $_POST['mgwpp_gallery_cta_link'] ) );
    }
}
add_action( 'save_post', 'mgwpp_save_advanced_gallery_metabox' );
