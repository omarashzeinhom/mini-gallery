<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class MGWPP_Gallery_Multi {
    public static function render( $post_id, $images, $paged = 1, $images_per_page = 6 ) {
        $offset = ( $paged - 1 ) * $images_per_page;
        $images_page = array_slice( $images, $offset, $images_per_page );
        $output = '<div id="mg-multi-carousel" class="mg-gallery multi-carousel" data-page="' . esc_attr( $paged ) . '">';
        foreach ( $images_page as $image ) {
            $output .= '<div class="mg-multi-carousel-slide">' .
                wp_get_attachment_image( $image->ID, 'medium', false, ['class' => 'mg-multi-carousel-slide', 'loading' => 'lazy'] ) .
            '</div>';
        }
        $output .= '</div>';
        return $output;
    }
}
