<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class MGWPP_Gallery_Single {
    public static function render( $post_id, $images, $settings = [] ) {
        $output = '<div id="mg-carousel" class="mg-gallery-single-carousel">';
        foreach ( $images as $image ) {
            $output .= '<div class="carousel-slide">' .
                wp_get_attachment_image( $image->ID, 'medium', false, ['loading' => 'lazy'] ) .
            '</div>';
        }
        $output .= '</div>';
        return $output;
    }
}
