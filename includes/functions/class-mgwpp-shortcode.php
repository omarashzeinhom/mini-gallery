<?php
if (! defined('ABSPATH')) {
    exit;
}
function mgwpp_gallery_shortcode($atts)
{
    $atts    = shortcode_atts(['id' => '', 'paged' => 1], $atts);
    $post_id = max(0, intval($atts['id']));
    $paged   = max(1, intval($atts['paged']));
    $output  = '';

    if ($post_id) {
        $gallery_type = get_post_meta($post_id, 'gallery_type', true) ?: 'single_carousel';
        $images_per_page = 6;
        $offset = ($paged - 1) * $images_per_page;
        $all_images = get_attached_media('image', $post_id);

        if ($all_images) {
            // Initialize gallery_html with default value
            $gallery_html = '<p>Gallery type not recognized.</p>';

            switch ($gallery_type) {
                case 'single_carousel':
                    if (!class_exists('MGWPP_Gallery_Single')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-single-gallery.php';
                    }
                    $gallery_html = MGWPP_Gallery_Single::render($post_id, $all_images);
                    break;

                case 'multi_carousel':
                    if (!class_exists('MGWPP_Gallery_Multi')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-multi-gallery.php';
                    }
                    $gallery_html = MGWPP_Gallery_Multi::render($post_id, $all_images, $paged, $images_per_page);
                    break;

                case 'grid':
                    if (!class_exists('MGWPP_Gallery_Grid')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-grid-gallery.php';
                    }
                    $gallery_html = MGWPP_Gallery_Grid::render($post_id, $all_images);
                    break;

                case 'mega_slider':
                    if (!class_exists('MGWPP_Mega_Slider')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-mega-slider.php';
                    }
                    $gallery_html = MGWPP_Mega_Slider::render($post_id, $all_images);
                    break;

                case 'pro_carousel':
                    wp_enqueue_style('mgwpp-pro-carousel-styles');
                    wp_enqueue_script('mgwpp-pro-carousel-js');
                    $gallery_html = MGWPP_Pro_Carousel::render($post_id, $all_images);
                    break;

                case 'neon_carousel':
                    wp_enqueue_style('mgwpp-neon-carousel-styles');
                    wp_enqueue_script('mgwpp-neon-carousel-js');
                    $gallery_html = MGWPP_Neon_Carousel::render($post_id, $all_images);
                    break;

                case 'threed_carousel':
                    if (!class_exists('MGWPP_3D_Carousel')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-3d-carousel.php';
                    }
                    $gallery_html = MGWPP_3D_Carousel::render($post_id, $all_images);
                    break;

                case 'full_page_slider':
                    if (!class_exists('MGWPP_Full_Page_Slider')) {
                        require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-full-page-slider.php';
                    }
                    $gallery_html = MGWPP_Full_Page_Slider::render($post_id, $all_images);
                    break;

                case 'testimonials_carousel':
                    $testimonials = get_posts([
                        'post_type' => 'testimonial',
                        'posts_per_page' => -1,
                        'suppress_filters' => false
                    ]);

                    $gallery_html = '<p>No testimonials found.</p>';
                    if (!empty($testimonials)) {
                        if (!class_exists('MGWPP_Testimonial_Carousel')) {
                            require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-testimonial-carousel.php';
                        }
                        $gallery_html = MGWPP_Testimonial_Carousel::render($post_id, $testimonials);
                    }
                    break;

                default:
                    $gallery_html = '<p>Gallery type not recognized.</p>';
            }

            if (!empty($gallery_html)) {
                $output .= '<div class="mgwpp-gallery-item">' . $gallery_html . '</div>';
            }
        } else {
            $output .= '<p>No images found for this gallery.</p>';
        }

        if ('multi_carousel' === $gallery_type && count($all_images) > $images_per_page) {
            $total_pages = ceil(count($all_images) / $images_per_page);
            $output .= '<div class="mgwpp-gallery-pagination">';
            // Pagination logic here
            $output .= '</div>';
        }
    } else {
        $output .= '<p>Invalid gallery ID.</p>';
    }

    return $output;
}