<?php
if (!defined('ABSPATH')) exit;

get_header();

var_dump('test');

if (have_posts()) :
    while (have_posts()) : the_post();

        echo '<div class="mgwpp-album">';

        // Album title
        echo '<h1>' . esc_html(get_the_title()) . '</h1>';

        // Album content
        echo '<div class="mgwpp-album-content">';
        echo esc_html(apply_filters('the_content', get_the_content()), 'mini-gallery');
        echo '</div>';

        // Get related galleries (array of post IDs)
        $related_galleries = get_post_meta(get_the_ID(), 'mgwpp_album_galleries', true);

        if (!empty($related_galleries) && is_array($related_galleries)) {
            foreach ($related_galleries as $gallery_id) {

                echo '<div class="mgwpp-single-gallery">';

                // Gallery title
                echo '<h2>' . esc_html(get_the_title($gallery_id)) . '</h2>';

                // 🔥 Render gallery via shortcode (no paged)
                echo do_shortcode('[mgwpp_gallery id="' . intval($gallery_id) . '"]');

                // Optional: fallback image rendering if shortcode fails
                $images = get_post_meta($gallery_id, 'mgwpp_gallery_images', true);
                if (!empty($images) && is_array($images)) {
                    echo '<div class="mgwpp-gallery-images">';
                    foreach ($images as $img_id) {
                        $src = wp_get_attachment_image_src($img_id, 'large');
                        if (!empty($src)) {
                            echo '<img src="' . esc_url($src[0]) . '" alt="" />';
                        }
                    }
                    echo '</div>';
                }

                echo '</div>'; // .mgwpp-single-gallery
            }
        } else {
            echo '<p>No galleries found for this album.</p>';
        }

        echo '</div>'; // .mgwpp-album

    endwhile;
endif;

get_footer();
