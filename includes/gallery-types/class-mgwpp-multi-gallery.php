<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MGWPP_Gallery_Multi {
    /**
     * Render the multi-carousel.
     *
     * @param int   $post_id         Optional post ID.
     * @param array $images          Array of image objects.
     * @param array $args            Array of options:
     *                               - images_per_page: Default images per page for "default" mode (min 2).
     *                               - display_mode: "default" (full-width) or "cards".
     *                               - auto_rotate_speed: Auto rotate interval (milliseconds).
     *
     * @return string HTML output for the multi-carousel.
     */
    public static function render( $post_id, $images, $args = array() ) {
        $defaults = array(
            'images_per_page'   => 6,        // default number on desktop (cards mode will clamp to min 2)
            'display_mode'      => 'default',// set to "cards" for product style cards
            'auto_rotate_speed' => 3000,     // in milliseconds
        );
        $args = wp_parse_args( $args, $defaults );
        
        // Enqueue styles and scripts.
        wp_enqueue_style( 'mgwpp-multi-carousel' );
        wp_enqueue_script( 'mgwpp-multi-carousel' );
       
        ob_start();
        ?>
        <!-- Pass options via data attributes. display_mode will allow CSS and JS to adjust layout -->
        <div class="mg-gallery multi-carousel <?php echo esc_attr( $args['display_mode'] ); ?>" 
             data-auto-rotate="<?php echo intval( $args['auto_rotate_speed'] ); ?>"
             data-images-per-page="<?php echo intval( max(2, $args['images_per_page']) ); ?>">
            <div class="slides-wrapper">
                <?php if ( ! empty( $images ) && is_array( $images ) ) : ?>
                    <?php foreach ( $images as $image ) : ?>
                        <div class="mg-multi-carousel-slide">
                            <?php
                            // Using 'full' size for max resolution; change to a custom size if desired.
                            echo wp_get_attachment_image(
                                $image->ID,
                                'large',
                                false,
                                array(
                                    'loading' => 'lazy',
                                    'class'   => 'multi-slide-image',
                                )
                            );
                            ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-images"><?php _e( 'No images found', 'mini-gallery' ); ?></div>
                <?php endif; ?>
            </div><!-- .slides-wrapper -->
        </div><!-- .multi-carousel -->
        <?php
        return ob_get_clean();
    }
}
