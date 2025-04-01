<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function mgwpp_gallery_shortcode( $atts ) {
    $atts = shortcode_atts( [ 'id' => '', 'paged' => 1 ], $atts );
    $post_id = max( 0, intval( $atts['id'] ) );
    $paged   = max( 1, intval( $atts['paged'] ) );
    $output  = '';

    if ( $post_id ) {
        // Retrieve the gallery type from post meta
        $gallery_type = get_post_meta( $post_id, 'gallery_type', true );
        if ( ! $gallery_type ) {
            $gallery_type = 'single_carousel'; // Fallback to default if not set
        }

        $images_per_page = 6; // Number of images per page for multi-carousel
        $offset          = ( $paged - 1 ) * $images_per_page;

        // Retrieve all images for the gallery
        $all_images = get_attached_media( 'image', $post_id );

        if ( $all_images ) {
            switch ( $gallery_type ) {
                case 'single_carousel':
                    if ( ! class_exists( 'MGWPP_Gallery_Single' ) ) {
                        include_once plugin_dir_path( __FILE__ ) . 'includes/gallery-types/class-mgwpp-single-gallery.php';
                    }
                    $output .= MGWPP_Gallery_Single::render( $post_id, $all_images );
                    break;

                case 'multi_carousel':
                    if ( ! class_exists( 'MGWPP_Gallery_Multi' ) ) {
                        include_once plugin_dir_path( __FILE__ ) . 'includes/gallery-types/class-mgwpp-multi-gallery.php';
                    }
                    $output .= MGWPP_Gallery_Multi::render( $post_id, $all_images, $paged, $images_per_page );
                    break;

                case 'grid':
                    if ( ! class_exists( 'MGWPP_Gallery_Grid' ) ) {
                        include_once plugin_dir_path( __FILE__ ) . 'includes/gallery-types/class-mgwpp-grid-gallery.php';
                    }
                    $output .= MGWPP_Gallery_Grid::render( $post_id, $all_images );
                    break;

                case 'mega_slider':
                    $output .= MGWPP_Mega_Slider::render( $post_id, $all_images );
                    break;

                case 'pro_carousel':
                    wp_enqueue_style( 'mgwpp-pro-carousel-styles' );
                    wp_enqueue_script( 'mgwpp-pro-carousel-js' );
                    $output .= MGWPP_Pro_Carousel::render( $post_id, $all_images );
                    break;

                case 'neon_carousel':
                    wp_enqueue_style( 'mgwpp-neon-carousel-styles' );
                    wp_enqueue_script( 'mgwpp-neon-carousel-js' );
                    $output .= MGWPP_Neon_Carousel::render( $post_id, $all_images );
                    break;
                    
                case 'threed_carousel':
                    if ( ! class_exists( 'MGWPP_3D_Carousel' ) ) {
                        include_once plugin_dir_path( __FILE__ ) . 'includes/gallery-types/class-mgwpp-3d-carousel.php';
                    }
                    $output .= MGWPP_3D_Carousel::render( $post_id, $all_images );
                    break;
            }
        } else {
            $output .= '<p>No images found for this gallery.</p>';
        }

        // Handle pagination if necessary for multi-carousel
        if ( 'multi_carousel' === $gallery_type && count( $all_images ) > $images_per_page ) {
            $total_pages = ceil( count( $all_images ) / $images_per_page );
            $output     .= '<div class="mgwpp-gallery-pagination">';
            // Add your pagination links here if needed
            $output     .= '</div>';
        }
    } else {
        $output .= '<p>Invalid gallery ID.</p>';
    }

    return $output;
}