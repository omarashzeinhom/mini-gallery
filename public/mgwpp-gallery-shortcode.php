<?php 
class MGWPP_Gallery_ShortCode{
    public static function mgwpp_gallery_register_shortcode(){
        add_shortcode('mgwpp_gallery', array('MGWPP_Gallery_Shortcode', 'render_gallery'));
    }

    public static function mgwpp_render_gallery($atts){
        ob_start();
        $atts = shortcode_atts(['id' => '', 'paged' => 1], $atts);
        $post_id = max(0, intval($atts['id']));
        $paged = max(1, intval($atts['paged']));
        $output = '';

        if ($post_id) {
            // Retrieve the gallery type from post meta
            $gallery_type = get_post_meta($post_id, 'gallery_type', true);
            if (!$gallery_type) {
                $gallery_type = 'single_carousel'; // Fallback to default if not set
            }

            $images_per_page = 6; // Number of images per page for multi-carousel
            $offset = ($paged - 1) * $images_per_page;

            // Retrieve all images for the gallery
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

                    // Slice images for current page
                    $images = array_slice($all_images, $offset, $images_per_page);
                    foreach ($images as $image) {
                        $imgwpp_url = wp_get_attachment_image_src($image->ID, 'medium');
                        $output .= '<div class="mg-multi-carousel-slide"><img class="mg-multi-carousel-slide" src="' . esc_url($imgwpp_url[0]) . '" alt="' . esc_attr($image->post_title) . '" loading="lazy"></div>';
                    }
                    $output .= '</div>';
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

add_action('init', array('MGWPP_Gallery_Shortcode', 'register_shortcode'));

;?>