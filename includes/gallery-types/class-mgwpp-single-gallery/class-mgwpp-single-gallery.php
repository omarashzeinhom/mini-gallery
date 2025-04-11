<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Gallery_Single {
    /**
     * Render the single gallery.
     *
     * @param int   $post_id  Post ID (optional, for extended use).
     * @param array $images   Array of image objects.
     * @param array $settings Optional settings for custom behavior.
     *
     * Supported $settings keys:
     *   - bg_color: Background color for the gallery container (default: transparent).
     *   - transition_speed: Slide fade transition speed (default: 0.5s).
     *   - auto_rotate_speed: Auto-rotate interval in milliseconds (default: 5000).
     *   - show_nav: Whether to show navigation buttons (default: true).
     *   - swipe_threshold: Pixel threshold for swipe detection (default: 30).
     *
     * @return string HTML output of the gallery.
     */
    public static function render( $post_id, $images, $settings = [] ) {
        // Enqueue necessary styles and scripts.
        wp_enqueue_style( 'mgwpp-single-gallery' );
        wp_enqueue_script( 'mgwpp-single-gallery' );
        
        // Default settings.
        $defaults = array(
            'bg_color'           => 'transparent', // To avoid the white flash on first image.
            'transition_speed'   => '0.5s',
            'auto_rotate_speed'  => 5000,          // Milliseconds.
            'show_nav'           => true,          // Show navigation buttons by default.
            'swipe_threshold'    => 30,            // Minimum pixels to consider as a swipe.
        );
        $settings = wp_parse_args( $settings, $defaults );
        
        // Inline styles using the custom settings.
        $custom_css = "
            .mgwpp-single-gallery .gallery-container {
                background: {$settings['bg_color']};
            }
            .mgwpp-single-gallery .carousel-slide {
                transition: opacity {$settings['transition_speed']} ease-in-out;
            }
        ";
        echo '<style>' . $custom_css . '</style>';
        
        // Pass some custom settings as data attributes for use in JavaScript.
        ob_start();
        ?>
        <div class="mgwpp-single-gallery" 
             data-auto-rotate="<?php echo intval( $settings['auto_rotate_speed'] ); ?>"
             data-swipe-threshold="<?php echo intval( $settings['swipe_threshold'] ); ?>">
            <div class="gallery-container">
                <?php if ( ! empty( $images ) && is_array( $images ) ) : ?>
                    <div class="main-image">
                        <?php foreach ( $images as $index => $image ) : ?>
                            <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                <?php
                                echo wp_get_attachment_image(
                                    $image->ID,
                                    'large', // You might change this size as needed.
                                    false,
                                    array(
                                        'loading'     => 'lazy',
                                        'class'       => 'slide-image',
                                        'data-caption'=> esc_attr( wp_get_attachment_caption( $image->ID ) ),
                                    )
                                );
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ( $settings['show_nav'] ) : ?>
                        <div class="gallery-controls">
                            <button class="nav-prev">❮</button>
                            <div class="image-counter">1/<?php echo count( $images ); ?></div>
                            <button class="nav-next">❯</button>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="no-images"><?php _e( 'No images found', 'mini-gallery' ); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
