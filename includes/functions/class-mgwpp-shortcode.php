<?php
if (!defined('ABSPATH')) {
    exit;
}
function mgwpp_gallery_shortcode($atts)
{
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
                    $output .= '<div class="carousel-slide">' .
                        wp_get_attachment_image($image->ID, 'medium', false, ['loading' => 'lazy']) .
                        '</div>';
                }
                $output .= '</div>';
            } elseif ($gallery_type === 'multi_carousel') {
                $output .= '<div id="mg-multi-carousel" class="mg-gallery multi-carousel" data-page="' . esc_attr($paged) . '">';

                // Slice images for current page
                $images = array_slice($all_images, $offset, $images_per_page);
                foreach ($images as $image) {
                    $output .= '<div class="mg-multi-carousel-slide">' .
                        wp_get_attachment_image($image->ID, 'medium', false, ['class' => 'mg-multi-carousel-slide', 'loading' => 'lazy']) .
                        '</div>';
                }
                $output .= '</div>';
            } elseif ($gallery_type === 'grid') {
                $output .= '<div class="grid-layout">';
                foreach ($all_images as $image) {
                    $output .= '<div class="grid-item">' .
                        wp_get_attachment_image($image->ID, 'medium', false, ['loading' => 'lazy']) .
                        '</div>';
                }
                $output .= '</div>';
            } elseif ($gallery_type === 'mega_slider') {
                $output .= MGWPP_Mega_Slider::render($post_id, $all_images);
            } elseif ($gallery_type === 'pro_carousel') {
                wp_enqueue_style('mgwpp-pro-carousel-styles');
                wp_enqueue_script('mgwpp-pro-carousel-js');

                $output .= '<div class="mg-pro-carousel">';
                $output .= '<button class="mg-pro-carousel__nav mg-pro-carousel__nav--prev">‹</button>';
                $output .= '<button class="mg-pro-carousel__nav mg-pro-carousel__nav--next">›</button>';
                $output .= '<div class="mg-pro-carousel__container">';
                $output .= '<div class="mg-pro-carousel__track">';

                foreach ($all_images as $image) {
                    $output .= sprintf(
                        '<div class="mg-pro-carousel__card">
                            <img class="mg-pro-carousel__image" src="%s" alt="%s" loading="lazy">
                            <div class="mg-pro-carousel__content">
                                <h3 class="mg-pro-carousel__title">%s</h3>
                                <p class="mg-pro-carousel__caption">%s</p>
                            </div>
                        </div>',
                        esc_url(wp_get_attachment_image_url($image->ID, 'large')),
                        esc_attr(get_post_meta($image->ID, '_wp_attachment_image_alt', true)),
                        esc_html($image->post_title),
                        esc_html(wp_trim_words($image->post_content, 15))
                    );
                }

                $output .= '</div></div>';
                // In your PHP render method
                $output .= '<div class="mg-pro-carousel__thumbs" style="display: none;">'; // Hidden but exists
                foreach ($all_images as $thumb) {
                    $output .= sprintf(
                        '<img class="mg-pro-carousel__thumb" src="%s" alt="%s">',
                        esc_url(wp_get_attachment_image_url($thumb->ID, 'thumbnail')),
                        esc_attr(get_post_meta($thumb->ID, '_wp_attachment_image_alt', true))
                    );
                }
                $output .= '</div>';

                $output .= '</div></div>';
            }
        } else {
            $output .= '<p>No images found for this gallery.</p>';
        }

        // Handle pagination if necessary for multi-carousel
        if ($gallery_type === 'multi_carousel' && count($all_images) > $images_per_page) {
            $total_pages = ceil(count($all_images) / $images_per_page);
            $output .= '<div class="mgwpp-gallery-pagination">';
            if ($paged > 1) {
                //$output .= '<a href="' . esc_url(add_query_arg(['paged' => $paged - 1], get_permalink($post_id))) . '">Previous</a>';
            }
            if ($paged < $total_pages) {
                //$output .= '<a href="' . esc_url(add_query_arg(['paged' => $paged + 1], get_permalink($post_id))) . '">Next</a>';
            }
            $output .= '</div>';
        } elseif ($gallery_type === 'neon_carousel') {
            wp_enqueue_style('mgwpp-neon-carousel-styles');
            wp_enqueue_script('mgwpp-neon-carousel-js');
            $output .= MGWPP_Neon_Carousel::render($post_id, $all_images);
        }
    } else {
        $output .= '<p>Invalid gallery ID.</p>';
    }

    return $output;
}
