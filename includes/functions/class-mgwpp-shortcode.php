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
            $gallery_html = '<p>Gallery type not recognized.</p>';

            switch ($gallery_type) {
                case 'single_carousel':
                    wp_enqueue_style('mg-single-carousel-styles');
                    wp_enqueue_script('mg-single-carousel-js');
                    if (!class_exists('MGWPP_Gallery_Single')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-single-gallery/class-mgwpp-single-gallery.php';
                    }
                    $gallery_html = MGWPP_Gallery_Single::render($post_id, $all_images);
                    break;

                case 'multi_carousel':
                    wp_enqueue_style('mg-multi-carousel-styles');
                    wp_enqueue_script('mg-multi-carousel-js');
                    if (!class_exists('MGWPP_Gallery_Multi')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-multi-gallery/class-mgwpp-multi-gallery.php';
                    }
                    $gallery_html = MGWPP_Gallery_Multi::render($post_id, $all_images, $paged, $images_per_page);
                    break;

                case 'grid':
                    wp_enqueue_style('mg-grid-styles');
                    if (!class_exists('MGWPP_Gallery_Grid')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-grid-gallery.php';
                    }
                    $gallery_html = MGWPP_Gallery_Grid::render($post_id, $all_images);
                    break;

                case 'mega_slider':
                    wp_enqueue_style('mg-mega-carousel-styles');
                    wp_enqueue_script('mg-mega-carousel-js');
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
                    wp_enqueue_style('mgwpp-threed-carousel-styles');
                    wp_enqueue_script('mgwpp-threed-carousel-js');
                    if (!class_exists('MGWPP_3D_Carousel')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-3d-carousel.php';
                    }
                    $gallery_html = MGWPP_3D_Carousel::render($post_id, $all_images);
                    break;

                case 'full_page_slider':
                    wp_enqueue_style('mg-fullpage-slider-styles');
                    wp_enqueue_script('mg-fullpage-slider-js');
                    if (!class_exists('MGWPP_Full_Page_Slider')) {
                        require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-full-page-slider.php';
                    }
                    $gallery_html = MGWPP_Full_Page_Slider::render($post_id, $all_images);
                    break;

                case 'spotlight_carousel':
                    wp_enqueue_style('mg-spotlight-slider-styles');
                    wp_enqueue_script('mg-spotlight-slider-js');
                    if (!class_exists('MGWPP_Spotlight_Carousel')) {
                        require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-spotlight-carousel.php';
                    }
                    $gallery_html = MGWPP_Spotlight_Carousel::render($post_id, $all_images);
                    break;

                case 'testimonials_carousel':
                    wp_enqueue_style('mgwpp-testimonial-carousel-styles');
                    wp_enqueue_script('mgwpp-testimonial-carousel-js');
                    $testimonials = get_posts([
                        'post_type' => 'mgwpp_testimonial',
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
    } else {
        $output .= '<p>Invalid gallery ID.</p>';
    }

    return $output;
}
