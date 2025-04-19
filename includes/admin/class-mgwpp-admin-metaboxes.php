<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register Slide Editor metabox and Advanced Gallery Settings metabox
add_action( 'add_meta_boxes', 'mgwpp_register_slide_and_advanced_metaboxes' );
function mgwpp_register_slide_and_advanced_metaboxes() {
    // Slide Editor Metabox
    add_meta_box(
        'mgwpp_slide_editor',
        __( 'Slide Editor', 'mini-gallery' ),
        'mgwpp_slide_editor_callback',
        'mgwpp_soora',
        'normal',
        'high'
    );

    // Advanced Gallery Settings Metabox
    add_meta_box(
        'mgwpp_gallery_advanced',
        __( 'Advanced Gallery Settings', 'mini-gallery' ),
        'mgwpp_render_advanced_gallery_metabox',
        'mgwpp_soora',
        'normal',
        'default'
    );
}

/**
 * Slide Editor Metabox Callback
 */
function mgwpp_slide_editor_callback( $post ) {
    $images = get_attached_media( 'image', $post->ID );
    $slide_settings = get_post_meta( $post->ID, '_mgwpp_slide_settings', true );

    wp_nonce_field( 'mgwpp_slide_editor_nonce', 'mgwpp_slide_editor_nonce' );
    ?>
    <div class="mgwpp-editor-wrapper">
        <div class="mgwpp-editor-preview">
            <iframe src="<?php echo esc_url( add_query_arg([ 'mgwpp_preview' => 1, 'gallery_id' => $post->ID ], home_url()) ); ?>"></iframe>
        </div>
        <div class="mgwpp-editor-controls">
            <?php foreach ( $images as $image ) :
                $settings = isset( $slide_settings[ $image->ID ] ) ? $slide_settings[ $image->ID ] : [];
                ?>
                <div class="mgwpp-slide-settings" data-attachment-id="<?php echo esc_attr( $image->ID ); ?>">
                    <h4><?php echo esc_html( $image->post_title ); ?></h4>
                    <div class="mgwpp-form-group">
                        <label><?php esc_html_e( 'Button Text', 'mini-gallery' ); ?></label>
                        <input type="text"
                               name="mgwpp_slide_settings[<?php echo esc_attr( $image->ID ); ?>][button_text]"
                               value="<?php echo esc_attr( $settings['button_text'] ?? 'Learn More' ); ?>">
                    </div>
                    <div class="mgwpp-form-group">
                        <label><?php esc_html_e( 'Button Link', 'mini-gallery' ); ?></label>
                        <input type="url"
                               name="mgwpp_slide_settings[<?php echo esc_attr( $image->ID ); ?>][button_link]"
                               value="<?php echo esc_url( $settings['button_link'] ?? '#' ); ?>">
                    </div>
                    <div class="mgwpp-form-group">
                        <label><?php esc_html_e( 'Button Color', 'mini-gallery' ); ?></label>
                        <input type="color"
                               name="mgwpp_slide_settings[<?php echo esc_attr( $image->ID ); ?>][button_color]"
                               value="<?php echo esc_attr( $settings['button_color'] ?? '#ffffff' ); ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

add_action( 'save_post_mgwpp_soora', 'mgwpp_save_slide_settings' );
/**
 * Save Slide Editor Settings
 */
function mgwpp_save_slide_settings( $post_id ) {
    if ( ! isset( $_POST['mgwpp_slide_editor_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mgwpp_slide_editor_nonce'] ) ), 'mgwpp_slide_editor_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Retrieve and sanitize slide settings
    if (!isset($_POST['mgwpp_slide_settings'])) {
        return;
    }
    $settings = sanitize_key(wp_unslash($_POST['mgwpp_slide_settings']));


    if ( ! is_array( $settings ) ) {
        $settings = [];
    }

    // Sanitize each slide's settings
    $sanitized_settings = [];
    foreach ( $settings as $attachment_id => $slide ) {
        $sanitized_slide = [];
        foreach ( $slide as $key => $value ) {
            $sanitized_slide[ sanitize_key( $key ) ] = sanitize_text_field( $value );
        }
        $sanitized_settings[ absint( $attachment_id ) ] = $sanitized_slide;
    }

    update_post_meta( $post_id, '_mgwpp_slide_settings', $sanitized_settings );
}
/**
 * Advanced Gallery Settings Metabox Callback
 */
function mgwpp_render_advanced_gallery_metabox( $post ) {
    wp_nonce_field( 'mgwpp_save_advanced_gallery', 'mgwpp_gallery_advanced_nonce' );

    // Retrieve existing meta values or defaults
    $overlay_value = get_post_meta( $post->ID, 'mgwpp_gallery_overlay', true );
    $navigation    = get_post_meta( $post->ID, 'mgwpp_gallery_navigation', true );
    $image_limit   = get_post_meta( $post->ID, 'mgwpp_gallery_image_limit', true );
    $disable_lazy  = get_post_meta( $post->ID, 'mgwpp_gallery_disable_lazy', true );
    $cta_text      = get_post_meta( $post->ID, 'mgwpp_gallery_cta_text', true );
    $cta_link      = get_post_meta( $post->ID, 'mgwpp_gallery_cta_link', true );

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
                <p class="description"><?php esc_html_e( 'Tip: Combining pagination with dots may cause style conflicts. Select one or two options only.', 'mini-gallery' ); ?></p>
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
                <p class="description"><?php esc_html_e( 'For galleries on the homepage, disabling lazy load may improve UX.', 'mini-gallery' ); ?></p>
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

add_action( 'save_post_mgwpp_soora', 'mgwpp_save_advanced_gallery_metabox' );
/**
 * Save the Advanced Gallery Settings.
 */
function mgwpp_save_advanced_gallery_metabox( $post_id ) {
    if ( ! isset( $_POST['mgwpp_gallery_advanced_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mgwpp_gallery_advanced_nonce'] ) ), 'mgwpp_save_advanced_gallery' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( 'mgwpp_soora' !== get_post_type( $post_id ) ) {
        return;
    }

    // Save each setting with proper unslashing and sanitization
    if ( isset( $_POST['mgwpp_gallery_overlay'] ) ) {
        $overlay = sanitize_text_field( wp_unslash( $_POST['mgwpp_gallery_overlay'] ) );
        update_post_meta( $post_id, 'mgwpp_gallery_overlay', $overlay );
    }
    if ( isset( $_POST['mgwpp_gallery_navigation'] ) ) {
        $navigation = sanitize_text_field( wp_unslash( $_POST['mgwpp_gallery_navigation'] ) );
        update_post_meta( $post_id, 'mgwpp_gallery_navigation', $navigation );
    }
    if ( isset( $_POST['mgwpp_gallery_image_limit'] ) ) {
        update_post_meta( $post_id, 'mgwpp_gallery_image_limit', absint( $_POST['mgwpp_gallery_image_limit'] ) );
    }
    $disable_lazy = isset( $_POST['mgwpp_gallery_disable_lazy'] ) && '1' === $_POST['mgwpp_gallery_disable_lazy'] ? 1 : 0;
    update_post_meta( $post_id, 'mgwpp_gallery_disable_lazy', $disable_lazy );

    if ( isset( $_POST['mgwpp_gallery_cta_text'] ) ) {
        $cta_text = sanitize_text_field( wp_unslash( $_POST['mgwpp_gallery_cta_text'] ) );
        update_post_meta( $post_id, 'mgwpp_gallery_cta_text', $cta_text );
    }
    if ( isset( $_POST['mgwpp_gallery_cta_link'] ) ) {
        $cta_link = esc_url_raw( wp_unslash( $_POST['mgwpp_gallery_cta_link'] ) );
        update_post_meta( $post_id, 'mgwpp_gallery_cta_link', $cta_link );
    }
}
