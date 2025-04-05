<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class MGWPP_Gallery_Grid {
    public static function render( $post_id, $images ) {
        $output = '<div class="grid-layout">';
        foreach ( $images as $image ) {
            $output .= '<div class="grid-item">' .
                wp_get_attachment_image( $image->ID, 'medium', false, ['loading' => 'lazy'] ) .
            '</div>';
        }
        $output .= '</div>';
        return $output;
    }
}
