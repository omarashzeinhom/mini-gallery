<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Mega_Slider {
    public static function render( $post_id, $images, $settings = [] ) {
        // Check if we're in Elementor
        $is_elementor = did_action( 'elementor/loaded' );

        // Default settings
        $default_settings = [
            'placeholder_image' => [ 'url' => '' ],
            'viewport_height'   => [ 'size' => '600', 'unit' => 'px' ],
            'show_arrows'       => 'yes',
            'show_dots'         => 'yes',
            'autoplay'          => 'yes',
            'autoplay_delay'    => [ 'size' => '3000', 'unit' => '' ],
            'lazy_load_first'   => 'yes', // NEW OPTION
        ];

        // Merge Elementor settings if present, otherwise use defaults
        $settings = $is_elementor ? wp_parse_args( $settings, $default_settings ) : $default_settings;

        // Extract settings
        $placeholder      = ! empty( $settings['placeholder_image']['url'] ) ? $settings['placeholder_image']['url'] : '';
        $viewport_height  = ! empty( $settings['viewport_height']['size'] ) ? $settings['viewport_height']['size'] . $settings['viewport_height']['unit'] : '600px';
        $show_arrows      = $settings['show_arrows'] === 'yes';
        $show_dots        = $settings['show_dots'] === 'yes';
        $autoplay         = $settings['autoplay'] === 'yes';
        $autoplay_delay   = ! empty( $settings['autoplay_delay']['size'] ) ? $settings['autoplay_delay']['size'] . $settings['autoplay_delay']['unit'] : '3000';
        $lazy_load_first  = $settings['lazy_load_first'] === 'yes';

        ob_start(); ?>
        <div class="mg-mega-carousel"
             data-autoplay="<?php echo esc_attr( $autoplay ? 'true' : 'false' ); ?>"
             data-autoplay-delay="<?php echo esc_attr( $autoplay_delay ); ?>">
            <div class="mg-carousel__viewport" style="height: <?php echo esc_attr( $viewport_height ); ?>;">
                <?php foreach ( $images as $index => $image ) : ?>
                    <div class="mg-carousel__slide <?php echo $index === 0 ? 'mg-active' : ''; ?>">
                        <?php 
                        if ( $index === 0 && $lazy_load_first ) {
                            // FIRST IMAGE: Use a placeholder until page loads, then replace using lazy load.
                            echo wp_get_attachment_image( $image->ID, 'full', false, [
                                'class'    => 'mg-carousel__image lazy-first',
                                'data-src' => esc_url( wp_get_attachment_url( $image->ID ) ),
                                'src'      => esc_url( $placeholder ),
                                'alt'      => esc_attr( get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) ),
                            ] );
                        } else {
                            // OTHER IMAGES: Lazy load
                            echo wp_get_attachment_image( $image->ID, 'full', false, [
                                'class'    => 'mg-carousel__image lazy-load',
                                'data-src' => esc_url( wp_get_attachment_url( $image->ID ) ),
                                'alt'      => esc_attr( get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) ),
                                'loading'  => 'lazy',
                            ] );
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ( $show_arrows ) : ?>
            <div class="mg-carousel__controls">
                <!-- Updated navigation buttons with new class names -->
                <button class="mgwpp__prev-mega-slider mg-carousel__nav" aria-label="Previous slide">
                    <svg class="mg-carousel__arrow" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                        <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M15 18l-6-6 6-6" />
                    </svg>
                </button>

                <button class="mgwpp__next-mega-slider mg-carousel__nav" aria-label="Next slide">
                    <svg class="mg-carousel__arrow" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                        <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M9 18l6-6-6-6" />
                    </svg>
                </button>
                <?php if ( $show_dots ) : ?>
                    <div class="mg-mega-carousel-dots-container"></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
