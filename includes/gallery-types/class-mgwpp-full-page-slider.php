<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MGWPP_Full_Page_Slider {
    public static function render( $post_id, $images, $settings = [] ) {
        if ( empty( $images ) || ! is_array( $images ) ) {
            return ''; // Exit early if images are not valid
        }

        ob_start(); ?>
        <div class="mg-fullpage-slider">
            <div class="mg-fullpage-viewport">
                <?php foreach ( $images as $index => $image ) :
                    $image_id = isset( $image->ID ) ? intval( $image->ID ) : 0;
                    $image_url = $image_id ? esc_url( wp_get_attachment_url( $image_id ) ) : '';
                    $image_alt = $image_id ? esc_attr( get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ) : '';
                    $image_title = $image_id ? esc_html( get_the_title( $image_id ) ) : '';
                    $image_content = $image_id ? wp_kses_post( get_post_field( 'post_content', $image_id ) ) : '';
                    ?>
                    <div class="mg-fullpage-slide <?php echo ( $index === 0 ? 'mg-active' : '' ); ?>">
                        <div class="mg-fullpage-overlay"></div>
                        <?php if ( $image_url ) : ?>
                            <img class="mg-fullpage-image"
                                 src="<?php echo $image_url; ?>"
                                 alt="<?php echo $image_alt; ?>">
                        <?php endif; ?>
                        <div class="mg-fullpage-content">
                            <?php if ( $image_title ) : ?>
                                <h1 class="text-8xl font-bold mb-6"><?php echo $image_title; ?></h1>
                            <?php endif; ?>
                            <?php if ( $image_content ) : ?>
                                <p class="text-xl opacity-80 mb-8"><?php echo $image_content; ?></p>
                            <?php endif; ?>
                            <button class="mg-fullpage-buy">Explore Collection</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="mg-fullpage-nav mg-prev">❮</button>
            <button class="mg-fullpage-nav mg-next">❯</button>
        </div>
        <?php
        return ob_get_clean();
    }
}
?>
