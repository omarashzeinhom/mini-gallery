<?php 
class MGWPP_Gallery_ShortCode {
    public static function mgwpp_gallery_register_shortcode(){
        // Register shortcode to call the render_gallery method
        add_shortcode('mgwpp_gallery', array('MGWPP_Gallery_ShortCode', 'mgwpp_render_gallery'));
    }

    public static function mgwpp_render_gallery() {
        ob_start();
        
        // Get the current post ID and page number for pagination
        $post_id = get_the_ID();
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $output = '';

        if ($post_id) {
            // Retrieve gallery type (single_carousel, multi_carousel, grid)
            $gallery_type = get_post_meta($post_id, 'gallery_type', true);
            if (!$gallery_type) {
                $gallery_type = 'single_carousel'; // Default if no gallery type set
            }

            $images_per_page = 6; // Number of images per page for multi-carousel
            $offset = ($paged - 1) * $images_per_page;

            // Retrieve all attached images
            $all_images = get_attached_media('image', $post_id);

            if ($all_images) {
                if ($gallery_type === 'single_carousel') {
                    $output .= '<div id="mg-carousel" class="mg-gallery-single-carousel">';
                    foreach ($all_images as $image) {
                        $imgwpp_url = wp_get_attachment_image_src($image->ID, 'medium');
                        $output .= '<div class="carousel-slide"><img src="' . esc_url($imgwpp_url[0]) . '" alt="' . esc_attr($image->post_title) . '" loading="lazy"></div>';
                    }
                    $output .= '</div>';
                } elseif ($gallery_type === 'multi_carousel') {
                    $output .= '<div id="mg-multi-carousel" class="mg-gallery multi-carousel" data-page="' . esc_attr($paged) . '">';

                    // Slice images for the current page
                    $images = array_slice($all_images, $offset, $images_per_page);
                    foreach ($images as $image) {
                        $imgwpp_url = wp_get_attachment_image_src($image->ID, 'medium');
                        $output .= '<div class="mg-multi-carousel-slide"><img class="mg-multi-carousel-slide" src="' . esc_url($imgwpp_url[0]) . '" alt="' . esc_attr($image->post_title) . '" loading="lazy"></div>';
                    }
                    $output .= '</div>';
                    
                    // Add pagination (Previous / Next)
                    $total_images = count($all_images);
                    $total_pages = ceil($total_images / $images_per_page);
                    
                    if ($paged > 1) {
                        $output .= '<a href="' . esc_url(add_query_arg('paged', $paged - 1)) . '" class="mg-pagination-prev">Previous</a>';
                    }
                    
                    if ($paged < $total_pages) {
                        $output .= '<a href="' . esc_url(add_query_arg('paged', $paged + 1)) . '" class="mg-pagination-next">Next</a>';
                    }
                } elseif ($gallery_type === 'grid') {
                    $output .= '<div class="grid-layout">';
                    foreach ($all_images as $image) {
                        $imgwpp_url = wp_get_attachment_image_src($image->ID, 'medium');
                        $output .= '<div class="grid-item"><img src="' . esc_url($imgwpp_url[0]) . '" alt="' . esc_attr($image->post_title) . '" loading="lazy"></div>';
                    }
                    $output .= '</div>';
                }
            } else {
                $output .= '<p>No images found for this gallery.</p>';
            }
        } else {
            $output .= '<p>Invalid gallery ID.</p>';
        }
        
        return $output;
    }
}

add_action('init', array('MGWPP_Gallery_ShortCode', 'mgwpp_gallery_register_shortcode'));
?>
